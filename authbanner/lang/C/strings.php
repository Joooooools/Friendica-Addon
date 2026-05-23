<?php

if (! function_exists("string_plural_select_en")) {
	function string_plural_select_en($n)
	{
		$n = intval($n);
		return intval($n != 1);
	}
}
$a->strings['Profile banner'] = 'Profile banner';
