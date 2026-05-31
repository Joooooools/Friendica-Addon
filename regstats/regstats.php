<?php

/**
 * Name: RegStats
 * Description: Anonymously logs and visualizes registration statistics, including spam blocks, captcha and validation failures, successful registrations, moderation actions, and mail delivery errors.
 * Version: 1.0
 * Author: Jools <https://friendica.de/profile/jools>
 * License: AGPL-3.0-or-later
 *
 * SPDX-FileCopyrightText: 2026 [Jools]
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Friendica\Core\Hook;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\BaseModule;
use Friendica\Core\Renderer;

function regstats_install()
{
	Hook::unregister('register_post', 'addon/regstats/regstats.php', 'regstats_register_post');
	Hook::register('register_post', 'addon/regstats/regstats.php', 'regstats_register_post', 100);
	Hook::register('moderation_users_tabs', 'addon/regstats/regstats.php', 'regstats_users_tabs');
	Hook::register('register_account', 'addon/regstats/regstats.php', 'regstats_register_account');
	Hook::register('Friendica\Module\Register_mod_content', 'addon/regstats/regstats.php', 'regstats_register_mod_content');
	Hook::register('moderation_mod_init', 'addon/regstats/regstats.php', 'regstats_moderation_mod_init');
	Hook::register('moderation_mod_post', 'addon/regstats/regstats.php', 'regstats_moderation_mod_post');
	Hook::register('user_mod_post', 'addon/regstats/regstats.php', 'regstats_user_mod_post');
	Hook::register('page_end', 'addon/regstats/regstats.php', 'regstats_page_end');
}

function regstats_uninstall()
{
	Hook::unregister('register_post', 'addon/regstats/regstats.php', 'regstats_register_post');
	Hook::unregister('moderation_users_tabs', 'addon/regstats/regstats.php', 'regstats_users_tabs');
	Hook::unregister('register_account', 'addon/regstats/regstats.php', 'regstats_register_account');
	Hook::unregister('Friendica\Module\Register_mod_content', 'addon/regstats/regstats.php', 'regstats_register_mod_content');
	Hook::unregister('moderation_mod_init', 'addon/regstats/regstats.php', 'regstats_moderation_mod_init');
	Hook::unregister('moderation_mod_post', 'addon/regstats/regstats.php', 'regstats_moderation_mod_post');
	Hook::unregister('user_mod_post', 'addon/regstats/regstats.php', 'regstats_user_mod_post');
	Hook::unregister('page_end', 'addon/regstats/regstats.php', 'regstats_page_end');
}

/**
 * Hook: register_post - Analyzes registration POST parameters to detect failures
 * before Friendica redirects or aborts script execution.
 */
function regstats_register_post(array &$arr): void
{
	// Exclude logged in users (e.g. admins creating accounts)
	if (DI::userSession()->getLocalUserId()) {
		return;
	}

	try {
		// 1. Core Honeypot: The hidden 'email' field has text
		if (!empty($_POST['email'])) {
			regstats_log('honeypot_core');
			return;
		}

		// 2. Guardian Honeypot: The hidden 'special_mail_field' has text
		if (!empty($_POST['special_mail_field'])) {
			regstats_log('honeypot_guardian');
			return;
		}

		// 3. Captcha Check (regcaptcha)
		$regcaptchaState = DI::session()->get('regcaptcha_state');
		if (is_array($regcaptchaState)) {
			$captchaFailed = false;

			// Check decoy fields in math mode
			foreach ($regcaptchaState['decoy_fields'] ?? [] as $decoy) {
				if (trim((string) ($_POST[$decoy] ?? '')) !== '') {
					$captchaFailed = true;
					break;
				}
			}

			// Check math timing
			if (!$captchaFailed && isset($regcaptchaState['rendered_at'])) {
				$elapsed = time() - (int) $regcaptchaState['rendered_at'];
				if ($elapsed < 3 || $elapsed > 1800) {
					$captchaFailed = true;
				}
			}

			// Check math mathematical answer
			if (!$captchaFailed && isset($regcaptchaState['real_field'], $regcaptchaState['answer'])) {
				$given = trim((string) ($_POST[$regcaptchaState['real_field']] ?? ''));
				if ($given === '' || (int) $given !== (int) $regcaptchaState['answer']) {
					$captchaFailed = true;
				}
			}

			if ($captchaFailed) {
				regstats_mark_logged('captcha');
				regstats_log('captcha_failed');
				return;
			}
		}

		// Check hCaptcha mode of regcaptcha
		$regcaptchaMode = DI::config()->get('regcaptcha', 'mode', 'math');
		if ($regcaptchaMode === 'hcaptcha' && empty($_POST['h-captcha-response'])) {
			regstats_mark_logged('captcha');
			regstats_log('captcha_failed');
			return;
		}

		// 4. Core Validation: E-mail mismatch or invalid email format / domain
		$email  = !empty($_POST['field1']) ? trim($_POST['field1']) : '';
		$repeat = !empty($_POST['repeat']) ? trim($_POST['repeat']) : '';

		if ($email !== $repeat) {
			regstats_log_validation_failed();
			return;
		}

		if ($email !== '') {
			if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !\Friendica\Util\Network::isEmailDomainValid($email)) {
				regstats_log_validation_failed();
				return;
			}
		}

		// Nickname checks
		if (!empty($_POST['nickname'])) {
			$nickname = $_POST['nickname'];
			if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $nickname) || is_numeric(substr($nickname, 0, 1))) {
				regstats_log_validation_failed();
				return;
			}
		}

		// Password checks (if manually provided)
		if (isset($_POST['password1'], $_POST['confirm']) && $_POST['password1'] !== $_POST['confirm']) {
			regstats_log_validation_failed();
			return;
		}

		// Full name / Display name checks
		$username = !empty($_POST['username']) ? trim($_POST['username']) : '';
		if ($username !== '') {
			$username            = preg_replace('/ +/', ' ', $username);
			$username_min_length = max(1, min(64, intval(DI::config()->get('system', 'username_min_length', 3))));
			$username_max_length = max(1, min(64, intval(DI::config()->get('system', 'username_max_length', 48))));
			if (mb_strlen($username) < $username_min_length || mb_strlen($username) > $username_max_length) {
				regstats_log_validation_failed();
				return;
			}

			if (!DI::config()->get('system', 'no_regfullname') && strpos($username, ' ') === false) {
				regstats_log_validation_failed();
				return;
			}
		}

		// 5. Duplicate Check: Nickname or email already taken
		if (!empty($_POST['nickname'])) {
			$nickname = strtolower($_POST['nickname']);
			if (DBA::exists('user', ['nickname' => $nickname]) || DBA::exists('userd', ['username' => $nickname])) {
				regstats_log_duplicate_failed();
				return;
			}
		}

		if ($email !== '') {
			if (DBA::exists('user', ['email' => $email]) || (DI::config()->get('system', 'block_extended_register', false) && DBA::exists('user', ['email' => $email]))) {
				regstats_log_duplicate_failed();
				return;
			}
		}

		// 6. Blocked checks: Nickname blocked or email domain blocked
		if (!empty($_POST['nickname']) && \Friendica\Model\User::isNicknameBlocked($_POST['nickname'])) {
			regstats_log_blocked_nickname();
			return;
		}

		if ($email !== '' && !\Friendica\Util\Network::isEmailDomainAllowed($email)) {
			regstats_log_blocked_email();
			return;
		}

	} catch (\Throwable $e) {
		// Log the error defensively but do not interrupt the registration process
		DI::logger()->warning('RegStats: Error checking registration POST parameters', ['error' => $e->getMessage()]);
	}
}

