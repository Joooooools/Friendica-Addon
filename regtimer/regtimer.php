<?php

/**
 * Name: Registration Timer
 * Description: Allows administrators to automatically switch the node's registration policy during a configured time window (e.g. close registration at night). The switch is performed by the background worker, so the exact switching time depends on the worker schedule.
 * Version: 1.0
 * Author: Jools <https://friendica.de/profile/jools>
 * License: AGPL-3.0-or-later
 *
 * SPDX-FileCopyrightText: 2026 [Jools]
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Friendica\DI;
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\Core\System;

/*
 * Registration policy values as used by Friendica core in the 'config'/'register_policy' setting:
 *   0 = Closed (Register::CLOSED)
 *   1 = Requires Approval (Register::APPROVE)
 *   2 = Open (Register::OPEN)
 *
 * This addon does NOT touch the config cache on every request. Instead it hooks into
 * the 'cron' event, which is fired by the background worker (cron job OR daemon), and
 * persistently writes the desired register_policy via DI::config()->set(). Outside the
 * configured window it restores the configured "normal" (daytime) policy.
 *
 * Because the change is driven by the worker, the moment of switching is only as precise
 * as the worker schedule (typically every 5-10 minutes). See README for details.
 *
 * Note on the register_policy values and on Register::getPolicy():
 * The configured values above are what we WRITE. Note that Friendica's effective policy
 * is computed by Register::getPolicy(), which has an extra rule: if the system setting
 * 'admin_inactivity_limit' is set and all admins have been inactive longer than that, it
 * returns CLOSED regardless of register_policy. So on such a node the effective policy may
 * differ from what this addon sets. That is core behaviour, not something we override.
 *
 * Note on concurrency:
 * The admin settings save uses Friendica's transactional config API
 * (DI::config()->beginTransaction()->set(...)->commit()), exactly as core does in
 * Site.php for register_policy and the other registration settings. All six values are
 * committed atomically, so a concurrent cron run can never observe a half-written state.
 * (The single-value writes in cron and uninstall stay non-transactional - a transaction
 * for one value would be needless overhead - and additionally the cron re-validates every
 * value it reads and aborts cleanly on anything inconsistent.)
 */

// Whitelist of valid register_policy values (Register::CLOSED / APPROVE / OPEN).
const REGTIMER_VALID_POLICIES = [0, 1, 2];

/**
 * Validate that a value is one of the allowed register_policy values.
 * Strict: accepts a real integer 0/1/2, or a string that is exactly "0", "1" or "2".
 * Rejects floats, numeric strings like "1.9", booleans, etc. Returns the int or null.
 */
function regtimer_valid_policy($value): ?int
{
	if (is_int($value) && in_array($value, REGTIMER_VALID_POLICIES, true)) {
		return $value;
	}

	if (is_string($value) && preg_match('/^[012]$/', $value)) {
		return intval($value);
	}

	return null;
}

/**
 * Wrapper around DI::config()->set() for single-value writes (used in cron and uninstall,
 * where only one value - register_policy - is written and a transaction would be overkill).
 *
 * The non-transactional DatabaseConfig::set() returns bool (it forwards the result of the
 * underlying DB insert), so an explicit false means the write failed. We compare with
 * === false rather than a truthy check so that a future signature returning void/null would
 * not be misread as a failure. set() can also throw; callers wrap this in try/catch.
 * Returns true on success, false if set() explicitly reported failure.
 */
function regtimer_set(string $cat, string $key, $value): bool
{
	return DI::config()->set($cat, $key, $value) !== false;
}

function regtimer_install()
{
	// Hook::register() can return false (or throw) if the hook could not be stored.
	// If that happened silently, the addon would appear active in the panel but its
	// cron callback would never fire - registration would simply never switch at night,
	// with no obvious cause. So we check and log loudly on failure.
	try {
		if (Hook::register('cron', __FILE__, 'regtimer_cron') === false) {
			DI::logger()->warning('regtimer: Hook::register(cron) returned false - the cron hook may not be active');
		}
	} catch (\Throwable $e) {
		DI::logger()->warning('regtimer: failed to register cron hook on install', [
			'error' => $e->getMessage(),
		]);
	}
}

