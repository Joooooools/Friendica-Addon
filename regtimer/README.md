# Registration Timer (regtimer)

Ein Friendica-Addon, mit dem Administratoren die Registrierungsrichtlinie ihrer Instanz zeitgesteuert automatisch umschalten lassen können (z. B. nachts „schließen" oder auf „Zustimmung erforderlich" setzen und morgens wieder „öffnen").

---

## Deutsch

Dieses Addon ermöglicht es Administratoren, festzulegen, dass die Registrierung der Instanz innerhalb eines bestimmten Zeitfensters automatisch auf eine andere Registrierungsrichtlinie umgestellt wird – und außerhalb dieses Fensters automatisch wieder auf die normale Richtlinie zurückgestellt wird.

### Funktionsweise
Das Addon klinkt sich in den `cron`-Hook von Friendica ein. Dieser Hook wird vom **Hintergrund-Worker** ausgelöst, also durch den regelmäßig laufenden Cronjob bzw. den Daemon, der ohnehin auf jeder Friendica-Instanz für die Hintergrundarbeit zuständig ist.

Bei jedem Worker-Durchlauf prüft das Addon die aktuelle Uhrzeit:
- Liegt sie **innerhalb** des konfigurierten Zeitfensters, wird die Registrierungsrichtlinie der Instanz dauerhaft (persistent in der Konfiguration) auf den für diesen Zeitraum gewählten Wert gesetzt.
- Liegt sie **außerhalb**, wird die Richtlinie auf den für die übrige Zeit gewählten Wert zurückgestellt.

Anders als bei einer reinen Laufzeit-Überschreibung wird hier der **tatsächliche** Konfigurationswert `register_policy` umgeschaltet. Das bedeutet: Der im Admin-Panel angezeigte Wert entspricht immer dem real wirksamen Zustand, und es werden ausschließlich offizielle, addon-taugliche Schnittstellen verwendet.

> **Hinweis:** Da das Umschalten den persistenten Konfigurationswert verändert, wird die im Admin-Panel unter *Site* angezeigte Registrierungsrichtlinie durch dieses Addon automatisch verändert. Das ist beabsichtigt.

### ⚠️ Wichtig: Der Umschaltzeitpunkt hängt vom Worker ab
Das Addon schaltet die Registrierung **nicht sekundengenau** zur eingestellten Uhrzeit um. Die Umstellung wird vom Hintergrund-Worker durchgeführt, und damit gilt:

- **Die Genauigkeit hängt davon ab, wie oft der Worker läuft.** Üblich ist ein Intervall von 5–10 Minuten. Läuft der Worker z. B. alle 10 Minuten, kann eine eingestellte Startzeit von `22:00` irgendwann zwischen `22:00` und `22:10` wirksam werden. Je kürzer das Worker-Intervall, desto genauer die Umschaltung.
- **Die Umschaltung erfolgt nur, solange der Worker tatsächlich läuft.** Bei einem cronbasierten Setup ist das beim nächsten geplanten Cron-Lauf der Fall; bei einem Daemon-Setup beim nächsten internen Cron-Zyklus des Daemons.
- **Bei Verwendung des „Frontend-Worker"-Notbehelfs** (für Hostings ohne echten Cron/Daemon) hängt der Zeitpunkt von Besucherzugriffen ab und kann entsprechend ungenau oder verzögert sein. Für eine zuverlässige zeitgesteuerte Umschaltung wird ein echter Cronjob oder der Daemon empfohlen.

Das typische Worker-Intervall wird beim Einrichten von Friendica festgelegt, z. B. so:

```
*/10 * * * * cd /pfad/zu/friendica; /usr/bin/php bin/worker.php
```

Der Wert `*/10` bedeutet „alle 10 Minuten". Wer die Umschaltung genauer haben möchte, kann das Worker-Intervall verkürzen (z. B. `*/5`), sollte dabei aber die Serverlast im Blick behalten.

### Zeitzonen-Verhalten
- Start- und Endzeit werden in der persönlichen Zeitzone des Administrators eingegeben, der die Einstellungen speichert.
- Diese Zeitzone wird gespeichert; die Auswertung des Zeitfensters erfolgt immer relativ zu dieser Zeitzone.
- Die Benutzeroberfläche zeigt sowohl die aktuelle Uhrzeit der Instanz (Server-Standard) als auch deine persönliche Administrator-Uhrzeit an, um die Einrichtung zu erleichtern.

### Konfiguration
Die Konfiguration erfolgt über das Admin-Dashboard unter **Addons** > **Registration Timer**:
1. **Registrierungs-Timer aktivieren**: Schaltet die zeitgesteuerte Umstellung ein/aus.
2. **Startzeit**: Beginn des Zeitraums (Format `HH:MM`, z. B. `22:00`).
3. **Endzeit**: Ende des Zeitraums (Format `HH:MM`, z. B. `06:00`).
4. **Registrierungsrichtlinie während des Zeitraums**: Status innerhalb des Zeitfensters (z. B. nachts) – Geschlossen, Zustimmung erforderlich oder Offen.
5. **Registrierungsrichtlinie außerhalb des Zeitraums**: Status außerhalb des Zeitfensters (z. B. tagsüber), auf den zurückgestellt wird.

Ein über Mitternacht reichendes Fenster (z. B. `22:00`–`06:00`) wird korrekt behandelt.

### Verhalten beim Deaktivieren
Wird das Addon deaktiviert, während es gerade die „Nacht"-Richtlinie aktiv hält, würde der Worker die Richtlinie nicht mehr zurückstellen. Um zu vermeiden, dass `register_policy` dauerhaft auf „Geschlossen" oder „Zustimmung erforderlich" hängen bleibt, stellt das Addon beim Deaktivieren sofort die für „außerhalb des Zeitraums" konfigurierte Richtlinie wieder her. Sollte dieses Zurücksetzen fehlschlagen, wird eine Warnung angezeigt und der Vorgang protokolliert – prüfe in dem Fall die Registrierungsrichtlinie manuell in den Seiteneinstellungen.

### Hinweis: Nach einem Addon-Update den Timer prüfen
Friendica ruft beim Aktualisieren eines Addons intern dieselbe Routine auf wie beim Deaktivieren, und es gibt keine Möglichkeit, beide Fälle programmatisch zu unterscheiden. Das Addon greift deshalb beim Aktualisieren bewusst **nicht** in seine eigene Aktivierung ein – deine Einstellungen und der Aktiv-Status bleiben über ein Update hinweg erhalten. Es schadet aber nicht, nach einem Update einmal kurz in den Addon-Einstellungen zu prüfen, ob der Timer noch wie gewünscht aktiviert ist und die Zeiten stimmen.

**Sonderfall – Update genau während des Nachtfensters:** Da das Addon beim Deaktivieren die Tages-Richtlinie wiederherstellt (damit eine echte Deinstallation die Registrierung nicht dauerhaft gesperrt lässt) und ein Update für das Addon nicht von einer Deaktivierung unterscheidbar ist, kann ein Update, das zufällig in das aktive Nachtfenster fällt, die Registrierung **kurzzeitig** auf die Tages-Richtlinie zurückstellen. Beim nächsten Worker-Lauf wird automatisch wieder korrekt auf die Nacht-Richtlinie geschaltet. Das Zeitfenster dieses Zustands entspricht also höchstens dem Worker-Intervall (typisch bis zu 10 Minuten). Wenn dir das wichtig ist, führe Addon-Updates bevorzugt außerhalb des konfigurierten Nachtfensters durch.

### Hinweis: Admin-Inaktivität kann die effektive Richtlinie überschreiben
Friendica berechnet die tatsächlich wirksame Registrierungsrichtlinie über eine interne Funktion (`Register::getPolicy()`). Diese enthält eine zusätzliche Regel: Ist die System-Einstellung `admin_inactivity_limit` gesetzt und waren alle Administratoren länger als dieser Zeitraum inaktiv, gibt Friendica unabhängig vom gespeicherten Wert „Geschlossen" zurück. Auf einem solchen Knoten kann die wirksame Richtlinie also von dem abweichen, was dieses Addon setzt. Das ist Standardverhalten von Friendica und wird vom Addon bewusst nicht umgangen.

A Friendica addon that lets administrators automatically switch their node's registration policy on a time-based schedule (e.g. close registration at night and reopen it in the morning).

### How it works
The addon hooks into Friendica's `cron` hook. This hook is triggered by the **background worker** — the regularly running cron job or daemon that already handles background processing on every Friendica node.

On each worker run, the addon checks the current time:
- If it is **inside** the configured window, the node's registration policy is set persistently (in the configuration) to the policy chosen for that period.
- If it is **outside**, the policy is restored to the policy chosen for the remaining time.

Unlike a pure runtime override, this switches the **actual** `register_policy` configuration value. This means the value shown in the admin panel always matches the effective state, and only official addon-facing APIs are used.

> **Note:** Because the switch changes the persistent configuration value, the registration policy shown in the admin panel under *Site* will be changed automatically by this addon. This is intended.

### ⚠️ Important: switching time depends on the worker
The addon does **not** switch registration at the exact configured second. The change is performed by the background worker, which means:

- **Accuracy depends on how often the worker runs.** A 5–10 minute interval is typical. If the worker runs every 10 minutes, a configured start time of `22:00` may take effect anywhere between `22:00` and `22:10`. The shorter the worker interval, the more precise the switch.
- **The switch only happens while the worker is actually running.** With a cron-based setup this is at the next scheduled cron run; with a daemon setup at the daemon's next internal cron cycle.
- **When using the "frontend worker" fallback** (for hosting without a real cron/daemon), the timing depends on visitor traffic and may be inaccurate or delayed. A real cron job or the daemon is recommended for reliable scheduled switching.

The typical worker interval is set up during Friendica installation, e.g.:

```
*/10 * * * * cd /path/to/friendica; /usr/bin/php bin/worker.php
```

The `*/10` means "every 10 minutes". For a tighter switch you can shorten the worker interval (e.g. `*/5`), keeping server load in mind.

### Timezone Behavior
- Start and end times are entered in the personal timezone of the administrator saving the settings.
- This timezone is stored, and the evaluation of the time window is always done relative to it.
- The UI displays both the current instance time (server default) and your personal admin time to make setup easy.

### Configuration
Configuration is done via the Admin Dashboard under **Addons** > **Registration Timer**:
1. **Enable Registration Timer**: Enables or disables the scheduled switching.
2. **Start Time**: Start of the time period (format `HH:MM`, e.g. `22:00`).
3. **End Time**: End of the time period (format `HH:MM`, e.g. `06:00`).
4. **Registration Policy during Time Period**: State inside the window (e.g. at night) — Closed, Requires Approval, or Open.
5. **Registration Policy outside the Time Period**: State outside the window (e.g. during the day) to restore to.

A window that crosses midnight (e.g. `22:00`–`06:00`) is handled correctly.

### Behaviour when disabling
If the addon is disabled while it is currently holding the "night" policy, the worker would no longer run to restore it. To prevent `register_policy` from staying stuck on Closed or Requires Approval, the addon immediately restores the policy configured for "outside the time period" when it is disabled. If this restore fails, a warning is shown and logged — in that case check the registration policy manually under Site settings.

### Note: re-check the timer after an addon update
When you update an addon, Friendica internally calls the same routine as when disabling it, and there is no way to tell the two cases apart programmatically. The addon therefore deliberately does **not** touch its own activation state on update — your settings and the enabled status are preserved across updates. Still, it does no harm to briefly check the addon settings after an update to confirm the timer is still enabled as intended and the times are correct.

**Special case – updating during the night window:** Because the addon restores the day policy when disabled (so that a genuine uninstall never leaves registration permanently locked), and because an update is indistinguishable from a deactivation, an update that happens to fall inside the active night window can **briefly** switch registration back to the day policy. The next worker run automatically switches it back to the night policy. The window for this state is therefore at most one worker interval (typically up to 10 minutes). If this matters to you, prefer running addon updates outside the configured night window.

### Note: admin inactivity can override the effective policy
Friendica computes the actually effective registration policy via an internal function (`Register::getPolicy()`). It has an extra rule: if the system setting `admin_inactivity_limit` is set and all administrators have been inactive for longer than that period, Friendica returns "Closed" regardless of the stored value. On such a node the effective policy can therefore differ from what this addon sets. This is standard Friendica behaviour and is intentionally not bypassed by the addon.


## Lizenz / License

 GNU Affero General Public License v3.0 (AGPL-3.0-or-later)

Autor: [Jools](https://friendica.de/profile/jools)

Erstellt mit Unterstützung von Gemini und Claude. / Created with Gemini and Claude.

Weitere Informationen: [friendica.dev](https://friendica.dev)