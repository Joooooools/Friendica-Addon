
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


## Installation

1.  Navigiere in das Addon-Verzeichnis deiner Friendica-Installation.

2.  Klone dieses Repository oder kopiere die Dateien in einen Ordner namens personalpostexporter:



    ```
    git clone https://github.com/IhrUsername/personalpostexporter.git personalpostexporter
    ```

3.  Aktiviere das Addon im Administrationsbereich von Friendica unter **Administration -> Konfiguration -> Addons**.


## Anforderungen

-   **Friendica:** Aktuelle Version.

-   **PHP Erweiterungen:**  zip (wird zur Erstellung des Archivs benötigt).

-   **Schreibrechte:** Das System benötigt Schreibrechte im temporären Verzeichnis des Systems (für Sperrdateien und die ZIP-Erstellung).


## Benutzung

Nach der Aktivierung finden Nutzer in ihren **Einstellungen** unter **Zusatz-Einstellungen** den Punkt **Personal Post Exporter**. Dort kann der Zeitraum, das Design und die Art der Bilddarstellung gewählt werden.

## Lizenz

Dieses Projekt ist unter der **AGPL-3.0-or-later** lizenziert.
