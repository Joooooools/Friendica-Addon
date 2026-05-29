
# EasyCompose — Friendica Addon

  

Ein Schreib- und Barrierefreiheits-Assistent für den Friendica-Editor (/compose). / A writing and accessibility assistant for the Friendica editor (/compose).

  

---

  

## Deutsch

  

### Funktionen

  

**Schreibassistent-Panel**

  

Über ein ausklappbares Panel direkt unter dem Editor werden beim Schreiben folgende Analysen live durchgeführt:

-  **Absatzstruktur**: Zeigt an, ob der Text für seine Länge ausreichend strukturiert ist.

-  **Textbalance**: Weist auf besonders lange Sätze (> 25 Wörter) hin, um den Lesefluss zu wahren.

-  **Link-Dichte**: Gibt Hinweise bei einer ungewöhnlich hohen Anzahl von URLs.

-  **Hashtag-Dichte**: Hilft dabei, die Verwendung von Hashtags auszubalancieren.

  

**Barrierefreiheits-Checkliste (A11y)**

-  **Bildbeschreibungen**: Prüft, ob eingebettete Bilder (`[img]`) über eine Alt-Text-Beschreibung verfügen.

-  **Emoji-Häufung**: Warnt bei langen Ketten von Emojis ohne Leerzeichen (dies kann Sprachausgaben für sehbehinderte Menschen stören).

-  **Strukturierung**: Zeigt an, ob bei längeren Texten Absätze zur besseren Lesbarkeit verwendet wurden.

  

**Vorschau**

Aktiviert ein Split-Screen-Layout (Editor links, Beitrags-Vorschau rechts), welches ressourcenschonend manuell über die Vorschau-Schaltfläche aktualisiert wird.

**Fokus-Vorschau (inkl. Mobil-Ansicht)**
Bietet eine bildschirmfüllende Vorschau des Beitrags. Neben der normalen Desktop-Vorschau kann hier eine Vorschau für die mobile Ansicht simuliert werden, um die Darstellung auf Smartphones zu prüfen.

  

**Zen-Modus (Ablenkungsfrei)**

  

Blendet alle ablenkenden Elemente der Friendica-Benutzeroberfläche aus und maximiert das Eingabefeld für einen ungestörten Schreibfokus.

  

**Kompatibilität mit Mobilgeräten**

- EasyCompose ist ausschließlich für Desktop-Bildschirme (Bildschirmbreiten ab 992px) konzipiert und optimiert. Um die mobile Ansicht schlank und übersichtlich zu halten und den begrenzten Platz auf Touchscreens für die virtuelle Tastatur freizuhalten, werden alle Schaltflächen und Panels auf Smartphones und Tablets automatisch vollständig ausgeblendet.

  

### Kriterien für Lesbarkeit & Barrierefreiheit

  

Die Bewertungen und Empfehlungen in EasyCompose basieren auf etablierten Richtlinien für Online-Lesbarkeit, Barrierefreiheit (A11y) und Web-Schreibstile. Alle Berechnungen erfolgen lokal im Browser des Nutzers:

  

1.  **Absatzstruktur (Lesbarkeit & Scanbarkeit)**

Online-Leser überfliegen Texte (Scannen). Zu lange, ununterbrochene Textblöcke („Bleiwüsten“) schrecken ab und erschweren die Informationsaufnahme.

-  **Kurzer Beitrag (Score 100)**: Bei kurzen Texten (unter 600 Zeichen) sind keine Absätze zwingend nötig.

-  **Gut strukturiert (Score 100)**: Mehr als ein Absatz bei längeren Texten.

-  **Sehr kompakt (Score 60)**: Wenn der Text über 1200 Zeichen lang ist, aber weniger als 3 Absätze hat.

-  **Ein Block (Score 30)**: Wenn der Text über 600 Zeichen lang ist, aber aus einem einzigen ununterbrochenen Block besteht.

  