/**
 * Returns the absolute path to the configured log file.
 */
function regstats_get_logfile(): string
{
	return dirname(__DIR__, 2) . '/log/regstats.log';
}

/**
 * Checks if the log directory and log files are writable.
 */
function regstats_is_writable(): bool
{
	$logfile = regstats_get_logfile();
	$logdir  = dirname($logfile);

	if (!is_dir($logdir)) {
		if (!@mkdir($logdir, 0750, true)) {
			return false;
		}
	}

	if (!is_writable($logdir)) {
		return false;
	}

	if (file_exists($logfile) && !is_writable($logfile)) {
		return false;
	}

	$lockfile = $logfile . '.lock';
	if (file_exists($lockfile) && !is_writable($lockfile)) {
		return false;
	}

	return true;
}

/**
 * Builds a single log line for the given event.
 *
 * This is a pure, deterministic helper with no I/O and no dependency on the
 * DI container: given the same inputs it always returns the same string. It is
 * the single source of truth for which fields are written to disk and in which
 * order. The timestamp is passed in (rather than read from time() internally)
 * so the output is fully testable.
 *
 * @param string   $type      The event type.
 * @param array    $extra     Additional fields merged after the base fields.
 * @param int      $timestamp Unix timestamp for the 't' field.
 * @return string             A JSON-encoded line terminated by a newline.
 */
function regstats_build_log_entry(string $type, array $extra, int $timestamp): string
{
	$data = array_merge([
		't'    => $timestamp,
		'type' => $type,
	], $extra);

	return json_encode($data) . "\n";
}

/**
 * Appends a pre-built log entry to the log file.
 *
 * If the file does not exist yet, it is created and its permissions are set
 * once to 0640 (owner read/write, group read, no access for others) as a
 * defense-in-depth hardening. The chmod is applied only on initial creation,
 * never on subsequent appends, so a permission choice made by the operator is
 * preserved and no needless syscalls are issued on every write.
 *
 * @param string $logfile Absolute path to the log file.
 * @param string $entry   The already-encoded log line to append.
 */
function regstats_append_log_entry(string $logfile, string $entry): void
{
	$isNew = !file_exists($logfile);
	@file_put_contents($logfile, $entry, FILE_APPEND | LOCK_EX);
	if ($isNew && file_exists($logfile)) {
		@chmod($logfile, 0640);
	}
}

/**
 * Logs a registration failure or event to the log file.
 */
function regstats_log(string $type, array $extra = []): void
{
	if (!regstats_is_writable()) {
		DI::logger()->warning('RegStats: Log directory/file is not writable.');
		return;
	}

	try {
		$logfile  = regstats_get_logfile();
		$lockfile = $logfile . '.lock';
		$lockFp   = @fopen($lockfile, 'w');
		if (!$lockFp) {
			// Fallback if lock file cannot be opened/created
			$entry = regstats_build_log_entry($type, $extra, time());
			regstats_append_log_entry($logfile, $entry);
			return;
		}

		if (@flock($lockFp, LOCK_EX)) {
			// Check file size and rotate under lock
			if (file_exists($logfile) && filesize($logfile) > 2 * 1024 * 1024) {
				$rotated = $logfile . '.1';
				// Explicitly remove any previous archive first so the rename
				// behaves consistently across operating systems and filesystems
				// (e.g. Windows rename() fails if the target already exists).
				if (file_exists($rotated)) {
					@unlink($rotated);
				}
				if (!@rename($logfile, $rotated)) {
					DI::logger()->warning('RegStats: Log rotation failed', ['logfile' => $logfile]);
				}
			}

			$entry = regstats_build_log_entry($type, $extra, time());
			regstats_append_log_entry($logfile, $entry);

			@flock($lockFp, LOCK_UN);
		}
		@fclose($lockFp);
	} catch (\Throwable $e) {
		DI::logger()->warning('RegStats: Failed to write to log file', ['error' => $e->getMessage()]);
	}
}

/**
 * Hook: register_account - Logs successful registration events.
 */
function regstats_register_account(int $uid): void
{
	$policy = \Friendica\Module\Register::getPolicy() === \Friendica\Module\Register::APPROVE ? 'approve' : 'open';
	$user   = DBA::selectFirst('user', ['openid'], ['uid' => $uid]);
	$openid = (DBA::isResult($user) && !empty($user['openid'])) ? true : false;
	regstats_log('register', [
		'policy' => $policy,
		'import' => false,
		'openid' => $openid,
	]);
}

