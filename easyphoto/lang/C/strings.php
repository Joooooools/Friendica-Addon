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
