<div class="register-success-container">
    <div class="register-success-card">
        <div class="register-success-icon-wrapper">
            <svg class="register-success-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>

        <h1 class="register-success-title">{{$title}}</h1>

        {{if $notices}}
            <div class="register-success-alerts-container error">
                {{foreach $notices as $notice}}
                    <div class="register-success-alert-item">
                        <svg class="register-success-alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <p class="register-success-alert-text">{{$notice}}</p>
                    </div>
                {{/foreach}}
            </div>
        {{/if}}

        {{if $infos}}
            <div class="register-success-alerts-container info">
                {{foreach $infos as $info}}
                    <div class="register-success-alert-item">
                        <svg class="register-success-alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <p class="register-success-alert-text">{{$info}}</p>
                    </div>
                {{/foreach}}
            </div>
        {{/if}}

        <p class="register-success-message">{{$message|escape|nl2br nofilter}}</p>

        <div class="register-success-action-wrapper">
            <a href="{{$login_url}}" class="register-success-btn-primary">
                {{$login_text}}
                <svg class="register-success-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>
    </div>
</div>
