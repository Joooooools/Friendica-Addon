<div class="generic-content-wrapper realmember-dashboard">
    <div class="section-title-wrapper">
        <div class="realmember-header">
            <h2>✨ {{$title}}</h2>
            <div class="realmember-stats">Analysierte Konten: {{$total}}</div>
        </div>
    </div>

    <div class="realmember-header-notice">
        <div class="alert alert-warning">
            ℹ️ RealMember ist ein <strong>automatisiertes Assistenz-System</strong> basierend auf Heuristiken. Ein hoher Risiko-Score ist ein starkes Indiz, aber kein Beweis. Die endgültige Entscheidung über ein Konto sollte <strong>immer</strong> manuell erfolgen.
        </div>
    </div>

    <div class="realmember-filters">
        <div class="filter-tabs">
            <a href="realmember?filter=recent" class="filter-btn {{if $filter == 'recent'}}active{{/if}}">Letzte 48h</a>
            <a href="realmember?filter=new" class="filter-btn {{if $filter == 'new'}}active{{/if}}">Letzte 30 Tage</a>
            <a href="realmember?filter=pending" class="filter-btn {{if $filter == 'pending'}}active{{/if}}">Wartend / Unverifiziert</a>
            <a href="realmember?filter=all" class="filter-btn {{if $filter == 'all'}}active{{/if}}">Alle Nutzer</a>
            <a href="realmember?filter=spam" class="filter-btn {{if $filter == 'spam'}}active{{/if}}">Spamverdacht</a>
        </div>

        <form action="realmember" method="get" class="realmember-search-form">
            <input type="hidden" name="filter" value="{{$filter}}">
            <input type="hidden" name="sort" value="{{$sort}}">
            <input type="hidden" name="dir" value="{{$dir}}">
            <input type="text" name="search" value="{{$search}}" placeholder="E-Mail, Name oder Handle..." class="search-input">
            <button type="submit" class="search-btn">Suchen</button>
            {{if $search}}
                <a href="realmember?filter={{$filter}}&sort={{$sort}}&dir={{$dir}}" class="clear-search" title="Suche löschen">&times;</a>
            {{/if}}
        </form>
    </div>

    <div class="realmember-sort-bar">
        <span class="sort-label">Sortieren nach:</span>
        <div class="sort-options">
            <a href="realmember?filter={{$filter}}&search={{$search}}&sort=date&dir={{if $sort == 'date' && $dir == 'desc'}}asc{{else}}desc{{/if}}" class="sort-link {{if $sort == 'date'}}active{{/if}}">
                Datum {{if $sort == 'date'}}{{if $dir == 'asc'}}↑{{else}}↓{{/if}}{{/if}}
            </a>
            <a href="realmember?filter={{$filter}}&search={{$search}}&sort=name&dir={{if $sort == 'name' && $dir == 'asc'}}desc{{else}}asc{{/if}}" class="sort-link {{if $sort == 'name'}}active{{/if}}">
                Name {{if $sort == 'name'}}{{if $dir == 'asc'}}↑{{else}}↓{{/if}}{{/if}}
            </a>
            <a href="realmember?filter={{$filter}}&search={{$search}}&sort=email&dir={{if $sort == 'email' && $dir == 'asc'}}desc{{else}}asc{{/if}}" class="sort-link {{if $sort == 'email'}}active{{/if}}">
                E-Mail {{if $sort == 'email'}}{{if $dir == 'asc'}}↑{{else}}↓{{/if}}{{/if}}
            </a>
            <a href="realmember?filter={{$filter}}&search={{$search}}&sort=score&dir={{if $sort == 'score' && $dir == 'desc'}}asc{{else}}desc{{/if}}" class="sort-link {{if $sort == 'score'}}active{{/if}}">
                Risiko {{if $sort == 'score'}}{{if $dir == 'asc'}}↑{{else}}↓{{/if}}{{/if}}
            </a>
        </div>
    </div>

    <div class="section-content-wrapper">
        {{if $users}}
        <div class="realmember-table-container">
            <table class="realmember-table">
                <thead>
                    <tr>
                        <th class="col-risk">Risiko</th>
                        <th class="col-user">Benutzer</th>
                        <th class="col-contact">E-Mail-Adresse / Registriert</th>
                    </tr>
                </thead>
                <tbody>
                    {{foreach $users as $user}}
                    <tr class="main-row risk-{{$user.risk_level}} {{if $user.is_removed}}is-removed{{/if}}">
                        <td class="col-risk">
                            <div class="risk-indicator">
                                <span class="score">{{$user.score}}%</span>
                            </div>
                        </td>
                        <td class="col-user">
                            <div class="user-main">
                                <a href="{{$user.profile_url}}" class="user-link" target="_blank" rel="noopener" title="Profil anzeigen">
                                    <span class="username">{{$user.username}}</span>
                                    <span class="nickname">@{{$user.nickname}}</span>
                                </a>
                                {{if $user.is_removed}}<span class="removed-badge">GELÖSCHT</span>{{/if}}
                            </div>
                            {{if $user.note}}
                            <div class="user-note" title="Registrierungs-Notiz">
                                "{{$user.note}}"
                            </div>
                            {{/if}}
                        </td>
                        <td class="col-contact">
                            <div class="contact-email">{{$user.email}}</div>
                            <div class="contact-date">{{$user.register_date}}</div>
                        </td>
                    </tr>
                    <tr class="details-row risk-{{$user.risk_level}} {{if $user.is_removed}}is-removed{{/if}}">
                        <td colspan="3">
                            <div class="analysis-results">
                                <span class="results-label">Analyse-Ergebnisse:</span>
                                {{if $user.reasons}}
                                <ul class="reasons-list-horizontal">
                                    {{foreach $user.reasons as $reason}}
                                    <li>{{$reason}}</li>
                                    {{/foreach}}
                                </ul>
                                {{else}}
                                <span class="no-reasons">Keine Auffälligkeiten gefunden.</span>
                                {{/if}}
                            </div>
                        </td>
                    </tr>
                    {{/foreach}}
                </tbody>
            </table>
        </div>

        <div class="realmember-pager">
            {{$pager nofilter}}
        </div>
        {{else}}
        <div class="no-results">
            <p>Keine Nutzer gefunden, die den Filterkriterien entsprechen.</p>
        </div>
        {{/if}}

        <div class="realmember-criteria-section">
            <details>
                <summary class="criteria-summary">
                    <h3>📖 Was kann RealMember?</h3>
                </summary>
                <div class="criteria-content">
                    <div class="criteria-group">
                        <h4>✅ Das kann RealMember</h4>
                        <ul>
                            <li><strong>Risiko-Bewertung aller Nutzer:</strong> Jeder registrierte Account wird automatisch anhand mehrerer Kriterien analysiert und erhält einen Risiko-Score von 0 % bis 100 %.</li>
                            <li><strong>Wegwerf-E-Mail-Erkennung:</strong> E-Mail-Adressen werden gegen eine Community-gepflegte Liste mit tausenden bekannten Wegwerf-Anbietern geprüft.</li>
                            <li><strong>Verdächtige Domains erkennen:</strong> Domain-Endungen (TLDs), die häufig für Spam missbraucht werden, werden automatisch erkannt.</li>
                            <li><strong>Keyword-Scan:</strong> Nutzernamen und Registrierungs-Notizen werden nach fast 200 verdächtigen Begriffen aus den Bereichen Pharma, Krypto, Erotik und Marketing durchsucht.</li>
                            <li><strong>Bot-Erkennung (Entropie):</strong> Zufällig generierte Nicknames und E-Mail-Präfixe ohne natürliche Wortstruktur werden durch eine Muster-Analyse erkannt.</li>
                            <li><strong>Admin-Regeln einbeziehen:</strong> Deine eigenen E-Mail-Sperrlisten aus den Friendica-Einstellungen fließen automatisch in die Bewertung ein.</li>
                            <li><strong>Filtern & Sortieren:</strong> Du kannst die Ergebnisse nach Zeitraum, Spamverdacht, Name, E-Mail oder Risiko-Score filtern und sortieren.</li>
                            <li><strong>Suche:</strong> Eine Volltextsuche über Namen, Handles und E-Mail-Adressen ist integriert.</li>
                            <li><strong>Automatische Updates:</strong> Die Wegwerf-Domain-Liste kann per Cronjob täglich automatisch aktualisiert werden.</li>
                            <li><strong>Nahtlose Integration:</strong> RealMember erscheint als Tab in der Friendica-Moderation und fügt sich nahtlos in die bestehende Oberfläche ein.</li>
                        </ul>
                    </div>

                    <div class="criteria-group">
                        <h4>🔒 Sicherheitsgarantie</h4>
                        <ul>
                            <li><strong>Rein lesend:</strong> RealMember liest ausschließlich Daten. Es werden keine Datenbank-Einträge erstellt, geändert oder gelöscht.</li>
                            <li><strong>Keine automatischen Aktionen:</strong> RealMember sperrt, löscht oder verändert niemals selbständig ein Konto. Alle Entscheidungen trifft der Admin.</li>
                            <li><strong>Nur für Admins:</strong> Das Dashboard ist ausschließlich für Site-Administratoren sichtbar.</li>
                        </ul>
                    </div>

                    <div class="criteria-group">
                        <h4>⚠️ Das kann RealMember nicht</h4>
                        <ul>
                            <li><strong>Keine Garantie:</strong> RealMember ist ein Assistenz-System. Nicht jeder markierte Account ist tatsächlich Spam, und nicht jeder Spammer wird erkannt.</li>
                            <li><strong>Kein Inhalts-Scan:</strong> Veröffentlichte Beiträge, Kommentare oder Nachrichten der Nutzer werden nicht analysiert.</li>
                            <li><strong>Keine Echtzeit-Überwachung:</strong> Die Analyse erfolgt bei jedem Seitenaufruf. Es gibt keine Push-Benachrichtigungen bei neuen Spam-Verdachtsfällen.</li>
                            <li><strong>Kein Löschen oder Sperren:</strong> RealMember kann keine Konten sperren oder löschen. Dafür nutze den Moderationsbereich von Friendica.</li>
                        </ul>
                    </div>
                </div>
            </details>

            <details>
                <summary class="criteria-summary">
                    <h3>🛠️ Setup & Wartung</h3>
                </summary>
                <div class="criteria-content">
                    <div class="criteria-group">
                        <h4>📅 Automatische Updates per Cronjob</h4>
                        <p>RealMember verwendet eine Liste bekannter Trashmail-Anbieter. Diese Liste kann manuell oder automatisch per Cronjob aktualisiert werden.</p>
                        <p>Cronjob-Eintrag für dein Systemlaufwerk:</p>
                        <pre>0 3 * * * /usr/bin/php {{$criteria.updater_path}}</pre>
                        <p><small><em>Dieser Befehl aktualisiert die Liste jeden Tag um 03:00 Uhr nachts.</em></small></p>
                        <p><small><strong>Datenquelle:</strong> <a href="https://github.com/disposable-email-domains/disposable-email-domains" target="_blank" rel="noopener nofollow noreferrer">disposable-email-domains</a> auf GitHub · <strong>Lizenz:</strong> <a href="https://creativecommons.org/publicdomain/zero/1.0/" target="_blank" rel="noopener nofollow noreferrer">CC0 1.0 (Public Domain)</a></small></p>
                    </div>
                    <div class="criteria-group">
                        <h4>🚀 Manuelles Update</h4>
                        <p>Ein Cronjob ist nicht zwingend erforderlich! Alternativ lässt sich das Update-Skript einfach bei Bedarf einmalig manuell im Terminal aufzurufen, um sich die GitHub-Liste herunterzuladen:</p>
                        <pre>php {{$criteria.updater_path}}</pre>
                    </div>
                    <div class="criteria-group">
                        <div class="realmember-important-notice">
                            <strong>⚠️ Sicherheitshinweis:</strong> Das Update-Skript greift direkt auf externe Inhalte von GitHub zu. Nutze den automatisierten Cronjob nur, wenn du dieser Datenquelle vertraust. Ansonsten kann die Liste natürlich auch von Hand eingepflegt werden.
                        </div>
                    </div>
                </div>
            </details>

            <details>
                <summary class="criteria-summary">
                    <h3>🔍 Analyse-Kriterien & Scoring</h3>
                </summary>
                <div class="criteria-content">
                    <div class="criteria-group">
                        <h4>🛡️ Deine Admin-Regeln</h4>
                        <p>RealMember liest die E-Mail-Sperrliste aus deinen Friendica-Einstellungen (<code>disallowed_email</code>). Aktuell sind dort <strong>{{$criteria.manual_count}} Regeln</strong> hinterlegt.</p>
                        <p>Wenn die E-Mail-Adresse eines Nutzers exakt auf eine dieser Regeln passt, wird der Risiko-Score sofort auf <strong>100 % (Kritisch)</strong> gesetzt.</p>
                        <p><small>💡 Du findest diese Einstellung unter: <strong>Administration</strong> → <strong>Registrierung</strong> → <strong>Nicht erlaubte Domains für E-Mails</strong></small></p>
                    </div>

                    <div class="criteria-group">
                        <h4>📧 Wegwerf-E-Mail-Erkennung</h4>
                        <p>RealMember prüft jede E-Mail-Adresse gegen eine {{if $criteria.is_updated}}<strong>automatisch aktualisierte Community-Blockliste</strong>{{else}}<strong>mitgelieferte Basis-Blockliste</strong>{{/if}} mit aktuell <strong>{{$criteria.disposable_count}} bekannten Anbietern</strong>.</p>
                        <p class="realmember-last-update">Letzte Aktualisierung: <code>{{$criteria.last_update}}</code></p>
                        <p><small><strong>Quelle:</strong> <a href="https://github.com/disposable-email-domains/disposable-email-domains" target="_blank" rel="noopener">disposable-email-domains (GitHub)</a> · <strong>Lizenz:</strong> <a href="https://creativecommons.org/publicdomain/zero/1.0/" target="_blank" rel="noopener">CC0 1.0 (Public Domain)</a></small></p>
                    </div>

                    <div class="criteria-group">
                        <h4>🌐 Verdächtige Top-Level-Domains</h4>
                        <p>Bestimmte Domain-Endungen werden überproportional häufig für Spam-Registrierungen verwendet. RealMember überwacht folgende TLDs:</p>
                        <div class="tld-list">
                            {{foreach $criteria.bad_tlds as $tld}}<span class="tld-tag">{{$tld}}</span>{{/foreach}}
                        </div>
                    </div>

                    <div class="criteria-group">
                        <h4>🔤 Keyword-Erkennung</h4>
                        <p>RealMember durchsucht Nutzernamen und Registrierungs-Notizen nach <strong>{{count($criteria.keywords)}} verdächtigen Begriffen</strong> aus den Bereichen Pharma, Krypto, Erotik, Finanzen und Marketing.</p>
                        <div class="keyword-preview">
                             {{foreach $criteria.keywords as $kw}}<span class="kw-tag">{{$kw}}</span>{{/foreach}}
                        </div>
                    </div>

                    <div class="criteria-group">
                        <h4>🧠 Muster-Analyse (Entropie)</h4>
                        <p>Spam-Bots verwenden häufig zufällig generierte Nicknames wie <code>zxyprt882</code>, die keine natürliche Wortstruktur haben. RealMember erkennt solche Muster durch eine Analyse des Verhältnisses von Vokalen zu Konsonanten.</p>
                    </div>

                    <div class="criteria-group">
                        <h4>📊 Punkteverteilung</h4>
                        <p>Jedes Kriterium vergibt eine bestimmte Punktzahl. Die Summe ergibt den Risiko-Score eines Nutzers:</p>
                        <table class="realmember-points-table">
                            <thead>
                                <tr>
                                    <th>Kriterium</th>
                                    <th>Punkte</th>
                                    <th>Stufe</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="point-critical">
                                    <td>Admin-Sperrliste (<code>disallowed_email</code>)</td>
                                    <td>+100</td>
                                    <td>Kritisch</td>
                                </tr>
                                <tr>
                                    <td>Wegwerf-E-Mail-Anbieter</td>
                                    <td>+45</td>
                                    <td>Warnung</td>
                                </tr>
                                <tr>
                                    <td>Verdächtige Top-Level-Domain</td>
                                    <td>+30</td>
                                    <td>Auffällig</td>
                                </tr>
                                <tr>
                                    <td>Spam-Keyword in der Registrierungs-Notiz</td>
                                    <td>+25</td>
                                    <td>Auffällig</td>
                                </tr>
                                <tr>
                                    <td>Spam-Keyword im Nutzernamen</td>
                                    <td>+20</td>
                                    <td>Information</td>
                                </tr>
                                <tr>
                                    <td>Verdächtiges Namensmuster (Entropie)</td>
                                    <td>+20</td>
                                    <td>Information</td>
                                </tr>
                            </tbody>
                        </table>
                        <p><small><em>Der Maximalwert ist auf 100 % begrenzt. Mehrere Treffer addieren sich zum Gesamtrisiko.</em></small></p>
                    </div>
                </div>
            </details>
        </div>
    </div>

    <div class="realmember-footer">
        <small>🤖 Dieses Addon wurde mit Unterstützung von KI (Claude / Gemini) entwickelt.</small>
    </div>
</div>