/**
 * Hook: moderation_mod_init - Detects single-user moderation actions via GET links.
 */
function regstats_moderation_mod_init(string &$placeholder): void
{
	$cmd = DI::args()->getCommand();
	if (str_starts_with($cmd, 'moderation/users/pending') && DI::userSession()->isSiteAdmin()) {
		$parts  = explode('/', $cmd);
		$action = $parts[3] ?? ($_GET['action'] ?? '');
		$uid    = intval($parts[4] ?? ($_GET['uid'] ?? 0));
		if ($uid && ($action === 'allow' || $action === 'deny')) {
			$register = \Friendica\Model\Register::getPendingForUser($uid);
			if (DBA::isResult($register)) {
				if ($action === 'allow') {
					regstats_log('approved');
				} else {
					regstats_log('rejected');
				}
			}
		}
	}
}

/**
 * Hook: moderation_mod_post - Detects bulk moderation actions (Approve / Reject) via POST.
 */
function regstats_moderation_mod_post(array &$request): void
{
	$cmd = DI::args()->getCommand();
	if (str_starts_with($cmd, 'moderation/users/pending') && DI::userSession()->isSiteAdmin()) {
		$pending = $_POST['pending'] ?? [];
		if (!empty($_POST['page_users_approve']) && is_array($pending)) {
			foreach ($pending as $hash) {
				$register = \Friendica\Model\Register::getByHash($hash);
				if (DBA::isResult($register)) {
					regstats_log('approved');
				}
			}
		} elseif (!empty($_POST['page_users_deny']) && is_array($pending)) {
			foreach ($pending as $hash) {
				$register = \Friendica\Model\Register::getByHash($hash);
				if (DBA::isResult($register)) {
					regstats_log('rejected');
				}
			}
		}
	}
}

/**
 * Hook: user_mod_post - Detects Account Import via POST under 'user/import'.
 */
function regstats_user_mod_post(array &$request): void
{
	if (DI::args()->getCommand() !== 'user/import') {
		return;
	}

	$maxUidBefore = 0;
	$latestUser   = DBA::selectFirst('user', ['uid'], [], ['order' => ['uid' => true]]);
	if (DBA::isResult($latestUser)) {
		$maxUidBefore = intval($latestUser['uid']);
	}

	register_shutdown_function(function () use ($maxUidBefore) {
		$latestUser = DBA::selectFirst('user', ['uid'], [], ['order' => ['uid' => true]]);
		if (DBA::isResult($latestUser)) {
			$maxUidAfter = intval($latestUser['uid']);
			if ($maxUidAfter > $maxUidBefore) {
				$prevAddr = DI::pConfig()->get($maxUidAfter, 'system', 'previous_addr');
				if ($prevAddr) {
					$policy = \Friendica\Module\Register::getPolicy() === \Friendica\Module\Register::APPROVE ? 'approve' : 'open';
					$user   = DBA::selectFirst('user', ['openid'], ['uid' => $maxUidAfter]);
					$openid = (DBA::isResult($user) && !empty($user['openid'])) ? true : false;
					regstats_log('register', [
						'policy' => $policy,
						'import' => true,
						'openid' => $openid,
					]);
				}
			}
		}
	});
}

/**
 * Deduplication window in seconds. Within this window, a failure of a given
 * category is only counted once, regardless of whether it is detected via the
 * registration POST hook or via a later system notice scan.
 */
const REGSTATS_DEDUPE_WINDOW = 5;

/**
 * Marks a failure category as just logged by storing the current timestamp.
 *
 * This intentionally stores ONLY a short-lived timestamp marker per category.
 * It never stores hashes of system notices, because those notices can contain
 * sensitive data (e.g. the mail-failure notice contains login and password).
 */
function regstats_mark_logged(string $category): void
{
	if (!DI::session()) {
		return;
	}
	DI::session()->set('regstats_last_logged_' . $category, time());
}

/**
 * Returns true if a failure of the given category was logged within the
 * deduplication window. Used to avoid double-counting the same failure from
 * both the POST hook and the notice scan.
 */
function regstats_recently_logged(string $category): bool
{
	if (!DI::session()) {
		return false;
	}
	$last = DI::session()->get('regstats_last_logged_' . $category);
	return is_int($last) && (time() - $last) < REGSTATS_DEDUPE_WINDOW;
}

/**
 * Helper to match a notice string against an English string or its translation.
 */
function regstats_match_notice(string $notice, string $englishString): bool
{
	if (str_contains($notice, $englishString)) {
		return true;
	}
	$translated = DI::l10n()->t($englishString);
	if (str_contains($notice, $translated)) {
		return true;
	}
	return false;
}

/**
 * Helper to match the email send failure notice across different languages.
 */
function regstats_match_email_notice(string $notice): bool
{
	$english = 'Failed to send email message. Here your accout details:<br> login: %s<br> password: %s<br><br>You can change your password after login.';
	if (str_contains($notice, 'Failed to send email message')) {
		return true;
	}
	$translated = DI::l10n()->t($english);
	$parts      = explode('%s', $translated);
	if (!empty($parts[0]) && str_contains($notice, trim($parts[0]))) {
		return true;
	}
	return false;
}

/**
 * Helper to scan system notices for failures during registration.
 * This is only called when rendering the registration page content.
 */
