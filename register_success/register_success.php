<?php

/**
 * Name: Register Success
 * Description: Redirects newly registered users to a custom success/thank-you page instead of the default landing page.
 * Version: 1.0
 * Author: Jools <https://friendica.de/profile/jools>
 * License: AGPL-3.0-or-later
 *
 * SPDX-FileCopyrightText: 2026 [Jools]
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Module\Register;

/**
 * Maximum age (in seconds) of the "just registered" session flag for which an
 * automatic redirect to the success page is still performed.
 *
 * Friendica redirects to the home page right after account creation, so under
 * normal conditions the flag is only a few seconds old. The generous window
 * (5 minutes) is a safety margin: account creation may send a registration
 * e-mail synchronously, which can delay the redirecting request noticeably on
 * slow mail setups. The flag is single-use (removed on first read), so this
 * value only guards against stale flags from abandoned/interrupted sessions.
 */
const REGISTER_SUCCESS_REDIRECT_WINDOW = 300;

/**
 * Installs the register_success addon and registers hooks.
 */
function register_success_install(): void
{
	Hook::register('register_account', __FILE__, 'register_success_register_account');
	Hook::register('home_init', __FILE__, 'register_success_home_init');
	Hook::register('register_form', __FILE__, 'register_success_register_form');
}

/**
 * Uninstalls the register_success addon and unregisters hooks.
 */
function register_success_uninstall(): void
{
	Hook::unregister('register_account', __FILE__, 'register_success_register_account');
	Hook::unregister('home_init', __FILE__, 'register_success_home_init');
	Hook::unregister('register_form', __FILE__, 'register_success_register_form');
}

/**
 * Hook: register_account
 * Fired during successful user creation. Sets a temporary session flag with timestamp.
 */
function register_success_register_account(&$uid): void
{
	// Only set the session flag if no user is currently logged in (i.e. not creating secondary/delegate accounts)
	if (!DI::userSession()->getLocalUserId()) {
		DI::session()->set('register_success_just_registered', time());
	}
}

/**
 * Hook: home_init
 * Fired at the beginning of the home page request. Intercepts and redirects if the session flag is fresh.
 *
 * Note: this hook necessarily runs on every home-page request (for every
 * visitor). The cost is a single session read that short-circuits immediately
 * when no flag is present (the `if ($timestamp)` guard below), so the overhead
 * is negligible. There is no cheaper trigger point for the post-registration
 * redirect, so do not "optimise" this away.
 */
function register_success_home_init(&$data): void
{
	$timestamp = DI::session()->get('register_success_just_registered');
	if ($timestamp) {
		DI::session()->remove('register_success_just_registered');

		// Only redirect if the registration flag is still fresh (single-use,
		// removed above) and no system notices (errors) are pending.
		if (time() - $timestamp < REGISTER_SUCCESS_REDIRECT_WINDOW && empty(DI::sysmsg()->getNotices())) {
			DI::session()->set('register_success_authorized_view', true);
			DI::baseUrl()->redirect('register_success');
		}
	}
}

/**
 * Hook: register_form
 * Fired when rendering the registration page. Clears flags to prevent redirect if registration failed.
 */
function register_success_register_form(&$data): void
{
	DI::session()->remove('register_success_just_registered');
	DI::session()->remove('register_success_authorized_view');
}

/**
 * Declares that this addon acts as a Friendica module (route `/register_success`).
 */
function register_success_module(): void
{
}

/**
 * Controller for GET `/register_success` route.
 * Displays a nice success message.
 */
function register_success_content(): string
{
	// Restrict access to administrators (for preview) or newly registered users (exactly once)
	$authorized = DI::userSession()->isSiteAdmin() || DI::session()->get('register_success_authorized_view');
	if (!$authorized) {
		DI::baseUrl()->redirect();
	}
	DI::session()->remove('register_success_authorized_view');

	$policy = Register::getPolicy();

	if ($policy === Register::APPROVE) {
		$default_title = DI::l10n()->t('Registration Pending');
		$default_message = DI::l10n()->t("Thank you for registering!\n\nAs soon as your registration has been approved by the administration, you will receive an email notification. Your login credentials have just been sent via email. Sometimes it takes a few minutes for this email to arrive. Please also check your spam folder.");

		$title = DI::config()->get('register_success', 'title_approve', '');
		$message = DI::config()->get('register_success', 'message_approve', '');
	} else {
		$default_title = DI::l10n()->t('Registration Successful');
		$default_message = DI::l10n()->t("Thank you for registering!\n\nYour login credentials have just been sent via email. Sometimes it takes a few minutes for this email to arrive. Please also check your spam folder.");

		$title = DI::config()->get('register_success', 'title_open', '');
		$message = DI::config()->get('register_success', 'message_open', '');
	}

	if (trim($title) === '') {
		$title = $default_title;
	}
	if (trim($message) === '') {
		$message = $default_message;
	}

	// Flush notices and infos to display them on this page
	$notices = DI::sysmsg()->flushNotices();
	$infos = DI::sysmsg()->flushInfos();

	// Set browser tab/page title
	DI::page()['title'] = $title;

	// Register stylesheet
	DI::page()->registerStylesheet(DI::baseUrl() . '/addon/register_success/css/register_success.css');

	$tpl = Renderer::getMarkupTemplate('success.tpl', 'addon/register_success/');
	return Renderer::replaceMacros($tpl, [
		'$title' => $title,
		'$message' => $message,
		'$notices' => $notices,
		'$infos' => $infos,
		'$login_url' => DI::baseUrl() . '/login',
		'$login_text' => DI::l10n()->t('Go to Login'),
	]);
}

