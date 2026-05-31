<?php

/**
 * Name: Authbanner
 * Description: Displays the profile banner for logged-in users (without the ability to upload your own banner). Based on the addon Coverphoto by Random Penguin and the modified addon by feb.
 * Version: 1.3
 * Author: Jools <https://friendica.de/profile/jools>
 * License: AGPL-3.0-or-later
 *
 * Authbanner is based on the "Coverphoto" add-on by Random Penguin and the modified add-on by feb.
 */

use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Model\Contact;
use Friendica\Model\User;

function authbanner_install()
{
	Hook::register('page_content_top', __FILE__, 'authbanner_show_on_profile');
	Hook::register('addon_settings', __FILE__, 'authbanner_addon_settings');
	Hook::register('addon_settings_post', __FILE__, 'authbanner_addon_settings_post');
}

function authbanner_uninstall()
{
	Hook::unregister('page_content_top', __FILE__, 'authbanner_show_on_profile');
	Hook::unregister('addon_settings', __FILE__, 'authbanner_addon_settings');
	Hook::unregister('addon_settings_post', __FILE__, 'authbanner_addon_settings_post');
}

function authbanner_show_on_profile(&$html)
{
	if (authbanner_coverphoto_active()) {
		if (str_starts_with(DI::router()->getModuleClass(), 'Friendica\\Module\\Admin') && DI::userSession()->isSiteAdmin()) {
			DI::sysmsg()->addNotice(DI::l10n()->t('Conflict detected: both AuthBanner and CoverPhoto are enabled. AuthBanner is temporarily inactive to prevent issues. Please disable CoverPhoto if you want to use AuthBanner.'));
		}
		return;
	}

	$uid = DI::userSession()->getLocalUserId();
	if (!$uid || DI::pConfig()->get($uid, 'authbanner', 'disable')) {
		return;
	}

	$pagename = DI::args()->get(0);
	$allowed_pages = ["profile", "calendar", "notes", "contact"];

	if (in_array($pagename, $allowed_pages)) {
		$owner = DI::appHelper()->getProfileOwner();
		if ($owner == 0) {
			$owner = $uid;
		}

		$profile = ['header' => ''];
		try {
			if ($pagename == "contact") {
				$contact_id = (int) DI::args()->get(1);
				$profile = $contact_id ? Contact::selectFirst(['header'], ['id' => $contact_id, 'uid' => [0, $uid]]) : ['header' => ''];
			} else {
				$profile = User::getOwnerDataById($owner, false);
			}
		} catch (\Throwable $e) {
			DI::logger()->error('Error fetching profile header in AuthBanner: ' . $e->getMessage(), ['exception' => $e]);
		}

		if (!is_array($profile)) {
			$profile = ['header' => ''];
		}

		if (!empty($profile['header'])) {
			$header_url = $profile['header'];
			// Strict URL validation: only allow HTTPS starting with https:// or relative starting with / (excluding //)
			if (str_starts_with(strtolower($header_url), 'https://') || (str_starts_with($header_url, '/') && !str_starts_with($header_url, '//'))) {
				DI::page()->registerStylesheet(__DIR__ . '/authbanner.css');
				$banner_html = '
                        <div id="authbanner-standard-wrapper">
                                <img src="' . htmlspecialchars($header_url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" alt="' . htmlspecialchars(DI::l10n()->t('Profile banner'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" decoding="async" referrerpolicy="no-referrer" />
                        </div>';

				$html = $banner_html . $html;
			}
		}
	}
}

/**
 * Admin settings callback. Shows a warning or status message in the admin panel.
 */
function authbanner_addon_admin(string &$o): void
{
	if (authbanner_coverphoto_active()) {
		$o = '<div class="alert alert-danger"><strong>' . htmlspecialchars(DI::l10n()->t('Warning'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ':</strong> ' .
			htmlspecialchars(DI::l10n()->t('The "CoverPhoto" addon is also enabled. Both addons cannot be active at the same time. AuthBanner is temporarily inactive to avoid conflicts. Please deactivate CoverPhoto if you want to use AuthBanner.'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') .
			'</div>';
		return;
	}

	$o = '<div class="alert alert-success">' . htmlspecialchars(DI::l10n()->t('AuthBanner is ready. No conflicts detected.'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
}

/**
 * Helper function to safely detect if the CoverPhoto addon is active.
 * Uses fallback methods to prevent Fatal Errors on older Friendica versions.
 */
function authbanner_coverphoto_active(): bool
{
	try {
		if (method_exists(DI::class, 'addonHelper')) {
			$helper = DI::addonHelper();
			if (method_exists($helper, 'isAddonEnabled')) {
				return $helper->isAddonEnabled('coverphoto');
			}
		}

		$legacyClass = 'Friendica\\Core\\Addon';
		if (class_exists($legacyClass) && method_exists($legacyClass, 'isEnabled')) {
			return call_user_func([$legacyClass, 'isEnabled'], 'coverphoto');
		}
	} catch (\Throwable $e) {
		DI::logger()->error('Error checking if CoverPhoto is active: ' . $e->getMessage(), ['exception' => $e]);
	}

	return false;
}

/**
 * Hook callback: Render user settings for AuthBanner under settings/addons.
 */
function authbanner_addon_settings(array &$data): void
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$disable = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'authbanner', 'disable');

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/authbanner/');
	$html = Renderer::replaceMacros($t, [
		'$enabled' => [
			'authbanner-enabled',
			DI::l10n()->t('Enable AuthBanner'),
			!$disable,
			DI::l10n()->t('Show the profile banner for logged-in users.')
		],
		'$description' => DI::l10n()->t('This addon displays already existing profile banners (e.g. from Friendica, Mastodon, Bluesky, Sharkey, Calckey, Hubzilla, Tumblr, etc.) on user profiles. To keep it lightweight, this addon does not support uploading banners directly; you can easily upload or change your profile banner using an external client app (like Mona, Tusky, etc.).'),
	]);

	$data = [
		'addon' => 'authbanner',
		'title' => DI::l10n()->t('AuthBanner Settings'),
		'html'  => $html,
	];
}

/**
 * Hook callback: Process user settings form submission.
 */
function authbanner_addon_settings_post(array &$b): void
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (!empty($_POST['authbanner-submit'])) {
		$enabled = !empty($_POST['authbanner-enabled']) ? intval($_POST['authbanner-enabled']) : 0;
		if ($enabled) {
			DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'authbanner', 'disable');
		} else {
			DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'authbanner', 'disable', 1);
		}
	}
}
