<?php

if (!function_exists("string_plural_select_de")) {
	function string_plural_select_de($n)
	{
		$n = intval($n);
		return intval($n != 1);
	}
}

$a->strings['Account Import Guard'] = 'Konto-Import-Schutz';
$a->strings['Blocks unauthorized access to /user/import based on an administrative policy, while keeping account import available for authorized users. Also removes the public import link from the registration form.'] = 'Blockiert den unbefugten Zugriff auf /user/import basierend auf einer administrativen Richtlinie, während der Konto-Import für berechtigte Benutzer verfügbar bleibt. Entfernt außerdem den öffentlichen Import-Link aus dem Registrierungsformular.';
$a->strings['Only logged-in users'] = 'Nur angemeldete Benutzer';
$a->strings['Only site administrators'] = 'Nur Site-Administratoren';
$a->strings['Block for everyone, including administrators'] = 'Für alle verbieten (inklusive Administratoren)';
$a->strings['Save Settings'] = 'Einstellungen speichern';
$a->strings['Account Import Policy'] = 'Richtlinie für den Konto-Import';
$a->strings['Select who is allowed to import accounts on this node.'] = 'Wähle aus, wer auf diesem Server Konten importieren darf.';
$a->strings['Account import is not available.'] = 'Der Konto-Import ist auf dieser Instanz nicht verfügbar.';
