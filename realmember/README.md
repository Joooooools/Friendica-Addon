
# RealMember - Spam-Analyse Dashboard für Friendica

RealMember ist ein Assistenz-System für Friendica-Administratoren. Es klinkt sich nahtlos als eigener Tab in die Friendica-Moderationsübersicht ein und hilft Administratoren dabei, **bereits registrierte Nutzer** auf mögliche Spam-Muster zu analysieren.

> ⚠️ **Wichtig zur Funktionsweise:**
> RealMember überwacht **nicht** den Live-Registrierungsprozess und blockiert keine Neuanmeldungen in Echtzeit!
> Es handelt sich um ein **rein imperatives, passives Dashboard**. Die Analyse der Accounts findet ausschließlich in dem Moment statt, in dem der Administrator das RealMember-Dashboard aufruft.

---

## 📖 Was kann RealMember?

### ✅ Das kann RealMember:
- **Risiko-Bewertung:** Jeder registrierte Account wird beim Laden des Dashboards automatisch anhand mehrerer Kriterien analysiert und erhält einen Spam-Risiko-Score (0 % bis 100 %).
- **Wegwerf-E-Mail-Erkennung:** E-Mails werden gegen eine große, Community-gepflegte Liste bekannter Wegwerf-Anbieter geprüft (Disposables).
- **Verdächtige TLDs:** RealMember erkennt auffällige Domain-Endungen (z. B. `.xyz`, `.click`, `.top`), die unverhältnismäßig oft für Spam missbraucht werden.
- **Keyword-Scanning:** Nutzernamen und Registrierungs-Notizen werden auf fast 200 bekannte Spam-Begriffe aus den Bereichen Krypto, Marketing, Erotik und Pharma gescannt.
- **Bot-Erkennung (Entropie):** Nicknames und E-Mail-Präfixe ohne natürliche Wortstruktur (z. B. `zxrt889p`) können durch eine Vokal/Konsonant-Musteranalyse identifiziert werden.
- **Nickname-Häufigkeit im Fediverse:** RealMember zählt, auf wie vielen anderen Instanzen derselbe Nickname bereits bekannt ist (aus Friendicas eigener Kontaktdatenbank). Spammer registrieren denselben Handle oft parallel auf Dutzenden Servern — das ergibt ein zusätzliches, schwaches Signal.
- **Admin-Regeln:** Deine nativen E-Mail-Sperrlisten aus den Friendica-Settings (`disallowed_email`) fließen direkt in das Scoring ein (+100 % Kritik-Risiko).
- **Suchen & Filtern:** RealMember erlaubt die schnelle Filterung nach "Spamverdacht" und das exakte Sortieren nach dem höchsten Risiko-Score, wodurch verdächtige Accounts sofort ganz oben stehen.

### 🔒 Sicherheitsgarantien:
- **Rein lesend / Read-Only:** RealMember führt **keine** Datenbank-Änderungen (`INSERT`, `UPDATE` oder `DELETE`) durch.
- **Keine Automatismen:** RealMember wird niemals eigenmächtig Accounts sperren, löschen oder bearbeiten.
- **Privatsphäre:** Das Dashboard ist ausschließlich für den Site-Administrator sichtbar.
- **Autark:** Außer der optionalen Aktualisierung der Wegwerf-Mail-Liste werden keine externen Dienste kontaktiert.

### ⚠️ Das kann RealMember *nicht*:
- **Keine Registrierungs-Zensur:** RealMember blockt niemanden bei der Anmeldung.
- **Keine Push-Alarme:** Es gibt keine Echtzeit-Benachrichtigungen bei neuen Spammern.
- **Kein Inhalts-Scan:** Benutzerbeiträge, private Nachrichten oder Kommentare werden von RealMember nicht gelesen oder analysiert.
- **Keine Garantie:** RealMember ist "nur" ein Werkzeug. Ein Score von 100 % bedeutet nicht zwangsläufig, dass es ein Spammer ist (False Positives sind möglich). Die finale Entscheidung triffst immer du im nativen Moderations-Tab von Friendica.

### 🧠 Wichtig zur Nickname-Häufigkeit:
Die Layer-4-Prüfung ("Nickname auf X anderen Servern bekannt") ist **nur ein zusätzliches Signal** — keine Identifikation. Im Fediverse sind Nicknames **nicht eindeutig**: Jede Instanz vergibt ihre Nicknames unabhängig. Ein Nutzer `testi` auf *server-a.de* ist nicht zwangsläufig dieselbe Person wie `testi` auf *server-b.org*. Häufige Vornamen wie „stefan" oder „michael" werden naturgemäß auf vielen Servern auftauchen — das macht ihre Träger nicht zu Spammern.

Aus diesem Grund wertet RealMember die Häufigkeit nur **konservativ** aus:
- Sehr kurze Nicks (unter 6 Zeichen) werden bewusst nicht bewertet.
- Technische Standardnamen (`support`, `webmaster`, `moderator`, `friendica` …) werden ausgeschlossen.
- Es gibt **keine** Vornamen-Filterliste.
- Die maximale Punktzahl ist auf +25 begrenzt — das hebt einen Account höchstens auf die Stufe „Auffällig", niemals automatisch auf „Kritisch".

---

## 🛠️ Setup & Wartung

RealMember funktioniert direkt nach der Aktivierung im Admin-Panel und bringt eine kleine Basis-Liste für E-Mail-Anbieter mit. **Für eine hohe Erkennungsrate wird ein automatisches Update empfohlen.**

