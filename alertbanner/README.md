# AlertBanner Addon

Das `alertbanner`-Addon zeigt ein administratives Hinweisbanner oben auf der Friendica-Seite an.

### Funktionen

- Globaler Bannertext für die Instanz.
- Banner-Aktivierung getrennt von der Addon-Aktivierung.
- Sicherer Standard: Nach dem Aktivieren des Addons bleibt der Banner selbst inaktiv, bis ein Admin ihn ausdrücklich aktiviert.
- Stilvarianten: Info, Warnung, "Gefahr" und Erfolg.
- Sichtbarkeit: nur registrierte lokale Benutzer oder registrierte und nicht registrierte Besucher.
- Optionaler Anzeigezeitraum mit Beginn und Ende.
- Die Eingabe des Anzeigezeitraums erfolgt in der im Adminformular angezeigten Administrator-Zeitzone im Format `YYYY-MM-DD HH:MM` oder `TT.MM.JJJJ HH:MM`.
- Das Addon zeigt zusätzlich die Zeitzone und aktuelle Uhrzeit der Instanz sowie die Zeitzone und aktuelle Uhrzeit des Administrators an.
- Benutzer können den aktuell angezeigten Banner lokal im Browser schließen.

### Banner-Versionen

Ein geschlossener Banner bleibt im selben Browser verborgen, bis die Administration Bannertext, Stil, Sichtbarkeit, Beginn oder Ende des Anzeigezeitraums ändert. Eine dieser Änderungen erzeugt automatisch eine neue Banner-Version.

### Zeitangaben

Intern speichert das Addon Beginn und Ende in UTC. Im Adminformular werden die Werte in der Zeitzone des Administrators angezeigt und eingegeben.

The `alertbanner` addon displays an administrative notice banner at the top of the Friendica page.

### Features

- Node-wide custom banner text.
- Banner activation is separate from addon activation.
- Safe default: after enabling the addon, the banner itself stays inactive until an admin explicitly activates it.
- Style variants: info, warning, danger and success.
- Visibility control: registered local users only, or registered and unregistered visitors.
- Optional display period with start and end date/time.
- The display period is entered in the administrator timezone shown in the admin form, using the format `YYYY-MM-DD HH:MM` or `TT.MM.JJJJ HH:MM`.
- The addon shows both the instance timezone/current time and the administrator timezone/current time.
- Users can close the currently displayed banner locally in their browser.

### Banner versions

A dismissed banner stays hidden in the same browser until the admin changes the banner text, style, visibility, display start or display end. Changing one of these values automatically creates a new banner version.

### Time values

Internally, the addon stores the display start and end times in UTC. In the admin form, values are displayed and entered in the administrator timezone.


## Lizenz / License

GNU Affero General Public License v3.0 (AGPL-3.0-or-later)

Autor: [Jools](https://friendica.de/profile/jools)

Erstellt mit Unterstützung von Gemini und Claude. / Created with Gemini and Claude.

Weitere Informationen zu diesem Addon auf [friendica.dev](https://friendica.dev)
