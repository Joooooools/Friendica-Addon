
# PersonalPostExporter for Friendica

PersonalPostExporter ist ein Addon für Friendica. Es ermöglicht Nutzern, ihre eigenen Beiträge in ein portables, lokal betrachtbares HTML-Archiv zu exportieren.

## Funktionsumfang

-   **Eigenständiges Archiv:** Erzeugt eine ZIP-Datei, die HTML, CSS und JavaScript enthält, um Beiträge offline in jedem Webbrowser zu lesen.

-   **Chronologische Navigation:** Die Beiträge werden nach Jahren und Monaten sortiert aufbereitet.

-   **Volltextsuche:** Enthält eine clientseitige Suchfunktion (JavaScript), die das Durchsuchen des gesamten Textarchivs ermöglicht.

-   **Design-Optionen:** Unterstützt ein helles und ein dunkles Design für das exportierte Archiv.

-   **Ressourcenschonend:** Verfügt über einen integrierten Sperrmechanismus (Mutex), um die Anzahl gleichzeitiger Exporte pro Instanz zu begrenzen und Server-Überlastungen zu verhindern.

-   **Datenschutz:** Berücksichtigt private Beiträge und kennzeichnet diese im Archiv deutlich.


## Wichtige Hinweise & Haftungsausschluss

-   **Nutzung auf eigene Verantwortung:** Die Verwendung dieses Addons erfolgt auf eigene Gefahr und Verantwortung des Nutzers. Der Entwickler übernimmt keine Haftung für etwaige Serverinstabilitäten, Timeouts oder die Vollständigkeit der exportierten Inhalte.

-   **Kein technisches Backup:** Dieses Addon dient der Ansicht und Archivierung. Die exportierten Daten sind nicht für einen Re-Import in eine andere Friendica-Instanz formatiert.

-   **Medien-Inhalte:** Um die Dateigröße und Serverlast gering zu halten, werden Bilder **nicht** in die ZIP-Datei kopiert. Sie verbleiben auf dem Server und werden im Archiv lediglich verlinkt. Sollten die Originalbilder auf dem Server gelöscht werden, sind sie auch im Archiv nicht mehr sichtbar.

-   **Ausschlüsse:** Kommentare, Likes, Shares (geteilte Inhalte Dritter) und andere soziale Interaktionen sind nicht Teil des Exports.


## Anforderungen

-   **Friendica:** Aktuelle Version.

-   **PHP Erweiterungen:**  zip (wird zur Erstellung des Archivs benötigt).

-   **Schreibrechte:** Das System benötigt Schreibrechte im temporären Verzeichnis des Systems (für Sperrdateien und die ZIP-Erstellung).


---

# PersonalPostExporter for Friendica

PersonalPostExporter is an addon for Friendica. It allows users to export their own posts into a portable, locally viewable HTML archive.

## Features

-   **Self-contained Archive:** Generates a ZIP file containing HTML, CSS, and JavaScript to read posts offline in any web browser.

-   **Chronological Navigation:** Posts are prepared and sorted by years and months.

-   **Full-text Search:** Contains a client-side search function (JavaScript) that allows searching the entire text archive.

-   **Design Options:** Supports a light and dark design for the exported archive.

-   **Resource-friendly:** Features an integrated lock mechanism (mutex) to limit the number of simultaneous exports per instance and prevent server overload.

-   **Privacy:** Respects private posts and marks them clearly in the archive.


## Important Notes & Disclaimer

-   **Use at Your Own Risk:** The use of this addon is at the user's own risk and responsibility. The developer assumes no liability for any server instabilities, timeouts, or the completeness of the exported contents.

-   **Not a Technical Backup:** This addon is for viewing and archiving purposes only. The exported data is not formatted for re-import into another Friendica instance.

-   **Media Content:** To keep the file size and server load low, images are **not** copied into the ZIP file. They remain on the server and are only linked in the archive. If the original images on the server are deleted, they will no longer be visible in the archive either.

-   **Exclusions:** Comments, likes, shares (shared content from third parties), and other social interactions are not part of the export.


## Requirements

-   **Friendica:** Current version.

-   **PHP Extensions:** zip (required to create the archive).

-   **Write Permissions:** The system requires write permissions in the system's temporary directory (for lock files and ZIP creation).


## Lizenz / License

GNU Affero General Public License v3.0 (AGPL-3.0-or-later)

Autor: [Jools](https://friendica.de/profile/jools)

Erstellt mit Unterstützung von Gemini und Claude. / Created with Gemini and Claude.

Weitere Informationen: [friendica.dev](https://friendica.dev)