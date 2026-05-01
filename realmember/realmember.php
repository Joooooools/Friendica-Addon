<?php
/**
 * Name: RealMember
 * Description: Advanced read-only spam detection for site administrators.
 * Version: 1.1
 * Author: Jools <https://friendica.de/profile/jools>
 * License: AGPL-3.0-or-later
 * 
 * SPDX-FileCopyrightText: 2026 [Jools]
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\Core\System;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Content\Pager;

/**
 * Register RealMember module and enforce security.
 */
function realmember_module()
{
	if (!DI::userSession()->getLocalUserId()) {
		System::externalRedirect(DI::baseUrl() . '/login');
	}
	if (!DI::userSession()->isSiteAdmin()) {
		System::externalRedirect(DI::baseUrl() . '/network');
	}
}

/**
 * Install and register hooks.
 */
function realmember_install()
{
	Hook::register('moderation_mod_init', __FILE__, 'realmember_moderation_mod_init');
	Hook::register('moderation_users_tabs', __FILE__, 'realmember_users_tabs');
}

/**
 * Uninstall and unregister hooks.
 */
function realmember_uninstall()
{
	Hook::unregister('moderation_mod_init', __FILE__, 'realmember_moderation_mod_init');
	Hook::unregister('moderation_users_tabs', __FILE__, 'realmember_users_tabs');
}

/**
 * Inject RealMember link into moderation sidebar.
 */
function realmember_moderation_mod_init()
{
	if (!DI::userSession()->isSiteAdmin()) {
		return;
	}

	$t = Renderer::getMarkupTemplate('realmember_menu.tpl', 'addon/realmember/');
	DI::page()['aside'] .= Renderer::replaceMacros($t, [
		'$url' => DI::baseUrl() . '/realmember',
		'$header' => 'RealMember',
		'$label' => DI::l10n()->t('Spam-Analyse'),
	]);
}

/**
 * Add RealMember tab to moderation users sub-navigation.
 */
function realmember_users_tabs(array &$arr)
{
	$arr['tabs'][] = [
		'label' => 'RealMember',
		'url' => 'realmember',
		'sel' => (($arr['selectedTab'] ?? '') == 'realmember' ? 'active' : ''),
		'title' => DI::l10n()->t('Spam-Analyse Dashboard'),
		'id' => 'admin-users-realmember',
		'accesskey' => 's',
	];
}

/**
 * Filter Criteria Data
 */
function realmember_get_criteria()
{
	$disallowed_email = DI::config()->get('system', 'disallowed_email');
	$manual_list = !empty($disallowed_email) ? explode(',', $disallowed_email) : [];
	$manual_list = array_map('trim', $manual_list);

	$disc_domains_path = __DIR__ . '/data/disposable_domains.php';
	$disc_data = file_exists($disc_domains_path) ? include $disc_domains_path : [];
	$disc_count = is_array($disc_data) ? count($disc_data) : 0;

	$keywords_path = __DIR__ . '/data/spam_keywords.php';
	$keywords = file_exists($keywords_path) ? include $keywords_path : [];
	if (!is_array($keywords)) {
		$keywords = [];
	}

	// Suspicious TLDs commonly abused for spam.
	// Each entry MUST start with '.' so str_ends_with() anchors on a real DNS
	// label boundary (otherwise '.co' would falsely match 'something.co.uk').
	$bad_tlds = [
		'.accountant', '.beauty', '.best', '.bid', '.buzz', '.cf', '.click',
		'.date', '.faith', '.fit', '.fun', '.ga', '.gq', '.icu', '.live',
		'.loan', '.ml', '.monster', '.mov', '.ninja', '.online', '.pw',
		'.quest', '.racing', '.rest', '.review', '.shop', '.site', '.space',
		'.stream', '.surf', '.tk', '.top', '.win', '.work', '.xyz', '.zip'
	];
	// Drop any entry without a leading dot (defensive against future edits).
	$bad_tlds = array_values(array_filter($bad_tlds, fn($tld) => str_starts_with($tld, '.')));

	return [
		'bad_tlds' => $bad_tlds,
		'disposable_count' => $disc_count,
		'is_updated' => file_exists(__DIR__ . '/data/last_update.txt'),
		'last_update' => file_exists(__DIR__ . '/data/last_update.txt') ? file_get_contents(__DIR__ . '/data/last_update.txt') : 'Nie (Statisch)',
		// realpath() returns false if the file is missing/unreadable; fall back
		// so the cron command shown in the UI is never an empty string.
		'updater_path' => realpath(__DIR__ . '/scripts/update_domains.php') ?: __DIR__ . '/scripts/update_domains.php',
		'manual_count' => count($manual_list),
		'manual_list' => $manual_list,
		'keywords' => $keywords,
		// Pre-computed because Friendica's Smarty wrapper may not allow count() in templates.
		'keywords_count' => count($keywords),
	];
}

