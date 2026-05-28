<?php

if (!function_exists('string_plural_select_en')) {
	function string_plural_select_en($n)
	{
		$n = intval($n);
		return intval($n != 1);
	}
}
$a->strings['EasyCompose'] = 'EasyCompose';
$a->strings['Writing & Accessibility Assistant'] = 'Writing & Accessibility Assistant';
$a->strings['Structure & Balance'] = 'Structure & Balance';
$a->strings['Accessibility Checklist'] = 'Accessibility Checklist';
$a->strings['Readability & Style'] = 'Readability & Style';
$a->strings['Paragraph Structure'] = 'Paragraph Structure';
$a->strings['Text Balance'] = 'Text Balance';
$a->strings['Link Density'] = 'Link Density';
$a->strings['Hashtag Density'] = 'Hashtag Density';
$a->strings['Balanced'] = 'Balanced';
$a->strings['One block'] = 'One block';
$a->strings['Very compact'] = 'Very compact';
$a->strings['Well structured'] = 'Well structured';
$a->strings['Short post'] = 'Short post';
$a->strings['Easy to read'] = 'Easy to read';
$a->strings['Complex'] = 'Complex';
$a->strings['Medium'] = 'Medium';
$a->strings['Subtle'] = 'Subtle';
$a->strings['Very dense'] = 'Very dense';
$a->strings['Many links'] = 'Many links';
$a->strings['Many tags'] = 'Many tags';
$a->strings['All images have descriptions (Alt-Text)'] = 'All images have descriptions (Alt-Text)';
$a->strings['Images missing descriptions (Alt-Text) detected!'] = 'Images missing descriptions (Alt-Text) detected!';
$a->strings['No emoji overload (max 4 consecutive)'] = 'No emoji overload (max 4 consecutive)';
$a->strings['Emoji overload detected (hurts screen readers)'] = 'Emoji overload detected (hurts screen readers)';
$a->strings['Good text structuring (paragraphs used)'] = 'Good text structuring (paragraphs used)';
$a->strings['No paragraphs found (hard to read)'] = 'No paragraphs found (hard to read)';
$a->strings['No paragraphs required for short texts'] = 'No paragraphs required for short texts';
$a->strings['Your post is beautifully readable and accessible!'] = 'Your post is beautifully readable and accessible!';
$a->strings['Tip: Adding double line breaks to create paragraphs makes long texts much easier to scan.'] = 'Tip: Adding double line breaks to create paragraphs makes long texts much easier to scan.';
$a->strings['Tip: Some sentences are very long (> 25 words). Shortening them increases reading flow!'] = 'Tip: Some sentences are very long (> 25 words). Shortening them increases reading flow!';
$a->strings['Tip: Typing in ALL CAPS feels like shouting. Consider using standard capitalization.'] = 'Tip: Typing in ALL CAPS feels like shouting. Consider using standard capitalization.';
$a->strings['Tip: Too many hashtags make the text feel restless. Try to focus on the key ones.'] = 'Tip: Too many hashtags make the text feel restless. Try to focus on the key ones.';
$a->strings['Tip: Emoji clusters can be disruptive for visitors using assistive technology.'] = 'Tip: Emoji clusters can be disruptive for visitors using assistive technology.';
$a->strings['Tip: An image is missing a description (Alt-Text). Adding a brief text ensures everyone can participate!'] = 'Tip: An image is missing a description (Alt-Text). Adding a brief text ensures everyone can participate!';
$a->strings['Tip: Text is extremely long. Real-time analysis paused for performance.'] = 'Tip: Text is extremely long. Real-time analysis paused for performance.';
$a->strings['Preview Paused'] = 'Preview Paused';
$a->strings['The post is extremely long. The live preview has been paused to keep your browser tab responsive.'] = 'The post is extremely long. The live preview has been paused to keep your browser tab responsive.';
$a->strings['characters'] = 'characters';
$a->strings['Distraction-free mode'] = 'Distraction-free mode';
$a->strings['Writing Assistant'] = 'Writing Assistant';
$a->strings['Distraction-Free'] = 'Distraction-Free';
$a->strings['Image Descriptions'] = 'Image Descriptions';
$a->strings['Preview'] = 'Preview';
$a->strings['Refresh Preview'] = 'Refresh Preview';
$a->strings['Publish'] = 'Publish';
$a->strings['Focus Preview'] = 'Focus Preview';
$a->strings['Mobile'] = 'Mobile';
$a->strings['Back to Editor'] = 'Back to Editor';
$a->strings['Loading Preview...'] = 'Loading Preview...';
$a->strings['Just now · Preview'] = 'Just now · Preview';
$a->strings['You'] = 'You';

$a->strings['How does the analysis work?'] = 'How does the analysis work?';
$a->strings['No external services · All processing on your own instance'] = 'No external services · All processing on your own instance';
$a->strings['Text analysis runs entirely in your browser — no data is sent anywhere for analysis. No third-party APIs, no cookies, no tracking. The optional post preview works exactly like Friendica\'s built-in preview button: your draft is sent to your own Friendica instance for rendering, just as it would be without this addon. The only server-side storage is your personal enable/disable preference in the standard Friendica settings.'] = 'Text analysis runs entirely in your browser — no data is sent anywhere for analysis. No third-party APIs, no cookies, no tracking. The optional post preview works exactly like Friendica\'s built-in preview button: your draft is sent to your own Friendica instance for rendering, just as it would be without this addon. The only server-side storage is your personal enable/disable preference in the standard Friendica settings.';

