<?php

/*
 * Name: AlertBanner
 * Description: Displays a customizable administrative notice banner at the top of the page.
 * Version: 1.0
 * Author: Jools <https://friendica.de/profile/jools>
 * License: AGPL-3.0-or-later
 *
 * SPDX-FileCopyrightText: 2026 [Jools]
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Util\DateTimeFormat;

const ALERTBANNER_STYLES = ['info', 'warning', 'danger', 'success'];
const ALERTBANNER_VISIBILITIES = ['registered', 'everyone'];
const ALERTBANNER_LOCAL_FORMAT = 'Y-m-d H:i';

function alertbanner_install(): void
{
	Hook::register('head', __FILE__, 'alertbanner_head');
	Hook::register('footer', __FILE__, 'alertbanner_footer');
	Hook::register('page_end', __FILE__, 'alertbanner_page_end');

	// Safe activation default: enabling the addon must never immediately show stale banner text.
	DI::config()->set('alertbanner', 'active', 0);
}

function alertbanner_uninstall(): void
{
	Hook::unregister('head', __FILE__, 'alertbanner_head');
	Hook::unregister('footer', __FILE__, 'alertbanner_footer');
	Hook::unregister('page_end', __FILE__, 'alertbanner_page_end');
}

function alertbanner_head(string &$html): void
{
	if (!alertbanner_should_show()) {
		return;
	}

	// Register our styles using standard local file path with a version suffix for cache busting
	$version = DI::addonHelper()->getAddonInfo('alertbanner')->getVersion();
	DI::page()->registerStylesheet(__DIR__ . '/css/alertbanner.css?av=' . $version);
}

function alertbanner_footer(string &$footer): void
{
	if (!alertbanner_should_show()) {
		return;
	}

	// Register JS asset using standard Friendica Page API with a version suffix for cache busting
	$version = DI::addonHelper()->getAddonInfo('alertbanner')->getVersion();
	DI::page()->registerFooterScript(__DIR__ . '/js/alertbanner.js?av=' . $version);
}

function alertbanner_page_end(string &$html): void
{
	if (!alertbanner_should_show()) {
		return;
	}

	$text = alertbanner_get_text();
	$style = alertbanner_get_style();
	$visibility = alertbanner_get_visibility();
	$startsAt = alertbanner_get_starts_at();
	$endsAt = alertbanner_get_ends_at();
	$bgColor = alertbanner_get_bg_color();
	$textColor = alertbanner_get_text_color();

	$tpl = Renderer::getMarkupTemplate('banner.tpl', 'addon/alertbanner');
	$html .= Renderer::replaceMacros($tpl, [
		'$text' => $text,
		'$style' => $style,
		'$banner_id' => alertbanner_get_banner_id($text, $style, $visibility, $startsAt, $endsAt, $bgColor, $textColor),
		'$close_label' => DI::l10n()->t('Close'),
		'$bg_color' => alertbanner_hex_to_rgba($bgColor, 0.82),
		'$text_color' => $textColor,
	]);
}

function alertbanner_addon_admin(string &$output): void
{
	$adminTimezone = alertbanner_get_admin_timezone();
	$serverTimezone = alertbanner_get_server_timezone();

	$tpl = Renderer::getMarkupTemplate('admin.tpl', 'addon/alertbanner');

	$output .= Renderer::replaceMacros($tpl, [
		'$instance_tz_label' => DI::l10n()->t('Instance Timezone & Current Time'),
		'$instance_tz' => $serverTimezone,
		'$instance_time' => DateTimeFormat::convert('now', $serverTimezone, 'UTC', alertbanner_local_format()),
		'$admin_tz_label' => DI::l10n()->t('Admin Timezone & Current Time'),
		'$admin_tz' => $adminTimezone,
		'$admin_time' => DateTimeFormat::convert('now', $adminTimezone, 'UTC', alertbanner_local_format()),
		'$timezone_note' => DI::l10n()->t('The configured display period refers to your current timezone: %s', $adminTimezone),
		'$active' => [
			'alertbanner_active',
			DI::l10n()->t('Activate Alert Banner'),
			DI::config()->get('alertbanner', 'active') ?? 0,
			DI::l10n()->t('Show the notice banner globally. The addon can be enabled while the banner itself stays inactive.'),
		],
		'$text' => [
			'alertbanner_text',
			DI::l10n()->t('Banner Message'),
			alertbanner_get_text(),
			DI::l10n()->t('The notification message to display in the banner.'),
		],
		'$style' => [
			'alertbanner_style',
			DI::l10n()->t('Banner Style'),
			alertbanner_get_style(),
			DI::l10n()->t('Visual style variant of the banner.'),
			[
				'info' => DI::l10n()->t('Info (Blue)'),
				'warning' => DI::l10n()->t('Warning (Orange)'),
				'danger' => DI::l10n()->t('Danger (Red)'),
				'success' => DI::l10n()->t('Success (Green)'),
			],
		],
		'$visibility' => [
			'alertbanner_visibility',
			DI::l10n()->t('Banner Visibility'),
			alertbanner_get_visibility(),
			DI::l10n()->t('Choose whether the banner is shown only to registered local users or also to visitors who are not logged in.'),
			[
				'registered' => DI::l10n()->t('Registered users only'),
				'everyone' => DI::l10n()->t('Registered and unregistered users'),
			],
		],
		'$bg_color' => [
			'alertbanner_bg_color',
			DI::l10n()->t('Banner Background Color'),
			alertbanner_get_bg_color(),
			DI::l10n()->t('Choose a custom background color for the banner.'),
			false,
			'',
			'color',
		],
		'$text_color' => [
			'alertbanner_text_color',
			DI::l10n()->t('Banner Text Color'),
			alertbanner_get_text_color(),
			DI::l10n()->t('Choose a custom text color for the banner text and close button.'),
			false,
			'',
			'color',
		],
		'$starts_at' => [
			'alertbanner_starts_at',
			DI::l10n()->t('Start of display period'),
			alertbanner_utc_to_admin_time(alertbanner_get_starts_at(), $adminTimezone),
			DI::l10n()->t('Date and time in your administrator timezone shown above. Format: YYYY-MM-DD HH:MM. Leave empty to show immediately.'),
			false,
			'',
			'text',
			DateTimeFormat::convert('now', $adminTimezone, 'UTC', alertbanner_local_format()),
		],
		'$ends_at' => [
			'alertbanner_ends_at',
			DI::l10n()->t('End of display period'),
			alertbanner_utc_to_admin_time(alertbanner_get_ends_at(), $adminTimezone),
			DI::l10n()->t('Date and time in your administrator timezone shown above. Format: YYYY-MM-DD HH:MM. Leave empty to show until disabled.'),
			false,
			'',
			'text',
			DateTimeFormat::convert('7 days', $adminTimezone, 'UTC', alertbanner_local_format()),
		],
		'$submit' => DI::l10n()->t('Save Settings'),
	]);
}

function alertbanner_addon_admin_post(): void
{
	$style = trim((string) ($_POST['alertbanner_style'] ?? 'info'));
	if (!in_array($style, ALERTBANNER_STYLES, true)) {
		$style = 'info';
	}

	$visibility = trim((string) ($_POST['alertbanner_visibility'] ?? 'registered'));
	if (!in_array($visibility, ALERTBANNER_VISIBILITIES, true)) {
		$visibility = 'registered';
	}

	$bgColor = trim((string) ($_POST['alertbanner_bg_color'] ?? '#121216'));
	if (!preg_match('/^#[0-9a-fA-F]{6}$/', $bgColor)) {
		$bgColor = '#121216';
	}

	$textColor = trim((string) ($_POST['alertbanner_text_color'] ?? '#ffffff'));
	if (!preg_match('/^#[0-9a-fA-F]{6}$/', $textColor)) {
		$textColor = '#ffffff';
	}

	$adminTimezone = alertbanner_get_admin_timezone();
	$startsAt = alertbanner_normalize_admin_time((string) ($_POST['alertbanner_starts_at'] ?? ''), $adminTimezone, 'start');
	$endsAt = alertbanner_normalize_admin_time((string) ($_POST['alertbanner_ends_at'] ?? ''), $adminTimezone, 'end');

	if ($startsAt !== '' && $endsAt !== '' && $endsAt <= $startsAt) {
		DI::sysmsg()->addNotice(DI::l10n()->t('Invalid AlertBanner display period. The end date must be after the start date. The end date was cleared.'));
		$endsAt = '';
	}

	$text = mb_substr(trim((string) ($_POST['alertbanner_text'] ?? '')), 0, 2000);

	try {
		$transaction = DI::config()->beginTransaction();
		$transaction->set('alertbanner', 'active', !empty($_POST['alertbanner_active']) ? 1 : 0);
		$transaction->set('alertbanner', 'text', $text);
		$transaction->set('alertbanner', 'style', $style);
		$transaction->set('alertbanner', 'visibility', $visibility);
		$transaction->set('alertbanner', 'bg_color', $bgColor);
		$transaction->set('alertbanner', 'text_color', $textColor);
		$transaction->set('alertbanner', 'starts_at', $startsAt);
		$transaction->set('alertbanner', 'ends_at', $endsAt);
		$transaction->commit();

		DI::sysmsg()->addInfo(DI::l10n()->t('AlertBanner settings saved.'));
	} catch (\Throwable $e) {
		DI::logger()->warning('alertbanner: failed to save settings', [
			'error' => $e->getMessage(),
		]);
		DI::sysmsg()->addNotice(DI::l10n()->t('Settings could not be saved due to a server error. Please try again.'));
	}
}

function alertbanner_should_show(): bool
{
	static $shouldShow = null;

	if ($shouldShow !== null) {
		return $shouldShow;
	}

	// Do not show the banner in minimal or raw page layouts (e.g. theme previews, bookmarklets)
	if (isset($_GET['mode']) && ($_GET['mode'] === 'minimal' || $_GET['mode'] === 'raw')) {
		return $shouldShow = false;
	}

	if (!(DI::config()->get('alertbanner', 'active') ?? 0)) {
		return $shouldShow = false;
	}

	if (alertbanner_get_text() === '') {
		return $shouldShow = false;
	}

	$now = DateTimeFormat::utcNow();

	$startsAt = alertbanner_get_starts_at();
	if ($startsAt !== '' && $startsAt > $now) {
		return $shouldShow = false;
	}

	$endsAt = alertbanner_get_ends_at();
	if ($endsAt !== '' && $endsAt <= $now) {
		return $shouldShow = false;
	}

	if (alertbanner_get_visibility() === 'registered' && !DI::userSession()->isAuthenticated()) {
		return $shouldShow = false;
	}

	return $shouldShow = true;
}

function alertbanner_get_text(): string
{
	return trim((string) (DI::config()->get('alertbanner', 'text') ?? ''));
}

function alertbanner_get_style(): string
{
	$style = (string) (DI::config()->get('alertbanner', 'style') ?? 'info');

	return in_array($style, ALERTBANNER_STYLES, true) ? $style : 'info';
}

function alertbanner_get_visibility(): string
{
	$visibility = (string) (DI::config()->get('alertbanner', 'visibility') ?? 'registered');

	return in_array($visibility, ALERTBANNER_VISIBILITIES, true) ? $visibility : 'registered';
}

function alertbanner_get_starts_at(): string
{
	return trim((string) (DI::config()->get('alertbanner', 'starts_at') ?? ''));
}

function alertbanner_get_ends_at(): string
{
	return trim((string) (DI::config()->get('alertbanner', 'ends_at') ?? ''));
}

function alertbanner_get_banner_id(string $text, string $style, string $visibility, string $startsAt, string $endsAt, string $bgColor, string $textColor): string
{
	return md5($text . '|' . $style . '|' . $visibility . '|' . $startsAt . '|' . $endsAt . '|' . $bgColor . '|' . $textColor);
}

function alertbanner_get_server_timezone(): string
{
	$timezone = (string) (DI::config()->get('system', 'default_timezone') ?? 'UTC');

	return alertbanner_validate_timezone($timezone);
}

function alertbanner_get_admin_timezone(): string
{
	return alertbanner_validate_timezone(DI::appHelper()->getTimeZone());
}

function alertbanner_validate_timezone(string $timezone): string
{
	try {
		return (new \DateTimeZone($timezone))->getName();
	} catch (\Throwable $e) {
		return 'UTC';
	}
}

function alertbanner_local_format(): string
{
	$format = DI::l10n()->t('Y-m-d H:i');
	if (empty($format) || $format === 'Y-m-d H:i') {
		return ALERTBANNER_LOCAL_FORMAT;
	}
	return $format;
}

function alertbanner_utc_to_admin_time(string $utcTime, string $adminTimezone): string
{
	if ($utcTime === '' || substr($utcTime, 0, 10) === '0000-00-00' || substr($utcTime, 0, 10) === '0001-01-01') {
		return '';
	}

	return DateTimeFormat::convert($utcTime, alertbanner_validate_timezone($adminTimezone), 'UTC', alertbanner_local_format());
}

function alertbanner_normalize_admin_time(string $localTime, string $adminTimezone, string $field): string
{
	$localTime = trim($localTime);
	if ($localTime === '') {
		return '';
	}

	$timezone = new \DateTimeZone(alertbanner_validate_timezone($adminTimezone));

	$formats = [
		alertbanner_local_format(),
		'Y-m-d H:i',
		'd.m.Y H:i',
		'd.m.Y H.i',
		'Y-m-d H:i:s',
		'd.m.Y H:i:s',
	];

	$date = false;
	foreach ($formats as $fmt) {
		$parsed = \DateTimeImmutable::createFromFormat('!' . $fmt, $localTime, $timezone);
		$errors = \DateTimeImmutable::getLastErrors();
		if ($parsed !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
			$date = $parsed;
			break;
		}
	}

	if ($date === false) {
		if ($field === 'start') {
			DI::sysmsg()->addNotice(DI::l10n()->t('Invalid AlertBanner start date. The start date was cleared.'));
		} else {
			DI::sysmsg()->addNotice(DI::l10n()->t('Invalid AlertBanner end date. The end date was cleared.'));
		}

		return '';
	}

	return DateTimeFormat::convert($date->format(DateTimeFormat::MYSQL), 'UTC', $timezone->getName(), DateTimeFormat::MYSQL);
}

function alertbanner_get_bg_color(): string
{
	$bgColor = trim((string) (DI::config()->get('alertbanner', 'bg_color') ?? '#121216'));
	return preg_match('/^#[0-9a-fA-F]{6}$/', $bgColor) ? $bgColor : '#121216';
}

function alertbanner_get_text_color(): string
{
	$textColor = trim((string) (DI::config()->get('alertbanner', 'text_color') ?? '#ffffff'));
	return preg_match('/^#[0-9a-fA-F]{6}$/', $textColor) ? $textColor : '#ffffff';
}

function alertbanner_hex_to_rgba(string $hex, float $opacity): string
{
	$hex = ltrim($hex, '#');
	if (strlen($hex) === 6) {
		$r = hexdec(substr($hex, 0, 2));
		$g = hexdec(substr($hex, 2, 2));
		$b = hexdec(substr($hex, 4, 2));
		return "rgba($r, $g, $b, $opacity)";
	}
	return $hex;
}