/**
 * Main dashboard logic.
 */
function realmember_content()
{
	if (!DI::userSession()->getLocalUserId()) {
		System::externalRedirect(DI::baseUrl() . '/login');
	}
	if (!DI::userSession()->isSiteAdmin()) {
		System::externalRedirect(DI::baseUrl() . '/network');
	}

	DI::page()->registerStylesheet('addon/realmember/css/realmember.css');

	// Hard whitelists for all GET parameters. Even though the SQL paths below
	// use parameterized queries and an ORDER BY whitelist, normalizing here
	// keeps unvalidated input out of the template's hidden form fields and
	// link href attributes.
	$allowed_filters = ['all', 'recent', 'new', 'pending', 'spam'];
	$allowed_sorts   = ['name', 'email', 'date', 'score'];
	$allowed_dirs    = ['asc', 'desc'];

	$filter = $_GET['filter'] ?? 'all';
	if (!in_array($filter, $allowed_filters, true)) {
		$filter = 'all';
	}
	// Search input: trim and cap at 100 chars (covers any realistic target;
	// mb_substr keeps multi-byte characters intact).
	$search = trim($_GET['search'] ?? '');
	if ($search !== '') {
		$search = mb_substr($search, 0, 100, 'UTF-8');
	}
	$sort = $_GET['sort'] ?? 'date';
	if (!in_array($sort, $allowed_sorts, true)) {
		$sort = 'date';
	}
	$dir = strtolower($_GET['dir'] ?? 'desc');
	if (!in_array($dir, $allowed_dirs, true)) {
		$dir = 'desc';
	}
	$criteria = realmember_get_criteria();

	// Pre-load disposable domains list once (performance: avoid re-reading per user)
	$disposable_path = __DIR__ . '/data/disposable_domains.php';
	$disposable_domains = file_exists($disposable_path) ? include $disposable_path : [];
	if (!is_array($disposable_domains)) {
		$disposable_domains = [];
	}

	// Pre-build keyword regex pattern once (single match instead of per-keyword loop).
	// preg_quote with delimiter '/' protects against future keywords containing meta-chars.
	// \b word boundaries prevent substring false-positives (e.g. "loan" matching "Sloan").
	// /u flag enables Unicode-aware boundaries for multi-byte characters.
	$keyword_pattern = null;
	if (!empty($criteria['keywords'])) {
		$escaped = array_map(fn($k) => preg_quote($k, '/'), $criteria['keywords']);
		$keyword_pattern = '/\b(' . implode('|', $escaped) . ')\b/iu';
	}

	// Cache for federated nickname counts (one query per unique nickname per request)
	$nickname_count_cache = [];

	// Generate Moderation Tabs (consistent with core)
	$all = DBA::count('user', ["`uid` != ?", 0]);
	$active = DBA::count('user', ["`verified` AND NOT `blocked` AND NOT `account_removed` AND NOT `account_expired` AND `uid` != ?", 0]);
	$pending = \Friendica\Model\Register::getPendingCount();
	$blocked = DBA::count('user', ['blocked' => true, 'verified' => true, 'account_removed' => false]);
	$deleted = DBA::count('user', ['account_removed' => true]);

	$tabs = [
		[
			'label' => DI::l10n()->t('All') . ' (' . $all . ')',
			'url' => DI::baseUrl() . '/moderation/users',
			'sel' => '',
			'title' => DI::l10n()->t('List of all users'),
		],
		[
			'label' => DI::l10n()->t('Active') . ' (' . $active . ')',
			'url' => DI::baseUrl() . '/moderation/users/active',
			'sel' => '',
			'title' => DI::l10n()->t('List of active accounts'),
		],
		[
			'label' => DI::l10n()->t('Pending') . ($pending ? ' (' . $pending . ')' : ''),
			'url' => DI::baseUrl() . '/moderation/users/pending',
			'sel' => '',
			'title' => DI::l10n()->t('List of pending registrations'),
		],
		[
			'label' => DI::l10n()->t('Blocked') . ($blocked ? ' (' . $blocked . ')' : ''),
			'url' => DI::baseUrl() . '/moderation/users/blocked',
			'sel' => '',
			'title' => DI::l10n()->t('List of blocked users'),
		],
		[
			'label' => DI::l10n()->t('Deleted') . ($deleted ? ' (' . $deleted . ')' : ''),
			'url' => DI::baseUrl() . '/moderation/users/deleted',
			'sel' => '',
			'title' => DI::l10n()->t('List of pending user deletions'),
		],
	];

	// Fire hook so RealMember (and others like Ratioed) appear in the list
	$hook_data = ['tabs' => $tabs, 'selectedTab' => 'realmember'];
	Hook::callAll('moderation_users_tabs', $hook_data);
	$tabs = $hook_data['tabs'];

	$tab_tpl = Renderer::getMarkupTemplate('common_tabs.tpl');
	$tabs_html = Renderer::replaceMacros($tab_tpl, ['$tabs' => $tabs, '$more' => DI::l10n()->t('More')]);

	// Generate Moderation Sidebar (consistent with BaseModeration)
	// We DO NOT add RealMember here anymore as it is already a Tab!
	$aside_sub = [
		'information' => [
			DI::l10n()->t('Information'),
			[
				'overview' => [DI::baseUrl() . '/moderation', DI::l10n()->t('Overview'), 'overview'],
				'reports' => [DI::baseUrl() . '/moderation/reports', DI::l10n()->t('Reports'), 'overview'],
			]
		],
		'configuration' => [
			DI::l10n()->t('Configuration'),
			[
				'users' => [DI::baseUrl() . '/moderation/users', DI::l10n()->t('Users'), 'users'],
			]
		],
		'tools' => [
			DI::l10n()->t('Tools'),
			[
				'contactblock' => [DI::baseUrl() . '/moderation/blocklist/contact', DI::l10n()->t('Contact Blocklist'), 'contactblock'],
				'blocklist' => [DI::baseUrl() . '/moderation/blocklist/server', DI::l10n()->t('Server Blocklist'), 'blocklist'],
				'deleteitem' => [DI::baseUrl() . '/moderation/item/delete', DI::l10n()->t('Delete Item'), 'deleteitem'],
			]
		],
		'diagnostics' => [
			DI::l10n()->t('Diagnostics'),
			[
				'itemsource' => [DI::baseUrl() . '/moderation/item/source', DI::l10n()->t('Item Source'), 'itemsource'],
			]
		],
	];

	// Inject RealMember sidebar (same as the hook does on /moderation/* pages)
	$realmember_menu_tpl = Renderer::getMarkupTemplate('realmember_menu.tpl', 'addon/realmember/');
	DI::page()['aside'] .= Renderer::replaceMacros($realmember_menu_tpl, [
		'$url' => DI::baseUrl() . '/realmember',
		'$header' => 'RealMember',
		'$label' => DI::l10n()->t('Spam-Analyse'),
	]);

	$aside_tpl = Renderer::getMarkupTemplate('moderation/aside.tpl');
	DI::page()['aside'] .= Renderer::replaceMacros($aside_tpl, [
		'$subpages' => $aside_sub,
		'$admtxt' => DI::l10n()->t('Moderation'),
		'$h_pending' => DI::l10n()->t('User registrations waiting for confirmation'),
		'$modurl' => 'moderation/'
	]);

	// Whitelist sorting
	$sort_map = [
		'name' => 'username',
		'email' => 'email',
		'date' => 'register_date'
	];
	$order_field = $sort_map[$sort] ?? 'register_date';

	// Defense-in-Depth: hard-coded allowlist for the SQL ORDER BY column.
	$allowed_order_fields = ['username', 'email', 'register_date'];
	if (!in_array($order_field, $allowed_order_fields, true)) {
		$order_field = 'register_date';
	}
	$order_dir = (strtolower($dir) === 'asc') ? 'ASC' : 'DESC';

	// Constructing a robust condition string for DBA::p calls
	$condition = " `user`.`uid` != ? ";
	$params = [0];

	if ($filter === 'recent') {
		$condition .= " AND `user`.`register_date` > DATE_SUB(NOW(), INTERVAL 48 HOUR) ";
	} elseif ($filter === 'new') {
		$condition .= " AND `user`.`register_date` > DATE_SUB(NOW(), INTERVAL 30 DAY) ";
	} elseif ($filter === 'pending') {
		// Subquery (not JOIN) because $condition is reused in three queries
		// below — including a COUNT(*) without the register-JOIN.
		$condition .= " AND `user`.`uid` IN (SELECT `uid` FROM `register`) ";
	}

	if (!empty($search)) {
		$condition .= " AND (`user`.`username` LIKE ? OR `user`.`nickname` LIKE ? OR `user`.`email` LIKE ?) ";
		$wildcard = '%' . $search . '%';
		$params[] = $wildcard;
		$params[] = $wildcard;
		$params[] = $wildcard;
	}

	// Pagination setup
	$pager = new Pager(DI::l10n(), DI::args()->getQueryString(), 20);
	$limit_start = (int) $pager->getStart();
	$limit_count = (int) $pager->getItemsPerPage();

	$base_url = DI::baseUrl();
	$results = [];

	if ($filter === 'spam') {
		// Fetch ALL matching users (with JOIN for notes) to filter by heuristic score in PHP
		$select_sql = "SELECT `user`.`uid`, `user`.`username`, `user`.`nickname`, `user`.`email`, `user`.`register_date`, 
		                      `user`.`verified`, `user`.`blocked`, `user`.`account_removed`, `register`.`note`
		               FROM `user` 
		               LEFT JOIN `register` ON `user`.`uid` = `register`.`uid`
		               WHERE " . $condition . " 
		               ORDER BY `user`.`account_removed` ASC, `user`.`$order_field` $order_dir";

		$usersSet = DBA::p($select_sql, ...$params);
		$filtered = [];
		if ($usersSet) {
			while ($user = DBA::fetch($usersSet)) {
				$user['is_removed'] = (bool) $user['account_removed'];
				// rawurlencode() because nicknames come from DB without re-validation.
				$user['profile_url'] = $base_url . '/profile/' . rawurlencode($user['nickname']);

				$scoreData = realmember_calculate_risk($user, $criteria, $disposable_domains, $keyword_pattern, $nickname_count_cache);
				if ($scoreData['score'] > 0) {
					$filtered[] = array_merge($user, $scoreData);
				}
			}
			DBA::close($usersSet);
		}

		$total = count($filtered);
		$results = array_slice($filtered, $limit_start, $limit_count);
	} else {
		// Standard Flow: Count first
		$total_sql = "SELECT COUNT(*) AS total FROM `user` WHERE " . $condition;
		$total_res = DBA::p($total_sql, ...$params);
		$total = 0;
		if ($total_res) {
			$row = DBA::fetch($total_res);
			$total = (int) ($row['total'] ?? 0);
			DBA::close($total_res);
		}

		// When sorting by score, the DB cannot paginate (score is computed in PHP).
		// Load the full filtered set, score everyone, sort and slice in PHP.
		// For other sort fields, the DB paginates efficiently as before.
		if ($sort === 'score') {
			$select_sql = "SELECT `user`.`uid`, `user`.`username`, `user`.`nickname`, `user`.`email`, `user`.`register_date`, 
			                      `user`.`verified`, `user`.`blocked`, `user`.`account_removed`, `register`.`note`
			               FROM `user` 
			               LEFT JOIN `register` ON `user`.`uid` = `register`.`uid`
			               WHERE " . $condition;

			$usersSet = DBA::p($select_sql, ...$params);
			$all_scored = [];
			if ($usersSet) {
				while ($user = DBA::fetch($usersSet)) {
					$user['is_removed'] = (bool) $user['account_removed'];
					$user['profile_url'] = $base_url . '/profile/' . rawurlencode($user['nickname']);

					$scoreData = realmember_calculate_risk($user, $criteria, $disposable_domains, $keyword_pattern, $nickname_count_cache);
					$all_scored[] = array_merge($user, $scoreData);
				}
				DBA::close($usersSet);
			}
			$results = $all_scored;
		} else {
			// Standard path: DB paginates by name/email/date.
			$select_sql = "SELECT `user`.`uid`, `user`.`username`, `user`.`nickname`, `user`.`email`, `user`.`register_date`, 
			                      `user`.`verified`, `user`.`blocked`, `user`.`account_removed`, `register`.`note`
			               FROM `user` 
			               LEFT JOIN `register` ON `user`.`uid` = `register`.`uid`
			               WHERE " . $condition . " 
			               ORDER BY `user`.`account_removed` ASC, `user`.`$order_field` $order_dir 
			               LIMIT $limit_start, $limit_count";

			$usersSet = DBA::p($select_sql, ...$params);
			if ($usersSet) {
				while ($user = DBA::fetch($usersSet)) {
					$user['is_removed'] = (bool) $user['account_removed'];
					$user['profile_url'] = $base_url . '/profile/' . rawurlencode($user['nickname']);

					$scoreData = realmember_calculate_risk($user, $criteria, $disposable_domains, $keyword_pattern, $nickname_count_cache);
					$results[] = array_merge($user, $scoreData);
				}
				DBA::close($usersSet);
			}
		}
	}

	// Score sort: applied to the full set when sort=score (loaded above), then sliced.
	if ($sort === 'score') {
		usort($results, function ($a, $b) use ($dir) {
			if ($a['is_removed'] !== $b['is_removed']) {
				return $a['is_removed'] <=> $b['is_removed'];
			}
			return (strtolower($dir) === 'asc')
				? $a['score'] <=> $b['score']
				: $b['score'] <=> $a['score'];
		});
		$results = array_slice($results, $limit_start, $limit_count);
	}

	$t = Renderer::getMarkupTemplate('realmember.tpl', 'addon/realmember/');
	return $tabs_html . Renderer::replaceMacros($t, [
		'$title' => 'RealMember Spam-Analyse',
		'$users' => $results,
		'$filter' => $filter,
		'$search' => $search,
		'$sort' => $sort,
		'$dir' => $dir,
		'$total' => $total,
		'$criteria' => $criteria,
		'$pager' => $pager->renderFull($total),
	]);
}

