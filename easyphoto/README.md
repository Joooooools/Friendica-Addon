
  

# EasyPhoto

  

  

EasyPhoto ist eine Erweiterung für Friendica, die das Hinzufügen von Bildbeschreibungen (ALT-Texten) vereinfacht. Eine Liste der Bilder erscheint automatisch unterhalb des Texteditors, sobald Bilder im Beitrag enthalten sind.

  

  

## Funktionen

  

  

-  **Einfache Bearbeitung:** Bildbeschreibungen können direkt in Feldern unterhalb des Editors eingetragen werden. Die Änderungen werden sofort in den Beitrag übernommen.

  


## Security & Privacy

-  **Schutz der Privatsphäre:** Vorschaubilder von externen Servern werden nicht automatisch geladen, um Tracking durch Drittanbieter zu verhindern.

  

-  **Sicherheit:** Eingegebene Texte werden gefiltert, um die Stabilität des Beitrags und der Plattform zu gewährleisten.

  

-  **Vielseitigkeit:** Die Funktion steht in allen Textbereichen zur Verfügung, in denen Bilder verwendet werden können (Beiträge, Kommentare, Profile).

  

  

## Installation

  

  

1. Kopieren des Ordners `easyphoto` in das Verzeichnis `addon/` der Friendica-Installation.

  

2. Aktivierung der Erweiterung im Administrationsbereich der Instanz.


## Kompatibilität / Konflikte

- **QuickPhoto:** EasyPhoto ist nicht kompatibel mit dem Addon `QuickPhoto`, da beide Erweiterungen gleichzeitig in den Beitragseditor eingreifen. Um Anzeigefehler und Browser-Konflikte zu vermeiden, besitzt EasyPhoto eine integrierte Schutzfunktion: Sobald `QuickPhoto` aktiv ist, deaktiviert EasyPhoto seine Funktionen temporär. In diesem Fall wird Administrator:innen im Friendica-Adminbereich ein entsprechender Warnhinweis angezeigt.

  
  

  

## Verwendung

  

  

Nach dem Einfügen eines Bildes in den Editor wird unterhalb des Textfeldes eine Liste mit den Vorschaubildern und den dazugehörigen Eingabefeldern für die Beschreibungen angezeigt. Eingegebener Text wird automatisch an der richtigen Stelle im Beitrag gespeichert.

  

  

---

  

  

# EasyPhoto (English)

  

  

EasyPhoto is an extension for Friendica that simplifies adding image descriptions (ALT texts). A list of images automatically appears below the text editor as soon as images are included in a post.

  

  

## Features

  

  

-  **Easy Editing:** Image descriptions can be entered directly into fields below the editor. Changes are immediately applied to the post.

  

-  **Privacy Protection:** Previews of images from external servers are not loaded automatically to prevent tracking by third-party providers.

  

-  **Security:** Entered text is filtered to ensure the stability of the post and the platform.

  

-  **Versatility:** The feature is available in all text areas where images can be used (posts, comments, profiles).

  

  

## Installation

  

  

1. Copy the `easyphoto` folder to the `addon/` directory of the Friendica installation.

  

2. Activate the extension in the administration area of the instance.


## Compatibility / Conflicts

- **QuickPhoto:** EasyPhoto is not compatible with the `QuickPhoto` addon, as both extensions modify the post editor simultaneously. To prevent display errors and browser conflicts, EasyPhoto features a built-in safeguard: as soon as `QuickPhoto` is active, EasyPhoto temporarily disables its features. In this case, site administrators will see a corresponding warning in the Friendica admin area.

  
  

  

## Usage

  

  

After inserting an image into the editor, a list with the preview images and the corresponding input fields for the descriptions is displayed below the text field. Entered text is automatically saved at the correct position in the post.

  

  

## Lizenz / License

GNU Affero General Public License v3.0 (AGPL-3.0-or-later)

Autor: [Jools](https://friendica.de/profile/jools)

Erstellt mit Unterstützung von Gemini und Claude. / Created with Gemini and Claude.

Weitere Informationen zu diesem Addon auf [friendica.dev](https://friendica.dev)