function regtimer_uninstall()
{
	// NOTE on the edge case "Hook::unregister() fails and the hook row survives":
	// We do NOT clear regtimer.enabled here as a fail-safe. An earlier version did, but
	// that was wrong: uninstall() also runs on every addon *update* (Friendica calls
	// uninstall()+install() when addon files change), and there is no argument or context
	// that lets us tell an update apart from a real deactivation. Clearing enabled would
	// therefore silently switch the timer off after every update, even though the admin
	// had it on. That common, real problem is far worse than the rare edge case it tried
	// to guard against - and that edge case is self-correcting anyway: if the hook
	// survives and the cron keeps running, it simply keeps applying the schedule, which
	// is the correct behaviour for an addon that is in fact still active. A genuine
	// deactivation is handled by the reset-on-disable path in the admin form and by the
	// policy restore below. See the README note about re-checking settings after updates.

	// Restore BEFORE unregistering the hook: restoring the registration policy is the
	// more important task (never leave registration accidentally locked), so we do it
	// first. Hook::unregister() can itself throw, which must not prevent the restore.
	//
	// Because uninstall() also runs on updates (see note above), the restore is
	// conservative and idempotent: only restore if the policy currently looks like one
	// WE set, i.e. it equals the configured night value and differs from the day value.
	// If it already holds the day value, or some third value an admin set manually, we
	// leave it untouched - so an update does not disturb a correct current state.
	//
	// ACCEPTED TRADE-OFF: if an update happens to run during the active night window,
	// this restore briefly switches register_policy back to the day value; the next
	// worker run (<= one worker interval later) switches it back to night. We accept this
	// short transient because the alternative - skipping the restore - cannot be done
	// safely: an update is indistinguishable from a real deactivation, and skipping it
	// would risk leaving registration permanently locked after a genuine uninstall. The
	// genuine-deactivation correctness wins over avoiding a brief, self-correcting blip.
	// This is documented in the README ("updating during the night window").
	try {
		$night_policy = regtimer_valid_policy(DI::config()->get('regtimer', 'policy'));
		$day_policy = regtimer_valid_policy(DI::config()->get('regtimer', 'day_policy'));

		if ($night_policy !== null && $day_policy !== null && $night_policy !== $day_policy) {
			$current_policy = regtimer_valid_policy(DI::config()->get('config', 'register_policy'));

			if ($current_policy === $night_policy) {
				if (regtimer_set('config', 'register_policy', $day_policy)) {
					DI::logger()->info('regtimer: uninstalled, restored register_policy to day value', [
						'from' => $night_policy,
						'to' => $day_policy,
					]);
				} else {
					DI::logger()->warning('regtimer: uninstall restore reported failure (set returned false)', [
						'target' => $day_policy,
					]);
				}
			}
		}
	} catch (\Throwable $e) {
		// The uninstall context does not guarantee a fully working persistence layer.
		// Never let a restore failure abort the uninstall itself.
		try {
			DI::logger()->warning('regtimer: could not restore register_policy on uninstall', [
				'error' => $e->getMessage(),
			]);
		} catch (\Throwable $ignored) {
			// Even logging may be unavailable here; swallow silently.
		}
	}

	try {
		Hook::unregister('cron', __FILE__, 'regtimer_cron');
	} catch (\Throwable $e) {
		try {
			DI::logger()->warning('regtimer: failed to unregister cron hook on uninstall', [
				'error' => $e->getMessage(),
			]);
		} catch (\Throwable $ignored) {
		}
	}
}

/**
 * Determine whether the given HH:MM "current" time falls inside the [start, end] window.
 * Handles windows that cross midnight (e.g. 22:00 -> 06:00).
 */
function regtimer_in_window(string $current, string $start, string $end): bool
{
	if ($start === $end) {
		// Zero-length window: treat as "never inside".
		return false;
	}

	if ($start < $end) {
		return ($current >= $start && $current < $end);
	}

	// Window crosses midnight.
	return ($current >= $start || $current < $end);
}

/**
 * cron hook callback.
 *
 * Friendica calls this with the App instance as the first argument during every
 * worker "Cron" run. We use it to evaluate the configured time window and switch
 * the persistent register_policy accordingly.
 */
