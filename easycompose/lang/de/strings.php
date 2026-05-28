<?php

if (! function_exists("string_plural_select_de")) {
	function string_plural_select_de($n)
	{
		$n = intval($n);
		return intval($n != 1);
	}
}
$a->strings['EasyCompose'] = 'EasyCompose';
$a->strings['Writing & Accessibility Assistant'] = 'Schreib- & Barrierefreiheits-Assistent';
$a->strings['Structure & Balance'] = 'Struktur & Balance';
$a->strings['Accessibility Checklist'] = 'Barrierefreiheits-Checkliste';
$a->strings['Readability & Style'] = 'Leserlichkeit & Stil';
$a->strings['Paragraph Structure'] = 'Absatzstruktur';
$a->strings['Text Balance'] = 'Textbalance';
$a->strings['Link Density'] = 'Link-Dichte';
$a->strings['Hashtag Density'] = 'Hashtag-Dichte';
$a->strings['Balanced'] = 'Ausgewogen';
$a->strings['One block'] = 'Ein Block';
$a->strings['Very compact'] = 'Sehr kompakt';
$a->strings['Well structured'] = 'Gut strukturiert';
$a->strings['Short post'] = 'Kurzer Post';
$a->strings['Easy to read'] = 'Leicht lesbar';
$a->strings['Complex'] = 'Verschachtelt';
$a->strings['Medium'] = 'Medium';
$a->strings['Subtle'] = 'Dezent';
$a->strings['Very dense'] = 'Sehr dicht';
$a->strings['Many links'] = 'Viele Links';
$a->strings['Many tags'] = 'Viele Tags';
$a->strings['All images have descriptions (Alt-Text)'] = 'Alle Bilder haben Beschreibungen (Alt-Text)';
$a->strings['Images missing descriptions (Alt-Text) detected!'] = 'Bilder ohne Beschreibung (Alt-Text) entdeckt!';
$a->strings['No emoji overload (max 4 consecutive)'] = 'Keine Emoji-Häufung (max. 4 aufeinanderfolgend)';
$a->strings['Emoji overload detected (hurts screen readers)'] = 'Emoji-Häufung entdeckt (stört Screenreader)';
$a->strings['Good text structuring (paragraphs used)'] = 'Gute Textstrukturierung (Absätze genutzt)';
$a->strings['No paragraphs found (hard to read)'] = 'Keine Absätze gefunden (schwer zu lesen)';
$a->strings['No paragraphs required for short texts'] = 'Keine Absätze für kurze Texte nötig';
$a->strings['Your post is beautifully readable and accessible!'] = 'Dein Beitrag ist hervorragend lesbar und barrierefrei!';
$a->strings['Tip: Adding double line breaks to create paragraphs makes long texts much easier to scan.'] = 'Tipp: Doppelte Zeilenumbrüche für Absätze machen lange Texte viel einfacher zu scannen.';
$a->strings['Tip: Some sentences are very long (> 25 words). Shortening them increases reading flow!'] = 'Tipp: Einige Sätze sind sehr lang (> 25 Wörter). Kürzen erhöht den Lesefluss!';
$a->strings['Tip: Typing in ALL CAPS feels like shouting. Consider using standard capitalization.'] = 'Tipp: Das Schreiben in Großbuchstaben (ALL CAPS) wirkt wie Schreien. Nutze Standardschreibweise.';
$a->strings['Tip: Too many hashtags make the text feel restless. Try to focus on the key ones.'] = 'Tipp: Zu viele Hashtags lassen den Text unruhig wirken. Konzentriere dich auf die wichtigsten.';
$a->strings['Tip: Emoji clusters can be disruptive for visitors using assistive technology.'] = 'Tipp: Emoji-Häufungen können für Besucher, die Vorlesehilfen nutzen, störend sein.';
$a->strings['Tip: An image is missing a description (Alt-Text). Adding a brief text ensures everyone can participate!'] = 'Tipp: Einem Bild fehlt eine Beschreibung (Alt-Text). Ein kurzer Text hilft jedem teilzuhaben!';
$a->strings['Tip: Text is extremely long. Real-time analysis paused for performance.'] = 'Tipp: Der Beitrag ist extrem lang. Die Echtzeit-Analyse wurde aus Performancegründen pausiert.';
$a->strings['Preview Paused'] = 'Vorschau pausiert';
$a->strings['The post is extremely long. The live preview has been paused to keep your browser tab responsive.'] = 'Der Beitrag ist extrem lang. Die Live-Vorschau wurde pausiert, um deinen Browser-Tab flüssig zu halten.';
$a->strings['characters'] = 'Zeichen';
$a->strings['Distraction-free mode'] = 'Ablenkungsfreier Modus';
$a->strings['Writing Assistant'] = 'Schreib-Assistent';
$a->strings['Distraction-Free'] = 'Zen-Modus';
$a->strings['Image Descriptions'] = 'Bild-Beschreibungen';
$a->strings['Preview'] = 'Vorschau';
$a->strings['Refresh Preview'] = 'Vorschau aktualisieren';
$a->strings['Publish'] = 'Beitrag veröffentlichen';
$a->strings['Focus Preview'] = 'Fokus-Vorschau';
$a->strings['Mobile'] = 'Mobil';
$a->strings['Back to Editor'] = 'Zurück zum Editor';
$a->strings['Loading Preview...'] = 'Lade Vorschau...';
$a->strings['Just now · Preview'] = 'Gerade eben · Vorschau';
$a->strings['You'] = 'Du';

