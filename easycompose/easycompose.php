<?php

/**
 * Name: EasyCompose
 * Description: A privacy-focused, client-side writing and accessibility assistant (optimized for desktop viewports). All analysis is performed locally in the user's browser, with no external services or third-party tracking. Preview rendering utilizes the standard Friendica preview route.
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

/**
 * Registers the addon hooks.
 */
function easycompose_install(): void
{
	Hook::register('jot_tool', __FILE__, 'easycompose_jot_tool');
	Hook::register('addon_settings', __FILE__, 'easycompose_addon_settings');
	Hook::register('addon_settings_post', __FILE__, 'easycompose_addon_settings_post');
}

/**
 * Unregisters the addon hooks.
 */
function easycompose_uninstall(): void
{
	Hook::unregister('jot_tool', __FILE__, 'easycompose_jot_tool');
	Hook::unregister('addon_settings', __FILE__, 'easycompose_addon_settings');
	Hook::unregister('addon_settings_post', __FILE__, 'easycompose_addon_settings_post');
}

/**
 * Hook callback: Injects client-side assets and localizations into the editor.
 * Only active on the standalone /compose page.
 */
function easycompose_jot_tool(string &$body): void
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'easycompose', 'disable')) {
		return;
	}

	if (DI::router()->getModuleClass() !== \Friendica\Module\Item\Compose::class) {
		return;
	}

	// Register our styles with a reliable cache-buster based on file modification time
	$css_file = __DIR__ . '/css/easycompose.css';
	$css_version = file_exists($css_file) ? filemtime($css_file) : '1.0';
	DI::page()->registerStylesheet(DI::baseUrl() . '/addon/easycompose/css/easycompose.css?v=' . $css_version);

	// Localized strings passed to client-side JS
	$l10n = [
		'title' => DI::l10n()->t('EasyCompose'),
		'subtitle' => DI::l10n()->t('Writing & Accessibility Assistant'),
		'structureTitle' => DI::l10n()->t('Structure & Balance'),
		'a11yTitle' => DI::l10n()->t('Accessibility Checklist'),
		'readabilityTitle' => DI::l10n()->t('Readability & Style'),

		'lblParagraphs' => DI::l10n()->t('Paragraph Structure'),
		'lblSentenceLength' => DI::l10n()->t('Text Balance'),
		'lblLinks' => DI::l10n()->t('Link Density'),
		'lblHashtags' => DI::l10n()->t('Hashtag Density'),

		'lblParaBalanced' => DI::l10n()->t('Balanced'),
		'lblParaOneBlock' => DI::l10n()->t('One block'),
		'lblParaCompact' => DI::l10n()->t('Very compact'),
		'lblParaStructured' => DI::l10n()->t('Well structured'),
		'lblParaShort' => DI::l10n()->t('Short post'),

		'lblBalanceEasy' => DI::l10n()->t('Easy to read'),
		'lblBalanceNested' => DI::l10n()->t('Complex'),
		'lblBalanceMedium' => DI::l10n()->t('Medium'),

		'lblLinkSubtle' => DI::l10n()->t('Subtle'),
		'lblLinkDense' => DI::l10n()->t('Very dense'),
		'lblLinkMany' => DI::l10n()->t('Many links'),

		'lblHashtagSubtle' => DI::l10n()->t('Subtle'),
		'lblHashtagDense' => DI::l10n()->t('Very dense'),
		'lblHashtagMany' => DI::l10n()->t('Many tags'),

		'a11yAltOk' => DI::l10n()->t('All images have descriptions (Alt-Text)'),
		'a11yAltWarn' => DI::l10n()->t('Images missing descriptions (Alt-Text) detected!'),
		'a11yEmojiOk' => DI::l10n()->t('No emoji overload (max 4 consecutive)'),
		'a11yEmojiWarn' => DI::l10n()->t('Emoji overload detected (hurts screen readers)'),
		'a11yParagraphOk' => DI::l10n()->t('Good text structuring (paragraphs used)'),
		'a11yParagraphWarn' => DI::l10n()->t('No paragraphs found (hard to read)'),
		'a11yParagraphNeutral' => DI::l10n()->t('No paragraphs required for short texts'),

		'tipExcellent' => DI::l10n()->t('Your post is beautifully readable and accessible!'),
		'tipNoParagraphs' => DI::l10n()->t('Tip: Adding double line breaks to create paragraphs makes long texts much easier to scan.'),
		'tipLongSentences' => DI::l10n()->t('Tip: Some sentences are very long (> 25 words). Shortening them increases reading flow!'),
		'tipShouting' => DI::l10n()->t('Tip: Typing in ALL CAPS feels like shouting. Consider using standard capitalization.'),
		'tipTooManyHashtags' => DI::l10n()->t('Tip: Too many hashtags make the text feel restless. Try to focus on the key ones.'),
		'tipEmojiFlood' => DI::l10n()->t('Tip: Emoji clusters can be disruptive for visitors using assistive technology.'),
		'tipMissingAlt' => DI::l10n()->t('Tip: An image is missing a description (Alt-Text). Adding a brief text ensures everyone can participate!'),
		'tipTooLong' => DI::l10n()->t('Tip: Text is extremely long. Real-time analysis paused for performance.'),
		'previewTooLongTitle' => DI::l10n()->t('Preview Paused'),
		'previewTooLongDesc' => DI::l10n()->t('The post is extremely long. The live preview has been paused to keep your browser tab responsive.'),
		'chars' => DI::l10n()->t('characters'),
		'distractionFree' => DI::l10n()->t('Distraction-free mode'),
		'btnAssistant' => DI::l10n()->t('Writing Assistant'),
		'btnZen' => DI::l10n()->t('Distraction-Free'),
		'btnEpZen' => DI::l10n()->t('Image Descriptions'),
		'btnPreview' => DI::l10n()->t('Preview'),
		'btnRefresh' => DI::l10n()->t('Refresh Preview'),
		'btnPublish' => DI::l10n()->t('Publish'),
		'btnFocusPreview' => DI::l10n()->t('Focus Preview'),
		'btnMobile' => DI::l10n()->t('Mobile'),
		'btnBackToEditor' => DI::l10n()->t('Back to Editor'),
		'btnLoadingPreview' => DI::l10n()->t('Loading Preview...'),
		'previewTimestamp' => DI::l10n()->t('Just now · Preview'),
		'lblYou' => DI::l10n()->t('You'),
		'brandText' => DI::l10n()->t('Addon: EasyCompose (deactivatable in settings)'),

		// Help & Privacy panel
		'helpToggleLabel' => DI::l10n()->t('How does the analysis work?'),
		'helpPrivacyBadge' => DI::l10n()->t('No external services · All processing on your own instance'),
		'helpPrivacyDetail' => DI::l10n()->t('Text analysis runs entirely in your browser — no data is sent anywhere for analysis. No third-party APIs, no cookies, no tracking. The optional post preview works exactly like Friendica\'s built-in preview button: your draft is sent to your own Friendica instance for rendering, just as it would be without this addon. The only server-side storage is your personal enable/disable preference in the standard Friendica settings.'),

		'helpParaTitle' => DI::l10n()->t('Paragraph Structure'),
		'helpParaBody' => DI::l10n()->t('Counts how many paragraphs your post contains (separated by blank lines). A single unbroken block of text scores low (30 %) because readers find it hard to scan. Two or more paragraphs score 100 %. Posts under 600 characters are always rated "Short post" regardless of structure.'),

		'helpBalanceTitle' => DI::l10n()->t('Text Balance'),
		'helpBalanceBody' => DI::l10n()->t('Measures average sentence length and flags sentences longer than 25 words. An average above 24 words scores 40 % ("Complex"), above 16 words scores 75 % ("Medium"), everything else scores 100 % ("Easy to read"). Shorter sentences improve readability for all audiences.'),

		'helpLinkTitle' => DI::l10n()->t('Link Density'),
		'helpLinkBody' => DI::l10n()->t('Counts all http/https URLs in your post. More than 5 links scores 30 % ("Very dense") — posts that are mostly links feel like spam to readers and federation filters. Up to 3 links scores 100 % ("Subtle").'),

		'helpHashtagTitle' => DI::l10n()->t('Hashtag Density'),
		'helpHashtagBody' => DI::l10n()->t('Counts #hashtags. More than 6 scores 30 % ("Very dense"). Posts with fewer, focused hashtags reach more people than hashtag-stuffed ones. Up to 3 hashtags scores 100 % ("Subtle").'),

		'helpAltTitle' => DI::l10n()->t('Alt-Text Check'),
		'helpAltBody' => DI::l10n()->t('Checks every [img] BBCode tag in your post for an alt text description. Alt text is essential for screen readers and users with visual impairments — it ensures everyone can participate in the conversation.'),

		'helpEmojiTitle' => DI::l10n()->t('Emoji Check'),
		'helpEmojiBody' => DI::l10n()->t('Detects sequences of 5 or more consecutive emoji. Screen readers read every emoji aloud by name ("grinning face", "thumbs up" …), so a long cluster becomes an exhausting wall of words for blind users. Up to 4 consecutive emoji is fine.'),

		'helpParagraphA11yTitle' => DI::l10n()->t('Paragraph Check (Accessibility)'),
		'helpParagraphA11yBody' => DI::l10n()->t('For posts longer than 300 characters, checks whether at least one paragraph break exists. Screen readers and cognitive-accessibility tools benefit greatly from structured text. Short posts are always rated neutral.'),
	];

	$js_file = __DIR__ . '/easycompose.js';
	$js_version = file_exists($js_file) ? filemtime($js_file) : '1.0';
	$js_url = DI::baseUrl() . '/addon/easycompose/easycompose.js?v=' . $js_version;
	$l10n_json = json_encode($l10n, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

	// Register JS asset using standard Friendica Page API
	DI::page()->registerFooterScript($js_url);

	// Inject toggle button inside the plugin wrapper in the editor toolbar,
	// and the localization maps alongside the main JS bundle.
	// NOTE for developers: The following SVGs are hardcoded static markup assets
	// and contain no user input, so these specific nodes introduce no XSS vector.
	// This does NOT make the addon XSS-immune as a whole: the focus-preview feature
	// copies Friendica's already-rendered preview DOM and relies entirely on the
	// server-side sanitization of the /compose preview route (see README).
	$button_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-feather"><path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z"></path><line x1="16" y1="8" x2="2" y2="22"></line><line x1="17.5" y1="15" x2="9" y2="15"></line></svg>';

	$distraction_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-maximize-2"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>';

	$btn_assistant = htmlspecialchars($l10n['btnAssistant'], ENT_QUOTES, 'UTF-8');
	$btn_zen = htmlspecialchars($l10n['btnZen'], ENT_QUOTES, 'UTF-8');

	$body .= <<<EOT
<div id="easy-compose-btn-wrapper">
	<button type="button" class="btn easy-compose-btn" id="easy-compose-toggle">
		{$button_svg} <span class="ec-btn-text">{$btn_assistant}</span>
	</button>
	<button type="button" class="btn easy-compose-btn" id="easy-compose-distraction-toggle">
		{$distraction_svg} <span class="ec-btn-text">{$btn_zen}</span>
	</button>
</div>
<script>
window.EasyComposeL10n = {$l10n_json};
</script>
EOT;
}

