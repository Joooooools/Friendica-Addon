<?php
/**
 * Name: RealMember
 * Description: Advanced read-only spam detection for site administrators.
 * Version: 1.0
 * Author: Jools <https://friendica.de/profile/jools>
 * License: AGPL-3.0-or-later
 * 
 * SPDX-FileCopyrightText: 2026 [Jools]
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Content\Pager;

/**
 * Register RealMember module.
 */
function realmember_module() {}

/**
 * Install and register hooks.
 */
function realmember_install()
{
	Hook::register('moderation_mod_init', 'addon/realmember/realmember.php', 'realmember_moderation_mod_init');
	Hook::register('moderation_users_tabs', 'addon/realmember/realmember.php', 'realmember_users_tabs');
}

/**
 * Uninstall and unregister hooks.
 */
function realmember_uninstall()
{
	Hook::unregister('moderation_mod_init', 'addon/realmember/realmember.php', 'realmember_moderation_mod_init');
	Hook::unregister('moderation_users_tabs', 'addon/realmember/realmember.php', 'realmember_users_tabs');
}

/**
 * Inject RealMember link into moderation sidebar.
 */
function realmember_moderation_mod_init()
{
	if (!DI::userSession()->isSiteAdmin()) {
		return;
	}

	$t = Renderer::getMarkupTemplate('realmember_menu.tpl', 'addon/realmember/');
	DI::page()['aside'] .= Renderer::replaceMacros($t, [
		'$url' => DI::baseUrl() . '/realmember',
		'$header' => 'RealMember',
		'$label' => DI::l10n()->t('Spam-Analyse'),
	]);
}

/**
 * Add RealMember tab to moderation users sub-navigation.
 */
function realmember_users_tabs(array &$arr)
{
	$arr['tabs'][] = [
		'label'     => 'RealMember',
		'url'       => 'realmember',
		'sel'       => ($arr['selectedTab'] == 'realmember' ? 'active' : ''),
		'title'     => DI::l10n()->t('Spam-Analyse Dashboard'),
		'id'        => 'admin-users-realmember',
		'accesskey' => 's',
	];
}

/**
 * Filter Criteria Data
 */
function realmember_get_criteria()
{
	$disallowed_email = DI::config()->get('system', 'disallowed_email');
	$manual_list = !empty($disallowed_email) ? explode(',', $disallowed_email) : [];
	$manual_list = array_map('trim', $manual_list);

	$disc_domains_path = __DIR__ . '/data/disposable_domains.php';
	$disc_count = file_exists($disc_domains_path) ? count(include $disc_domains_path) : 0;
	
	$keywords_path = __DIR__ . '/data/spam_keywords.php';
	$keywords = file_exists($keywords_path) ? include $keywords_path : [];

	return [
		'bad_tlds' => [
			'.accountant', '.beauty', '.best', '.bid', '.buzz', '.cf', '.click', '.date', '.faith', 
			'.fit', '.fun', '.ga', '.gq', '.icu', '.live', '.loan', '.ml', '.monster', '.mov', 
			'.ninja', '.online', '.pw', '.quest', '.racing', '.rest', '.review', '.shop', 
			'.site', '.space', '.stream', '.surf', '.tk', '.top', '.win', '.work', '.xyz', '.zip'
		],
		'disposable_count' => $disc_count,
		'is_updated' => file_exists(__DIR__ . '/data/last_update.txt'),
		'last_update' => file_exists(__DIR__ . '/data/last_update.txt') ? file_get_contents(__DIR__ . '/data/last_update.txt') : 'Nie (Statisch)',
		'updater_path' => realpath(__DIR__ . '/scripts/update_domains.php'),
		'manual_count' => count($manual_list),
		'manual_list' => $manual_list,
		'keywords' => $keywords
	];
}

/**
 * Main dashboard logic.
 */