RealMember bezieht die aktuelle Domain-Liste direkt aus einem öffentlichen GitHub-Repository der Community. Um die Liste stets aktuell zu halten, kannst du einen Cronjob einrichten, z. B. (oder für deinen User www-data):

```bash
0 3 * * * /usr/bin/php /var/www/html/friendica/addon/realmember/scripts/update_domains.php
```

> ⚠️ **Sicherheitshinweis zum Cronjob:**
> Bitte beachte, dass dieses Skript (`update_domains.php`) auf externe Inhalte zugreift und eine PHP-Datei (`disposable_domains.php`) generiert. Wir empfehlen diesen automatisierten Cronjob nur, wenn du der genannten GitHub-Quelle (disposable-email-domains) vertraust. Andernfalls kannst du das Update-Skript auch bei Bedarf manuell in der Konsole ausführen, um volle Kontrolle über die Änderungen zu behalten oder die Domains von Hand einpflegen.

---
---

# RealMember - Spam Analysis Dashboard for Friendica

RealMember is an assistance system for Friendica administrators. It seamlessly integrates as its own tab into the Friendica moderation overview and helps administrators analyze **already registered users** for potential spam patterns.

> ⚠️ **Important note on operation:**
> RealMember does **not** monitor the live registration process and does not block new sign-ups in real time!
> It is a **purely imperative, passive dashboard**. Account analysis takes place exclusively at the moment the administrator opens the RealMember dashboard.

---

## 📖 What can RealMember do?

### ✅ This is what RealMember can do:
- **Risk assessment:** Every registered account is automatically analyzed against several criteria when the dashboard is loaded and receives a spam risk score (0% to 100%).
- **Disposable email detection:** Emails are checked against a large, community-maintained list of known disposable email providers.
- **Suspicious TLDs:** RealMember recognizes suspicious domain endings (e.g., `.xyz`, `.click`, `.top`) that are disproportionately abused for spam.
- **Keyword scanning:** Usernames and registration notes are scanned for nearly 200 known spam terms from the areas of crypto, marketing, adult content, and pharma.
- **Bot detection (entropy):** Nicknames and email prefixes without natural word structure (e.g., `zxrt889p`) can be identified through a vowel/consonant pattern analysis.
- **Federated nickname frequency:** RealMember counts how many other Fediverse instances already know the same nickname (using Friendica's own contact database). Spammers often register the same handle on dozens of servers in parallel — this provides an additional, weak signal.
- **Admin rules:** Your native email blocklists from Friendica settings (`disallowed_email`) flow directly into scoring (+100% critical risk).
- **Search & filter:** RealMember enables quick filtering by "spam suspicion" and exact sorting by highest risk score, so suspicious accounts immediately appear at the top.

### 🔒 Security guarantees:
- **Read-only:** RealMember performs **no** database modifications (`INSERT`, `UPDATE`, or `DELETE`).
- **No automation:** RealMember will never block, delete, or modify accounts on its own.
- **Privacy:** The dashboard is exclusively visible to the site administrator.
- **Self-contained:** Apart from the optional disposable-email list update, no external services are contacted.

### ⚠️ What RealMember *cannot* do:
- **No registration censorship:** RealMember does not block anyone at sign-up.
- **No push alerts:** There are no real-time notifications about new spammers.
- **No content scanning:** User posts, private messages, or comments are not read or analyzed by RealMember.
- **No guarantee:** RealMember is "just" a tool. A score of 100% does not necessarily mean it is a spammer (false positives are possible). The final decision is always yours in the native Friendica moderation tab.

### 🧠 Important note on the nickname frequency check:
The Layer 4 check ("Nickname known on X other servers") is **only an additional signal** — not an identification. In the Fediverse, nicknames are **not unique**: each instance assigns its nicknames independently. A user `testi` on *server-a.de* is not necessarily the same person as `testi` on *server-b.org*. Common first names such as "stefan" or "michael" will naturally appear on many servers — that does not make their owners spammers.

For this reason, RealMember evaluates frequency only **conservatively**:
- Very short nicks (fewer than 6 characters) are intentionally not evaluated.
- Technical default names (`support`, `webmaster`, `moderator`, `friendica` …) are excluded.
- There is **no** first-name filter list.
- The maximum score contribution is capped at +25 — this raises an account at most to "noticeable" level, never automatically to "critical".

---

## 🛠️ Setup & Maintenance

RealMember works immediately after activation in the admin panel and ships with a small base list of email providers. **For a high detection rate, an automatic update is recommended.**

RealMember pulls the current domain list directly from a public GitHub community repository. To keep the list up to date, you can set up a cronjob, e.g. (or for your www-data user):

```bash
0 3 * * * /usr/bin/php /var/www/html/friendica/addon/realmember/scripts/update_domains.php
```

> ⚠️ **Security note on the cronjob:**
> Please note that this script (`update_domains.php`) accesses external content and generates a PHP file (`disposable_domains.php`). We recommend this automated cronjob only if you trust the named GitHub source (disposable-email-domains). Otherwise, you can also run the update script manually in the console as needed to retain full control over the changes — or maintain the domains by hand.



## Lizenz / License

 GNU Affero General Public License v3.0 (AGPL-3.0-or-later)

Autor: [Jools](https://friendica.de/profile/jools)

Erstellt mit Unterstützung von Gemini und Claude. / Created with Gemini and Claude.

Weitere Informationen: [friendica.dev](https://friendica.dev)