function regstats_check_notices(): void
{
	if (!DI::session() || !DI::sysmsg()) {
		return;
	}

	$notices = DI::sysmsg()->getNotices();
	if (is_array($notices)) {
		foreach ($notices as $notice) {
			// Captcha failures
			if (regstats_match_notice($notice, 'Registration Captcha: Incorrect answer. Please try again.')) {
				if (!regstats_recently_logged('captcha')) {
					regstats_mark_logged('captcha');
					regstats_log('captcha_failed');
				}
			}
			// Duplicate Nickname or Email failures
			if (
				regstats_match_notice($notice, 'Nickname is already registered. Please choose another.')
				|| regstats_match_notice($notice, 'Cannot use that email.')
			) {
				if (!regstats_recently_logged('duplicate')) {
					regstats_mark_logged('duplicate');
					regstats_log('duplicate_failed');
				}
			}
			// Validation failures
			if (
				regstats_match_notice($notice, 'Your nickname can only contain a-z, 0-9 and _.')
				|| regstats_match_notice($notice, 'Not a valid email address.')
				|| regstats_match_notice($notice, 'Password doesn\'t match.')
				|| regstats_match_notice($notice, "Password doesn't match.")
				|| regstats_match_notice($notice, 'Please enter your password.')
				|| regstats_match_notice($notice, 'Please enter the identical mail address in the second field.')
				|| regstats_match_notice($notice, 'Nickname cannot start with a digit.')
				|| regstats_match_notice($notice, 'Nickname can only contain US-ASCII characters.')
				|| regstats_match_notice($notice, "That doesn't appear to be your full (First Last) name.")
			) {
				if (!regstats_recently_logged('validation')) {
					regstats_mark_logged('validation');
					regstats_log('validation_failed');
				}
			}
			// Blocked Nickname failures
			if (regstats_match_notice($notice, 'The nickname was blocked from registration by the nodes admin.')) {
				if (!regstats_recently_logged('blocked_nick')) {
					regstats_mark_logged('blocked_nick');
					regstats_log('blocked_nickname');
				}
			}
			// Blocked Email failures
			if (regstats_match_notice($notice, 'Your email domain is not among those allowed on this site.')) {
				if (!regstats_recently_logged('blocked_email')) {
					regstats_mark_logged('blocked_email');
					regstats_log('blocked_email');
				}
			}
			// Mail Send failures (checked before notices are flushed by the register module)
			if (regstats_match_email_notice($notice)) {
				if (!regstats_recently_logged('mail_failed')) {
					regstats_mark_logged('mail_failed');
					regstats_log('mail_failed');
				}
			}
		}
	}
}

/**
 * Hook: page_end - Detects welcome email failures on register and moderation pages.
 */
function regstats_page_end(string &$content): void
{
	$module = DI::args()->getModuleName();
	if (!in_array($module, ['register', 'moderation'])) {
		return;
	}

	if (!DI::session() || !DI::sysmsg()) {
		return;
	}

	// 1. Try checking active notices (in case they weren't flushed yet)
	$notices = DI::sysmsg()->getNotices();
	if (is_array($notices)) {
		foreach ($notices as $notice) {
			if (regstats_match_email_notice($notice)) {
				// IMPORTANT: never hash or store the notice itself - the mail
				// failure notice can contain the user's login and password.
				// A short-lived per-category timestamp marker is sufficient
				// to prevent double-counting.
				if (!regstats_recently_logged('mail_failed')) {
					regstats_mark_logged('mail_failed');
					regstats_log('mail_failed');
					return;
				}
			}
		}
	}

	// 2. Also search in the fully rendered HTML content (since notices are flushed/cleared during template rendering)
	$english         = 'Failed to send email message';
	$translated      = DI::l10n()->t('Failed to send email message. Here your accout details:<br> login: %s<br> password: %s<br><br>You can change your password after login.');
	$parts           = explode('%s', $translated);
	$translated_part = !empty($parts[0]) ? trim(strip_tags($parts[0])) : '';

	if (str_contains($content, $english) ||
		(!empty($translated_part) && str_contains($content, $translated_part))
	) {
		if (!regstats_recently_logged('mail_failed')) {
			regstats_mark_logged('mail_failed');
			regstats_log('mail_failed');
		}
	}
}

/**
 * Helper to log validation failures from POST and prevent notice double logging.
 */
function regstats_log_validation_failed(): void
{
	regstats_mark_logged('validation');
	regstats_log('validation_failed');
}

/**
 * Helper to log duplicate failures from POST and prevent notice double logging.
 */
function regstats_log_duplicate_failed(): void
{
	regstats_mark_logged('duplicate');
	regstats_log('duplicate_failed');
}

/**
 * Helper to log blocked nickname failures from POST and prevent notice double logging.
 */
function regstats_log_blocked_nickname(): void
{
	regstats_mark_logged('blocked_nick');
	regstats_log('blocked_nickname');
}

/**
 * Helper to log blocked email failures from POST and prevent notice double logging.
 */
function regstats_log_blocked_email(): void
{
	regstats_mark_logged('blocked_email');
	regstats_log('blocked_email');
}

/**
 * Hook: Friendica\Module\Register_mod_content - Captures failures during open registration.
 */
function regstats_register_mod_content(array &$arr): void
{
	regstats_check_notices();
}

/**
 * Hook: moderation_users_tabs - Adds a tab to the user moderation section
 */
function regstats_users_tabs(array &$arr): void
{
	if (!DI::userSession()->isSiteAdmin()) {
		return;
	}

	if (!isset($arr['tabs'])) {
		$arr['tabs'] = [];
	}

	$arr['tabs'][] = [
		'label' => DI::l10n()->t('Registration Stats'),
		'url'   => DI::baseUrl() . '/regstats',
		'sel'   => (($arr['selectedTab'] ?? '') === 'regstats' || DI::args()->getCommand() === 'regstats' ? 'active' : ''),
		'title' => DI::l10n()->t('Registration statistics and blocks'),
		'id'    => 'admin-users-regstats',
	];
}

/**
 * Marker function to declare a custom module under /regstats
 */
function regstats_module(): void
{
}

/**
 * Empties the active log file and removes the rotated archive.
 *
 * Kept as a single helper so the clearing logic exists in exactly one place
 * and is used identically by both the locked and the fallback code path.
 * The active file is truncated (not deleted) so its permissions are preserved.
 *
 * @param string $logfile     Absolute path to the active log file.
 * @param string $rotatedFile Absolute path to the rotated archive file.
 */
function regstats_clear_files(string $logfile, string $rotatedFile): void
{
	if (file_exists($logfile)) {
		@file_put_contents($logfile, '');
	}
	if (file_exists($rotatedFile)) {
		@unlink($rotatedFile);
	}
}

