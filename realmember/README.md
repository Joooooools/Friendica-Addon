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
- **Admin-Regeln:** Deine nativen E-Mail-Sperrlisten aus den Friendica-Settings (`disallowed_email`) fließen direkt in das Scoring ein (+100 % Kritik-Risiko).
- **Suchen & Filtern:** RealMember erlaubt die schnelle Filterung nach "Spamverdacht" und das exakte Sortieren nach dem höchsten Risiko-Score, wodurch verdächtige Accounts sofort ganz oben stehen.

### 🔒 Sicherheitsgarantien:
- **Rein lesend / Read-Only:** RealMember führt **keine** Datenbank-Änderungen (`INSERT`, `UPDATE` oder `DELETE`) durch.
- **Keine Automatismen:** RealMember wird niemals eigenmächtig Accounts sperren, löschen oder bearbeiten.
- **Privatsphäre:** Das Dashboard ist ausschließlich für den Site-Administrator sichtbar.

### ⚠️ Das kann RealMember *nicht*:
- **Keine Registrierungs-Zensur:** RealMember blockt niemanden bei der Anmeldung.
- **Keine Push-Alarme:** Es gibt keine Echtzeit-Benachrichtigungen bei neuen Spammern.
- **Kein Inhalts-Scan:** Benutzerbeiträge, private Nachrichten oder Kommentare werden von RealMember nicht gelesen oder analysiert.
- **Keine Garantie:** RealMember ist "nur" ein Werkzeug. Ein Score von 100% bedeutet nicht zwangsläufig, dass es ein Spammer ist (False Positives sind möglich). Die finale Entscheidung triffst immer du im nativen Moderations-Tab von Friendica.

---

## 🛠️ Setup & Wartung

RealMember funktioniert direkt nach der Aktivierung im Admin-Panel und bringt eine kleine Basis-Liste für E-Mail-Anbieter mit. **Für eine hohe Erkennungsrate wird ein automatisches Update empfohlen.**

RealMember bezieht die aktuelle Domain-Liste direkt aus einem öffentlichen GitHub-Repository der Community. Um die Liste stets aktuell zu halten, kannst du einen Cronjob einrichten, z.B. (oder für deinen User www-data):

```bash
0 3 * * * /usr/bin/php /var/www/html/friendica/addon/realmember/scripts/update_domains.php
```

> ⚠️ **Sicherheitshinweis zum Cronjob:**  
> Bitte beachte, dass dieses Skript (`update_domains.php`) auf externe Inhalte zugreift und eine PHP-Datei (`disposable_domains.php`) generiert. Wir empfehlen diesen automatisierten Cronjob nur, wenn du der genannten GitHub-Quelle (disposable-email-domains) vertraust. Andernfalls kannst du das Update-Skript auch bei Bedarf manuell in der Konsole ausführen, um volle Kontrolle über die Änderungen zu behalten oder die Domains von Hand einpflegen.
