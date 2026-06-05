# Konto-Import-Schutz (Account Import Guard)

## Deutsch

Friendica-Addon für Version 2026.05.

Blockiert den unbefugten Zugriff auf `/user/import` basierend auf einer administrativen Richtlinie, während der Konto-Import für berechtigte Benutzer verfügbar bleibt.

Es entfernt außerdem den Link zum öffentlichen Import aus dem Registrierungsformular für nicht berechtigte Besucher. Der eigentliche Schutz erfolgt über den frühen Hook `init_1`, der nicht autorisierte Anfragen an `/user/import` abfängt und umleitet, noch bevor das Import-Modul hochgeladene Dateien verarbeiten kann.

### Admin-Einstellungen

Im Administrator-Bereich des Servers (unter *Addons* -> *Account Import Guard*) kann eine Richtlinie festgelegt werden:

- **Nur angemeldete Benutzer** (`users`, Standard): Nur angemeldete Benutzer dürfen den Import nutzen; Gäste werden blockiert.
- **Nur Site-Administratoren** (`admin`): Nur Administratoren dürfen den Import nutzen; normale Nutzer und Gäste werden blockiert.
- **Für alle verbieten (inklusive Administratoren)** (`all`): Der Import ist für absolut jeden deaktiviert.

### Verhalten

- Nicht autorisierter Zugriff auf `/user/import`: Umleitung zur Basis/Home mit einer Hinweismeldung
- Datenbank: Speichert nur die gewählte Richtlinie in der Konfigurationstabelle. Es werden keine Benutzer-, Beitrags- oder Kontaktdaten verändert.

### Hooks

- `init_1`: Blockiert den nicht autorisierten Zugriff
- `register_form`: Entfernt den Import-Block aus dem Registrierungs-Template, falls der Besucher nicht berechtigt ist
- `page_end`: Fallback-Bereinigung für bereits gerendertes HTML bei abweichenden Themes/Markup

---

## English

Friendica 2026.05 addon.

Blocks unauthorized access to `/user/import` based on an administrative policy, while keeping account import available for authorized users.

It also removes the public import link from the registration form for unauthorized visitors. The actual protection is the early `init_1` hook that redirects unauthorized `/user/import` requests before the import module can process uploads.

### Admin Settings

In the server administration panel (under *Addons* -> *Account Import Guard*), a policy can be selected:

- **Only logged-in users** (`users`, default): Only logged-in users can import accounts; anonymous visitors are blocked.
- **Only site administrators** (`admin`): Only site administrators can import accounts; normal users and visitors are blocked.
- **Block for everyone, including administrators** (`all`): Account import is disabled for everyone.

### Behavior

- Unauthorized access to `/user/import`: redirected to basis/home with a notice message
- Database: Stores only the chosen policy in the configuration table. No user, post, or contact data is modified.

### Hooks

- `init_1`: blocks unauthorized access
- `register_form`: removes the import block from the register template if the user is not authorized
- `page_end`: fallback removal for rendered register pages/themes with custom markup

---

## Lizenz / License

GNU Affero General Public License v3.0 (AGPL-3.0-or-later)

Autor / Author: [Jools](https://friendica.de/profile/jools)

Erstellt mit Unterstützung von Gemini und Claude. / Created with Gemini and Claude.

Weitere Informationen zu diesem Addon auf [friendica.dev](https://friendica.dev)
