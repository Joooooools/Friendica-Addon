<?php

/**
 * Name: Account Import Guard
 * Description: Blocks unauthorized access to /user/import based on an administrative policy, while keeping account import available for authorized users. Also removes the public import link from the registration form.
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

function accountimportguard_install(): void
{
	Hook::register('init_1', __FILE__, 'accountimportguard_init');
	Hook::register('register_form', __FILE__, 'accountimportguard_register_form');
	Hook::register('page_end', __FILE__, 'accountimportguard_page_end');
}

function accountimportguard_uninstall(): void
{
	Hook::unregister('init_1', __FILE__, 'accountimportguard_init');
	Hook::unregister('register_form', __FILE__, 'accountimportguard_register_form');
	Hook::unregister('page_end', __FILE__, 'accountimportguard_page_end');
}

function accountimportguard_should_block_import(): bool
{
	$policy = DI::config()->get('accountimportguard', 'policy', 'users');

	if ($policy === 'all') {
		return true;
	}

	if ($policy === 'admin') {
		return !DI::userSession()->isSiteAdmin();
	}

	// 'users' (default)
	return !DI::userSession()->getLocalUserId();
}

function accountimportguard_init(): void
{
	if (trim(DI::args()->getCommand(), '/') !== 'user/import') {
		return;
	}

	if (!accountimportguard_should_block_import()) {
		return;
	}

	DI::logger()->notice('Blocked account import attempt based on policy.', [
		'policy' => DI::config()->get('accountimportguard', 'policy', 'users'),
		'command' => DI::args()->getCommand(),
	]);

	DI::sysmsg()->addNotice(DI::l10n()->t('Account import is not available.'));

	if (DI::userSession()->getLocalUserId()) {
		DI::baseUrl()->redirect('');
	} else {
		DI::baseUrl()->redirect('register');
	}
}

function accountimportguard_register_form(array &$data): void
{
	if (!accountimportguard_should_block_import()) {
		return;
	}

	if (empty($data['template']) || !is_string($data['template'])) {
		return;
	}

	$data['template'] = accountimportguard_remove_import_block($data['template']);
}

/**
 * Fallback removal on rendered /register output.
 *
 * Some themes may provide a slightly different register.tpl than the core template.
 * The register_form hook above is preferred, this page_end hook is a defensive
 * fallback so the visible link is removed even when the template markup differs.
 */
function accountimportguard_page_end(string &$html): void
{
	if (!accountimportguard_should_block_import()) {
		return;
	}

	if (trim(DI::args()->getCommand(), '/') !== 'register') {
		return;
	}

	$html = accountimportguard_remove_import_block($html);

	$result = preg_replace(
		'#\s*<h[1-6][^>]*>\s*[^<]*Import[^<]*\s*</h[1-6]>\s*<div\s+id\s*=\s*["\']import-profile["\'][^>]*>\s*<a\s+href\s*=\s*["\'][^"\']*user/import["\'][^>]*>.*?</a>\s*</div>\s*#is',
		"\n",
		$html
	);

	if (is_string($result)) {
		$html = $result;
	}

	$result = preg_replace(
		'#\s*<div\s+id\s*=\s*["\']import-profile["\'][^>]*>\s*<a\s+href\s*=\s*["\'][^"\']*user/import["\'][^>]*>.*?</a>\s*</div>\s*#is',
		"\n",
		$html
	);

	if (is_string($result)) {
		$html = $result;
	}
}

function accountimportguard_remove_import_block(string $markup): string
{
	$result = preg_replace(
		'#\s*\{\{if\s+!\$additional\}\}\s*<h[1-6][^>]*>\s*\{\{\$importh\}\}\s*</h[1-6]>\s*<div\s+id\s*=\s*["\']import-profile["\'][^>]*>\s*<a\s+href\s*=\s*["\']user/import["\'][^>]*>\s*\{\{\$importt\}\}\s*</a>\s*</div>\s*\{\{/if\}\}\s*#is',
		"\n",
		$markup
	);

	if (is_string($result)) {
		return $result;
	}

	return $markup;
}

function accountimportguard_addon_admin(string &$o): void
{
	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/accountimportguard/');

	$options = [
		'users' => DI::l10n()->t('Only logged-in users'),
		'admin' => DI::l10n()->t('Only site administrators'),
		'all' => DI::l10n()->t('Block for everyone, including administrators'),
	];

	$o = Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		'$policy' => [
			'policy',
			DI::l10n()->t('Account Import Policy'),
			DI::config()->get('accountimportguard', 'policy', 'users'),
			DI::l10n()->t('Select who is allowed to import accounts on this node.'),
			$options,
		],
	]);
}

function accountimportguard_addon_admin_post(): void
{
	$policy = trim((string) ($_POST['policy'] ?? 'users'));

	if (!in_array($policy, ['users', 'admin', 'all'], true)) {
		$policy = 'users';
	}

	DI::config()->set('accountimportguard', 'policy', $policy);
}