$a->strings['How does the analysis work?'] = 'Wie funktioniert die Analyse?';
$a->strings['No external services · All processing on your own instance'] = 'Keine externen Dienste · Alle Verarbeitung auf deiner eigenen Instanz';
$a->strings['Text analysis runs entirely in your browser — no data is sent anywhere for analysis. No third-party APIs, no cookies, no tracking. The optional post preview works exactly like Friendica\'s built-in preview button: your draft is sent to your own Friendica instance for rendering, just as it would be without this addon. The only server-side storage is your personal enable/disable preference in the standard Friendica settings.'] = 'Die Textanalyse läuft vollständig im Browser – es werden keine Daten zur Analyse übertragen. Keine Drittanbieter-APIs, keine Cookies, kein Tracking. Die optionale Beitragsvorschau funktioniert genauso wie Friendicas eingebaute Vorschau-Schaltfläche: Der Entwurf wird zur Darstellung an deine eigene Friendica-Instanz übermittelt – genau wie ohne dieses Addon. Die einzige serverseitige Speicherung ist deine persönliche Aktiviert/Deaktiviert-Einstellung in den Standard-Friendica-Einstellungen.';

$a->strings['Paragraph Structure'] = 'Absatzstruktur';
$a->strings['Counts how many paragraphs your post contains (separated by blank lines). A single unbroken block of text scores low (30 %) because readers find it hard to scan. Two or more paragraphs score 100 %. Posts under 600 characters are always rated "Short post" regardless of structure.'] = 'Zählt, wie viele Absätze dein Beitrag enthält (getrennt durch Leerzeilen). Ein einziger, ununterbrochener Textblock erzielt nur 30 %, weil er schwer zu überfliegen ist. Zwei oder mehr Absätze ergeben 100 %. Beiträge unter 600 Zeichen erhalten immer die Bewertung „Kurzer Post", unabhängig von der Struktur.';

$a->strings['Text Balance'] = 'Textbalance';
$a->strings['Measures average sentence length and flags sentences longer than 25 words. An average above 24 words scores 40 % ("Complex"), above 16 words scores 75 % ("Medium"), everything else scores 100 % ("Easy to read"). Shorter sentences improve readability for all audiences.'] = 'Misst die durchschnittliche Satzlänge und markiert Sätze mit mehr als 25 Wörtern. Ein Durchschnitt über 24 Wörter ergibt 40 % („Verschachtelt"), über 16 Wörter 75 % („Medium"), alles darunter 100 % („Leicht lesbar"). Kürzere Sätze verbessern die Lesbarkeit für alle.';

$a->strings['Link Density'] = 'Link-Dichte';
$a->strings['Counts all http/https URLs in your post. More than 5 links scores 30 % ("Very dense") — posts that are mostly links feel like spam to readers and federation filters. Up to 3 links scores 100 % ("Subtle").'] = 'Zählt alle http/https-URLs im Beitrag. Mehr als 5 Links ergibt 30 % („Sehr dicht") — linkdominierte Beiträge wirken auf Leser und Föderationsfilter wie Spam. Bis zu 3 Links ergibt 100 % („Dezent").';