2.  **Textbalance & Satzlängen (Verständlichkeit)**

Schachtelsätze mit vielen Nebensätzen zwingen das Gehirn, Informationen zwischenzuspeichern, was die Verständlichkeit drastisch senkt. Sätze mit mehr als 25 Wörtern gelten als lange Sätze.

-  **Leicht lesbar (Score 100)**: Durchschnittliche Satzlänge liegt bei maximal 16 Wörtern, keine Schachtelsätze (> 25 Wörter).

-  **Medium (Score 75)**: Durchschnittliche Satzlänge liegt zwischen 17 und 24 Wörtern oder es gibt genau einen langen Schachtelsatz.

-  **Verschachtelt (Score 40)**: Durchschnittliche Satzlänge überschreitet 24 Wörter oder es gibt 2 oder mehr lange Sätze.

  

3.  **Linkdichte (Visuelle Ruhe)**

Zu viele Hyperlinks stören den Lesefluss, da das Auge an farbig hervorgehobenen URLs hängen bleibt. Zudem verleiten sie zum vorzeitigen Abspringen.

-  **Dezent (Score 100)**: Maximal 3 Links im Text (perfekt für den Informationsfluss).

-  **Medium (Score 70)**: 4 bis 5 Links (erhöhte Ablenkung).

-  **Sehr dicht (Score 30)**: Mehr als 5 Links (der Beitrag wirkt überladen, fast wie Spam).

  

4.  **Hashtag-Dichte (Ästhetik & Lesbarkeit)**

Hashtags sind hervorragend für die Auffindbarkeit, aber eine Inflation von Hashtags stört das Schriftbild massiv.

-  **Dezent (Score 100)**: Maximal 3 Hashtags (perfekt ausgewogen).

-  **Viele (Score 75)**: 4 bis 6 Hashtags (visuelle Unruhe).

-  **Überladen (Score 30)**: Mehr als 6 Hashtags (stört den Lesefluss erheblich).

  

5.  **Barrierefreiheits-Checkliste (Accessibility / A11y)**

Diese Kriterien basieren direkt auf den WCAG-Richtlinien (Web Content Accessibility Guidelines) für den barrierefreien Zugang zum Fediverse:

-  **Alt-Texte bei Bildern**: Jedes Bild (`[img]`) muss eine alternative Beschreibung besitzen (nativ via `[img alt="beschreibung"]` oder via EasyPhoto-Format `[img=url]beschreibung[/img]`). Screenreader lesen diese Texte vor. Fehlen sie, ist das Bild für blinde Menschen nicht existent (WCAG 2.2 SC 1.1.1).

-  **Emoji-Häufung**: Erkennt direkt aufeinanderfolgende Emoji-Ketten von 5 oder mehr Symbolen. Screenreader lesen jedes Emoji einzeln beim Namen vor (z.B. „Grinsendes Gesicht, Rotes Herz...“), was lange Emoji-Ketten im Audiofluss unerträglich macht.

-  **Schreien (ALL CAPS)**: Erkennt, ob ganze Sätze (mit mehr als 4 Wörtern) komplett in GROSSBUCHSTABEN geschrieben sind. ALL CAPS gilt online als Schreien und ist für Menschen mit Leseschwäche (Legasthenie) oder Sehbehinderungen extrem schwer zu entziffern, da charakteristische Wortumrisse fehlen.

  

---

  

### Hinweis für Admins: Theme-Kompatibilität

  

Bei stark angepassten oder vollständig eigenen Themes kann das CSS von EasyCompose (insbesondere das Split-Screen-Layout der Vorschau und die Zen-Modus-Stile) optisch in das bestehende Theme-Layout eingreifen. In solchen Fällen empfiehlt sich ein kurzer Test auf der `/compose`-Seite nach der Aktivierung. EasyCompose setzt zudem einige Klassen auf `document.body` (alle mit dem Präfix `ec-`); Kollisionen mit anderen Addons sind dadurch unwahrscheinlich, aber bei sehr exotischen Themes/Addons primär optisch denkbar.

  