/**
 * Creates the admin config panel.
 */
function register_success_addon_admin(string &$s): void
{
	if (!DI::userSession()->isSiteAdmin()) {
		return;
	}

	$title_open = DI::config()->get('register_success', 'title_open', '');
	$message_open = DI::config()->get('register_success', 'message_open', '');

	$title_approve = DI::config()->get('register_success', 'title_approve', '');
	$message_approve = DI::config()->get('register_success', 'message_approve', '');

	$default_title_open = DI::l10n()->t('Registration Successful');
	$default_message_open = DI::l10n()->t("Thank you for registering!\n\nYour login credentials have just been sent via email. Sometimes it takes a few minutes for this email to arrive. Please also check your spam folder.");
	$default_title_approve = DI::l10n()->t('Registration Pending');
	$default_message_approve = DI::l10n()->t("Thank you for registering!\n\nAs soon as your registration has been approved by the administration, you will receive an email notification. Your login credentials have just been sent via email. Sometimes it takes a few minutes for this email to arrive. Please also check your spam folder.");

	if (trim($title_open) === '') {
		$title_open = $default_title_open;
	}
	if (trim($message_open) === '') {
		$message_open = $default_message_open;
	}
	if (trim($title_approve) === '') {
		$title_approve = $default_title_approve;
	}
	if (trim($message_approve) === '') {
		$message_approve = $default_message_approve;
	}

	$policy = Register::getPolicy();
	switch ($policy) {
		case Register::CLOSED:
			$status_text = DI::l10n()->t('Closed (users cannot register)');
			$status_class = 'danger';
			break;
		case Register::APPROVE:
			$status_text = DI::l10n()->t('Approval Required (using Approval Required settings)');
			$status_class = 'warning';
			break;
		case Register::OPEN:
			$status_text = DI::l10n()->t('Open Registration (using Open Registration settings)');
			$status_class = 'success';
			break;
		default:
			$status_text = DI::l10n()->t('Unknown');
			$status_class = 'info';
			break;
	}

	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/register_success/');
	$s .= Renderer::replaceMacros($t, [
		'$description' => DI::l10n()->t('Configure the custom messages shown to users after successful registration.'),
		'$preview_url' => DI::baseUrl() . '/register_success',
		'$preview_text' => DI::l10n()->t('Preview Success Page'),
		'$status_label' => DI::l10n()->t('Current Registration Policy'),
		'$status_text' => $status_text,
		'$status_class' => $status_class,
		'$header_open' => DI::l10n()->t('Open Registration settings'),
		'$title_open' => ['register_success-title-open', DI::l10n()->t('Success Page Title'), $title_open],
		'$message_open' => ['register_success-message-open', DI::l10n()->t('Success Page Message'), $message_open],
		'$header_approve' => DI::l10n()->t('Approval Required settings'),
		'$title_approve' => ['register_success-title-approve', DI::l10n()->t('Success Page Title'), $title_approve],
		'$message_approve' => ['register_success-message-approve', DI::l10n()->t('Success Page Message'), $message_approve],
		'$submit' => DI::l10n()->t('Save Settings')
	]);
}

/**
 * Handles the post request from the admin panel.
 */
function register_success_addon_admin_post(): void
{
	if (!DI::userSession()->isSiteAdmin()) {
		return;
	}

	if (!empty($_POST['register_success-submit'])) {
		// Limit Title to 200 characters and Message to 10000 characters
		$title_open = mb_substr(trim($_POST['register_success-title-open'] ?? ''), 0, 200);
		$message_open = mb_substr(trim($_POST['register_success-message-open'] ?? ''), 0, 10000);
		$title_approve = mb_substr(trim($_POST['register_success-title-approve'] ?? ''), 0, 200);
		$message_approve = mb_substr(trim($_POST['register_success-message-approve'] ?? ''), 0, 10000);

		DI::config()->set('register_success', 'title_open', $title_open);
		DI::config()->set('register_success', 'message_open', $message_open);
		DI::config()->set('register_success', 'title_approve', $title_approve);
		DI::config()->set('register_success', 'message_approve', $message_approve);
	}
}
