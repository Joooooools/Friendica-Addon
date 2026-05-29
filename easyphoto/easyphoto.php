<?php

/**
 * Name: EasyPhoto
 * Description: Adds a simple image description editor below the post textarea for easier accessibility.
 * Version: 1.4
 * Author: Jools <https://friendica.de/profile/jools>
 * License: AGPL-3.0-or-later
 *
 * SPDX-FileCopyrightText: 2026 [Jools]
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Friendica\Core\Hook;
use Friendica\DI;

define('EASYPHOTO_VERSION', '1.4');

function easyphoto_install()
{
	// Documented Friendica convention: register stylesheet via the 'head' hook
	// and deferred script via the 'footer' hook.
	Hook::register('head', __FILE__, 'easyphoto_head');
	Hook::register('footer', __FILE__, 'easyphoto_footer');
}

function easyphoto_uninstall()
{
	Hook::unregister('head', __FILE__, 'easyphoto_head');
	Hook::unregister('footer', __FILE__, 'easyphoto_footer');
}

/**
 * Builds the <head> section: stylesheet + the small l10n bootstrap object the
 * script needs. We only emit anything for authenticated users.
 */
function easyphoto_head(string &$b)
{
	if (easyphoto_quickphoto_active()) {
		if (DI::args()->getModuleName() === 'admin' && DI::userSession()->isSiteAdmin()) {
			DI::sysmsg()->addNotice(DI::l10n()->t('Conflict detected: both EasyPhoto and QuickPhoto are enabled. EasyPhoto is temporarily inactive to prevent issues. Please disable QuickPhoto if you want to use EasyPhoto.'));
		}
		return;
	}

	if (!DI::userSession()->isAuthenticated()) {
		return;
	}

	// Register the stylesheet through Friendica's asset system (handles paths,
	// subfolder installs and cache busting).
	DI::page()->registerStylesheet(__DIR__ . '/easyphoto.css');

	$l10n = [
		'image'       => DI::l10n()->t('Image'),
		'placeholder' => DI::l10n()->t('Enter image description here...'),
		'privacy'     => DI::l10n()->t('External image (privacy protection)'),
	];

	// JSON_HEX_TAG additionally protects against </script> injection in translation strings.
	$l10nJson = json_encode($l10n, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

	// The l10n object must exist before the script runs, so it goes into <head>.
	$b .= "\n" . '<script type="text/javascript">window.easyphoto_l10n = ' . $l10nJson . ';</script>';
}

/**
 * Registers the deferred script in the footer (documented Friendica convention).
 */
function easyphoto_footer(string &$b)
{
	if (easyphoto_quickphoto_active()) {
		return;
	}

	if (!DI::userSession()->isAuthenticated()) {
		return;
	}

	DI::page()->registerFooterScript(__DIR__ . '/easyphoto.js');
}

/**
 * Admin settings callback. Shows a warning or status message in the admin panel.
 */
function easyphoto_addon_admin(string &$o): void
{
	if (easyphoto_quickphoto_active()) {
		$o = '<div class="alert alert-danger"><strong>' . DI::l10n()->t('Warning') . ':</strong> ' .
			DI::l10n()->t('The "QuickPhoto" addon is also enabled. Both addons cannot be active at the same time. EasyPhoto is temporarily inactive to avoid conflicts. Please deactivate QuickPhoto if you want to use EasyPhoto.') .
			'</div>';
		return;
	}

	$o = '<div class="alert alert-success">' . DI::l10n()->t('EasyPhoto is ready. No conflicts detected.') . '</div>';
}

/**
 * Helper function to safely detect if the QuickPhoto addon is active.
 * Uses fallback methods to prevent Fatal Errors on older Friendica versions.
 */
function easyphoto_quickphoto_active(): bool
{
	try {
		if (method_exists(DI::class, 'addonHelper')) {
			$helper = DI::addonHelper();
			if (method_exists($helper, 'isAddonEnabled')) {
				return $helper->isAddonEnabled('quickphoto');
			}
		}

		if (class_exists('Friendica\Core\Addon') && method_exists('Friendica\Core\Addon', 'isEnabled')) {
			return \Friendica\Core\Addon::isEnabled('quickphoto');
		}
	} catch (\Throwable $e) {
		// Suppress potential exceptions and assume no conflict to avoid system crashes.
	}

	return false;
}