function regtimer_cron($a)
{
	// All config reads are wrapped: get() can throw on persistence problems, and an
	// uncaught exception here would disturb other addons sharing the cron hook.
	try {
		if (!DI::config()->get('regtimer', 'enabled')) {
			return;
		}

		$start_time = DI::config()->get('regtimer', 'start_time');
		$end_time = DI::config()->get('regtimer', 'end_time');
		$raw_night = DI::config()->get('regtimer', 'policy');
		$raw_day = DI::config()->get('regtimer', 'day_policy');
		$timezone = DI::config()->get('regtimer', 'timezone') ?: 'UTC';
		$raw_current_policy = DI::config()->get('config', 'register_policy');
	} catch (\Throwable $e) {
		try {
			DI::logger()->warning('regtimer: failed to read configuration in cron', [
				'error' => $e->getMessage(),
			]);
		} catch (\Throwable $ignored) {
		}
		return;
	}

	$night_policy = regtimer_valid_policy($raw_night);
	$day_policy = regtimer_valid_policy($raw_day);

	// Re-validate everything we read. The stored config could be incomplete (an admin
	// save happening concurrently) or corrupted (manual edit / broken migration).
	// In any such case we abort this run cleanly rather than acting on bad data; the
	// next worker run will pick up consistent values.
	$time_pattern = '/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/';
	if (
		!is_string($start_time) || !preg_match($time_pattern, $start_time) ||
		!is_string($end_time) || !preg_match($time_pattern, $end_time) ||
		$night_policy === null || $day_policy === null
	) {
		// Guard the log call itself: if logging happened to fail here, an uncaught
		// exception would disturb the worker. Same defensive pattern as elsewhere.
		try {
			DI::logger()->notice('regtimer: skipping run, stored configuration is invalid or incomplete', [
				'start_time' => $start_time,
				'end_time' => $end_time,
				'policy' => $raw_night,
				'day_policy' => $raw_day,
			]);
		} catch (\Throwable $ignored) {
		}
		return;
	}

	try {
		$tz = new DateTimeZone($timezone);
	} catch (\Throwable $e) {
		$tz = new DateTimeZone('UTC');
	}

	try {
		$now = new DateTime('now', $tz);
		$current_time = $now->format('H:i');
	} catch (\Throwable $e) {
		return;
	}

	$inside = regtimer_in_window($current_time, $start_time, $end_time);
	$target_policy = $inside ? $night_policy : $day_policy;

	$current_policy = regtimer_valid_policy($raw_current_policy);

	// Only write when something actually changes, to avoid needless config writes
	// on every single worker run. (If the current policy is itself invalid we always
	// write, to bring it back into a known-good state.)
	if ($current_policy !== $target_policy) {
		try {
			$written = regtimer_set('config', 'register_policy', $target_policy);
			try {
				if ($written) {
					DI::logger()->info('regtimer: switched register_policy', [
						'from' => $current_policy,
						'to' => $target_policy,
						'time' => $current_time,
						'tz' => $timezone,
						'inside' => $inside,
					]);
				} else {
					DI::logger()->warning('regtimer: switch reported failure (set returned false)', [
						'target' => $target_policy,
					]);
				}
			} catch (\Throwable $ignored) {
				// Logging must never turn into an exception escaping the cron hook.
			}
		} catch (\Throwable $e) {
			// A failed config write must not bubble up and disturb other addons
			// running on the same cron hook. The log call is itself guarded.
			try {
				DI::logger()->warning('regtimer: failed to write register_policy', [
					'target' => $target_policy,
					'error' => $e->getMessage(),
				]);
			} catch (\Throwable $ignored) {
			}
		}
	}
}

