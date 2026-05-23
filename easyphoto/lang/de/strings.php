<?php

if (! function_exists("string_plural_select_de")) {
	function string_plural_select_de($n)
	{
		$n = intval($n);
		return intval($n != 1);
	}
}
$a->strings['Image'] = 'Bild';
$a->strings['Enter image description here...'] = 'Bildbeschreibung hier eingeben...';
$a->strings['External image (privacy protection)'] = 'Externes Bild (Schutz der Privatsphäre)';
