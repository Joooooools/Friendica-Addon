# AuthBanner (Deutsch / English)

## Deutsch

Das Friendica Addon Authbanner zeigt eingeloggten Usern bereits vorhandene Profilbanner im jeweiligen Userprofil an (Banner von Friendica, Sharkey, Calckey, Hubzilla, Mastodon, Bluesky, Tumblr, etc.). Dieses Addon wurde ganz bewusst schlank gehalten; der Upload bzw. die Änderung eines eigenen Banners erfolgt unkompliziert über eine externe App wie Mona, Tusky, o.a.

Authbanner basiert auf dem Addon "Coverphoto" von Random Penguin und dem modifizierten Addon von feb.

### Installation

1. Kopiere den Ordner `authbanner` in das Verzeichnis `addon/` der Friendica-Installation.
2. Aktiviere die Erweiterung im Administrationsbereich der Instanz.

### Kompatibilität / Konflikte

- **CoverPhoto:** AuthBanner ist nicht kompatibel mit dem Addon `CoverPhoto`, da beide Erweiterungen das Profilbanner auf denselben Seiten rendern. Um Darstellungsfehler zu vermeiden, besitzt AuthBanner eine integrierte Schutzfunktion: Sobald `CoverPhoto` aktiv ist, deaktiviert AuthBanner seine Funktionen temporär. In diesem Fall wird Administrator:innen im Administrationsbereich und über eine System-Benachrichtigung ein entsprechender Warnhinweis angezeigt.

### Einstellungen / Verwendung

- **Benutzeraktivierung:** Jedes Mitglied kann in den persönlichen Addon-Einstellungen ("AuthBanner Einstellungen") festlegen, ob das Profilbanner angezeigt werden soll. Standardmäßig ist die Anzeige aktiv. Das Deaktivieren der Checkbox blendet das Banner komplett aus.
- **Nur für angemeldete Benutzer:** Das Addon ist ausschließlich für angemeldete Benutzer auf der lokalen Instanz aktiv. Nicht eingeloggte Besucher oder externe Gäste bekommen kein Banner gerendert.

---

## English

The Friendica addon Authbanner displays existing profile banners of users to logged-in users on their respective profiles (banners from Friendica, Sharkey, Calckey, Hubzilla, Mastodon, Bluesky, Tumblr, etc.). This addon was intentionally kept lightweight; uploading or changing your own banner is done easily via external apps such as Mona, Tusky, etc.

Authbanner is based on the "Coverphoto" addon by Random Penguin and the modified version by feb.

### Installation

1. Copy the `authbanner` folder into the `addon/` directory of your Friendica installation.
2. Activate the addon in the admin panel of your instance.

### Compatibility / Conflicts

- **CoverPhoto:** AuthBanner is not compatible with the `CoverPhoto` addon, as both extensions render the profile banner on the same pages. To prevent display errors, AuthBanner features a built-in safeguard: as soon as `CoverPhoto` is active, AuthBanner temporarily disables its features. In this case, site administrators will see a corresponding warning in the admin area and via a system notice.

### Settings / Usage

- **User Activation:** Each member can configure in their personal addon settings ("AuthBanner Settings") whether they want the profile banner to be displayed. By default, it is enabled. Unchecking the box hides the banner entirely.
- **For Logged-in Users Only:** The addon is only active for logged-in users on the local instance. Visitors who are not logged in or external guests will not see a banner rendered.

---

## Lizenz / License

GNU Affero General Public License v3.0 (AGPL-3.0-or-later)

Autor: [Jools](https://friendica.de/profile/jools)

Erstellt mit Unterstützung von Gemini und Claude. / Created with Gemini and Claude.

Weitere Informationen zu diesem Addon auf [friendica.dev](https://friendica.dev)