/**
 * Hook callback: Render user settings for EasyCompose under settings/addons.
 */
function easycompose_addon_settings(array &$data): void
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$disable = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'easycompose', 'disable');
	// Note: escaping and HTML structure are handled by settings.tpl (Smarty auto-escape)
	$compose_url = DI::baseUrl() . '/compose';

	$t = Renderer::getMarkupTemplate('settings.tpl', 'addon/easycompose/');
	$html = Renderer::replaceMacros($t, [
		'$enabled' => [
			'easycompose-enabled',
			DI::l10n()->t('Enable EasyCompose'),
			!$disable,
			''
		],
		'$compose_url' => $compose_url,
		'$desc_part1' => DI::l10n()->t('This addon extends the editor at'),
		'$desc_part2' => DI::l10n()->t('with a custom layout and additional text analysis tools. While writing, the integrated assistant checks readability (e.g., sentence length and paragraphs) and accessibility (e.g., missing image descriptions). It also provides a manual post preview and a distraction-free mode.'),
		'$desc_mobile' => DI::l10n()->t('Please note: EasyCompose is designed and optimized exclusively for desktop viewports. To ensure a clean and clutter-free mobile editor, all assistant panels and buttons are automatically hidden on smartphones and tablets.'),
		'$desc_part3' => DI::l10n()->t('The addon operates autonomously: all text analysis is performed locally in the user\'s browser, no external scripts or resources are loaded, and no data is sent to external services for analysis.'),
		'$desc_part4' => DI::l10n()->t('If you deactivate this addon, the standard Friendica editor will be restored at')
	]);

	$data = [
		'addon' => 'easycompose',
		'title' => DI::l10n()->t('EasyCompose (Advanced Editor)'),
		'html' => $html,
	];
}

/**
 * Hook callback: Process user settings form submission.
 */
function easycompose_addon_settings_post(array &$b): void
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (!empty($_POST['easycompose-submit'])) {
		$enabled = !empty($_POST['easycompose-enabled']) ? intval($_POST['easycompose-enabled']) : 0;
		if ($enabled) {
			DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'easycompose', 'disable');
		} else {
			DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'easycompose', 'disable', 1);
		}
	}
}
