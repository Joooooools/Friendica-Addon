<?php

if (! function_exists("string_plural_select_de")) {
	function string_plural_select_de($n)
	{
		$n = intval($n);
		return intval($n != 1);
	}
}
$a->strings['Profile banner'] = 'Profilbanner';
$a->strings['Conflict detected: both AuthBanner and CoverPhoto are enabled. AuthBanner is temporarily inactive to prevent issues. Please disable CoverPhoto if you want to use AuthBanner.'] = 'Konflikt erkannt: Sowohl AuthBanner als auch CoverPhoto sind aktiviert. AuthBanner ist vorübergehend inaktiv, um Probleme zu vermeiden. Bitte deaktiviere CoverPhoto, falls du AuthBanner nutzen möchtest.';
$a->strings['Warning'] = 'Achtung';
$a->strings['The "CoverPhoto" addon is also enabled. Both addons cannot be active at the same time. AuthBanner is temporarily inactive to avoid conflicts. Please deactivate CoverPhoto if you want to use AuthBanner.'] = 'Das Addon „CoverPhoto“ ist ebenfalls aktiviert. Beide Addons können nicht gleichzeitig aktiv sein. AuthBanner ist vorübergehend inaktiv, um Konflikte zu vermeiden. Bitte deaktiviere CoverPhoto, falls du AuthBanner nutzen möchtest.';
$a->strings['AuthBanner is ready. No conflicts detected.'] = 'AuthBanner ist einsatzbereit. Es wurden keine Konflikte festgestellt.';
$a->strings['Enable AuthBanner'] = 'AuthBanner aktivieren';
$a->strings['Show the profile banner for logged-in users.'] = 'Zeigt das Profilbanner für angemeldete Benutzer an.';
$a->strings['AuthBanner Settings'] = 'AuthBanner Einstellungen';
$a->strings['This addon displays already existing profile banners (e.g. from Friendica, Mastodon, Bluesky, Sharkey, Calckey, Hubzilla, Tumblr, etc.) on user profiles. To keep it lightweight, this addon does not support uploading banners directly; you can easily upload or change your profile banner using an external client app (like Mona, Tusky, etc.).'] = 'Dieses Addon zeigt bereits vorhandene Profilbanner (z. B. von Friendica, Mastodon, Bluesky, Sharkey, Calckey, Hubzilla, Tumblr etc.) in den Benutzerprofilen an. Um das Addon bewusst schlank zu halten, ist kein direkter Upload möglich; das Hochladen oder Ändern eines eigenen Banners kann unkompliziert über eine externe App (wie Mona, Tusky etc.) erfolgen.';
