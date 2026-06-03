<?php

if (! function_exists("string_plural_select_de")) {
	function string_plural_select_de($n)
	{
		$n = intval($n);
		return intval($n != 1);
	}
}
$a->strings['Enable Registration Timer'] = 'Registrierungs-Timer aktivieren';
$a->strings['If enabled, the registration policy is switched automatically by the background worker based on the current local time.'] = 'Wenn aktiviert, wird die Registrierungsrichtlinie automatisch durch den Hintergrund-Worker basierend auf der aktuellen Uhrzeit umgeschaltet.';
$a->strings['Start Time'] = 'Startzeit';
$a->strings['Start of the time period (format HH:MM, in your admin timezone shown above).'] = 'Beginn des Zeitraums (Format HH:MM, in deiner oben angezeigten Administrator-Zeitzone).';
$a->strings['End Time'] = 'Endzeit';
$a->strings['End of the time period (format HH:MM, in your admin timezone shown above).'] = 'Ende des Zeitraums (Format HH:MM, in deiner oben angezeigten Administrator-Zeitzone).';
$a->strings['Registration Policy during Time Period'] = 'Registrierungsrichtlinie während des Zeitraums';
$a->strings['The registration policy enforced during the specified time period (e.g. at night).'] = 'Die Registrierungsrichtlinie, die während des angegebenen Zeitraums (z. B. nachts) erzwungen wird.';
$a->strings['Registration Policy outside the Time Period'] = 'Registrierungsrichtlinie außerhalb des Zeitraums';
$a->strings['The registration policy restored outside the specified time period (e.g. during the day).'] = 'Die Registrierungsrichtlinie, die außerhalb des angegebenen Zeitraums (z. B. tagsüber) wiederhergestellt wird.';
$a->strings['Closed'] = 'Geschlossen';
$a->strings['Requires Approval'] = 'Zustimmung erforderlich';
$a->strings['Open'] = 'Offen';
$a->strings['Save Settings'] = 'Einstellungen speichern';
$a->strings['Settings saved.'] = 'Einstellungen gespeichert.';
$a->strings['Invalid start time format. Use HH:MM (00:00 to 23:59).'] = 'Ungültiges Format für die Startzeit. Verwende HH:MM (00:00 bis 23:59).';
$a->strings['Invalid end time format. Use HH:MM (00:00 to 23:59).'] = 'Ungültiges Format für die Endzeit. Verwende HH:MM (00:00 bis 23:59).';
$a->strings['Invalid registration policy selected.'] = 'Ungültige Registrierungsrichtlinie ausgewählt.';
$a->strings['Settings could not be saved due to a server error. Please try again.'] = 'Die Einstellungen konnten aufgrund eines Serverfehlers nicht gespeichert werden. Bitte versuche es erneut.';
$a->strings['The Registration Timer settings could not be loaded due to a server error. Please try again later.'] = 'Die Einstellungen des Registrierungs-Timers konnten aufgrund eines Serverfehlers nicht geladen werden. Bitte versuche es später erneut.';
$a->strings['Settings saved, but the registration policy could not be restored automatically. Please check it manually under Site settings.'] = 'Einstellungen gespeichert, aber die Registrierungsrichtlinie konnte nicht automatisch wiederhergestellt werden. Bitte prüfe sie manuell in den Seiteneinstellungen.';
$a->strings['Instance Timezone & Current Time'] = 'Zeitzone & aktuelle Uhrzeit der Instanz';
$a->strings['Admin Timezone & Current Time'] = 'Deine Zeitzone & aktuelle Uhrzeit (Administrator)';
$a->strings['The configured time range refers to your current timezone: %s'] = 'Der konfigurierte Zeitraum bezieht sich auf deine aktuelle Zeitzone: %s';
$a->strings['Important: switching time depends on the background worker'] = 'Wichtig: Der Umschaltzeitpunkt hängt vom Hintergrund-Worker ab';
$a->strings['This addon does not switch the registration policy at the exact second you configure. The change is performed by Friendica\'s background worker (the cron job or the daemon that runs every few minutes). The actual switch therefore happens at the next worker run after the configured time, e.g. on a typical setup with the worker running every 10 minutes a start time of 22:00 may take effect anywhere between 22:00 and 22:10. The switch also only happens while the worker is actually running.'] = 'Dieses Addon schaltet die Registrierungsrichtlinie nicht sekundengenau zur eingestellten Zeit um. Die Umstellung wird vom Hintergrund-Worker von Friendica durchgeführt (dem Cronjob oder dem Daemon, der alle paar Minuten läuft). Die tatsächliche Umschaltung erfolgt daher beim nächsten Worker-Durchlauf nach der eingestellten Zeit – z. B. kann bei einem typischen Setup mit einem alle 10 Minuten laufenden Worker eine Startzeit von 22:00 irgendwann zwischen 22:00 und 22:10 wirksam werden. Die Umschaltung erfolgt außerdem nur, solange der Worker tatsächlich läuft.';