function regtimer_addon_admin_post()
{
	// Note on error handling in this function: the DI::logger() calls in the catch/error
	// paths below are intentionally NOT wrapped in their own try/catch (unlike the ones in
	// regtimer_cron()). This runs in an interactive admin request, not on the shared cron
	// hook, so a logging failure here would at worst produce an error page for the admin -
	// it cannot disturb the background worker or other addons. Wrapping every logger call
	// in a nested try/catch would only guard against "config save AND logging both broken
	// at the same time" (i.e. a severely broken server) at the cost of noticeably less
	// readable code, so we deliberately keep it simple here.
	if (!DI::userSession()->getLocalUserId()) {
		System::externalRedirect(DI::baseUrl() . '/login');
	}
	if (!DI::userSession()->isSiteAdmin()) {
		System::externalRedirect(DI::baseUrl() . '/network');
	}

	$enabled = !empty($_POST['enabled']) ? 1 : 0;
	$start_time = trim($_POST['start_time'] ?? '');
	$end_time = trim($_POST['end_time'] ?? '');
	$policy = regtimer_valid_policy($_POST['policy'] ?? null);
	$day_policy = regtimer_valid_policy($_POST['day_policy'] ?? null);

	// Validate start_time and end_time (format HH:MM)
	if (!preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $start_time)) {
		DI::sysmsg()->addNotice(DI::l10n()->t('Invalid start time format. Use HH:MM (00:00 to 23:59).'));
		return;
	}
	if (!preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $end_time)) {
		DI::sysmsg()->addNotice(DI::l10n()->t('Invalid end time format. Use HH:MM (00:00 to 23:59).'));
		return;
	}

	// Validate both policies against the whitelist. A manipulated POST could otherwise
	// store values like 999 or -1, which would put register_policy into an undefined state.
	if ($policy === null || $day_policy === null) {
		DI::sysmsg()->addNotice(DI::l10n()->t('Invalid registration policy selected.'));
		return;
	}

	// Save all addon settings in a single transaction, the same way Friendica core does
	// in Site.php. Either all six values are committed together or, on a persistence
	// error, none are - so a concurrent cron run can never read a half-written state.
	//
	// Note: the transactional interface differs from the direct one. Its set() returns
	// the transaction object (for chaining), NOT a bool; success/failure is signalled by
	// commit() either returning normally or throwing ConfigPersistenceException.
	try {
		$was_enabled = (bool) DI::config()->get('regtimer', 'enabled');

		DI::config()->beginTransaction()
			->set('regtimer', 'enabled', $enabled)
			->set('regtimer', 'start_time', $start_time)
			->set('regtimer', 'end_time', $end_time)
			->set('regtimer', 'policy', $policy)
			->set('regtimer', 'day_policy', $day_policy)
			->set('regtimer', 'timezone', DI::appHelper()->getTimeZone())
			->commit();
	} catch (\Throwable $e) {
		DI::logger()->warning('regtimer: failed to save settings', [
			'error' => $e->getMessage(),
		]);
		DI::sysmsg()->addNotice(DI::l10n()->t('Settings could not be saved due to a server error. Please try again.'));
		return;
	}

	// Reset-on-disable: if the addon is being switched off while it may currently be
	// holding register_policy at the "night" value, the cron will no longer run to
	// restore it - so register_policy could stay stuck on Closed/Approval forever.
	// We restore the defined "outside the window" (day) policy immediately here.
	if ($was_enabled && !$enabled) {
		try {
			if (regtimer_set('config', 'register_policy', $day_policy)) {
				DI::logger()->info('regtimer: addon disabled, restored register_policy', [
					'to' => $day_policy,
				]);
			} else {
				DI::logger()->warning('regtimer: disable restore reported failure (set returned false)', [
					'target' => $day_policy,
				]);
				DI::sysmsg()->addNotice(DI::l10n()->t('Settings saved, but the registration policy could not be restored automatically. Please check it manually under Site settings.'));
				return;
			}
		} catch (\Throwable $e) {
			DI::logger()->warning('regtimer: failed to restore register_policy on disable', [
				'target' => $day_policy,
				'error' => $e->getMessage(),
			]);
			DI::sysmsg()->addNotice(DI::l10n()->t('Settings saved, but the registration policy could not be restored automatically. Please check it manually under Site settings.'));
			return;
		}
	}

	DI::sysmsg()->addInfo(DI::l10n()->t('Settings saved.'));
}

