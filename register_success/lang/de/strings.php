<?php

if (! function_exists("string_plural_select_de")) {
	function string_plural_select_de($n)
	{
		$n = intval($n);
		return intval($n != 1);
	}
}
$a->strings["Registration Pending"] = "Registrierung eingereicht";
$a->strings["Thank you for registering!\n\nAs soon as your registration has been approved by the administration, you will receive an email notification. Your login credentials have just been sent via email. Sometimes it takes a few minutes for this email to arrive. Please also check your spam folder."] = "Vielen Dank für deine Registrierung!\n\nSobald deine Anmeldung von der Administration freigeschaltet wurde, bekommst du eine E-Mail darüber. Deine Zugangsdaten wurden soeben per E-Mail verschickt. Manchmal dauert es ein paar Minuten, bis diese E-Mail bei dir ankommt. Bitte schaue auch in deinen Spam-Ordner nach.";
$a->strings["Registration Successful"] = "Registrierung erfolgreich";
$a->strings["Thank you for registering!\n\nYour login credentials have just been sent via email. Sometimes it takes a few minutes for this email to arrive. Please also check your spam folder."] = "Vielen Dank für deine Registrierung!\n\nDeine Zugangsdaten wurden soeben per E-Mail verschickt. Manchmal dauert es ein paar Minuten, bis diese E-Mail bei dir ankommt. Bitte schaue auch in deinen Spam-Ordner nach.";
$a->strings["Preview Success Page"] = "Vorschau der Erfolgsseite";
$a->strings["Configure the custom messages shown to users after successful registration."] = "Konfiguriere die eigenen Nachrichten, die Benutzern nach erfolgreicher Registrierung angezeigt werden.";
$a->strings["Open Registration settings"] = "Einstellungen für offene Registrierungen";
$a->strings["Approval Required settings"] = "Einstellungen für Registrierungen mit Zustimmung";
$a->strings["Success Page Title"] = "Titel der Erfolgsseite";
$a->strings["Success Page Message"] = "Nachricht der Erfolgsseite";
$a->strings["Save Settings"] = "Einstellungen speichern";
$a->strings["Go to Login"] = "Gehe zum Login";
$a->strings["Current Registration Policy"] = "Aktuelle Registrierungs-Richtlinie";
$a->strings["Closed (users cannot register)"] = "Geschlossen (Benutzer können sich nicht registrieren)";
$a->strings["Approval Required (using Approval Required settings)"] = "Zustimmung erforderlich (nutzt Einstellungen für Registrierungen mit Zustimmung)";
$a->strings["Open Registration (using Open Registration settings)"] = "Offene Registrierung (nutzt Einstellungen für offene Registrierungen)";
$a->strings["Unknown"] = "Unbekannt";