---

  

### Privatsphäre & Sicherheit

  

-  **Keine Übertragung an Drittanbieter**: Alle Textanalysen erfolgen ausschließlich lokal im Webbrowser des Nutzers. Es werden keine externen Ressourcen, APIs, Tracking-Skripte oder Cookies geladen oder verwendet.

-  **Vorschau über Friendica-Core**: Die manuelle Beitrags-Vorschau wird über deine eigene Friendica-Instanz (Friendica-Core) gerendert.

-  **Minimale Speicherung**: Es werden keine eigenen Datenbanktabellen benötigt oder angelegt. Die einzige serverseitige Speicherung ist die persönliche Einstellung des Benutzers in der Standard-Friendica-Tabelle `pconfig` zur Aktivierung oder Deaktivierung des Addons.

-  **Keine Hintergrundprozesse**: Es laufen keine Hintergrundprozesse oder Cronjobs.

-  **CSRF**: Das Speichern der Einstellung läuft über den Standard-Hook `addon_settings_post`. Den CSRF-Token (`settings_addon`) prüft Friendica-Core selbst, bevor der Hook aufgerufen wird – das Addon muss und darf an dieser Stelle nichts eigenes prüfen.

  

---

  

### Technische Details & Performance

  

-  **MutationObserver (Subtree)**: Um programmatische Änderungen am Editor (z. B. durch Bildupload-Addons oder Drittanbieter-Plugins) sofort zu erfassen, überwacht ein `MutationObserver` das Eltern-Element des Textfelds. Mögliche Performance-Einflüsse bei dynamischen DOM-Änderungen werden durch ein hocheffizientes 300ms-Debounce auf ein absolutes Minimum reduziert.

-  **800ms Polling-Fallback**: Ein Sicherheits-Intervall prüft alle 800ms nach Änderungen, falls externe Erweiterungen (z. B. Passwort-Manager oder Browser-Autovervollständigungen) den Text ändern, ohne Browser-Events auszulösen. Dieses Intervall läuft ausschließlich bei geöffnetem Analyse-Panel und vergleicht nur rohe Variablen, was die CPU-Last auf extrem langsamen Geräten minimiert.

  

---

  

### Installation

  

1. Kopiere den Ordner `easycompose` in das Verzeichnis `addon/` deiner Friendica-Installation.

2. Aktiviere das Addon im Administrationsbereich unter **Addons**.

3. Der Assistent steht anschließend auf der eigenständigen Beitragsseite `/compose` zur Verfügung.

  

---

  

### Kompatibilität & getestete Umgebung

  

**Integration mit EasyPhoto und QuickPhoto:**

  

EasyCompose erkennt aktiv die Bild-Strukturen der Addons EasyPhoto und QuickPhoto (u. a. die CSS-Klasse `.ep-list` sowie die Platzhalter-Variablen `window.qp_i18n` und `window.easyphoto_l10n`) und das vereinfachte BBCode-Format `[img]url|beschreibung[/img]`. Diese Integration ist gewollt und notwendig, damit die Alt-Text-Prüfung und der Bild-Zen-Modus mit diesen Addons korrekt zusammenarbeiten. Technisch bedeutet das eine bewusste Kopplung an die internen Klassen- und Variablennamen dieser beiden Addons: Sollten EasyPhoto oder QuickPhoto diese Namen in zukünftigen Versionen ändern, kann die jeweilige Integration still ausfallen (kein Absturz, lediglich die betreffende Komfortfunktion entfällt). Die Kernfunktionen von EasyCompose bleiben davon unberührt.

  

---

  

## English

  

### Features

  

**Writing Assistant Panel**

  

A collapsible panel located below the editor offers real-time evaluations while typing:

