/*
 * RegStats dashboard interactions.
 *
 * SPDX-FileCopyrightText: 2026 [Jools]
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 * This script powers the interactive day/hour breakdown of the RegStats
 * dashboard. It is intentionally self-contained and scoped to the
 * "#regstats-dashboard" / ".regstats-*" elements so it cannot interfere with
 * other pages or addons. It is loaded only on the dashboard page via
 * DI::page()->registerFooterScript(), so it never runs on visitor-facing pages.
 *
 * The translated "Details for: %s" format string is provided by the server in
 * the data-details-format attribute of the #regstats-dashboard container, so no
 * inline (Smarty-rendered) JavaScript is required and a strict Content Security
 * Policy without 'unsafe-inline' is satisfied.
 */
(function () {
	'use strict';

	function init() {
		var dashboard = document.getElementById('regstats-dashboard');
		if (!dashboard) {
			return;
		}

		// Server-provided, already-translated format string (contains "%s").
		// Falls back to a neutral default if the attribute is missing.
		var detailsFormat = dashboard.getAttribute('data-details-format') || '%s';

		var fields = {
			'core-hp': 'honeypot-core',
			'guardian-hp': 'honeypot-guardian',
			'captcha': 'captcha-failed',
			'validation': 'validation-failed',
			'duplicate': 'duplicate-failed',
			'nickname': 'blocked-nickname',
			'email': 'blocked-email',
			'register': 'register',
			'reg-open': 'reg-open',
			'reg-need-approval': 'reg-need-approval',
			'imported': 'imported',
			'openid': 'openid',
			'approved': 'approved',
			'rejected': 'rejected',
			'mail-failed': 'mail-failed'
		};

		function setupBreakdown(type, prefix) {
			var bars = dashboard.querySelectorAll('.regstats-chart-bar-wrapper[data-type="' + type + '"]');
			bars.forEach(function (bar) {
				bar.addEventListener('click', function () {
					// Remove active highlight from all other bars of same type
					bars.forEach(function (b) {
						b.classList.remove('active-bar');
					});
					bar.classList.add('active-bar');

					var label = bar.getAttribute('data-label');
					var box = document.getElementById('regstats-' + type + '-breakdown');
					if (!box) {
						return;
					}

					// Update title
					var titleEl = box.querySelector('.regstats-breakdown-title');
					if (titleEl) {
						titleEl.textContent = detailsFormat.replace('%s', label);
					}

					// Update fields
					for (var field in fields) {
						if (!Object.prototype.hasOwnProperty.call(fields, field)) {
							continue;
						}
						var attrName = 'data-' + fields[field];
						var val = bar.getAttribute(attrName);
						var el = document.getElementById(prefix + field);
						if (el) {
							el.textContent = val || '0';
						}
					}

					box.style.display = 'block';
					box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
				});
			});
		}

		setupBreakdown('daily', 'regstats-db-');
		setupBreakdown('hourly', 'regstats-hb-');
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		// Script is loaded in the footer, so the DOM may already be ready.
		init();
	}
})();
