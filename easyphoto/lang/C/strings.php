<?php

if (! function_exists("string_plural_select_en")) {
	function string_plural_select_en($n)
	{
		$n = intval($n);
		return intval($n != 1);
	}
}
$a->strings['Image'] = 'Image';
$a->strings['Enter image description here...'] = 'Enter image description here...';
$a->strings['External image (privacy protection)'] = 'External image (privacy protection)';
$a->strings['Conflict detected: both EasyPhoto and QuickPhoto are enabled. EasyPhoto is temporarily inactive to prevent issues. Please disable QuickPhoto if you want to use EasyPhoto.'] = 'Conflict detected: both EasyPhoto and QuickPhoto are enabled. EasyPhoto is temporarily inactive to prevent issues. Please disable QuickPhoto if you want to use EasyPhoto.';
$a->strings['Warning'] = 'Warning';
$a->strings['The "QuickPhoto" addon is also enabled. Both addons cannot be active at the same time. EasyPhoto is temporarily inactive to avoid conflicts. Please deactivate QuickPhoto if you want to use EasyPhoto.'] = 'The "QuickPhoto" addon is also enabled. Both addons cannot be active at the same time. EasyPhoto is temporarily inactive to avoid conflicts. Please deactivate QuickPhoto if you want to use EasyPhoto.';
$a->strings['EasyPhoto is ready. No conflicts detected.'] = 'EasyPhoto is ready. No conflicts detected.';