function realmember_content()
{
	if (!DI::userSession()->isSiteAdmin()) {
		return DI::l10n()->t('Permission denied.');
	}

	DI::page()->registerStylesheet('addon/realmember/css/realmember.css');

	$filter = $_GET['filter'] ?? 'all';
	$search = trim($_GET['search'] ?? '');
	$sort   = $_GET['sort'] ?? 'date';
	$dir    = $_GET['dir'] ?? 'desc';
	$criteria = realmember_get_criteria();

	// Pre-load disposable domains list once (performance: avoid re-reading per user)
	$disposable_path = __DIR__ . '/data/disposable_domains.php';
	$disposable_domains = file_exists($disposable_path) ? include $disposable_path : [];
	if (!is_array($disposable_domains)) {
		$disposable_domains = [];
	}

	// Pre-build keyword regex pattern once (performance: single match instead of loop)
	$keyword_pattern = null;
	if (!empty($criteria['keywords'])) {
		$escaped = array_map('preg_quote', $criteria['keywords']);
		$keyword_pattern = '/' . implode('|', $escaped) . '/i';
	}

	// Generate Moderation Tabs (consistent with core)
	$all     = DBA::count('user', ["`uid` != ?", 0]);
	$active  = DBA::count('user', ["`verified` AND NOT `blocked` AND NOT `account_removed` AND NOT `account_expired` AND `uid` != ?", 0]);
	$pending = \Friendica\Model\Register::getPendingCount();
	$blocked = DBA::count('user', ['blocked' => true, 'verified' => true, 'account_removed' => false]);
	$deleted = DBA::count('user', ['account_removed' => true]);

	$tabs = [
		[
			'label' => DI::l10n()->t('All') . ' (' . $all . ')',
			'url'   => DI::baseUrl() . '/moderation/users',
			'sel'   => '',
			'title' => DI::l10n()->t('List of all users'),
		],
		[
			'label' => DI::l10n()->t('Active') . ' (' . $active . ')',
			'url'   => DI::baseUrl() . '/moderation/users/active',
			'sel'   => '',
			'title' => DI::l10n()->t('List of active accounts'),
		],
		[
			'label' => DI::l10n()->t('Pending') . ($pending ? ' (' . $pending . ')' : ''),
			'url'   => DI::baseUrl() . '/moderation/users/pending',
			'sel'   => '',
			'title' => DI::l10n()->t('List of pending registrations'),
		],
		[
			'label' => DI::l10n()->t('Blocked') . ($blocked ? ' (' . $blocked . ')' : ''),
			'url'   => DI::baseUrl() . '/moderation/users/blocked',
			'sel'   => '',
			'title' => DI::l10n()->t('List of blocked users'),
		],
		[
			'label' => DI::l10n()->t('Deleted') . ($deleted ? ' (' . $deleted . ')' : ''),
			'url'   => DI::baseUrl() . '/moderation/users/deleted',
			'sel'   => '',
			'title' => DI::l10n()->t('List of pending user deletions'),
		],
	];

	// Fire hook so RealMember (and others like Ratioed) appear in the list
	$hook_data = ['tabs' => $tabs, 'selectedTab' => 'realmember'];
	Hook::callAll('moderation_users_tabs', $hook_data);
	$tabs = $hook_data['tabs'];

	$tab_tpl = Renderer::getMarkupTemplate('common_tabs.tpl');
	$tabs_html = Renderer::replaceMacros($tab_tpl, ['$tabs' => $tabs, '$more' => DI::l10n()->t('More')]);

	// Generate Moderation Sidebar (consistent with BaseModeration)
	// We DO NOT add RealMember here anymore as it is already a Tab!
	$aside_sub = [
		'information' => [DI::l10n()->t('Information'), [
			'overview' => [DI::baseUrl() . '/moderation', DI::l10n()->t('Overview'), 'overview'],
			'reports'  => [DI::baseUrl() . '/moderation/reports', DI::l10n()->t('Reports'), 'overview'],
		]],
		'configuration' => [DI::l10n()->t('Configuration'), [
			'users' => [DI::baseUrl() . '/moderation/users', DI::l10n()->t('Users'), 'users'],
		]],
		'tools' => [DI::l10n()->t('Tools'), [
			'contactblock' => [DI::baseUrl() . '/moderation/blocklist/contact', DI::l10n()->t('Contact Blocklist'), 'contactblock'],
			'blocklist'    => [DI::baseUrl() . '/moderation/blocklist/server', DI::l10n()->t('Server Blocklist'), 'blocklist'],
			'deleteitem'   => [DI::baseUrl() . '/moderation/item/delete', DI::l10n()->t('Delete Item'), 'deleteitem'],
		]],
		'diagnostics' => [DI::l10n()->t('Diagnostics'), [
			'itemsource' => [DI::baseUrl() . '/moderation/item/source', DI::l10n()->t('Item Source'), 'itemsource'],
		]],
	];

	// Inject RealMember sidebar (same as the hook does on /moderation/* pages)
	$realmember_menu_tpl = Renderer::getMarkupTemplate('realmember_menu.tpl', 'addon/realmember/');
	DI::page()['aside'] .= Renderer::replaceMacros($realmember_menu_tpl, [
		'$url' => DI::baseUrl() . '/realmember',
		'$header' => 'RealMember',
		'$label' => DI::l10n()->t('Spam-Analyse'),
	]);

	$aside_tpl = Renderer::getMarkupTemplate('moderation/aside.tpl');
	DI::page()['aside'] .= Renderer::replaceMacros($aside_tpl, [
		'$subpages'  => $aside_sub,
		'$admtxt'    => DI::l10n()->t('Moderation'),
		'$h_pending' => DI::l10n()->t('User registrations waiting for confirmation'),
		'$modurl'    => 'moderation/'
	]);
	
	// Whitelist sorting 
	$sort_map = [
		'name'  => 'username',
		'email' => 'email',
		'date'  => 'register_date'
	];
	$order_field = $sort_map[$sort] ?? 'register_date';
	$order_dir   = (strtolower($dir) === 'asc') ? 'ASC' : 'DESC';

	// Constructing a robust condition string for DBA::p calls
	$condition = " `user`.`uid` != ? ";
	$params = [0];

	if ($filter === 'recent') {
		$condition .= " AND `user`.`register_date` > DATE_SUB(NOW(), INTERVAL 48 HOUR) ";
	} elseif ($filter === 'new') {
		$condition .= " AND `user`.`register_date` > DATE_SUB(NOW(), INTERVAL 30 DAY) ";
	} elseif ($filter === 'pending') {
		$condition .= " AND `user`.`uid` IN (SELECT `uid` FROM `register` WHERE `uid` != ?) ";
		$params[] = 0;
	}

	if (!empty($search)) {
		$condition .= " AND (`user`.`username` LIKE ? OR `user`.`nickname` LIKE ? OR `user`.`email` LIKE ?) ";
		$wildcard = '%' . $search . '%';
		$params[] = $wildcard;
		$params[] = $wildcard;
		$params[] = $wildcard;
	}

	// Pagination setup
	$pager = new Pager(DI::l10n(), DI::args()->getQueryString(), 20);
	$limit_start = $pager->getStart();
	$limit_count = $pager->getItemsPerPage();

	$base_url = DI::baseUrl();
	$results = [];

	if ($filter === 'spam') {
		// Fetch ALL matching users (with JOIN for notes) to filter by heuristic score in PHP
		$select_sql = "SELECT `user`.`uid`, `user`.`username`, `user`.`nickname`, `user`.`email`, `user`.`register_date`, 
		                      `user`.`verified`, `user`.`blocked`, `user`.`account_removed`, `register`.`note`
		               FROM `user` 
		               LEFT JOIN `register` ON `user`.`uid` = `register`.`uid`
		               WHERE " . $condition . " 
		               ORDER BY `user`.`account_removed` ASC, `user`.`$order_field` $order_dir";
		
		$usersSet = DBA::p($select_sql, ...$params);
		$filtered = [];
		if ($usersSet) {
			while ($user = DBA::fetch($usersSet)) {
				$user['is_removed'] = (bool)$user['account_removed'];
				$user['profile_url'] = $base_url . '/profile/' . $user['nickname'];
				
				$scoreData = realmember_calculate_risk($user, $criteria, $disposable_domains, $keyword_pattern);
				if ($scoreData['score'] > 0) {
					$filtered[] = array_merge($user, $scoreData);
				}
			}
			DBA::close($usersSet);
		}
		
		$total = count($filtered);
		$results = array_slice($filtered, $limit_start, $limit_count);
	} else {
		// Standard Flow: Count first
		$total_sql = "SELECT COUNT(*) AS total FROM `user` WHERE " . $condition;
		$total_res = DBA::p($total_sql, ...$params);
		$total = 0;
		if ($total_res) {
			$row = DBA::fetch($total_res);
			$total = (int)($row['total'] ?? 0);
			DBA::close($total_res);
		}

		// Fetch page only (with JOIN for notes)
		$select_sql = "SELECT `user`.`uid`, `user`.`username`, `user`.`nickname`, `user`.`email`, `user`.`register_date`, 
		                      `user`.`verified`, `user`.`blocked`, `user`.`account_removed`, `register`.`note`
		               FROM `user` 
		               LEFT JOIN `register` ON `user`.`uid` = `register`.`uid`
		               WHERE " . $condition . " 
		               ORDER BY `user`.`account_removed` ASC, `user`.`$order_field` $order_dir 
		               LIMIT $limit_start, $limit_count";
		
		$usersSet = DBA::p($select_sql, ...$params);
		if ($usersSet) {
			while ($user = DBA::fetch($usersSet)) {
				$user['is_removed'] = (bool)$user['account_removed'];
				$user['profile_url'] = $base_url . '/profile/' . $user['nickname'];
				
				$scoreData = realmember_calculate_risk($user, $criteria, $disposable_domains, $keyword_pattern);
				$results[] = array_merge($user, $scoreData);
			}
			DBA::close($usersSet);
		}
	}

	// Final sort by score if requested (affects current results set)
	if ($sort === 'score') {
		usort($results, function($a, $b) use ($dir) {
			if ($a['is_removed'] !== $b['is_removed']) {
				return $a['is_removed'] <=> $b['is_removed'];
			}
			return (strtolower($dir) === 'asc') 
				? $a['score'] <=> $b['score'] 
				: $b['score'] <=> $a['score'];
		});
	}

	$t = Renderer::getMarkupTemplate('realmember.tpl', 'addon/realmember/');
	return $tabs_html . Renderer::replaceMacros($t, [
		'$title' => 'RealMember Spam-Analyse',
		'$users' => $results,
		'$filter' => $filter,
		'$search' => $search,
		'$sort' => $sort,
		'$dir' => $dir,
		'$total' => $total,
		'$criteria' => $criteria,
		'$pager' => $pager->renderFull($total),
	]);
}

