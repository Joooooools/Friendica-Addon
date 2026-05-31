<?php

if (!function_exists('string_plural_select_de')) {
	function string_plural_select_de($n)
	{
		$n = intval($n);
		return intval($n != 1);
	}
}

$a->strings['Registration Stats']                                                                                                         = 'Registrierungs-Statistiken';
$a->strings['Registration statistics and blocks']                                                                                         = 'Registrierungsstatistiken und geblockte Versuche';
$a->strings['Statistics cleared successfully.']                                                                                           = 'Statistiken erfolgreich gelöscht.';
$a->strings['%d failures']                                                                                                                = '%d Fehlversuche';
$a->strings['Core Honeypot: %d']                                                                                                          = 'Core-Honeypot: %d';
$a->strings['Guardian Honeypot: %d']                                                                                                      = 'Guardian-Honeypot: %d';
$a->strings['Captcha failed: %d']                                                                                                         = 'Captcha fehlerhaft: %d';
$a->strings['Validation failed: %d']                                                                                                      = 'Validierung fehlerhaft: %d';
$a->strings['Duplicate entries: %d']                                                                                                      = 'Dubletten-Treffer: %d';
$a->strings['Blocked nicknames: %d']                                                                                                      = 'Gesperrte Spitznamen: %d';
$a->strings['Blocked emails: %d']                                                                                                         = 'Gesperrte E-Mail-Domains: %d';
$a->strings['Registration Failure Audit']                                                                                                 = 'Protokoll der Registrierungsfehler';
$a->strings['Summary (All Time)']                                                                                                         = 'Zusammenfassung (Gesamt)';
$a->strings['Core Honeypot']                                                                                                              = 'Core-Honeypot';
$a->strings['Guardian Addon (optional)']                                                                                                  = 'Guardian-Addon (optional)';
$a->strings['Reg-Captcha Addon (optional)']                                                                                               = 'Reg-Captcha-Addon (optional)';
$a->strings['Validation Failed']                                                                                                          = 'Validierung fehlerhaft';
$a->strings['Duplicate Entry']                                                                                                            = 'Dubletten-Treffer';
$a->strings['Blocked Nickname']                                                                                                           = 'Gesperrter Spitzname';
$a->strings['Blocked Email']                                                                                                              = 'Gesperrte E-Mail';
$a->strings['Daily Distribution (Last 14 Days)']                                                                                          = 'Tägliche Verteilung (Letzte 14 Tage)';
$a->strings['The bar height visualizes registration failures and blocked spam attempts. Hover over a bar or click on it to view all details for that day (including successful registrations).'] = 'Die Balkenhöhe visualisiert Registrierungsfehler und blockierte Spam-Versuche. Bewege den Mauszeiger über einen Balken oder klicke darauf, um alle Details für diesen Tag (inklusive erfolgreicher Registrierungen) anzuzeigen.';
$a->strings['Hourly Distribution']                                                                                                        = 'Stündliche Verteilung';
$a->strings['The bar height visualizes registration failures and blocked spam attempts. Hover over a bar or click on it to view all details for that hour (including successful registrations).'] = 'Die Balkenhöhe visualisiert Registrierungsfehler und blockierte Spam-Versuche. Bewege den Mauszeiger über einen Balken oder klicke darauf, um alle Details für diese Stunde (inklusive erfolgreicher Registrierungen) anzuzeigen.';
$a->strings['Actions']                                                                                                                    = 'Aktionen';
$a->strings['Clear Statistics']                                                                                                           = 'Statistiken zurücksetzen';
$a->strings['Are you sure you want to delete all statistical logs?']                                                                      = 'Möchtest du wirklich alle Protokolldaten unwiderruflich löschen?';
$a->strings['No statistics logged yet.']                                                                                                  = 'Noch keine Statistiken aufgezeichnet.';
$a->strings['inactive']                                                                                                                   = 'inaktiv';
$a->strings['A hidden field in the registration form. Real users do not see it, but spambots automatically fill it out and get blocked.'] = 'Ein unsichtbares Feld im Registrierungsformular. Echte Nutzer sehen es nicht, aber Spambots füllen es automatisch aus und werden blockiert.';
$a->strings["An additional hidden field generated by the 'Guardian' addon to intercept advanced spambots."]                               = 'Ein zusätzliches unsichtbares Feld, das vom „Guardian“-Addon generiert wird, um fortschrittliche Spambots abzufangen.';
$a->strings['Registration attempts blocked because the spam protection captcha was solved incorrectly or timed out.']                     = 'Registrierungsversuche, die blockiert wurden, weil das Spam-Schutz-Captcha falsch gelöst wurde oder abgelaufen war.';
$a->strings['Invalid input data, such as usernames with forbidden characters or mismatching email confirmation fields.']                  = 'Ungültige Eingaben, wie Benutzernamen mit Sonderzeichen oder ungleiche E-Mail-Bestätigungsfelder.';
$a->strings['Registration attempts rejected because the username or email address is already registered on this server.']                 = 'Abgelehnte Versuche, da der Benutzername oder die E-Mail-Adresse auf diesem Server bereits registriert ist.';
$a->strings['Registration attempts rejected due to validation issues (e.g. invalid username format, mismatching email addresses).']       = 'Registrierungsversuche, die aufgrund von Validierungsproblemen (z. B. ungültiges Benutzernamen-Format, ungleiche E-Mail-Adressen) abgelehnt wurden.';
$a->strings['Registration attempts blocked because the nickname is on the blocklist or reserved.']                                        = 'Registrierungsversuche, die blockiert wurden, weil der Spitzname auf der Sperrliste des Servers steht oder reserviert ist.';
$a->strings['Registration attempts blocked because the email address or domain is disallowed.']                                           = 'Registrierungsversuche, die blockiert wurden, weil die E-Mail-Adresse oder -Domain gesperrt ist.';

