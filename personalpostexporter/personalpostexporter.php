<?php
/**
 * Name: PersonalPostExporter
 * Description: Export user's own posts as a standalone HTML archive.
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
use Friendica\Database\DBA;
use Friendica\Addon\PersonalPostExporter\Exporter;

// Load the logic class manually since we are not using Composer autoloading for this addon
require_once __DIR__ . '/src/Exporter.php';

/**
 * Install the addon
 */
function personalpostexporter_install()
{
    Hook::register('addon_settings', __FILE__, 'personalpostexporter_addon_settings');
    Hook::register('addon_settings_post', __FILE__, 'personalpostexporter_addon_settings_post');
    DI::logger()->notice('PersonalPostExporter Addon installed');
}

/**
 * Uninstall the addon
 */
function personalpostexporter_uninstall()
{
    Hook::unregister('addon_settings', __FILE__, 'personalpostexporter_addon_settings');
    Hook::unregister('addon_settings_post', __FILE__, 'personalpostexporter_addon_settings_post');

    // Clean up our lock directory for tidiness
    $lock_path = Exporter::getLockPath();
    if (is_dir($lock_path)) {
        $files = glob($lock_path . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        @rmdir($lock_path);
    }
}

/**
 * Display the addon settings (Modular Template UI)
 */
function personalpostexporter_addon_settings(array &$data)
{
    if (!DI::userSession()->getLocalUserId()) {
        return;
    }

    DI::page()->registerStylesheet('addon/personalpostexporter/personalpostexporter.css');

    $user_raw = DBA::selectFirst('user', ['register_date'], ['uid' => DI::userSession()->getLocalUserId()]);
    $reg_date_raw = $user_raw['register_date'] ?? date('Y-m-d');

    // Standard Friendica date format for the locale
    $date_format = DI::l10n()->t('Y-m-d');

    try {
        $reg_dt = new \DateTime($reg_date_raw);
        $reg_year = (int) $reg_dt->format('Y');
        $reg_formatted = $reg_dt->format($date_format);
    } catch (\Exception $e) {
        $reg_year = (int) date('Y');
        $reg_formatted = date($date_format);
    }

    $exporter = new Exporter();
    $locked_slots = $exporter->getLockedSlots();
    $max_slots = 2;

    if ($locked_slots >= $max_slots) {
        DI::sysmsg()->addNotice(DI::l10n()->t('Maximum number of concurrent exports reached. Please wait about 20 minutes.'));
    }

    // Prepare macros for fields
    $media_config = [
        'personalpostexporter-media',
        DI::l10n()->t('Image Display'),
        'link',
        DI::l10n()->t('Choose display style. Images are <strong>NOT</strong> bundled into the ZIP file!'),
        [
            'link' => DI::l10n()->t('Display images directly (Load from server)'),
            'placeholder' => DI::l10n()->t('Links only (Show placeholders)'),
        ]
    ];

    $theme_config = [
        'personalpostexporter-theme',
        DI::l10n()->t('Archive Theme'),
        'light',
        DI::l10n()->t('Select the visual style for your archive.'),
        [
            'light' => DI::l10n()->t('Light'),
            'dark' => DI::l10n()->t('Dark'),
        ]
    ];

    $years = ['all' => DI::l10n()->t('All Time')];
    $current_year = (int) date('Y');
    for ($y = $current_year; $y >= $reg_year; $y--) {
        if ($y < $current_year - 10) {
            $years['older'] = DI::l10n()->t('Older than 10 years');
            break;
        }
        $years[(string) $y] = (string) $y;
    }

    $year_config = [
        'personalpostexporter-year',
        DI::l10n()->t('Export Period'),
        'all',
        DI::l10n()->t('Select the time range for your export.'),
        $years
    ];

    // Load main UI template
    $t = Renderer::getMarkupTemplate('settings.tpl', 'addon/personalpostexporter/');
    $html = Renderer::replaceMacros($t, [
        '$description' => DI::l10n()->t('With this exporter, you can download your own archive from your Friendica instance. You will receive a ZIP file containing your posts arranged in a blog-like style. A search function is also included.'),
        '$important_label' => DI::l10n()->t('IMPORTANT:'),
        '$importance_warning' => DI::l10n()->t('This archive is for viewing and searching only. It is not a technical backup and cannot be used to re-import your posts into another Friendica instance if you move your account.'),
        '$info_title' => DI::l10n()->t('Important information about your export:'),
        '$label_exported' => DI::l10n()->t('What is exported?'),
        '$text_exported' => DI::l10n()->t('Your posts (your wall), including your private posts.'),
        '$label_private' => DI::l10n()->t('private posts'),
        '$label_excluded' => DI::l10n()->t('What is excluded?'),
        '$text_excluded_1' => DI::l10n()->t('Comments, likes, shares, and other social interactions are'),
        '$text_excluded_not' => DI::l10n()->t('not'),
        '$text_excluded_2' => DI::l10n()->t('included in the export.'),
        '$label_privacy' => DI::l10n()->t('Privacy:'),
        '$text_privacy' => DI::l10n()->t('Private posts are clearly marked in your archive with a lock icon (🔒).'),
        '$label_images' => DI::l10n()->t('Image Warning:'),
        '$text_images_1' => DI::l10n()->t('Images are'),
        '$text_images_never' => DI::l10n()->t('NEVER'),
        '$text_images_2' => DI::l10n()->t('bundled into the ZIP file. They remain on the server and are only linked.'),
        '$label_how' => DI::l10n()->t('How to use?'),
        '$text_how_1' => DI::l10n()->t('Unzip the file and open'),
        '$text_how_code' => 'index.html',
        '$text_how_2' => DI::l10n()->t('with a double-click. Any web browser works.'),
        '$busy_label' => DI::l10n()->t('System Busy:'),
        '$busy_alert' => ($locked_slots >= $max_slots) ? DI::l10n()->t('Maximum number of concurrent exports reached. Please wait about 20 minutes.') : '',
        '$member_since' => DI::l10n()->t('Member since:'),
        '$reg_date' => $reg_formatted,
        '$media_config' => $media_config,
        '$theme_config' => $theme_config,
        '$year_config' => $year_config,
        '$export_notice' => DI::l10n()->t('Please leave this window open during the export. For very large archives, the process might timeout; in that case, please try exporting year by year. Once you click the button, please wait a moment for the process to start.'),
    ]);

    $data = [
        'addon' => 'personalpostexporter',
        'title' => DI::l10n()->t('Personal Post Exporter'),
        'html' => $html,
    ];

    if ($locked_slots < $max_slots) {
        $data['submit'] = DI::l10n()->t('Start Export');
    }
}

/**
 * Handle the button click
 */
function personalpostexporter_addon_settings_post(array &$b)
{
    if (!DI::userSession()->getLocalUserId() || empty($_POST['personalpostexporter-submit'])) {
        return;
    }

    $media = in_array($_POST['personalpostexporter-media'] ?? '', ['link', 'placeholder']) ? $_POST['personalpostexporter-media'] : 'link';
    $theme = in_array($_POST['personalpostexporter-theme'] ?? '', ['light', 'dark']) ? $_POST['personalpostexporter-theme'] : 'light';
    $year_raw = $_POST['personalpostexporter-year'] ?? 'all';
    $year = ($year_raw === 'all' || $year_raw === 'older' || ctype_digit($year_raw)) ? $year_raw : 'all';

    $exporter = new Exporter();
    if ($exporter->getLockedSlots() >= 2) {
        DI::sysmsg()->addNotice(DI::l10n()->t('System busy. Please try again later.'));
        return;
    }

    if (!$exporter->run($media, $year, $theme)) {
        DI::sysmsg()->addNotice(DI::l10n()->t('No posts found for the selected period.'));
    }
}