$a->strings['Hashtag Density'] = 'Hashtag-Dichte';
$a->strings['Counts #hashtags. More than 6 scores 30 % ("Very dense"). Posts with fewer, focused hashtags reach more people than hashtag-stuffed ones. Up to 3 hashtags scores 100 % ("Subtle").'] = 'Zählt #Hashtags. Mehr als 6 ergibt 30 % („Sehr dicht"). Beiträge mit wenigen, gezielten Hashtags erreichen mehr Menschen als solche mit übermäßig vielen. Bis zu 3 Hashtags ergibt 100 % („Dezent").';

$a->strings['Alt-Text Check'] = 'Alt-Text-Prüfung';
$a->strings['Checks every [img] BBCode tag in your post for an alt text description. Alt text is essential for screen readers and users with visual impairments — it ensures everyone can participate in the conversation.'] = 'Prüft jeden [img]-BBCode-Tag im Beitrag auf eine Alt-Text-Beschreibung. Alt-Text ist unverzichtbar für Screenreader und sehbeeinträchtigte Nutzer — er stellt sicher, dass alle am Gespräch teilnehmen können.';

$a->strings['Emoji Check'] = 'Emoji-Prüfung';
$a->strings['Detects sequences of 5 or more consecutive emoji. Screen readers read every emoji aloud by name ("grinning face", "thumbs up" …), so a long cluster becomes an exhausting wall of words for blind users. Up to 4 consecutive emoji is fine.'] = 'Erkennt Folgen von 5 oder mehr aufeinanderfolgenden Emoji. Screenreader lesen jedes Emoji beim Namen vor („grinsendes Gesicht", „Daumen hoch" …), sodass eine lange Emoji-Kette für blinde Nutzer zu einem ermüdenden Wortwall wird. Bis zu 4 aufeinanderfolgende Emoji sind in Ordnung.';

$a->strings['Paragraph Check (Accessibility)'] = 'Absatz-Prüfung (Barrierefreiheit)';
$a->strings['For posts longer than 300 characters, checks whether at least one paragraph break exists. Screen readers and cognitive-accessibility tools benefit greatly from structured text. Short posts are always rated neutral.'] = 'Prüft bei Beiträgen über 300 Zeichen, ob mindestens ein Absatzumbruch vorhanden ist. Screenreader und Hilfsmittel für kognitive Barrierefreiheit profitieren stark von strukturiertem Text. Kurze Beiträge werden immer neutral bewertet.';

$a->strings['EasyCompose (Advanced Editor)'] = 'EasyCompose (Erweiterter Editor)';
$a->strings['Enable EasyCompose'] = 'EasyCompose aktivieren';
$a->strings['This addon extends the editor at'] = 'Dieses Addon erweitert den Editor unter';
$a->strings['with a custom layout and additional text analysis tools. While writing, the integrated assistant checks readability (e.g., sentence length and paragraphs) and accessibility (e.g., missing image descriptions). It also provides a manual post preview and a distraction-free mode.'] = 'durch ein angepasstes Layout und zusätzliche Textanalyse-Werkzeuge. Beim Schreiben prüft der integrierte Assistent die Lesbarkeit (z. B. Satzlängen und Absätze) sowie die Barrierefreiheit (z. B. fehlende Bildbeschreibungen). Zudem bietet es eine Beitrags-Vorschau und einen ablenkungsfreien Modus.';
$a->strings['Please note: EasyCompose is designed and optimized exclusively for desktop viewports. To ensure a clean and clutter-free mobile editor, all assistant panels and buttons are automatically hidden on smartphones and tablets.'] = 'Bitte beachte: EasyCompose ist exklusiv für Desktop-Bildschirme entwickelt und optimiert. Um den mobilen Editor schlank und übersichtlich zu halten, werden alle Assistenten-Schaltflächen und Panels auf Smartphones und Tablets automatisch ausgeblendet.';
$a->strings['The addon operates autonomously: all text analysis is performed locally in the user\'s browser, no external scripts or resources are loaded, and no data is sent to external services for analysis.'] = 'Das Addon arbeitet autark: Alle Textanalysen erfolgen lokal im Browser des Nutzers, es werden keinerlei externe Skripte oder Ressourcen nachgeladen und es werden keine Daten zur Analyse an externe Dienste gesendet.';
$a->strings['If you deactivate this addon, the standard Friendica editor will be restored at'] = 'Wird dieses Addon deaktiviert, wird der standardmäßige Friendica-Editor wiederhergestellt unter';
$a->strings['Addon: EasyCompose (deactivatable in settings)'] = 'Addon: EasyCompose (deaktivierbar unter Einstellungen)';