-  **Paragraph Structure**: Analyzes whether your post is appropriately structured for its length.

-  **Text Balance**: Highlights very long sentences (> 25 words) to help maintain reading flow.

-  **Link Density**: Alerts you when there is an unusually high concentration of links.

-  **Hashtag Density**: Helps you keep the amount of hashtags balanced.

  

**Accessibility Checklist (A11y)**

-  **Image Descriptions**: Checks if embedded images (`[img]`) include alternative descriptions (alt-text).

-  **Emoji Overload**: Detects consecutive emoji clusters without spaces (which disrupt screen readers).

-  **Paragraph Requirements**: Confirms if paragraphs are properly utilized in longer writings.

  

**Preview**

Enables a dynamic split-screen layout with the editor on the left and the rendered post preview on the right, updated manually via the preview button to conserve server resources.

**Focus Preview (incl. Mobile View)**
Provides a fullscreen preview of the post. In addition to the standard desktop preview, it allows simulating a mobile view to check how the post renders on smartphones.

  

**Zen Mode (Distraction-Free)**

  

Hides header, footer, sidebars, and widgets to provide a clean, writing-focused typography layout.

  

**Mobile Compatibility**

- EasyCompose is designed and optimized exclusively for desktop viewports (screen widths of 992px and above). To ensure a clean, clutter-free mobile editing environment and preserve the limited screen estate on touch devices for the virtual keyboard, all assistant buttons, panels, and Zen-mode options are automatically hidden on smartphones and tablets.

  

### Readability & Accessibility Criteria

  

The evaluations and recommendations in EasyCompose are based on established guidelines for online readability, web accessibility (A11y), and web writing styles. All calculations are performed locally within your browser:

  

1.  **Paragraph Structure (Readability & Scanability)**

Online readers scan texts. Excessively long, unbroken blocks of text discourage reading and hinder information retention.

-  **Short post (Score 100)**: For short texts (under 600 characters), paragraph breaks are not strictly required.

-  **Well structured (Score 100)**: More than one paragraph in longer texts.

-  **Very compact (Score 60)**: Text is longer than 1200 characters but has fewer than 3 paragraphs.

-  **One block (Score 30)**: Text is longer than 600 characters but consists of a single unbroken block.

  

2.  **Text Balance & Sentence Lengths (Comprehensibility)**

Complex nested sentences force the brain to buffer information, drastically reducing comprehension. Sentences with more than 25 words are considered long sentences.

-  **Easy to read (Score 100)**: Average sentence length is at most 16 words, with zero long sentences (> 25 words).

-  **Medium (Score 75)**: Average sentence length is between 17 and 24 words, or there is exactly one long sentence.

-  **Complex (Score 40)**: Average sentence length exceeds 24 words, or there are 2 or more long sentences.

  

3.  **Link Density (Visual Calmness)**

Too many hyperlinks disrupt the reading flow as the eye gets caught on color-highlighted URLs. They also encourage premature abandonment of the post.

-  **Subtle (Score 100)**: Maximum of 3 links in the text (recommended for optimal informational flow).

-  **Medium (Score 70)**: 4 to 5 links (increased distraction).

-  **Very dense (Score 30)**: More than 5 links (post feels cluttered, almost like spam).

  

4.  **Hashtag Density (Aesthetics & Readability)**

Hashtags are excellent for discoverability, but an inflation of hashtags severely disrupts typography.

-  **Subtle (Score 100)**: Maximum of 3 hashtags (balanced density).

-  **Many (Score 75)**: 4 to 6 hashtags (visual restlessness).

-  **Very dense (Score 30)**: More than 6 hashtags (significantly impairs reading flow).

  

5.  **Accessibility Checklist (A11y)**

These criteria are directly derived from WCAG (Web Content Accessibility Guidelines) for accessible participation in the Fediverse:

