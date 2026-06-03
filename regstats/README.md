# RegStats

Anonymously logs and visualizes registration statistics, successes, moderation events, and spambot block attempts.
Anonyme Protokollierung und Visualisierung von Registrierungs-Statistiken, Moderations-Aktionen und Spambot-Blockierungen.

---

## Deutsch

### Funktionsumfang

Das **RegStats**-Addon erfasst und visualisiert Registrierungs-Statistiken anonymisiert. Es bietet ein interaktives Dashboard zur Auswertung folgender Metriken:

#### Blockierte Spam- & Fehlversuche (Audit-Log)

- **Core-Honeypot** – Ausgelöst, wenn das versteckte E-Mail-Feld des Cores ausgefüllt wird.
- **Guardian-Honeypot** – Ausgelöst, wenn das versteckte Feld des Guardian-Addons (falls vorhanden) ausgefüllt wird.
- **Captcha-Fehler** – Ausgelöst bei Fehleingabe, Zeitüberschreitungen oder dem Ausfüllen von Täuschungsfeldern des Captchas.
- **Validierungsfehler** – Ausgelöst bei unzulässigen Benutzernamen oder ungleichen E-Mail-Adressen.
- **Dubletten-Treffer** – Ausgelöst, wenn die Registrierungsdaten (Benutzername oder E-Mail) bereits vergeben sind.
- **Gesperrter Spitzname / Gesperrte E-Mail** – Ausgelöst, wenn ein gesperrter Name oder eine gesperrte E-Mail-Domain verwendet wird.

#### Erfolgreiche Registrierungen & Moderation

- **Registrierungen gesamt** – Aufgeteilt in offene Registrierungen, Registrierungen mit Admin-Zustimmung, Konto-Importe und OpenID-Registrierungen.
- **Moderations-Aktionen** – Anzahl der durch Admins freigeschalteten oder abgelehnten Registrierungsanfragen.
- **Mail-Fehler** – Fehlgeschlagener Versand der Willkommens- oder Passwort-E-Mail nach erfolgreicher Registrierung.

### Interaktive Diagramme

- **Tägliche & stündliche Verteilung** – Das Dashboard visualisiert die Daten der letzten 14 Tage sowie eine stündliche Auswertung.
- **Interaktiver Breakdown** – Durch Klicken auf einen beliebigen Balken im Diagramm klappt eine detaillierte Auswertung aller 15 Metriken für diesen Tag oder diese Stunde auf.
- **Detaillierte Tooltips** – Beim Bewegen des Mauszeigers über die Balken wird eine detaillierte Zusammenfassung aller Werte eingeblendet.

### Datenschutz & Architektur

- **Keine Datenbanknutzung** – Das Addon legt keine Tabellen an. Alle Daten werden in die Datei `log/regstats.log` im Friendica-Verzeichnis geschrieben.
  *(Hinweis zu Session-Writes: Das Addon verwendet standardmäßige PHP-Session-Variablen zur Deduplizierung von Fehlermeldungen bei Weiterleitungen. Falls Friendica für datenbankbasierte Sessions konfiguriert ist, werden diese Zustände im Rahmen der normalen Session-Speicherung von Friendica persistiert. Es erfolgen keine direkten Datenbank-Schreibzugriffe durch das Addon selbst.)*
- **Datenschutz** – RegStats speichert ausschließlich stark minimierte technische Ereignisdaten wie Zeitstempel und Ereignistyp. Es werden keine IP-Adressen, Namen, E-Mail-Adressen, User-IDs oder User-Agenten gespeichert. Die Logdateien sind durch automatische Größenrotation begrenzt und können im Admin-Dashboard gelöscht werden.
- **Log-Rotation** – Die Logdatei wird bei Erreichen von 2 MB automatisch rotiert, indem sie in `regstats.log.1` umbenannt wird (was ein älteres Archiv überschreibt). Die maximale Protokoll-Speichergröße auf dem Server ist somit dauerhaft auf 4 MB begrenzt.
- **Statistiken löschen** – Das Leeren der Statistiken im Dashboard leert die aktive `regstats.log` und löscht das rotierte Archiv `regstats.log.1` vollständig vom Server.
- **Lokale Auswertung** – Das Dashboard wird im Administrationsbereich über reines HTML, CSS und natives JavaScript generiert. Es lädt keine externen Bibliotheken, CDNs oder Tracker nach.
- **Zugriffsschutz** – Der Aufruf der Statistik-Seite ist ausschließlich für eingeloggte Seiten-Administratoren erlaubt. Nicht autorisierte Aufrufe leiten automatisch zur Login-Seite weiter.

### Best-Effort Metriken (Technische Einschränkungen)

