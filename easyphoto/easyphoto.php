<?php

/**
 * Name: EasyPhoto
 * Description: Adds a simple image description editor below the post textarea for easier accessibility.
 * Version: 1.3
 * Author: Jools <https://friendica.de/profile/jools>
 * License: AGPL-3.0-or-later
 *
 * SPDX-FileCopyrightText: 2026 [Jools]
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Friendica\Core\Hook;
use Friendica\DI;

define('EASYPHOTO_VERSION', '1.3');

function easyphoto_install()
{
	// Back to the proven page_header hook for optimal loading sequence and timing.
	Hook::register('page_header', 'addon/easyphoto/easyphoto.php', 'easyphoto_header');
}

function easyphoto_uninstall()
{
	Hook::unregister('page_header', 'addon/easyphoto/easyphoto.php', 'easyphoto_header');
}

function easyphoto_header(&$header)
{
	if (!DI::userSession()->isAuthenticated()) {
		return;
	}

	$l10n = [
		'image' => DI::l10n()->t('Image'),
		'placeholder' => DI::l10n()->t('Enter image description here...'),
		'privacy' => DI::l10n()->t('External image (privacy protection)'),
	];

	$baseUrl = htmlspecialchars(DI::baseUrl(), ENT_QUOTES, 'UTF-8');

	// JSON_HEX_TAG additionally protects against </script> injection in translation strings.
	$l10nJson = json_encode($l10n, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

	// We use DI::baseUrl() for the paths to ensure compatibility with subfolder installations,
	// but we inject the tags directly into the header string to ensure the MutationObserver
	// starts early enough and catches all textareas (including those added via AJAX early on).
	$header .= "\n" . '<script type="text/javascript">const easyphoto_l10n = ' . $l10nJson . ';</script>';
	$header .= "\n" . '<link rel="stylesheet" href="' . $baseUrl . '/addon/easyphoto/easyphoto.css?v=' . EASYPHOTO_VERSION . '" />';
	$header .= "\n" . '<script type="text/javascript" src="' . $baseUrl . '/addon/easyphoto/easyphoto.js?v=' . EASYPHOTO_VERSION . '"></script>';
}