/**
 * Calculate the risk score for a user based on multiple read-only signals.
 *
 * SECURITY NOTE on the returned 'reasons' array:
 * The strings contain interpolated user-controlled data (email domains,
 * matched keywords, admin patterns). They MUST always be rendered through
 * Smarty's default auto-escaping (`{{$reason}}`, never `{{$reason nofilter}}`).
 * If a future change wants to render reasons as HTML (icons, bold), restructure
 * them into typed data ({type, value, ...}) and assemble in the template —
 * never insert raw HTML into these strings.
 *
 * @param array $user                  User data from database
 * @param array $criteria              Analysis criteria (TLDs, keywords, manual rules)
 * @param array $disposable_domains    Pre-loaded list of disposable email domains
 * @param string|null $keyword_pattern Pre-built regex pattern for keyword matching
 * @param array &$nickname_count_cache Reference to per-request cache for nickname counts
 * @return array Score, reasons, and risk level
 */
function realmember_calculate_risk($user, $criteria, $disposable_domains = [], $keyword_pattern = null, &$nickname_count_cache = [])
{
	$score = 0;
	$reasons = [];

	// 1. Email Analysis
	$email = strtolower($user['email'] ?? '');
	$parts = explode('@', $email);
	$domain = $parts[1] ?? '';

	// Layer 1: Manual System Disallowed List (100 Points)
	// Strict suffix match consistent with Friendica core's disallowed_email
	// behaviour — NOT fnmatch() with glob patterns (which would surprise admins
	// entering "example.com" and trigger a divergence from what core blocks).
	if (!empty($criteria['manual_list'])) {
		foreach ($criteria['manual_list'] as $item) {
			$pat = strtolower(trim($item));
			if ($pat === '' || $domain === '') {
				continue;
			}
			if ($email === $pat || $domain === $pat || str_ends_with($domain, '.' . $pat)) {
				$score += 100;
				$reasons[] = "Systemweit gesperrte E-Mail (Admin-Regel: $pat)";
				break;
			}
		}
	}

	// Layer 2: Suspicious TLDs
	if ($score < 100 && !empty($criteria['bad_tlds'])) {
		foreach ($criteria['bad_tlds'] as $tld) {
			if (str_ends_with($domain, $tld)) {
				$score += 30;
				$reasons[] = "Verdächtige TLD ($tld)";
				break;
			}
		}
	}

	// Layer 3: Disposable Domains (uses pre-loaded list)
	if ($score < 100 && !empty($disposable_domains) && in_array($domain, $disposable_domains)) {
		$score += 45;
		$reasons[] = "Bekannter TrashMail-Anbieter";
	}

	// 2. Keyword check (uses pre-built regex for performance)
	$note = strtolower($user['note'] ?? '');
	$username = strtolower($user['username'] ?? '');

	if ($keyword_pattern) {
		if ($note !== '' && preg_match_all($keyword_pattern, $note, $matches)) {
			foreach (array_unique($matches[0]) as $kw) {
				$score += 25;
				$reasons[] = "Keyword in Notiz gefunden: '$kw'";
			}
		}
		if ($username !== '' && preg_match_all($keyword_pattern, $username, $matches)) {
			foreach (array_unique($matches[0]) as $kw) {
				$score += 20;
				$reasons[] = "Keyword im Nutzernamen gefunden: '$kw'";
			}
		}
	}

	// 3. Entropie / Bot-Muster-Erkennung
	$nickname = $user['nickname'] ?? '';
	$email_prefix = str_replace(['.', '-', '_'], '', $parts[0] ?? ''); // Punkte ignorieren

	$check_entropy = function ($orig_str) {
		$str_clean = str_replace(['.', '-', '_'], '', $orig_str);

		// mb_strlen + Unicode-aware vowel class so that names like "müllermäxchen"
		// or "björnsson" are not falsely flagged. ASCII-only [aeiouy] would not
		// recognise the umlauts and bias the heuristic against names with diacritics.
		$len = mb_strlen($str_clean, 'UTF-8');
		if ($len > 7) {
			$vowel_re = '/[aeiouyäöüáéíóúàèìòùâêîôûãñåøæ]/iu';
			$vowels = preg_match_all($vowel_re, $str_clean);
			$consonants_and_nums = $len - $vowels;

			// 1. Extrem wenige Vokale oder mieses Verhältnis (z.B. rtkz99)
			if ($vowels < 2 || ($consonants_and_nums / max(1, $vowels)) > 4) {
				return true;
			}

			// 2. Tastenfeld-Smash prüfen — übliche Namens-Cluster vorher normalisieren,
			// damit echte Namen ("schlaephucke") nicht bestraft werden.
			$normalized = str_ireplace(
				['sch', 'ch', 'tz', 'ck', 'th', 'ph', 'qu'],
				['s', 'c', 'z', 'k', 't', 'f', 'q'],
				$orig_str
			);

			// a) 6 Konsonanten am Stück
			if (preg_match('/[bcdfghjklmnpqrstvwxz]{6,}/i', $normalized)) {
				return true;
			}
			// b) 5 Vokale am Stück
			if (preg_match('/[aeiouyäöüáéíóúàèìòùâêîôûãñåøæ]{5,}/iu', $normalized)) {
				return true;
			}
		}
		return false;
	};

	if ($check_entropy($nickname)) {
		$score += 20;
		$reasons[] = "Verdächtiges Nickname-Muster (Bot-Signatur)";
	}

	if ($check_entropy($email_prefix)) {
		$score += 20;
		$reasons[] = "Verdächtiges E-Mail-Präfix (Bot-Signatur)";
	}

	// Layer 4: Federated Nickname Frequency
	// Counts how often this nickname appears across the Fediverse via Friendica's
	// `contact` table (uid=0 = public contacts). Spammers typically reuse the same
	// handle on many instances, so this is a useful additional signal.
	//
	// We deliberately do NOT whitelist common first names — that would be culturally
	// biased and unmaintainable. A common name like "stefan" appearing on many
	// servers earns at most "auffällig" (+25), which is appropriate as a signal.
	// Only short nicks (<6 chars) and role-based technical nicks are skipped.
	$technical_nicks = [
		'support', 'contact', 'webmaster', 'postmaster', 'hostmaster',
		'moderator', 'administrator', 'friendica', 'noreply', 'no-reply',
		'feedback', 'newsletter', 'service', 'official'
	];
	if (strlen($nickname) >= 6 && !in_array(strtolower($nickname), $technical_nicks, true)) {
		if (!isset($nickname_count_cache[$nickname])) {
			$count_result = DBA::p(
				"SELECT COUNT(*) AS total FROM `contact` 
				 WHERE `nick` = ? AND `uid` = ? 
				   AND NOT `self` AND NOT `deleted` AND NOT `blocked`",
				$nickname,
				0
			);
			$nick_count = 0;
			if ($count_result) {
				$row = DBA::fetch($count_result);
				$nick_count = (int) ($row['total'] ?? 0);
				DBA::close($count_result);
			}
			$nickname_count_cache[$nickname] = $nick_count;
		}
		$nick_count = $nickname_count_cache[$nickname];

		if ($nick_count >= 30) {
			$score += 25;
			$reasons[] = "Nickname auf $nick_count anderen Servern bekannt (sehr verdächtig)";
		} elseif ($nick_count >= 10) {
			$score += 15;
			$reasons[] = "Nickname auf $nick_count anderen Servern bekannt (auffällig)";
		} elseif ($nick_count >= 5) {
			$score += 5;
			$reasons[] = "Nickname auf $nick_count anderen Servern bekannt";
		}
	}

	return [
		'score' => min(100, $score),
		'reasons' => $reasons,
		'risk_level' => realmember_get_level($score)
	];
}

function realmember_get_level($score)
{
	if ($score >= 70)
		return 'critical';
	if ($score >= 40)
		return 'warning';
	if ($score >= 20)
		return 'info';
	return 'safe';
}