/**
 * Module POST method for action handling (e.g. clearing logs)
 */
function regstats_post(): void
{
	if (!DI::userSession()->getLocalUserId()) {
		DI::sysmsg()->addNotice(DI::l10n()->t('Please login to continue.'));
		DI::session()->set('return_path', DI::args()->getQueryString());
		DI::baseUrl()->redirect('login');
	}

	if (!DI::userSession()->isSiteAdmin()) {
		return;
	}

	BaseModule::checkFormSecurityTokenRedirectOnError('/regstats', 'regstats');

	if (!empty($_POST['clear_log'])) {
		$logfile     = regstats_get_logfile();
		$rotatedFile = $logfile . '.1';
		$lockfile    = $logfile . '.lock';

		$lockFp = @fopen($lockfile, 'w');
		if ($lockFp) {
			if (@flock($lockFp, LOCK_EX)) {
				regstats_clear_files($logfile, $rotatedFile);
				@flock($lockFp, LOCK_UN);
			}
			@fclose($lockFp);
		} else {
			regstats_clear_files($logfile, $rotatedFile);
		}
		DI::sysmsg()->addInfo(DI::l10n()->t('Statistics cleared successfully.'));
		DI::baseUrl()->redirect('regstats');
	}
}

/**
 * Module GET method to render the admin dashboard content
 */
