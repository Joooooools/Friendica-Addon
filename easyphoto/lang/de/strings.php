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
$a->strings['Conflict detected: both EasyPhoto and QuickPhoto are enabled. EasyPhoto is temporarily inactive to prevent issues. Please disable QuickPhoto if you want to use EasyPhoto.'] = 'Konflikt erkannt: Sowohl EasyPhoto als auch QuickPhoto sind aktiviert. EasyPhoto ist vorübergehend inaktiv, um Probleme zu vermeiden. Bitte deaktiviere QuickPhoto, falls du EasyPhoto nutzen möchtest.';
$a->strings['Warning'] = 'Achtung';
$a->strings['The "QuickPhoto" addon is also enabled. Both addons cannot be active at the same time. EasyPhoto is temporarily inactive to avoid conflicts. Please deactivate QuickPhoto if you want to use EasyPhoto.'] = 'Das Addon „QuickPhoto“ ist ebenfalls aktiviert. Beide Addons können nicht gleichzeitig aktiv sein. EasyPhoto ist vorübergehend inaktiv, um Konflikte zu vermeiden. Bitte deaktiviere QuickPhoto, falls du EasyPhoto nutzen möchtest.';
$a->strings['EasyPhoto is ready. No conflicts detected.'] = 'EasyPhoto ist einsatzbereit. Es wurden keine Konflikte festgestellt.';