function regtimer_addon_admin(string &$o)
{
	// As in regtimer_addon_admin_post(), logger calls in this interactive admin context
	// are intentionally not individually guarded - a logging failure here only affects
	// this admin page, never the background worker or other addons.
	if (!DI::userSession()->getLocalUserId()) {
		System::externalRedirect(DI::baseUrl() . '/login');
	}
	if (!DI::userSession()->isSiteAdmin()) {
		System::externalRedirect(DI::baseUrl() . '/network');
	}

	// Read all config values up front, inside try/catch. get() can throw on a broken
	// persistence layer; without this the whole admin page would hard-fail with an
	// unhandled exception instead of showing a readable message.
	try {
		$instance_tz = DI::config()->get('system', 'default_timezone') ?: 'UTC';
		$cfg_enabled = DI::config()->get('regtimer', 'enabled');
		$cfg_start = DI::config()->get('regtimer', 'start_time');
		$cfg_end = DI::config()->get('regtimer', 'end_time');
		$cfg_policy = DI::config()->get('regtimer', 'policy');
		$cfg_day_policy = DI::config()->get('regtimer', 'day_policy');
	} catch (\Throwable $e) {
		DI::logger()->warning('regtimer: failed to read configuration for admin page', [
			'error' => $e->getMessage(),
		]);
		$o = '<div class="settings-block"><p>'
			. DI::l10n()->t('The Registration Timer settings could not be loaded due to a server error. Please try again later.')
			. '</p></div>';
		return;
	}

	try {
		$instance_tz_obj = new DateTimeZone($instance_tz);
	} catch (\Throwable $e) {
		$instance_tz_obj = new DateTimeZone('UTC');
	}

	$admin_tz = DI::appHelper()->getTimeZone();
	try {
		$admin_tz_obj = new DateTimeZone($admin_tz);
	} catch (\Throwable $e) {
		$admin_tz_obj = new DateTimeZone('UTC');
	}

	$instance_time = (new DateTime('now', $instance_tz_obj))->format('H:i');
	$admin_time = (new DateTime('now', $admin_tz_obj))->format('H:i');

	$policy_choices = [
		0 => DI::l10n()->t('Closed'),
		1 => DI::l10n()->t('Requires Approval'),
		2 => DI::l10n()->t('Open'),
	];

	$policy_value = regtimer_valid_policy($cfg_policy);
	$day_policy_value = regtimer_valid_policy($cfg_day_policy);

	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/regtimer/');
	$o = Renderer::replaceMacros($t, [
		'$worker_note_title' => DI::l10n()->t('Important: switching time depends on the background worker'),
		'$worker_note' => DI::l10n()->t('This addon does not switch the registration policy at the exact second you configure. The change is performed by Friendica\'s background worker (the cron job or the daemon that runs every few minutes). The actual switch therefore happens at the next worker run after the configured time, e.g. on a typical setup with the worker running every 10 minutes a start time of 22:00 may take effect anywhere between 22:00 and 22:10. The switch also only happens while the worker is actually running.'),
		'$submit' => DI::l10n()->t('Save Settings'),
		'$instance_tz_label' => DI::l10n()->t('Instance Timezone & Current Time'),
		'$instance_tz' => $instance_tz,
		'$instance_time' => $instance_time,
		'$admin_tz_label' => DI::l10n()->t('Admin Timezone & Current Time'),
		'$admin_tz' => $admin_tz,
		'$admin_time' => $admin_time,
		'$timezone_note' => DI::l10n()->t('The configured time range refers to your current timezone: %s', $admin_tz),
		'$enabled' => [
			'enabled',
			DI::l10n()->t('Enable Registration Timer'),
			$cfg_enabled,
			DI::l10n()->t('If enabled, the registration policy is switched automatically by the background worker based on the current local time.'),
		],
		'$start_time' => [
			'start_time',
			DI::l10n()->t('Start Time'),
			$cfg_start ?: '22:00',
			DI::l10n()->t('Start of the time period (format HH:MM, in your admin timezone shown above).'),
		],
		'$end_time' => [
			'end_time',
			DI::l10n()->t('End Time'),
			$cfg_end ?: '06:00',
			DI::l10n()->t('End of the time period (format HH:MM, in your admin timezone shown above).'),
		],
		'$policy' => [
			'policy',
			DI::l10n()->t('Registration Policy during Time Period'),
			$policy_value ?? 1,
			DI::l10n()->t('The registration policy enforced during the specified time period (e.g. at night).'),
			$policy_choices,
		],
		'$day_policy' => [
			'day_policy',
			DI::l10n()->t('Registration Policy outside the Time Period'),
			$day_policy_value ?? 2,
			DI::l10n()->t('The registration policy restored outside the specified time period (e.g. during the day).'),
			$policy_choices,
		],
	]);
}