function regstats_content(): string
{
	if (!DI::userSession()->getLocalUserId()) {
		DI::sysmsg()->addNotice(DI::l10n()->t('Please login to continue.'));
		DI::session()->set('return_path', DI::args()->getQueryString());
		DI::baseUrl()->redirect('login');
	}

	if (!DI::userSession()->isSiteAdmin()) {
		DI::sysmsg()->addNotice(DI::l10n()->t('You don\'t have access to administration pages.'));
		DI::baseUrl()->redirect('');
	}

	// Register stylesheet
	DI::page()->registerStylesheet(DI::baseUrl() . '/addon/regstats/css/regstats.css');

	// Generate Moderation Tabs (consistent with core / RealMember)
	$all_count     = DBA::count('user', ["`uid` != ?", 0]);
	$active_count  = DBA::count('user', ["`verified` AND NOT `blocked` AND NOT `account_removed` AND NOT `account_expired` AND `uid` != ?", 0]);
	$pending_count = \Friendica\Model\Register::getPendingCount();
	$blocked_count = DBA::count('user', ['blocked' => true, 'verified' => true, 'account_removed' => false]);
	$deleted_count = DBA::count('user', ['account_removed' => true]);

	$tabs = [
		[
			'label' => DI::l10n()->t('All') . ' (' . $all_count . ')',
			'url'   => DI::baseUrl() . '/moderation/users',
			'sel'   => '',
			'title' => DI::l10n()->t('List of all users'),
		],
		[
			'label' => DI::l10n()->t('Active') . ' (' . $active_count . ')',
			'url'   => DI::baseUrl() . '/moderation/users/active',
			'sel'   => '',
			'title' => DI::l10n()->t('List of active accounts'),
		],
		[
			'label' => DI::l10n()->t('Pending') . ($pending_count ? ' (' . $pending_count . ')' : ''),
			'url'   => DI::baseUrl() . '/moderation/users/pending',
			'sel'   => '',
			'title' => DI::l10n()->t('List of pending registrations'),
		],
		[
			'label' => DI::l10n()->t('Blocked') . ($blocked_count ? ' (' . $blocked_count . ')' : ''),
			'url'   => DI::baseUrl() . '/moderation/users/blocked',
			'sel'   => '',
			'title' => DI::l10n()->t('List of blocked users'),
		],
		[
			'label' => DI::l10n()->t('Deleted') . ($deleted_count ? ' (' . $deleted_count . ')' : ''),
			'url'   => DI::baseUrl() . '/moderation/users/deleted',
			'sel'   => '',
			'title' => DI::l10n()->t('List of pending user deletions'),
		],
	];

	// Fire hook so other addons (including RegStats itself via the hook) appear in the list
	$hook_data = ['tabs' => $tabs, 'selectedTab' => 'regstats'];
	Hook::callAll('moderation_users_tabs', $hook_data);
	$tabs = $hook_data['tabs'];

	$tab_tpl   = Renderer::getMarkupTemplate('common_tabs.tpl');
	$tabs_html = Renderer::replaceMacros($tab_tpl, ['$tabs' => $tabs, '$more' => DI::l10n()->t('More')]);

	// Build the moderation sidebar (same pattern as RealMember / BaseModeration)
	$aside_sub = [
		'information' => [
			DI::l10n()->t('Information'),
			[
				'overview' => [DI::baseUrl() . '/moderation', DI::l10n()->t('Overview'), 'overview'],
				'reports'  => [DI::baseUrl() . '/moderation/reports', DI::l10n()->t('Reports'), 'overview'],
			],
		],
		'configuration' => [
			DI::l10n()->t('Configuration'),
			[
				'users' => [DI::baseUrl() . '/moderation/users', DI::l10n()->t('Users'), 'users'],
			],
		],
		'tools' => [
			DI::l10n()->t('Tools'),
			[
				'contactblock' => [DI::baseUrl() . '/moderation/blocklist/contact', DI::l10n()->t('Contact Blocklist'), 'contactblock'],
				'blocklist'    => [DI::baseUrl() . '/moderation/blocklist/server', DI::l10n()->t('Server Blocklist'), 'blocklist'],
				'deleteitem'   => [DI::baseUrl() . '/moderation/item/delete', DI::l10n()->t('Delete Item'), 'deleteitem'],
			],
		],
		'diagnostics' => [
			DI::l10n()->t('Diagnostics'),
			[
				'itemsource' => [DI::baseUrl() . '/moderation/item/source', DI::l10n()->t('Item Source'), 'itemsource'],
			],
		],
	];


	$aside_tpl = Renderer::getMarkupTemplate('moderation/aside.tpl');
	DI::page()['aside'] .= Renderer::replaceMacros($aside_tpl, [
		'$subpages'  => $aside_sub,
		'$admtxt'    => DI::l10n()->t('Moderation'),
		'$h_pending' => DI::l10n()->t('User registrations waiting for confirmation'),
		'$modurl'    => 'moderation/',
	]);

	// Parse log files (active log and rotated log)
	$logfile = regstats_get_logfile();
	$lines   = [];

	foreach ([$logfile . '.1', $logfile] as $file) {
		if (file_exists($file)) {
			$fileLines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			if (is_array($fileLines)) {
				$lines = array_merge($lines, $fileLines);
			}
		}
	}

	// Keep only the most recent 20,000 lines
	if (count($lines) > 20000) {
		$lines = array_slice($lines, -20000);
	}

	$events = [];
	foreach ($lines as $line) {
		$data = json_decode($line, true);
		if (is_array($data) && isset($data['t'], $data['type'])) {
			$events[] = $data;
		}
	}

	// Initialize stats totals
	$totalHoneypotCore     = 0;
	$totalHoneypotGuardian = 0;
	$totalCaptcha          = 0;
	$totalValidation       = 0;
	$totalDuplicate        = 0;
	$totalBlockedNickname  = 0;
	$totalBlockedEmail     = 0;
	$totalRegistrations    = 0;
	$totalApproved         = 0;
	$totalRejected         = 0;
	$totalImported         = 0;
	$totalOpenID           = 0;
	$totalOpen             = 0;
	$totalNeedApproval     = 0;
	$totalMailFailed       = 0;

	// Initialize daily breakdown (last 14 days)
	$days = [];
	for ($i = 13; $i >= 0; $i--) {
		$time           = strtotime("-$i days");
		$dateStr        = date('Y-m-d', $time);
		$dayLabel       = date('d.m.', $time);
		$days[$dateStr] = [
			'label'       => $dayLabel,
			'short_label' => $dayLabel,
			'count'       => 0,
			'types'       => [
				'honeypot_core'     => 0,
				'honeypot_guardian' => 0,
				'captcha_failed'    => 0,
				'validation_failed' => 0,
				'duplicate_failed'  => 0,
				'blocked_nickname'  => 0,
				'blocked_email'     => 0,
				'register'          => 0,
				'reg_open'          => 0,
				'reg_need_approval' => 0,
				'imported'          => 0,
				'openid'            => 0,
				'approved'          => 0,
				'rejected'          => 0,
				'mail_failed'       => 0,
			],
		];
	}

	// Initialize hourly breakdown
	$hours = [];
	for ($h = 0; $h < 24; $h++) {
		$hStr         = sprintf('%02d', $h);
		$hours[$hStr] = [
			'label'       => $hStr . ':00',
			'short_label' => $hStr,
			'count'       => 0,
			'types'       => [
				'honeypot_core'     => 0,
				'honeypot_guardian' => 0,
				'captcha_failed'    => 0,
				'validation_failed' => 0,
				'duplicate_failed'  => 0,
				'blocked_nickname'  => 0,
				'blocked_email'     => 0,
				'register'          => 0,
				'reg_open'          => 0,
				'reg_need_approval' => 0,
				'imported'          => 0,
				'openid'            => 0,
				'approved'          => 0,
				'rejected'          => 0,
				'mail_failed'       => 0,
			],
		];
	}

	$maxDailyCount  = 0;
	$maxHourlyCount = 0;

	foreach ($events as $e) {
		$t    = $e['t'];
		$type = $e['type'];

		// Increment global totals
		switch ($type) {
			case 'honeypot_core':
				$totalHoneypotCore++;
				break;
			case 'honeypot_guardian':
				$totalHoneypotGuardian++;
				break;
			case 'captcha_failed':
				$totalCaptcha++;
				break;
			case 'validation_failed':
				$totalValidation++;
				break;
			case 'duplicate_failed':
				$totalDuplicate++;
				break;
			case 'blocked_nickname':
				$totalBlockedNickname++;
				break;
			case 'blocked_email':
				$totalBlockedEmail++;
				break;
			case 'register':
				$totalRegistrations++;
				if (!empty($e['import'])) {
					$totalImported++;
				}
				if (!empty($e['openid'])) {
					$totalOpenID++;
				}
				if (($e['policy'] ?? '') === 'open') {
					$totalOpen++;
				} elseif (($e['policy'] ?? '') === 'approve') {
					$totalNeedApproval++;
				}
				break;
			case 'approved':
				$totalApproved++;
				break;
			case 'rejected':
				$totalRejected++;
				break;
			case 'mail_failed':
				$totalMailFailed++;
				break;
		}

		// Daily grouping
		$dateStr = date('Y-m-d', $t);
		if (isset($days[$dateStr])) {
			if (in_array($type, ['honeypot_core', 'honeypot_guardian', 'captcha_failed', 'validation_failed', 'duplicate_failed', 'blocked_nickname', 'blocked_email'])) {
				$days[$dateStr]['count']++;
				if ($days[$dateStr]['count'] > $maxDailyCount) {
					$maxDailyCount = $days[$dateStr]['count'];
				}
			}
			if (isset($days[$dateStr]['types'][$type])) {
				$days[$dateStr]['types'][$type]++;
			}
			if ($type === 'register') {
				if (!empty($e['import'])) {
					$days[$dateStr]['types']['imported']++;
				}
				if (!empty($e['openid'])) {
					$days[$dateStr]['types']['openid']++;
				}
				if (($e['policy'] ?? '') === 'open') {
					$days[$dateStr]['types']['reg_open']++;
				} elseif (($e['policy'] ?? '') === 'approve') {
					$days[$dateStr]['types']['reg_need_approval']++;
				}
			}
		}

		// Hourly grouping
		$hourStr = date('H', $t);
		if (isset($hours[$hourStr])) {
			if (in_array($type, ['honeypot_core', 'honeypot_guardian', 'captcha_failed', 'validation_failed', 'duplicate_failed', 'blocked_nickname', 'blocked_email'])) {
				$hours[$hourStr]['count']++;
				if ($hours[$hourStr]['count'] > $maxHourlyCount) {
					$maxHourlyCount = $hours[$hourStr]['count'];
				}
			}
			if (isset($hours[$hourStr]['types'][$type])) {
				$hours[$hourStr]['types'][$type]++;
			}
			if ($type === 'register') {
				if (!empty($e['import'])) {
					$hours[$hourStr]['types']['imported']++;
				}
				if (!empty($e['openid'])) {
					$hours[$hourStr]['types']['openid']++;
				}
				if (($e['policy'] ?? '') === 'open') {
					$hours[$hourStr]['types']['reg_open']++;
				} elseif (($e['policy'] ?? '') === 'approve') {
					$hours[$hourStr]['types']['reg_need_approval']++;
				}
			}
		}
	}

	// Calculate percentages and tooltips for days
	foreach ($days as $d => &$dayData) {
		$dayData['percent'] = $maxDailyCount > 0 ? round(($dayData['count'] / $maxDailyCount) * 100) : 0;
		$tooltipParts       = [
			$dayData['label'] . ": " . sprintf(DI::l10n()->t('%d failures'), $dayData['count']),
			"------------------",
			DI::l10n()->t('Core Honeypot: %d', $dayData['types']['honeypot_core']),
			DI::l10n()->t('Guardian Honeypot: %d', $dayData['types']['honeypot_guardian']),
			DI::l10n()->t('Captcha failed: %d', $dayData['types']['captcha_failed']),
			DI::l10n()->t('Validation failed: %d', $dayData['types']['validation_failed']),
			DI::l10n()->t('Duplicate entries: %d', $dayData['types']['duplicate_failed']),
			DI::l10n()->t('Blocked nicknames: %d', $dayData['types']['blocked_nickname']),
			DI::l10n()->t('Blocked emails: %d', $dayData['types']['blocked_email']),
			"------------------",
			DI::l10n()->t('Successful Registrations: %d', $dayData['types']['register']),
			"  ↳ " . DI::l10n()->t('Open Registrations: %d', $dayData['types']['reg_open']),
			"  ↳ " . DI::l10n()->t('With Admin Approval: %d', $dayData['types']['reg_need_approval']),
			"  ↳ " . DI::l10n()->t('Imported Accounts: %d', $dayData['types']['imported']),
			"  ↳ " . DI::l10n()->t('OpenID Registrations: %d', $dayData['types']['openid']),
			DI::l10n()->t('Approved by Admin: %d', $dayData['types']['approved']),
			DI::l10n()->t('Rejected by Admin: %d', $dayData['types']['rejected']),
			DI::l10n()->t('Mail Delivery Failed: %d', $dayData['types']['mail_failed']),
		];
		$dayData['tooltip'] = implode("\n", $tooltipParts);
	}
	unset($dayData);

	// Calculate percentages and tooltips for hours
	foreach ($hours as $hStr => &$hourData) {
		$hourData['percent'] = $maxHourlyCount > 0 ? round(($hourData['count'] / $maxHourlyCount) * 100) : 0;
		$tooltipParts        = [
			$hourData['label'] . ": " . sprintf(DI::l10n()->t('%d failures'), $hourData['count']),
			"------------------",
			DI::l10n()->t('Core Honeypot: %d', $hourData['types']['honeypot_core']),
			DI::l10n()->t('Guardian Honeypot: %d', $hourData['types']['honeypot_guardian']),
			DI::l10n()->t('Captcha failed: %d', $hourData['types']['captcha_failed']),
			DI::l10n()->t('Validation failed: %d', $hourData['types']['validation_failed']),
			DI::l10n()->t('Duplicate entries: %d', $hourData['types']['duplicate_failed']),
			DI::l10n()->t('Blocked nicknames: %d', $hourData['types']['blocked_nickname']),
			DI::l10n()->t('Blocked emails: %d', $hourData['types']['blocked_email']),
			"------------------",
			DI::l10n()->t('Successful Registrations: %d', $hourData['types']['register']),
			"  ↳ " . DI::l10n()->t('Open Registrations: %d', $hourData['types']['reg_open']),
			"  ↳ " . DI::l10n()->t('With Admin Approval: %d', $hourData['types']['reg_need_approval']),
			"  ↳ " . DI::l10n()->t('Imported Accounts: %d', $hourData['types']['imported']),
			"  ↳ " . DI::l10n()->t('OpenID Registrations: %d', $hourData['types']['openid']),
			DI::l10n()->t('Approved by Admin: %d', $hourData['types']['approved']),
			DI::l10n()->t('Rejected by Admin: %d', $hourData['types']['rejected']),
			DI::l10n()->t('Mail Delivery Failed: %d', $hourData['types']['mail_failed']),
		];
		$hourData['tooltip'] = implode("\n", $tooltipParts);
	}
	unset($hourData);

	// Check active status of other registration addons
	$guardianActive = DI::addonHelper()->isAddonEnabled('guardian');
	$captchaActive  = DI::addonHelper()->isAddonEnabled('regcaptcha');

	// Retrieve notices
	$notices = DI::sysmsg()->flushNotices();
	if (!regstats_is_writable()) {
		$notices[] = DI::l10n()->t('Warning: The log directory is not writable. Registration statistics cannot be recorded.');
	}

	// Live count of registrations currently awaiting admin approval
	$pendingCount = \Friendica\Model\Register::getPendingCount();

	$statsSince = '';
	if (!empty($events)) {
		$statsSince = date('Y-m-d H:i', $events[0]['t']);
	}

	$t = Renderer::getMarkupTemplate('dashboard.tpl', 'addon/regstats');
	return $tabs_html . Renderer::replaceMacros($t, [
		'$title'                   => DI::l10n()->t('Registration Statistics'),
		'$stats_since'             => $statsSince,
		'$lbl_stats_since'         => DI::l10n()->t('Statistics logging since:'),
		'$lbl_rotation_notice'     => DI::l10n()->t('To protect disk space, older logs are automatically rotated.'),
		'$notices'                 => $notices,
		'$total_honeypot_core'     => $totalHoneypotCore,
		'$total_honeypot_guardian' => $totalHoneypotGuardian,
		'$total_captcha'           => $totalCaptcha,
		'$total_validation'        => $totalValidation,
		'$total_duplicate'         => $totalDuplicate,
		'$total_blocked_nickname'  => $totalBlockedNickname,
		'$total_blocked_email'     => $totalBlockedEmail,
		'$total_registrations'     => $totalRegistrations,
		'$total_open'              => $totalOpen,
		'$total_need_approval'     => $totalNeedApproval,
		'$total_approved'          => $totalApproved,
		'$total_rejected'          => $totalRejected,
		'$total_imported'          => $totalImported,
		'$total_openid'            => $totalOpenID,
		'$total_mail_failed'       => $totalMailFailed,
		'$days'                    => array_values($days),
		'$hours'                   => array_values($hours),
		'$form_security_token'     => BaseModule::getFormSecurityToken('regstats'),
		'$guardian_active'         => $guardianActive,
		'$captcha_active'          => $captchaActive,

		// Translated labels
		'$lbl_headline'          => DI::l10n()->t('Registration Failure Audit'),
		'$lbl_summary'           => DI::l10n()->t('Summary (All Time)'),
		'$lbl_core_hp'           => DI::l10n()->t('Core Honeypot'),
		'$lbl_guardian_hp'       => DI::l10n()->t('Guardian Addon (optional)'),
		'$lbl_captcha_failed'    => DI::l10n()->t('Reg-Captcha Addon (optional)'),
		'$lbl_validation_failed' => DI::l10n()->t('Validation Failed'),
		'$lbl_duplicate_failed'  => DI::l10n()->t('Duplicate Entry'),
		'$lbl_blocked_nickname'  => DI::l10n()->t('Blocked Nickname'),
		'$lbl_blocked_email'     => DI::l10n()->t('Blocked Email'),
		'$lbl_daily_chart'       => DI::l10n()->t('Daily Distribution (Last 14 Days)'),
		'$lbl_daily_desc'        => DI::l10n()->t('The bar height visualizes registration failures and blocked spam attempts. Hover over a bar or click on it to view all details for that day (including successful registrations).'),
		'$lbl_hourly_chart'      => DI::l10n()->t('Hourly Distribution'),
		'$lbl_hourly_desc'       => DI::l10n()->t('The bar height visualizes registration failures and blocked spam attempts. Hover over a bar or click on it to view all details for that hour (including successful registrations).'),
		'$lbl_details_for'       => DI::l10n()->t('Details for: %s'),
		'$lbl_actions'           => DI::l10n()->t('Actions'),
		'$lbl_clear_stats'       => DI::l10n()->t('Clear Statistics'),
		'$lbl_clear_confirm'     => DI::l10n()->t('Are you sure you want to delete all statistical logs?'),
		'$lbl_no_data'           => DI::l10n()->t('No statistics logged yet.'),
		'$lbl_inactive'          => DI::l10n()->t('inactive'),

		// Metric descriptions for beginners
		'$desc_core_hp'          => DI::l10n()->t('A hidden field in the registration form. Real users do not see it, but spambots automatically fill it out and get blocked.'),
		'$desc_guardian_hp'      => DI::l10n()->t("An additional hidden field generated by the 'Guardian' addon to intercept advanced spambots."),
		'$desc_captcha'          => DI::l10n()->t('Registration attempts blocked because the spam protection captcha was solved incorrectly or timed out.'),
		'$desc_validation'       => DI::l10n()->t('Registration attempts rejected due to validation issues (e.g. invalid username format, mismatching email addresses).'),
		'$desc_duplicate'        => DI::l10n()->t('Registration attempts rejected because the username or email address is already registered on this server.'),
		'$desc_blocked_nickname' => DI::l10n()->t('Registration attempts blocked because the nickname is on the blocklist or reserved.'),
		'$desc_blocked_email'    => DI::l10n()->t('Registration attempts blocked because the email address or domain is disallowed.'),

		// New labels and descriptions for successful registrations/moderation
		'$lbl_reg_headline'       => DI::l10n()->t('Registrations & Moderation'),
		'$lbl_reg_registrations'  => DI::l10n()->t('Total Registrations'),
		'$lbl_reg_open'           => DI::l10n()->t('Open Registrations'),
		'$lbl_reg_need_approval'  => DI::l10n()->t('With Admin Approval'),
		'$lbl_reg_pending'        => DI::l10n()->t('Awaiting Approval'),
		'$lbl_reg_approved'       => DI::l10n()->t('Approved'),
		'$lbl_reg_rejected'       => DI::l10n()->t('Rejected'),
		'$lbl_reg_imported'       => DI::l10n()->t('Imported Accounts'),
		'$lbl_reg_openid'         => DI::l10n()->t('OpenID Registrations'),
		'$desc_reg_registrations' => DI::l10n()->t('Total successful registrations on this node (all time).'),
		'$desc_reg_open'          => DI::l10n()->t('Registrations that completed immediately because the node had open registration enabled.'),
		'$desc_reg_need_approval' => DI::l10n()->t('Registrations submitted while the node required administrator approval before activation.'),
		'$desc_reg_pending'       => DI::l10n()->t('Registrations currently waiting for administrator approval. This is a live count from the database.'),
		'$desc_reg_approved'      => DI::l10n()->t('Pending registrations that were approved by an administrator.'),
		'$desc_reg_rejected'      => DI::l10n()->t('Pending registrations that were denied/rejected by an administrator.'),
		'$desc_reg_imported'      => DI::l10n()->t('Registrations that were account migrations from other Fediverse instances.'),
		'$desc_reg_openid'        => DI::l10n()->t('Registrations that used an OpenID identity for authentication.'),
		'$lbl_reg_mail_failed'    => DI::l10n()->t('Mail Send Failed'),
		'$desc_reg_mail_failed'   => DI::l10n()->t('The welcome or password email could not be sent to the user after successful registration.'),
		'$pending_count'          => $pendingCount,
	]);
}