- **Importierte Konten** – Da Friendica beim Importieren von Profilen keinen direkten Hook bereitstellt, vergleicht das Addon die höchste UID in der Benutzerdatenbank vor und nach der Profilerstellung. Bei stark parallelen Registrierungen kann es hier zu geringfügigen Ungenauigkeiten kommen.
- **Moderationsaktionen** – Das Addon registriert Freigaben und Ablehnungen vorläufig und verifiziert deren erfolgreichen Datenbank-Commit mithilfe einer Shutdown-Funktion nach Abschluss der Core-Transaktion. Dies garantiert maximale Datenintegrität ohne Fehlzählungen bei abgebrochenen Aktionen.

### Installation & Konfiguration

1. Aktiviere das Addon in den Addon-Einstellungen (`/admin/addons`).
2. Nach der Aktivierung ist das Dashboard über folgenden Navigationspfad erreichbar:
   **Moderation → Nutzer → Reiter „Registration Stats"**
   (URL: `/moderation/users` → dort den neuen Reiter anklicken)
3. Das Dashboard ist auch direkt über `https://deine-domain.de/regstats` aufrufbar.

---

## English

### Functionality

The **RegStats** addon tracks and visualizes registration statistics anonymously. It provides an interactive dashboard with the following metrics:

#### Blocked Spam & Failure Attempts (Audit Log)

- **Core Honeypot** – Triggered when the hidden core registration field is populated.
- **Guardian Honeypot** – Triggered when the hidden field from the Guardian addon is populated.
- **Captcha Errors** – Triggered when the user solves the captcha incorrectly, encounters a captcha timeout, or fills decoy fields.
- **Validation Errors** – Triggered when the user submits invalid nicknames or mismatching email addresses.
- **Duplicate Errors** – Triggered when the user submits a nickname or email that is already registered on the node.
- **Blocked Nickname / Blocked Email** – Triggered when a blocked nickname or email domain is used.

#### Successful Registrations & Moderation

- **Total Registrations** – Grouped by open registrations, registrations with admin approval, imported accounts, and OpenID registrations.
- **Moderation Actions** – Number of registration requests approved or rejected by site administrators.
- **Mail Errors** – Failed delivery of the welcome or password email after a successful registration.

### Interactive Charts

- **Daily & Hourly Distribution** – Displays metrics for the last 14 days and hourly charts.
- **Interactive Breakdown** – Clicking any bar in the charts expands a detailed grid listing all 15 metrics for that specific day or hour.
- **Detailed Tooltips** – Hovering over chart bars shows a detailed breakdown of all metrics.

### Privacy & Architecture

- **Zero Database Footprint** – The addon does not create any database tables. All events are logged to `log/regstats.log` inside the Friendica root directory.
  *(Note on Session Writes: The addon utilizes standard PHP session variables to deduplicate failure notifications during page redirects. If Friendica is configured to use database-backed sessions, these states are persisted as part of Friendica's standard session handling. No direct database writes are performed by the addon itself.)*
- **Privacy-First** – RegStats exclusively stores highly minimized technical event data such as timestamps and event types. No IP addresses, names, email addresses, user IDs, or user agents are stored. Log files are limited by automatic size-based rotation and can be deleted via the admin dashboard.
- **Auto-Rotation** – To protect disk space, the log file automatically rotates when it exceeds 2 MB by renaming itself to `regstats.log.1` (overwriting the previous `.1` archive). The maximum log footprint is strictly limited to 4 MB.
- **Clear Statistics** – Resetting statistics via the dashboard empties the active `regstats.log` and completely deletes the rotated archive `regstats.log.1` from the server.
- **Self-Contained Rendering** – The statistics dashboard is rendered in the administration panel using pure, offline-first HTML, CSS, and native JavaScript. No external libraries, CDNs, or tracker files are used.
- **Access Control** – The statistics page is strictly limited to logged-in site administrators. Unauthorized visits are automatically redirected to the login page.

### Best-Effort Metrics (Technical Limitations)

- **Imported Accounts** – Since Friendica does not provide a direct hook for account migrations, imports are detected by checking the highest user UID before and after the profile is generated. Under heavy concurrent registration load, this best-effort metric may exhibit minor counting inaccuracies.
- **Moderation Actions** – Approvals and rejections are staged dynamically and verified after the core transaction completes using a shutdown function. This ensures high data integrity by logging events only when database changes are committed.

### Installation & Configuration

1. Enable the addon in the admin settings (`/admin/addons`).
2. Once active, the dashboard is accessible via the following navigation path:
   **Moderation → Users → "Registration Stats" tab**
   (URL: `/moderation/users` → click the new tab)
3. The dashboard is also directly accessible at `https://your-domain.tld/regstats`.


## Lizenz / License

GNU Affero General Public License v3.0 (AGPL-3.0-or-later)

Autor: [Jools](https://friendica.de/profile/jools)

Erstellt mit Unterstützung von Gemini und Claude. / Created with Gemini and Claude.

Weitere Informationen zu diesem Addon auf [friendica.dev](https://friendica.dev)