/**
 * Calculate the risk score for a user based on multiple read-only signals.
 * 
 * @param array $user           User data from database
 * @param array $criteria       Analysis criteria (TLDs, keywords, manual rules)
 * @param array $disposable     Pre-loaded list of disposable email domains
 * @param string|null $kw_pattern Pre-built regex pattern for keyword matching
 * @return array Score, reasons, and risk level
 */
function realmember_calculate_risk($user, $criteria, $disposable_domains = [], $keyword_pattern = null)
{
	$score = 0;
	$reasons = [];

	// 1. Email Analysis
	$email = strtolower($user['email'] ?? '');
	$parts = explode('@', $email);
	$domain = $parts[1] ?? '';

	// Layer 1: Manual System Disallowed List (100 Points)
	if (!empty($criteria['manual_list'])) {
		foreach ($criteria['manual_list'] as $item) {
			$pat = strtolower(trim($item));
			if ($domain && (fnmatch($pat, $domain) || ($pat == $domain) || ($pat == $email))) {
				$score += 100;
				$reasons[] = "Systemweit gesperrte E-Mail (Admin-Regel: $pat)";
				break;
			}
		}
	}

	// Layer 2: Suspicious TLDs
	if ($score < 100 && !empty($criteria['bad_tlds'])) {
		foreach ($criteria['bad_tlds'] as $tld) {
			if (str_ends_with($domain, $tld)) {
				$score += 30;
				$reasons[] = "Verdächtige TLD ($tld)";
				break;
			}
		}
	}

	// Layer 3: Disposable Domains (uses pre-loaded list)
	if ($score < 100 && !empty($disposable_domains) && in_array($domain, $disposable_domains)) {
		$score += 45;
		$reasons[] = "Bekannter TrashMail-Anbieter";
	}

	// 2. Keyword check (uses pre-built regex for performance)
	$note = strtolower($user['note'] ?? '');
	$username = strtolower($user['username'] ?? '');
	
	if ($keyword_pattern) {
		if ($note !== '' && preg_match_all($keyword_pattern, $note, $matches)) {
			foreach (array_unique($matches[0]) as $kw) {
				$score += 25;
				$reasons[] = "Keyword in Notiz gefunden: '$kw'";
			}
		}
		if ($username !== '' && preg_match_all($keyword_pattern, $username, $matches)) {
			foreach (array_unique($matches[0]) as $kw) {
				$score += 20;
				$reasons[] = "Keyword im Nutzernamen gefunden: '$kw'";
			}
		}
	}

	// 3. Entropie / Bot-Muster-Erkennung
	$nickname = $user['nickname'] ?? '';
	$email_prefix = str_replace(['.', '-', '_'], '', $parts[0] ?? ''); // Punkte ignorieren

	$check_entropy = function($orig_str) {
		// Für das Buchstabenverhältnis Satzzeichen entfernen
		$str_clean = str_replace(['.', '-', '_'], '', $orig_str);
		if (strlen($str_clean) > 7) {
			$vowels = preg_match_all('/[aeiouy]/i', $str_clean);
			$consonants_and_nums = strlen($str_clean) - $vowels;
			
			// 1. Extrem wenige Vokale oder mieses Verhältnis (z.B. rtkz99)
			if ($vowels < 2 || ($consonants_and_nums / max(1, $vowels)) > 4) {
				return true;
			}
			
			// 2. Tastenfeld-Smash prüfen
			// VORHER: Übliche Namens-Silben (Cluster) im Deutschen/Englischen normalisieren, 
			// damit echte Namen (z.B. "schlaephucke" oder "schwaab") nicht bestraft werden.
			// Satzzeichen bleiben als "Trenner" im String!
			$normalized = str_ireplace(
			    ['sch', 'ch', 'tz', 'ck', 'th', 'ph', 'qu'], 
			    ['s',   'c',  'z',  'k',  't',  'f',  'q'], 
			    $orig_str
			);
			
			// a) 6 Konsonanten am Stück (ohne Unterbrechung durch Punkte/Vokale)
			if (preg_match('/[bcdfghjklmnpqrstvwxz]{6,}/i', $normalized)) {
			    return true;
			}
			
			// b) 5 Vokale am Stück (z.B. aouie)
			if (preg_match('/[aeiouy]{5,}/i', $normalized)) {
			    return true;
			}
		}
		return false;
	};

	if ($check_entropy($nickname)) {
		$score += 20;
		$reasons[] = "Verdächtiges Nickname-Muster (Bot-Signatur)";
	}
	
	if ($check_entropy($email_prefix)) {
		$score += 20;
		$reasons[] = "Verdächtiges E-Mail-Präfix (Bot-Signatur)";
	}

	return [
		'score' => min(100, $score),
		'reasons' => $reasons,
		'risk_level' => realmember_get_level($score)
	];
}

function realmember_get_level($score)
{
	if ($score >= 70) return 'critical';
	if ($score >= 40) return 'warning';
	if ($score >= 20) return 'info';
	return 'safe';
}
