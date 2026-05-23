<div class="generic-content-wrapper realmember-dashboard panel panel-default">
    <div class="panel-body">
        <div class="section-title-wrapper">
            <div class="realmember-header">
                <h2>✨ {{$title}}</h2>
                <div class="realmember-stats">{{$txt_analyzed_accounts}}</div>
            </div>
        </div>

        <div class="realmember-header-notice">
            <div class="alert alert-warning">
                {{$txt_header_notice nofilter}}
            </div>
        </div>

    <div class="realmember-filters">
        <div class="filter-tabs">
            <a href="realmember?filter=recent" class="filter-btn {{if $filter == 'recent'}}active{{/if}}">{{$txt_filter_recent}}</a>
            <a href="realmember?filter=new" class="filter-btn {{if $filter == 'new'}}active{{/if}}">{{$txt_filter_new}}</a>
            <a href="realmember?filter=pending" class="filter-btn {{if $filter == 'pending'}}active{{/if}}">{{$txt_filter_pending}}</a>
            <a href="realmember?filter=all" class="filter-btn {{if $filter == 'all'}}active{{/if}}">{{$txt_filter_all}}</a>
            <a href="realmember?filter=spam" class="filter-btn {{if $filter == 'spam'}}active{{/if}}">{{$txt_filter_spam}}</a>
        </div>

        <form action="realmember" method="get" class="realmember-search-form">
            <input type="hidden" name="filter" value="{{$filter}}">
            <input type="hidden" name="sort" value="{{$sort}}">
            <input type="hidden" name="dir" value="{{$dir}}">
            <input type="text" name="search" value="{{$search}}" placeholder="{{$txt_search_placeholder}}" class="search-input">
            <button type="submit" class="search-btn">{{$txt_search_btn}}</button>
            {{if $search}}
                <a href="realmember?filter={{$filter}}&sort={{$sort}}&dir={{$dir}}" class="clear-search" title="{{$txt_clear_search}}">&times;</a>
            {{/if}}
        </form>
    </div>

    <div class="realmember-sort-bar">
        <span class="sort-label">{{$txt_sort_by}}</span>
        <div class="sort-options">
            <a href="realmember?filter={{$filter}}&search={{$search}}&sort=date&dir={{if $sort == 'date' && $dir == 'desc'}}asc{{else}}desc{{/if}}" class="sort-link {{if $sort == 'date'}}active{{/if}}">
                {{$txt_sort_date}} {{if $sort == 'date'}}{{if $dir == 'asc'}}↑{{else}}↓{{/if}}{{/if}}
            </a>
            <a href="realmember?filter={{$filter}}&search={{$search}}&sort=name&dir={{if $sort == 'name' && $dir == 'asc'}}desc{{else}}asc{{/if}}" class="sort-link {{if $sort == 'name'}}active{{/if}}">
                {{$txt_sort_name}} {{if $sort == 'name'}}{{if $dir == 'asc'}}↑{{else}}↓{{/if}}{{/if}}
            </a>
            <a href="realmember?filter={{$filter}}&search={{$search}}&sort=email&dir={{if $sort == 'email' && $dir == 'asc'}}desc{{else}}asc{{/if}}" class="sort-link {{if $sort == 'email'}}active{{/if}}">
                {{$txt_sort_email}} {{if $sort == 'email'}}{{if $dir == 'asc'}}↑{{else}}↓{{/if}}{{/if}}
            </a>
            <a href="realmember?filter={{$filter}}&search={{$search}}&sort=score&dir={{if $sort == 'score' && $dir == 'desc'}}asc{{else}}desc{{/if}}" class="sort-link {{if $sort == 'score'}}active{{/if}}">
                {{$txt_sort_score}} {{if $sort == 'score'}}{{if $dir == 'asc'}}↑{{else}}↓{{/if}}{{/if}}
            </a>
        </div>
    </div>

    <div class="section-content-wrapper">
        {{if $users}}
        <div class="realmember-table-container">
            <table class="realmember-table">
                <thead>
                    <tr>
                        <th class="col-risk">{{$txt_th_risk}}</th>
                        <th class="col-user">{{$txt_th_user}}</th>
                        <th class="col-contact">{{$txt_th_contact}}</th>
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
                                <a href="{{$user.profile_url}}" class="user-link" target="_blank" rel="noopener" title="{{$txt_view_profile}}">
                                    <span class="username">{{$user.username}}</span>
                                    <span class="nickname">@{{$user.nickname}}</span>
                                </a>
                                {{if $user.is_removed}}<span class="removed-badge">{{$txt_badge_deleted}}</span>{{/if}}
                            </div>
                            {{if $user.note}}
                            <div class="user-note" title="{{$txt_note_title}}">
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
                                <span class="results-label">{{$txt_analysis_results}}</span>
                                {{if $user.reasons}}
                                <ul class="reasons-list-horizontal">
                                    {{foreach $user.reasons as $reason}}
                                    <li>{{$reason}}</li>
                                    {{/foreach}}
                                </ul>
                                {{else}}
                                <span class="no-reasons">{{$txt_no_reasons}}</span>
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
            <p>{{$txt_no_results}}</p>
        </div>
        {{/if}}

        <div class="realmember-criteria-section">
            <details>
                <summary class="criteria-summary">
                    <h3>{{$txt_what_can_realmember}}</h3>
                </summary>
                <div class="criteria-content">
                    <div class="criteria-group">
                        <h4>{{$txt_can_do}}</h4>
                        <ul>
                            <li>{{$txt_desc_risk nofilter}}</li>
                            <li>{{$txt_desc_disposable nofilter}}</li>
                            <li>{{$txt_desc_domains nofilter}}</li>
                            <li>{{$txt_desc_keywords nofilter}}</li>
                            <li>{{$txt_desc_entropy nofilter}}</li>
                            <li>{{$txt_desc_admin_rules nofilter}}</li>
                            <li>{{$txt_desc_filters nofilter}}</li>
                            <li>{{$txt_desc_search nofilter}}</li>
                            <li>{{$txt_desc_updates nofilter}}</li>
                            <li>{{$txt_desc_integration nofilter}}</li>
                        </ul>
                    </div>

                    <div class="criteria-group">
                        <h4>{{$txt_safety_guarantee}}</h4>
                        <ul>
                            <li>{{$txt_desc_read_only nofilter}}</li>
                            <li>{{$txt_desc_no_auto nofilter}}</li>
                            <li>{{$txt_desc_admin_only nofilter}}</li>
                        </ul>
                    </div>

                    <div class="criteria-group">
                        <h4>{{$txt_cannot_do}}</h4>
                        <ul>
                            <li>{{$txt_desc_no_guarantee nofilter}}</li>
                            <li>{{$txt_desc_no_content_scan nofilter}}</li>
                            <li>{{$txt_desc_no_realtime nofilter}}</li>
                            <li>{{$txt_desc_no_delete nofilter}}</li>
                        </ul>
                    </div>
                </div>
            </details>

            <details>
                <summary class="criteria-summary">
                    <h3>{{$txt_setup_maintenance}}</h3>
                </summary>
                <div class="criteria-content">
                    <div class="criteria-group">
                        <h4>{{$txt_cron_updates}}</h4>
                        <p>{{$txt_disposable_desc}}</p>
                        <p>{{$txt_cron_entry}}</p>
                        <pre>0 3 * * * /usr/bin/php {{$criteria.updater_path}}</pre>
                        <p><small><em>{{$txt_cron_hint}}</em></small></p>
                        <p><small>{{$txt_data_source_license nofilter}}</small></p>
                    </div>
                    <div class="criteria-group">
                        <h4>{{$txt_manual_update}}</h4>
                        <p>{{$txt_manual_desc}}</p>
                        <pre>php {{$criteria.updater_path}}</pre>
                    </div>
                    <div class="criteria-group">
                        <div class="realmember-important-notice">
                            {{$txt_safety_notice nofilter}}
                        </div>
                    </div>
                </div>
            </details>

            <details>
                <summary class="criteria-summary">
                    <h3>{{$txt_criteria_scoring}}</h3>
                </summary>
                <div class="criteria-content">
                    <div class="criteria-group">
                        <h4>{{$txt_your_admin_rules}}</h4>
                        <p>{{$txt_admin_rules_desc nofilter}}</p>
                        <p>{{$txt_admin_rules_match nofilter}}</p>
                        <p><small>{{$txt_admin_rules_hint nofilter}}</small></p>
                    </div>

                    <div class="criteria-group">
                        <h4>{{$txt_disposable_detect}}</h4>
                        <p>{{$txt_disposable_detect_desc nofilter}}</p>
                        <p class="realmember-last-update">{{$txt_last_update nofilter}}</p>
                        <p><small>{{$txt_source_license nofilter}}</small></p>
                    </div>

                    <div class="criteria-group">
                        <h4>{{$txt_suspicious_tlds}}</h4>
                        <p>{{$txt_suspicious_tlds_desc}}</p>
                        <div class="tld-list">
                            {{foreach $criteria.bad_tlds as $tld}}<span class="tld-tag">{{$tld}}</span>{{/foreach}}
                        </div>
                    </div>

                    <div class="criteria-group">
                        <h4>{{$txt_keyword_detection}}</h4>
                        <p>{{$txt_keyword_detection_desc nofilter}}</p>
                        <div class="keyword-preview">
                             {{foreach $criteria.keywords as $kw}}<span class="kw-tag">{{$kw}}</span>{{/foreach}}
                        </div>
                    </div>

                    <div class="criteria-group">
                        <h4>{{$txt_pattern_analysis}}</h4>
                        <p>{{$txt_pattern_analysis_desc nofilter}}</p>
                    </div>

                    <div class="criteria-group">
                        <h4>{{$txt_points_distribution}}</h4>
                        <p>{{$txt_points_distribution_desc}}</p>
                        <table class="realmember-points-table">
                            <thead>
                                <tr>
                                    <th>{{$txt_th_criterion}}</th>
                                    <th>{{$txt_th_points}}</th>
                                    <th>{{$txt_th_level}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="point-critical">
                                    <td>{{$txt_row_admin_list nofilter}}</td>
                                    <td>+100</td>
                                    <td>{{$txt_level_critical}}</td>
                                </tr>
                                <tr>
                                    <td>{{$txt_row_disposable}}</td>
                                    <td>+45</td>
                                    <td>{{$txt_level_warning}}</td>
                                </tr>
                                <tr>
                                    <td>{{$txt_row_tld}}</td>
                                    <td>+30</td>
                                    <td>{{$txt_level_suspicious}}</td>
                                </tr>
                                <tr>
                                    <td>{{$txt_row_keyword_note nofilter}}</td>
                                    <td>+25</td>
                                    <td>{{$txt_level_suspicious}}</td>
                                </tr>
                                <tr>
                                    <td>{{$txt_row_keyword_user nofilter}}</td>
                                    <td>+20</td>
                                    <td>{{$txt_level_info}}</td>
                                </tr>
                                <tr>
                                    <td>{{$txt_row_entropy}}</td>
                                    <td>+20</td>
                                    <td>{{$txt_level_info}}</td>
                                </tr>
                                <tr>
                                    <td>{{$txt_row_fediverse_30}}</td>
                                    <td>+25</td>
                                    <td>{{$txt_level_suspicious}}</td>
                                </tr>
                                <tr>
                                    <td>{{$txt_row_fediverse_10}}</td>
                                    <td>+15</td>
                                    <td>{{$txt_level_info}}</td>
                                </tr>
                                <tr>
                                    <td>{{$txt_row_fediverse_5}}</td>
                                    <td>+5</td>
                                    <td>{{$txt_level_info}}</td>
                                </tr>
                            </tbody>
                        </table>
                        <p><small>{{$txt_points_note nofilter}}</small></p>
                    </div>
                    <div class="criteria-group">
                        <h4>{{$txt_fediverse_frequency}}</h4>
                        <p>{{$txt_fediverse_frequency_desc nofilter}}</p>
                        <p><small>{{$txt_fediverse_frequency_hint nofilter}}</small></p>
                    </div>
                </div>
            </details>
        </div>
    </div>

    <div class="realmember-footer">
        <small>{{$txt_footer}}</small>
    </div>
    </div>
</div>