$a->strings['Successful Registrations: %d'] = 'Erfolgreiche Registrierungen: %d';
$a->strings['Approved by Admin: %d']        = 'Vom Admin freigeschaltet: %d';
$a->strings['Rejected by Admin: %d']        = 'Vom Admin abgelehnt: %d';

$a->strings['Registrations & Moderation']                                                                          = 'Registrierungen & Moderation';
$a->strings['Total Registrations']                                                                                 = 'Registrierungen gesamt';
$a->strings['Open Registrations']                                                                                  = 'Offene Registrierungen';
$a->strings['With Admin Approval']                                                                                 = 'Mit Admin-Zustimmung';
$a->strings['Approved']                                                                                            = 'Freigeschaltet';
$a->strings['Rejected']                                                                                            = 'Abgelehnt';
$a->strings['Imported Accounts']                                                                                   = 'Konto-Importe';
$a->strings['Awaiting Approval']                                                                                   = 'Ausstehende Freischaltungen';
$a->strings['Registrations currently waiting for administrator approval. This is a live count from the database.'] = 'Registrierungen, die aktuell auf Freischaltung durch einen Administrator warten. Dies ist ein Live-Wert aus der Datenbank.';
$a->strings['Total successful registrations on this node (all time).']                                             = 'Gesamtzahl der erfolgreichen Registrierungen auf dieser Instanz.';
$a->strings['Registrations that completed immediately because the node had open registration enabled.']            = 'Registrierungen, die sofort abgeschlossen wurden, weil offene Registrierung aktiviert war.';
$a->strings['Registrations submitted while the node required administrator approval before activation.']           = 'Registrierungen, die eingereicht wurden, während der Server eine Freischaltung durch den Administrator erforderte.';
$a->strings['Pending registrations that were approved by an administrator.']                                       = 'Ausstehende Registrierungen, die von einem Administrator freigeschaltet wurden.';
$a->strings['Pending registrations that were denied/rejected by an administrator.']                                = 'Ausstehende Registrierungen, die von einem Administrator abgelehnt wurden.';
$a->strings['Registrations that were account migrations from other Fediverse instances.']                          = 'Registrierungen, die als Konto-Importe von anderen Fediverse-Instanzen durchgeführt wurden.';
$a->strings['OpenID Registrations']                                                                                = 'OpenID-Registrierungen';
$a->strings['Registrations that used an OpenID identity for authentication.']                                      = 'Registrierungen, bei denen eine OpenID-Identität zur Authentifizierung verwendet wurde.';
$a->strings['Mail Delivery Failed: %d']                                                                            = 'Mail-Versand fehlgeschlagen: %d';
$a->strings['Mail Send Failed']                                                                                    = 'Mail-Versand fehlgeschlagen';
$a->strings['The welcome or password email could not be sent to the user after successful registration.']          = 'Die Willkommens- oder Passwort-E-Mail konnte nach erfolgreicher Registrierung nicht an den Benutzer gesendet werden.';
$a->strings['Details for: %s']                                                                                     = 'Details für: %s';
$a->strings['The bar height visualizes registration failures and blocked spam attempts. Hover over a bar or click on it to view all details for that day (including successful registrations).'] = 'Die Balkenhöhe visualisiert Registrierungsfehler und blockierte Spam-Versuche. Bewege den Mauszeiger über einen Balken oder klicke darauf, um alle Details für diesen Tag (inklusive erfolgreicher Registrierungen) anzuzeigen.';
$a->strings['The bar height visualizes registration failures and blocked spam attempts. Hover over a bar or click on it to view all details for that hour (including successful registrations).'] = 'Die Balkenhöhe visualisiert Registrierungsfehler und blockierte Spam-Versuche. Bewege den Mauszeiger über einen Balken oder klicke darauf, um alle Details für diese Stunde (inklusive erfolgreicher Registrierungen) anzuzeigen.';
$a->strings['Open Registrations: %d']                                                                              = 'Offene Registrierungen: %d';
$a->strings['With Admin Approval: %d']                                                                             = 'Mit Admin-Zustimmung: %d';
$a->strings['Imported Accounts: %d']                                                                               = 'Konto-Importe: %d';
$a->strings['OpenID Registrations: %d']                                                                            = 'OpenID-Registrierungen: %d';
$a->strings['Registration Statistics']                                                                             = 'Registrierungs-Statistiken';
$a->strings['Statistics logging since:']                                                                           = 'Statistik-Aufzeichnung seit:';
$a->strings['To protect disk space, older logs are automatically rotated.']                                        = 'Zur Schonung des Speicherplatzes werden ältere Protokolle automatisch rotiert.';
$a->strings['Warning: The log directory is not writable. Registration statistics cannot be recorded.']             = 'Warnung: Das Protokollverzeichnis ist nicht beschreibbar. Registrierungsstatistiken können nicht aufgezeichnet werden.';