$a->strings['Paragraph Structure'] = 'Paragraph Structure';
$a->strings['Counts how many paragraphs your post contains (separated by blank lines). A single unbroken block of text scores low (30 %) because readers find it hard to scan. Two or more paragraphs score 100 %. Posts under 600 characters are always rated "Short post" regardless of structure.'] = 'Counts how many paragraphs your post contains (separated by blank lines). A single unbroken block of text scores low (30 %) because readers find it hard to scan. Two or more paragraphs score 100 %. Posts under 600 characters are always rated "Short post" regardless of structure.';

$a->strings['Text Balance'] = 'Text Balance';
$a->strings['Measures average sentence length and flags sentences longer than 25 words. An average above 24 words scores 40 % ("Complex"), above 16 words scores 75 % ("Medium"), everything else scores 100 % ("Easy to read"). Shorter sentences improve readability for all audiences.'] = 'Measures average sentence length and flags sentences longer than 25 words. An average above 24 words scores 40 % ("Complex"), above 16 words scores 75 % ("Medium"), everything else scores 100 % ("Easy to read"). Shorter sentences improve readability for all audiences.';

$a->strings['Link Density'] = 'Link Density';
$a->strings['Counts all http/https URLs in your post. More than 5 links scores 30 % ("Very dense") — posts that are mostly links feel like spam to readers and federation filters. Up to 3 links scores 100 % ("Subtle").'] = 'Counts all http/https URLs in your post. More than 5 links scores 30 % ("Very dense") — posts that are mostly links feel like spam to readers and federation filters. Up to 3 links scores 100 % ("Subtle").';

$a->strings['Hashtag Density'] = 'Hashtag Density';
$a->strings['Counts #hashtags. More than 6 scores 30 % ("Very dense"). Posts with fewer, focused hashtags reach more people than hashtag-stuffed ones. Up to 3 hashtags scores 100 % ("Subtle").'] = 'Counts #hashtags. More than 6 scores 30 % ("Very dense"). Posts with fewer, focused hashtags reach more people than hashtag-stuffed ones. Up to 3 hashtags scores 100 % ("Subtle").';

$a->strings['Alt-Text Check'] = 'Alt-Text Check';
$a->strings['Checks every [img] BBCode tag in your post for an alt text description. Alt text is essential for screen readers and users with visual impairments — it ensures everyone can participate in the conversation.'] = 'Checks every [img] BBCode tag in your post for an alt text description. Alt text is essential for screen readers and users with visual impairments — it ensures everyone can participate in the conversation.';

$a->strings['Emoji Check'] = 'Emoji Check';
$a->strings['Detects sequences of 5 or more consecutive emoji. Screen readers read every emoji aloud by name ("grinning face", "thumbs up" …), so a long cluster becomes an exhausting wall of words for blind users. Up to 4 consecutive emoji is fine.'] = 'Detects sequences of 5 or more consecutive emoji. Screen readers read every emoji aloud by name ("grinning face", "thumbs up" …), so a long cluster becomes an exhausting wall of words for blind users. Up to 4 consecutive emoji is fine.';

$a->strings['Paragraph Check (Accessibility)'] = 'Paragraph Check (Accessibility)';
$a->strings['For posts longer than 300 characters, checks whether at least one paragraph break exists. Screen readers and cognitive-accessibility tools benefit greatly from structured text. Short posts are always rated neutral.'] = 'For posts longer than 300 characters, checks whether at least one paragraph break exists. Screen readers and cognitive-accessibility tools benefit greatly from structured text. Short posts are always rated neutral.';

$a->strings['EasyCompose (Advanced Editor)'] = 'EasyCompose (Advanced Editor)';
$a->strings['Enable EasyCompose'] = 'Enable EasyCompose';
$a->strings['This addon extends the editor at'] = 'This addon extends the editor at';
$a->strings['with a custom layout and additional text analysis tools. While writing, the integrated assistant checks readability (e.g., sentence length and paragraphs) and accessibility (e.g., missing image descriptions). It also provides a manual post preview and a distraction-free mode.'] = 'with a custom layout and additional text analysis tools. While writing, the integrated assistant checks readability (e.g., sentence length and paragraphs) and accessibility (e.g., missing image descriptions). It also provides a manual post preview and a distraction-free mode.';
$a->strings['Please note: EasyCompose is designed and optimized exclusively for desktop viewports. To ensure a clean and clutter-free mobile editor, all assistant panels and buttons are automatically hidden on smartphones and tablets.'] = 'Please note: EasyCompose is designed and optimized exclusively for desktop viewports. To ensure a clean and clutter-free mobile editor, all assistant panels and buttons are automatically hidden on smartphones and tablets.';
$a->strings['The addon operates autonomously: all text analysis is performed locally in the user\'s browser, no external scripts or resources are loaded, and no data is sent to external services for analysis.'] = 'The addon operates autonomously: all text analysis is performed locally in the user\'s browser, no external scripts or resources are loaded, and no data is sent to external services for analysis.';
$a->strings['If you deactivate this addon, the standard Friendica editor will be restored at'] = 'If you deactivate this addon, the standard Friendica editor will be restored at';
$a->strings['Addon: EasyCompose (deactivatable in settings)'] = 'Addon: EasyCompose (deactivatable in settings)';
