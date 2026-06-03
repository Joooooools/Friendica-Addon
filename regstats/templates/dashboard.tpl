<div id="adminpage">
	<div id="regstats-dashboard" data-details-format="{{$lbl_details_for}}">
		<div class="regstats-header">
			<div class="regstats-title-row">
				<h1><i class="fa fa-line-chart"></i> {{$title}}</h1>
			</div>
			{{if $stats_since}}
				<p class="regstats-subtitle">
					<i class="fa fa-info-circle"></i> {{$lbl_stats_since}} <strong>{{$stats_since}}</strong>. {{$lbl_rotation_notice}}
				</p>
			{{/if}}
		</div>

	{{if $notices}}
		<div class="regstats-notices">
			{{foreach $notices as $notice}}
				<div class="alert alert-info">{{$notice}}</div>
			{{/foreach}}
		</div>
	{{/if}}

	<!-- Registration Failures Summary -->
	<h2 class="regstats-section-title"><i class="fa fa-shield"></i> {{$lbl_headline}}</h2>
	<div class="regstats-card-grid">
		<div class="regstats-card" aria-label="{{$desc_core_hp}}">
			<div class="regstats-card-tooltip">{{$desc_core_hp}}</div>
			<div class="regstats-card-value">{{$total_honeypot_core}}</div>
			<div class="regstats-card-label">
				{{$lbl_core_hp}} <i class="fa fa-question-circle regstats-info-icon"></i>
			</div>
		</div>
		<div class="regstats-card{{if !$guardian_active}} regstats-card-inactive{{/if}}" aria-label="{{$desc_guardian_hp}}">
			<div class="regstats-card-tooltip">{{$desc_guardian_hp}}</div>
			<div class="regstats-card-value">{{$total_honeypot_guardian}}</div>
			<div class="regstats-card-label">
				{{$lbl_guardian_hp}} <i class="fa fa-question-circle regstats-info-icon"></i>
				{{if !$guardian_active}}
					<span class="regstats-badge-inactive">({{$lbl_inactive}})</span>
				{{/if}}
			</div>
		</div>
		<div class="regstats-card{{if !$captcha_active}} regstats-card-inactive{{/if}}" aria-label="{{$desc_captcha}}">
			<div class="regstats-card-tooltip">{{$desc_captcha}}</div>
			<div class="regstats-card-value">{{$total_captcha}}</div>
			<div class="regstats-card-label">
				{{$lbl_captcha_failed}} <i class="fa fa-question-circle regstats-info-icon"></i>
				{{if !$captcha_active}}
					<span class="regstats-badge-inactive">({{$lbl_inactive}})</span>
				{{/if}}
			</div>
		</div>
		<div class="regstats-card" aria-label="{{$desc_validation}}">
			<div class="regstats-card-tooltip">{{$desc_validation}}</div>
			<div class="regstats-card-value">{{$total_validation}}</div>
			<div class="regstats-card-label">
				{{$lbl_validation_failed}} <i class="fa fa-question-circle regstats-info-icon"></i>
			</div>
		</div>
		<div class="regstats-card" aria-label="{{$desc_duplicate}}">
			<div class="regstats-card-tooltip">{{$desc_duplicate}}</div>
			<div class="regstats-card-value">{{$total_duplicate}}</div>
			<div class="regstats-card-label">
				{{$lbl_duplicate_failed}} <i class="fa fa-question-circle regstats-info-icon"></i>
			</div>
		</div>
		<div class="regstats-card" aria-label="{{$desc_blocked_nickname}}">
			<div class="regstats-card-tooltip">{{$desc_blocked_nickname}}</div>
			<div class="regstats-card-value">{{$total_blocked_nickname}}</div>
			<div class="regstats-card-label">
				{{$lbl_blocked_nickname}} <i class="fa fa-question-circle regstats-info-icon"></i>
			</div>
		</div>
		<div class="regstats-card" aria-label="{{$desc_blocked_email}}">
			<div class="regstats-card-tooltip">{{$desc_blocked_email}}</div>
			<div class="regstats-card-value">{{$total_blocked_email}}</div>
			<div class="regstats-card-label">
				{{$lbl_blocked_email}} <i class="fa fa-question-circle regstats-info-icon"></i>
			</div>
		</div>
	</div>

	<!-- Registrations & Moderation Summary -->
	<h2 class="regstats-section-title"><i class="fa fa-users"></i> {{$lbl_reg_headline}}</h2>
	<div class="regstats-card-grid">
		<div class="regstats-card" aria-label="{{$desc_reg_registrations}}">
			<div class="regstats-card-tooltip">{{$desc_reg_registrations}}</div>
			<div class="regstats-card-value">{{$total_registrations}}</div>
			<div class="regstats-card-label">
				{{$lbl_reg_registrations}} <i class="fa fa-question-circle regstats-info-icon"></i>
			</div>
		</div>
		<div class="regstats-card" aria-label="{{$desc_reg_open}}">
			<div class="regstats-card-tooltip">{{$desc_reg_open}}</div>
			<div class="regstats-card-value">{{$total_open}}</div>
			<div class="regstats-card-label">
				{{$lbl_reg_open}} <i class="fa fa-question-circle regstats-info-icon"></i>
			</div>
		</div>
		<div class="regstats-card" aria-label="{{$desc_reg_need_approval}}">
			<div class="regstats-card-tooltip">{{$desc_reg_need_approval}}</div>
			<div class="regstats-card-value">{{$total_need_approval}}</div>
			<div class="regstats-card-label">
				{{$lbl_reg_need_approval}} <i class="fa fa-question-circle regstats-info-icon"></i>
			</div>
		</div>
		<div class="regstats-card regstats-card--pending{{if $pending_count > 0}} regstats-card--alert{{/if}}" aria-label="{{$desc_reg_pending}}">
			<div class="regstats-card-tooltip">{{$desc_reg_pending}}</div>
			<a href="{{$baseurl}}/moderation/users/pending" class="regstats-card-link">
				<div class="regstats-card-value">{{$pending_count}}</div>
				<div class="regstats-card-label">
					{{$lbl_reg_pending}} <i class="fa fa-question-circle regstats-info-icon"></i>
				</div>
			</a>
		</div>
		<div class="regstats-card" aria-label="{{$desc_reg_approved}}">
			<div class="regstats-card-tooltip">{{$desc_reg_approved}}</div>
			<div class="regstats-card-value">{{$total_approved}}</div>
			<div class="regstats-card-label">
				{{$lbl_reg_approved}} <i class="fa fa-question-circle regstats-info-icon"></i>
			</div>
		</div>
		<div class="regstats-card" aria-label="{{$desc_reg_rejected}}">
			<div class="regstats-card-tooltip">{{$desc_reg_rejected}}</div>
			<div class="regstats-card-value">{{$total_rejected}}</div>
			<div class="regstats-card-label">
				{{$lbl_reg_rejected}} <i class="fa fa-question-circle regstats-info-icon"></i>
			</div>
		</div>
		<div class="regstats-card" aria-label="{{$desc_reg_imported}}">
			<div class="regstats-card-tooltip">{{$desc_reg_imported}}</div>
			<div class="regstats-card-value">{{$total_imported}}</div>
			<div class="regstats-card-label">
				{{$lbl_reg_imported}} <i class="fa fa-question-circle regstats-info-icon"></i>
			</div>
		</div>
		<div class="regstats-card" aria-label="{{$desc_reg_openid}}">
			<div class="regstats-card-tooltip">{{$desc_reg_openid}}</div>
			<div class="regstats-card-value">{{$total_openid}}</div>
			<div class="regstats-card-label">
				{{$lbl_reg_openid}} <i class="fa fa-question-circle regstats-info-icon"></i>
			</div>
		</div>
		<div class="regstats-card" aria-label="{{$desc_reg_mail_failed}}">
			<div class="regstats-card-tooltip">{{$desc_reg_mail_failed}}</div>
			<div class="regstats-card-value">{{$total_mail_failed}}</div>
			<div class="regstats-card-label">
				{{$lbl_reg_mail_failed}} <i class="fa fa-question-circle regstats-info-icon"></i>
			</div>
		</div>
	</div>

	<!-- Interactive Charts -->
	<div class="regstats-details-row">
		<!-- Daily Distribution -->
		<div class="regstats-details-card">
			<h2><i class="fa fa-calendar"></i> {{$lbl_daily_chart}}</h2>
			<p class="regstats-card-desc">{{$lbl_daily_desc}}</p>
			
			<div class="regstats-chart-container">
				<div class="regstats-chart-y-axis">
					<span>100%</span>
					<span>50%</span>
					<span>0%</span>
				</div>
				<div class="regstats-chart-bars">
					{{foreach $days as $day}}
						<div class="regstats-chart-bar-wrapper"
							 aria-label="{{$day.tooltip}}"
							 data-type="daily"
							 data-label="{{$day.label}}"
							 data-count="{{$day.count}}"
							 data-honeypot-core="{{$day.types.honeypot_core}}"
							 data-honeypot-guardian="{{$day.types.honeypot_guardian}}"
							 data-captcha-failed="{{$day.types.captcha_failed}}"
							 data-validation-failed="{{$day.types.validation_failed}}"
							 data-duplicate-failed="{{$day.types.duplicate_failed}}"
							 data-blocked-nickname="{{$day.types.blocked_nickname}}"
							 data-blocked-email="{{$day.types.blocked_email}}"
							 data-register="{{$day.types.register}}"
							 data-reg-open="{{$day.types.reg_open}}"
							 data-reg-need-approval="{{$day.types.reg_need_approval}}"
							 data-imported="{{$day.types.imported}}"
							 data-openid="{{$day.types.openid}}"
							 data-approved="{{$day.types.approved}}"
							 data-rejected="{{$day.types.rejected}}"
							 data-mail-failed="{{$day.types.mail_failed}}">
							<div class="regstats-chart-bar-tooltip">{{$day.tooltip}}</div>
							<div class="regstats-chart-bar-track">
								<div class="regstats-chart-bar-fill" style="height: {{$day.percent|default:0}}%;"></div>
							</div>
							<span class="regstats-chart-bar-label">{{$day.short_label}}</span>
						</div>
					{{/foreach}}
				</div>
			</div>

			<!-- Daily Breakdown Details -->
			<div id="regstats-daily-breakdown" class="regstats-breakdown-box" style="display: none;">
				<h3 class="regstats-breakdown-title"></h3>
				
				<div class="regstats-breakdown-grid">
					<!-- Failures -->
					<div class="regstats-breakdown-section">
						<h4><i class="fa fa-shield"></i> {{$lbl_headline}}</h4>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_core_hp}}</span>
							<span class="regstats-breakdown-value" id="regstats-db-core-hp">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_guardian_hp}}</span>
							<span class="regstats-breakdown-value" id="regstats-db-guardian-hp">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_captcha_failed}}</span>
							<span class="regstats-breakdown-value" id="regstats-db-captcha">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_validation_failed}}</span>
							<span class="regstats-breakdown-value" id="regstats-db-validation">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_duplicate_failed}}</span>
							<span class="regstats-breakdown-value" id="regstats-db-duplicate">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_blocked_nickname}}</span>
							<span class="regstats-breakdown-value" id="regstats-db-nickname">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_blocked_email}}</span>
							<span class="regstats-breakdown-value" id="regstats-db-email">0</span>
						</div>
					</div>

					<!-- Successful / Moderation / Mail -->
					<div class="regstats-breakdown-section">
						<h4><i class="fa fa-users"></i> {{$lbl_reg_headline}}</h4>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_reg_registrations}}</span>
							<span class="regstats-breakdown-value" id="regstats-db-register">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label" style="padding-left: 15px;">↳ {{$lbl_reg_open}}</span>
							<span class="regstats-breakdown-value" id="regstats-db-reg-open">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label" style="padding-left: 15px;">↳ {{$lbl_reg_need_approval}}</span>
							<span class="regstats-breakdown-value" id="regstats-db-reg-need-approval">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label" style="padding-left: 15px;">↳ {{$lbl_reg_imported}}</span>
							<span class="regstats-breakdown-value" id="regstats-db-imported">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label" style="padding-left: 15px;">↳ {{$lbl_reg_openid}}</span>
							<span class="regstats-breakdown-value" id="regstats-db-openid">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_reg_approved}}</span>
							<span class="regstats-breakdown-value" id="regstats-db-approved">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_reg_rejected}}</span>
							<span class="regstats-breakdown-value" id="regstats-db-rejected">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_reg_mail_failed}}</span>
							<span class="regstats-breakdown-value" id="regstats-db-mail-failed">0</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Hourly Distribution -->
		<div class="regstats-details-card">
			<h2><i class="fa fa-clock-o"></i> {{$lbl_hourly_chart}}</h2>
			<p class="regstats-card-desc">{{$lbl_hourly_desc}}</p>

			<div class="regstats-chart-container">
				<div class="regstats-chart-y-axis">
					<span>100%</span>
					<span>50%</span>
					<span>0%</span>
				</div>
				<div class="regstats-chart-bars">
					{{foreach $hours as $hour}}
						<div class="regstats-chart-bar-wrapper"
							 aria-label="{{$hour.tooltip}}"
							 data-type="hourly"
							 data-label="{{$hour.label}}"
							 data-count="{{$hour.count}}"
							 data-honeypot-core="{{$hour.types.honeypot_core}}"
							 data-honeypot-guardian="{{$hour.types.honeypot_guardian}}"
							 data-captcha-failed="{{$hour.types.captcha_failed}}"
							 data-validation-failed="{{$hour.types.validation_failed}}"
							 data-duplicate-failed="{{$hour.types.duplicate_failed}}"
							 data-blocked-nickname="{{$hour.types.blocked_nickname}}"
							 data-blocked-email="{{$hour.types.blocked_email}}"
							 data-register="{{$hour.types.register}}"
							 data-reg-open="{{$hour.types.reg_open}}"
							 data-reg-need-approval="{{$hour.types.reg_need_approval}}"
							 data-imported="{{$hour.types.imported}}"
							 data-openid="{{$hour.types.openid}}"
							 data-approved="{{$hour.types.approved}}"
							 data-rejected="{{$hour.types.rejected}}"
							 data-mail-failed="{{$hour.types.mail_failed}}">
							<div class="regstats-chart-bar-tooltip">{{$hour.tooltip}}</div>
							<div class="regstats-chart-bar-track">
								<div class="regstats-chart-bar-fill" style="height: {{$hour.percent|default:0}}%;"></div>
							</div>
							<span class="regstats-chart-bar-label">{{$hour.short_label}}</span>
						</div>
					{{/foreach}}
				</div>
			</div>

			<!-- Hourly Breakdown Details -->
			<div id="regstats-hourly-breakdown" class="regstats-breakdown-box" style="display: none;">
				<h3 class="regstats-breakdown-title"></h3>
				
				<div class="regstats-breakdown-grid">
					<!-- Failures -->
					<div class="regstats-breakdown-section">
						<h4><i class="fa fa-shield"></i> {{$lbl_headline}}</h4>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_core_hp}}</span>
							<span class="regstats-breakdown-value" id="regstats-hb-core-hp">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_guardian_hp}}</span>
							<span class="regstats-breakdown-value" id="regstats-hb-guardian-hp">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_captcha_failed}}</span>
							<span class="regstats-breakdown-value" id="regstats-hb-captcha">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_validation_failed}}</span>
							<span class="regstats-breakdown-value" id="regstats-hb-validation">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_duplicate_failed}}</span>
							<span class="regstats-breakdown-value" id="regstats-hb-duplicate">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_blocked_nickname}}</span>
							<span class="regstats-breakdown-value" id="regstats-hb-nickname">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_blocked_email}}</span>
							<span class="regstats-breakdown-value" id="regstats-hb-email">0</span>
						</div>
					</div>

					<!-- Successful / Moderation / Mail -->
					<div class="regstats-breakdown-section">
						<h4><i class="fa fa-users"></i> {{$lbl_reg_headline}}</h4>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_reg_registrations}}</span>
							<span class="regstats-breakdown-value" id="regstats-hb-register">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label" style="padding-left: 15px;">↳ {{$lbl_reg_open}}</span>
							<span class="regstats-breakdown-value" id="regstats-hb-reg-open">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label" style="padding-left: 15px;">↳ {{$lbl_reg_need_approval}}</span>
							<span class="regstats-breakdown-value" id="regstats-hb-reg-need-approval">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label" style="padding-left: 15px;">↳ {{$lbl_reg_imported}}</span>
							<span class="regstats-breakdown-value" id="regstats-hb-imported">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label" style="padding-left: 15px;">↳ {{$lbl_reg_openid}}</span>
							<span class="regstats-breakdown-value" id="regstats-hb-openid">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_reg_approved}}</span>
							<span class="regstats-breakdown-value" id="regstats-hb-approved">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_reg_rejected}}</span>
							<span class="regstats-breakdown-value" id="regstats-hb-rejected">0</span>
						</div>
						<div class="regstats-breakdown-item">
							<span class="regstats-breakdown-label">{{$lbl_reg_mail_failed}}</span>
							<span class="regstats-breakdown-value" id="regstats-hb-mail-failed">0</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Administrative Actions -->
	<div class="regstats-actions-card">
		<h3>{{$lbl_actions}}</h3>
		<form method="post" action="regstats" onsubmit="return confirm('{{$lbl_clear_confirm|escape:'javascript'}}');">
			<input type="hidden" name="form_security_token" value="{{$form_security_token}}" />
			<input type="hidden" name="clear_log" value="1" />
			<button type="submit" class="regstats-btn-danger">
				<i class="fa fa-trash-o"></i> {{$lbl_clear_stats}}
			</button>
		</form>
	</div>

</div>
</div>