-  **Alt-Text for Images**: Every image (`[img]`) must have an alternative text description (either natively via `[img alt="description"]` or via EasyPhoto format `[img=url]description[/img]`). Screen readers read these descriptions aloud. Without them, the image is non-existent to blind users (WCAG 2.2 SC 1.1.1).

-  **Emoji Overload**: Detects consecutive emoji chains of 5 or more symbols. Screen readers read every single emoji aloud by its phonetic name (e.g., "grinning face, red heart..."), making long consecutive clusters exhausting to listen to.

-  **Shouting (ALL CAPS)**: Detects whether whole sentences (with more than 4 words) are written entirely in UPPERCASE. ALL CAPS is online shorthand for shouting and is extremely difficult to read for people with reading difficulties (dyslexia) or low vision due to the lack of distinct word shapes (ascenders and descenders).

  

---

  

### Note for Admins: Theme Compatibility

  

With heavily customized or fully custom themes, EasyCompose's CSS (particularly the split-screen preview layout and Zen Mode styles) may visually interfere with the existing theme layout. In such cases, a brief test on the `/compose` page after activation is recommended. EasyCompose also sets a few classes on `document.body` (all prefixed with `ec-`); collisions with other addons are therefore unlikely, but with very exotic themes/addons remain conceivable on a purely cosmetic level.

  

---

  

### Privacy & Security

  

-  **No third-party data transfer**: All text analysis runs entirely locally inside the user's web browser. No external scripts, APIs, tracking mechanisms, or cookies are loaded or utilized.

-  **Preview via Friendica Core**: The manual post preview is rendered using your own Friendica instance (Friendica Core).

-  **Minimal storage**: No custom database tables are created or required. The only server-side storage is the user's preference (enable/disable toggle) saved in the standard Friendica `pconfig` table.

-  **No background processes**: No cronjobs or background worker processes are registered.

-  **CSRF**: The setting is saved via the standard `addon_settings_post` hook. The CSRF token (`settings_addon`) is verified by Friendica Core itself before the hook is invoked, so the addon neither can nor should re-check it here.

  

---

  

### Technical Details & Performance

  

-  **MutationObserver (Subtree)**: To reliably detect programmatic changes to the editor (e.g. from photo upload addons or third-party plugins), a `MutationObserver` watches the text area's parent element. Any potential performance impact during highly dynamic DOM alterations is significantly mitigated by a highly efficient 300ms debounce.

-  **800ms Polling-Fallback**: A safety fallback interval polls for value changes every 800ms to capture programmatic edits (e.g. by password managers or auto-fill tools) that do not fire standard events. This interval is strictly active only while the analysis panel is open and performs only cheap string comparisons to ensure zero noticeable CPU overhead even on low-end hardware.

  

---

  

### Installation

  

1. Copy the `easycompose` folder into the `addon/` directory of your Friendica instance.

2. Enable the addon in the admin panel under **Addons**.

3. The editor assistant automatically integrates on the standalone `/compose` page.

  

---

  

### Compatibility & Tested Environment

  

**Integration with EasyPhoto and QuickPhoto:**

  

EasyCompose actively detects the image structures of the EasyPhoto and QuickPhoto addons (including the CSS class `.ep-list` and the placeholder variables `window.qp_i18n` and `window.easyphoto_l10n`) as well as the simplified BBCode format `[img]url|description[/img]`. This integration is intentional and necessary so that the alt-text check and the image Zen mode work correctly alongside these addons. Technically this is a deliberate coupling to the internal class and variable names of those two addons: should EasyPhoto or QuickPhoto rename them in future versions, the respective integration may fail silently (no crash, only the relevant convenience feature stops working). EasyCompose's core functionality is unaffected.

  

---

  

## Lizenz / License

  

GNU Affero General Public License v3.0 (AGPL-3.0-or-later)

  

Autor: Jools

  

Erstellt mit Unterstützung von Gemini und Claude. / Created with Gemini and Claude.

  

Weitere Informationen: friendica.dev
