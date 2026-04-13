<?php
namespace Friendica\Addon\PersonalPostExporter;

use Friendica\DI;
use Friendica\Database\DBA;
use Friendica\Model\Contact;
use Friendica\Model\Post;
use Friendica\Protocol\Activity;
use Friendica\Core\System;
use Friendica\Content\Text\BBCode;
use Friendica\Model\Photo;
use Friendica\Core\Renderer;

/**
 * Exporter Logic for PersonalPostExporter Addon
 * 
 * ATTENTION: This addon must NEVER write to the database! 
 * DBA::insert, DBA::update, DBA::delete and PConfig::set are strictly FORBIDDEN in this class.
 */
class Exporter
{
    private array $months = [];
    private ?bool $is_de = null;
    private array $labels = [];

    /**
     * Entry point for the export process
     */
    public function run(string $media = 'link', string $year = 'all', string $theme = 'light'): bool
    {
        $uid = DI::userSession()->getLocalUserId();
        if (!$uid) {
            DI::sysmsg()->addNotice(DI::l10n()->t('Access denied.'));
            return false;
        }

        $user = DBA::selectFirst('user', ['uid', 'username', 'nickname', 'timezone'], ['uid' => $uid]);
        if (!$user) {
            DI::sysmsg()->addNotice(DI::l10n()->t('User not found.'));
            return false;
        }

        $self_contact_id = Contact::getPublicIdByUserId($uid);
        if (empty($self_contact_id)) {
            DI::sysmsg()->addNotice(DI::l10n()->t('Could not identify user contact.'));
            return false;
        }

        $condition = [
            'uid' => $uid,
            'author-id' => $self_contact_id,
            'deleted' => 0,
            'visible' => 1,
            'gravity' => 0,
            'origin' => 1,
        ];

        if ($year !== 'all') {
            $user_timezone = $user['timezone'] ?: 'UTC';
            $tz = $this->getUserTimezone($user_timezone);
            $utc = new \DateTimeZone('UTC');

            if ($year === 'older') {
                $cutoff_year = (int) date('Y') - 10;
                $dt_cutoff = new \DateTime($cutoff_year . '-01-01 00:00:00', $tz);
                $dt_cutoff->setTimezone($utc);
                $condition = DBA::mergeConditions($condition, ["`created` < ?", $dt_cutoff->format('Y-m-d H:i:s')]);
            } else {
                $dt_start = new \DateTime($year . '-01-01 00:00:00', $tz);
                $dt_start->setTimezone($utc);

                $dt_end = new \DateTime($year . '-12-31 23:59:59', $tz);
                $dt_end->setTimezone($utc);

                $condition = DBA::mergeConditions($condition, ["`created` >= ? AND `created` <= ?", $dt_start->format('Y-m-d H:i:s'), $dt_end->format('Y-m-d H:i:s')]);
            }
        }

        if (DBA::count('post-user-view', $condition) == 0) {
            return false;
        }

        try {
            $this->createArchive($user, $self_contact_id, $year, $media, $theme);
        } catch (\Exception $e) {
            DI::logger()->error('PersonalPostExporter: ' . $e->getMessage());
            DI::sysmsg()->addNotice($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Main archive creation logic
     */
    private function createArchive(array $user, int $contact_id, string $year, string $media, string $theme)
    {
        $uid = $user['uid'];
        $current_limit = ini_get('memory_limit');
        $limit_bytes = $this->returnBytes($current_limit);
        $target_limit = '512M';
        
        if ($limit_bytes < $this->returnBytes($target_limit) && $limit_bytes != -1) {
            if (@ini_set('memory_limit', $target_limit) !== false) {
                $limit_bytes = $this->returnBytes($target_limit);
            } else {
                DI::logger()->notice('PersonalPostExporter: Could not increase memory_limit to ' . $target_limit);
            }
        }

        @set_time_limit(600);

        $lock_file = $this->acquireLock($uid);
        if (!$lock_file) {
            DI::sysmsg()->addNotice(DI::l10n()->t('Server is busy with other exports. Please try again in a few minutes.'));
            return;
        }

        $cleanup = [$lock_file];
        register_shutdown_function(function () use (&$cleanup) {
            $error = error_get_last();
            if ($error && strpos($error['message'], 'Allowed memory size') !== false) {
                DI::logger()->error('PersonalPostExporter FATAL: Memory limit exhausted: ' . $error['message']);
            }
            foreach ($cleanup as $f) {
                if ($f && file_exists($f)) {
                    @unlink($f);
                }
            }
        });

        try {
            $this->is_de = $this->checkIsGerman();
            $this->months = $this->getLocalizedMonths();
            $this->labels = [
                'view_orig' => DI::l10n()->t('🔗 View Original Post'),
                'gallery'   => DI::l10n()->t('📷 Gallery'),
                'alt'       => DI::l10n()->t('Image description'),
                'view'      => DI::l10n()->t('📸 View original')
            ];

            $utc_tz = new \DateTimeZone('UTC');
            $user_tz = $this->getUserTimezone($user['timezone'] ?: 'UTC');

            $temp_dir = DI::system()->getTempPath();
            $tmp_file = tempnam($temp_dir, 'postexport');
            $cleanup[] = $tmp_file;
            $zip = new \ZipArchive();
            $zip_res = $zip->open($tmp_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            if ($zip_res !== true) {
                DI::logger()->error('PersonalPostExporter: Could not create ZIP archive. Error code: ' . $zip_res);
                throw new \Exception('Could not create ZIP archive.');
            }

            $avatar_data_uri = $this->getBase64Avatar($uid);

            // Add UI components from templates
            $zip->addFromString('index.html', $this->getIndexHtml($user, $avatar_data_uri, $theme));
            $zip->addFile(dirname(__DIR__) . '/templates/archive/style.css', 'style.css');
            $zip->addFile(dirname(__DIR__) . '/templates/archive/viewer.js', 'viewer.js');

            $params = [$uid, $contact_id];
            $sql = "SELECT puv.`uri-id`, puv.`created`, puv.`title`, puv.`body`, puv.`plink`, puv.`private`, 
                       puv.`has-media`, puv.`author-name`, puv.`author-link`, puv.`author-avatar`
                FROM `post-user-view` AS puv 
                INNER JOIN `post` AS p ON p.`uri-id` = puv.`uri-id`
                WHERE puv.`uid` = ? 
                  AND puv.`author-id` = ? 
                  AND puv.`deleted` = 0 
                  AND p.`deleted` = 0
                  AND puv.`visible` = 1
                  AND puv.`gravity` = 0 
                  AND puv.`origin` = 1
                  AND puv.`body` != '' 
                  AND puv.`verb` NOT IN (?, ?)";
            $params[] = Activity::ANNOUNCE;
            $params[] = Activity::SHARE;

            if ($year !== 'all') {
                if ($year === 'older') {
                    $cutoff_year = (int) date('Y') - 10;
                    $dt_cutoff = new \DateTime($cutoff_year . '-01-01 00:00:00', $user_tz);
                    $dt_cutoff->setTimezone($utc_tz);
                    $sql .= " AND puv.`created` < ? ";
                    $params[] = $dt_cutoff->format('Y-m-d H:i:s');
                } else {
                    $dt_start = new \DateTime($year . '-01-01 00:00:00', $user_tz);
                    $dt_start->setTimezone($utc_tz);
                    $dt_end = new \DateTime($year . '-12-31 23:59:59', $user_tz);
                    $dt_end->setTimezone($utc_tz);
                    $sql .= " AND puv.`created` >= ? AND puv.`created` <= ? ";
                    $params[] = $dt_start->format('Y-m-d H:i:s');
                    $params[] = $dt_end->format('Y-m-d H:i:s');
                }
            }

            $sql .= " ORDER BY puv.`created` DESC";
            $res = DBA::p($sql, ...$params);

            $current_month = '';
            $month_temp_file = null;
            $mf = null;
            $is_first_in_month = true;
            $count_in_month = 0;
            $files_to_cleanup = [];

            $manifest = [];
            $search_index_file = tempnam($temp_dir, 'ppex_search');
            $cleanup[] = $search_index_file;
            $sf = fopen($search_index_file, 'w');
            if (!$sf) {
                throw new \Exception(DI::l10n()->t('Could not create temporary file. Disk full?'));
            }
            if (fwrite($sf, 'window.FRIENDICA_SEARCH_INDEX = [') === false) {
                throw new \Exception(DI::l10n()->t('Could not create temporary file. Disk full?'));
            }
            $is_first_search = true;

            while ($item = DBA::fetch($res)) {
                if (memory_get_usage(true) > ($limit_bytes * 0.9) && $limit_bytes != -1) {
                    throw new \Exception(DI::l10n()->t('Memory limit reached.'));
                }
                if (connection_status() != CONNECTION_NORMAL) {
                    throw new \Exception('Connection lost.');
                }

                $date = new \DateTime($item['created'], $utc_tz);
                $date->setTimezone($user_tz);
                $month_key = $date->format('Y-m');

                if ($current_month !== '' && $current_month !== $month_key) {
                    fwrite($mf, '];');
                    fclose($mf);
                    $zip->addFile($month_temp_file, 'data/' . $current_month . '.js');
                    $manifest[] = ['key' => $current_month, 'label' => $this->getMonthLabel($current_month), 'count' => $count_in_month];
                    $current_month = '';
                }

                if ($current_month === '') {
                    $current_month = $month_key;
                    @set_time_limit(300);
                    $month_temp_file = tempnam($temp_dir, 'ppex_mon_' . $current_month);
                    $cleanup[] = $month_temp_file;
                    $mf = fopen($month_temp_file, 'w');
                    if (!$mf) {
                        throw new \Exception(DI::l10n()->t('Could not create temporary file. Disk full?'));
                    }
                    if (fwrite($mf, 'window.FRIENDICA_EXPORT_DATA["' . $current_month . '"] = [') === false) {
                        throw new \Exception(DI::l10n()->t('Could not create temporary file. Disk full?'));
                    }
                    $is_first_in_month = true;
                    $count_in_month = 0;
                }

                $body = $item['body'];
                $fields = ['uri-id', 'uri', 'body', 'title', 'author-name', 'author-link', 'author-avatar', 'author-gsid', 'guid', 'created', 'plink', 'network', 'has-media', 'quote-uri-id', 'post-type'];
                $shared = DI::contentItem()->getSharedPost($item, $fields);
                if (!empty($shared['post'])) {
                    $body .= "\n" . DI::contentItem()->createSharedBlockByArray($shared['post'], false, true);
                }

                $itemSplitAttachments = DI::postMediaRepository()->splitAttachments($item['uri-id'], [], $item['has-media'], true);
                $missing_links = [];
                foreach ($itemSplitAttachments['link'] as $attachment) {
                    if (strpos($body, $attachment->url) === false) {
                        $missing_links[] = (!empty($attachment->name) && $attachment->name !== $attachment->url) ? '[url=' . $attachment->url . ']' . $attachment->name . '[/url]' : '[url]' . $attachment->url . '[/url]';
                    }
                }
                if (!empty($missing_links)) $body .= "\n\n[hr]\n" . implode("\n", $missing_links);

                $body = BBCode::setMentionsToNicknames($body);
                $html_content = BBCode::convertForUriId($item['uri-id'], $body, BBCode::EXTERNAL);
                $filtered_html = $this->filterHtml($html_content, $media);

                $gallery_url = '';
                if (strpos($html_content, '<img') !== false) {
                    if (preg_match('/href=["\']([^"\']+\/image\/[^"\']+)["\']/i', $html_content, $m)) $gallery_url = $m[1];
                    elseif (preg_match('/src=["\']([^"\']+\/photo\/[^"\']+)["\']/i', $html_content, $m)) $gallery_url = $m[1];
                    if ($gallery_url && strpos($gallery_url, 'http') !== 0 && strpos($gallery_url, '//') !== 0) $gallery_url = rtrim((string) DI::baseUrl(), '/') . '/' . ltrim($gallery_url, '/');
                }

                $post_data = [
                    'id' => $item['uri-id'],
                    'date' => $this->formatDate($date),
                    'title' => htmlspecialchars($item['title'] ?? '', ENT_QUOTES | ENT_HTML5),
                    'html' => $filtered_html,
                    'plink' => $this->sanitizeUrl($item['plink']),
                    'private' => (bool) $item['private'],
                    'view_orig_label' => $this->labels['view_orig'],
                    'gallery_url' => $this->sanitizeUrl($gallery_url),
                    'gallery_label' => $this->labels['gallery']
                ];

                if (!$is_first_in_month) {
                    fwrite($mf, ',');
                }
                if (fwrite($mf, json_encode($post_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) === false) {
                    throw new \Exception(DI::l10n()->t('Could not create temporary file. Disk full?'));
                }
                $is_first_in_month = false;
                $count_in_month++;

                $search_entry = [
                    'id' => $item['uri-id'],
                    'm' => $current_month,
                    'd' => $this->formatDate($date, true),
                    't' => htmlspecialchars($item['title'] ?? '', ENT_QUOTES | ENT_HTML5),
                    's' => trim(strip_tags($filtered_html))
                ];
                if (!$is_first_search) {
                    fwrite($sf, ',');
                }
                if (fwrite($sf, json_encode($search_entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) === false) {
                    throw new \Exception(DI::l10n()->t('Could not create temporary file. Disk full?'));
                }
                $is_first_search = false;

                unset($html_content, $filtered_html);
            }
            DBA::close($res);

            if ($current_month !== '') {
                fwrite($mf, '];');
                fclose($mf);
                $zip->addFile($month_temp_file, 'data/' . $current_month . '.js');
                $manifest[] = ['key' => $current_month, 'label' => $this->getMonthLabel($current_month), 'count' => $count_in_month];
            }

            fwrite($sf, '];');
            fclose($sf);
            $zip->addFromString('data/manifest.js', 'window.FRIENDICA_MANIFEST = ' . json_encode($manifest) . ';');
            $zip->addFile($search_index_file, 'data/search.js');
            $zip->close();

            foreach ($cleanup as $f) {
                if ($f !== $lock_file && $f !== $tmp_file) {
                    @unlink($f);
                }
            }

            $fh = fopen($tmp_file, 'rb');
            if (!$fh) {
                throw new \Exception('Could not open generated archive for downloading.');
            }
            $ob_limit = 20;
            while (ob_get_level() > 0 && $ob_limit-- > 0) {
                ob_end_clean();
            }

            header('Content-Type: application/zip');
            $safe_nickname = preg_replace('/[^a-zA-Z0-9_\-]/', '', $user['nickname']);
            header('Content-Disposition: attachment; filename=friendica_export_' . $safe_nickname . '_' . date('Y-m-d') . '.zip');
            header('Content-Length: ' . filesize($tmp_file));

            fpassthru($fh);
            fclose($fh);
            @unlink($tmp_file);
            System::exit();

        } finally {
            if (isset($res)) @DBA::close($res);
            if (isset($sf) && is_resource($sf)) @fclose($sf);
            if (isset($mf) && is_resource($mf)) @fclose($mf);
            if (isset($fh) && is_resource($fh)) @fclose($fh);
            if (isset($zip) && $zip instanceof \ZipArchive) @$zip->close();
            foreach ($cleanup as $f) {
                @unlink($f);
            }
        }
    }

    /**
     * Helpers moved from main file
     */
    private function returnBytes($val): int
    {
        $val = trim($val);
        if ($val === '') return 0;
        if ($val === '-1') return -1;
        $last = strtolower($val[strlen($val) - 1]);
        $val = (int) $val;
        switch ($last) {
            case 'g':
                $val *= 1024;
                // intentional fall-through
            case 'm':
                $val *= 1024;
                // intentional fall-through
            case 'k':
                $val *= 1024;
                break;
            default:
                // No known suffix — value is already in bytes
                break;
        }
        return $val;
    }

    private function getUserTimezone(string $tz_str): \DateTimeZone
    {
        try { return new \DateTimeZone($tz_str ?: 'UTC'); } 
        catch (\Exception $e) { return new \DateTimeZone('UTC'); }
    }

    private function getBase64Avatar(int $uid): string
    {
        foreach ([5, 4] as $scale) {
            $photo = Photo::selectFirst([], ['uid' => $uid, 'scale' => $scale, 'profile' => true]);
            if (DBA::isResult($photo)) {
                $data = Photo::getImageDataForPhoto($photo);
                if (!empty($data)) return 'data:' . $photo['type'] . ';base64,' . base64_encode($data);
            }
        }
        return 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80"><rect width="80" height="80" fill="#eee"/><text x="50%" y="54%" font-family="Arial" font-size="40" text-anchor="middle" fill="#ccc">👤</text></svg>');
    }

    private function formatDate(\DateTime $date, bool $short = false): string
    {
        $day = $date->format('j');
        $month = (int) $date->format('n');
        $year = $date->format('Y');
        $time = $date->format('H:i');

        if ($short) return sprintf('%02d. %s %s', $day, $this->months[$month], $year);
        
        if ($this->is_de) return sprintf('%d. %s %s, %s Uhr', $day, $this->months[$month], $year, $time);
        return sprintf('%s %d, %s %s', $this->months[$month], $day, $year, $time);
    }

    private function getMonthLabel(string $month_key): string
    {
        $date = new \DateTime($month_key . '-01');
        $m = (int) $date->format('n');
        $y = $date->format('Y');
        return $this->months[$m] . ' ' . $y;
    }

    private function filterHtml(string $html, string $privacy): string
    {
        if (strpos($html, '<') === false) return $html;
        // Allowed tags for the archive. 
        // Note: data: URIs in img src are intentionally allowed for offline compatibility (embedded icons, etc.)
        $allowed_tags = '<a><b><blockquote><br><cite><code><dd><del><div><dl><dt><em><h1><h2><h3><h4><h5><h6><hr><i><img><ins><li><ol><p><pre><q><s><samp><small><span><strong><sub><sup><table><tbody><td><tfoot><th><thead><tr><ul>';
        $html = strip_tags($html, $allowed_tags);
        $html = preg_replace('/\bon[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]*)/i', '', $html);
        $html = preg_replace('/href\s*=\s*(?:"\s*javascript:[^"]*"|\'\s*javascript:[^\']*\'|javascript:[^\s>]*)/i', 'href="#"', $html);

        if ($privacy === 'placeholder' && strpos($html, '<img') !== false) {
            $base_url = (string) DI::baseUrl();
            $html = preg_replace_callback('/(<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>)?\s*(<img[^>]+src=["\']([^"\']+)["\'][^>]*>)\s*(<\/a>)?/i', function ($matches) use ($base_url) {
                $url = !empty($matches[4]) ? $matches[4] : $matches[2];
                $alt = '';
                if (preg_match('/alt=["\']([^"\']+)["\']/i', $matches[3], $am)) $alt = trim($am[1]);
                if (empty($alt) && preg_match('/title=["\']([^"\']+)["\']/i', $matches[3], $tm)) $alt = trim($tm[1]);
                if (strpos($url, 'http') !== 0 && strpos($url, '//') !== 0) $url = rtrim($base_url, '/') . '/' . ltrim($url, '/');
                $display_alt = '';
                if (!empty($alt) && $alt !== $url) {
                    $display_alt = '<div class="placeholder-info"><strong>' . $this->labels['alt'] . ':</strong> ' . htmlspecialchars($alt, ENT_QUOTES) . '</div>';
                }
                return '<div class="privacy-placeholder">' . $display_alt . '<a href="' . htmlspecialchars($url, ENT_QUOTES) . '" target="_blank" class="placeholder-btn">' . $this->labels['view'] . '</a>' . '</div>';
            }, $html);
        }

        if (strpos($html, '<a') !== false) {
            $html = preg_replace_callback('/<a\s+(.*?)href=["\'](.*?)["\'](.*?)>/i', function ($matches) {
                $url = $matches[2];
                if (strpos($url, 'search?tag=') !== false) {
                    $parts = parse_url($url); parse_str($parts['query'] ?? '', $qp);
                    if ($tag = ($qp['tag'] ?? '')) {
                        return '<a href="javascript:void(0)" data-tag="' . htmlspecialchars($tag, ENT_QUOTES | ENT_HTML5) . '" class="internal-tag">';
                    }
                }
                if (strpos($url, 'http') === 0) return '<a href="' . htmlspecialchars($url, ENT_QUOTES) . '" rel="noreferrer noopener nofollow" target="_blank">';
                return $matches[0];
            }, $html);
        }
        return $html;
    }

    private function getIndexHtml(array $user, string $avatar_url, string $theme): string
    {
        $hostname = parse_url(DI::baseUrl(), PHP_URL_HOST);
        $full_handle = $user['nickname'] . '@' . $hostname;
        
        $l10n = [
            'search' => DI::l10n()->t('Search archive...'),
            'welcome_h' => DI::l10n()->t('Welcome to your Archive'),
            'welcome_p' => DI::l10n()->t('Select a month from the sidebar to start browsing.'),
            'res_for' => DI::l10n()->t('Results for'),
            'clear_search' => DI::l10n()->t('Clear Search'),
            'no_res' => DI::l10n()->t('No results for'),
            'next' => DI::l10n()->t('Next'),
            'prev' => DI::l10n()->t('Previous'),
            'page_info' => DI::l10n()->t('Page %d of %d'),
            'meta_title' => DI::l10n()->t('🔍 Meta Information'),
            'meta_published' => DI::l10n()->t('PUBLISHED'),
            'meta_source' => DI::l10n()->t('ORIGINAL SOURCE'),
            'meta_view_on' => DI::l10n()->t('View on Friendica ↗')
        ];

        $tpl_path = dirname(__DIR__) . '/templates/archive/index.tpl';
        $content = file_get_contents($tpl_path);

        $macros = [
            '{{$lang}}' => $this->is_de ? 'de' : 'en',
            '{{$username}}' => htmlspecialchars($user['username']),
            '{{$full_handle}}' => htmlspecialchars($full_handle),
            '{{$avatar_url}}' => htmlspecialchars($avatar_url),
            '{{$theme}}' => $theme,
            '{{$search_placeholder}}' => htmlspecialchars($l10n['search']),
            '{{$welcome_h}}' => htmlspecialchars($l10n['welcome_h']),
            '{{$welcome_p}}' => htmlspecialchars($l10n['welcome_p']),
            '{{$l10n_json}}' => json_encode($l10n, JSON_UNESCAPED_UNICODE) ?: '{}'
        ];

        return str_replace(array_keys($macros), array_values($macros), $content);
    }

    private function checkIsGerman(): bool
    {
        return (strpos(DI::l10n()->getCurrentLang(), 'de') === 0);
    }

    private function getLocalizedMonths(): array
    {
        return [
            1 => DI::l10n()->t('January'),
            2 => DI::l10n()->t('February'),
            3 => DI::l10n()->t('March'),
            4 => DI::l10n()->t('April'),
            5 => DI::l10n()->t('May'),
            6 => DI::l10n()->t('June'),
            7 => DI::l10n()->t('July'),
            8 => DI::l10n()->t('August'),
            9 => DI::l10n()->t('September'),
            10 => DI::l10n()->t('October'),
            11 => DI::l10n()->t('November'),
            12 => DI::l10n()->t('December'),
        ];
    }

    /**
     * Attempts to acquire an export slot.
     * 
     * Uses a Mutex (flock) to ensure that the slot check and lock creation 
     * happen atomically, preventing race conditions where more than 2 
     * users could start an export simultaneously.
     */
    private function acquireLock(int $uid): ?string
    {
        $path = $this->getLockPath();
        $mutex_file = $path . '/mutex.lock';
        $fh = fopen($mutex_file, 'c');
        if (!$fh) {
            return null;
        }

        $lock_file_path = null;
        if (flock($fh, LOCK_EX)) {
            $user_lock = $path . '/user_' . $uid . '.lock';
            $now = time();

            // 1. Check if THIS user already has an active lock
            if (file_exists($user_lock)) {
                if ($now - filemtime($user_lock) < 1200) {
                    flock($fh, LOCK_UN);
                    fclose($fh);
                    throw new \Exception(DI::l10n()->t('You already have an export running. Please wait for it to finish.'));
                }
                @unlink($user_lock);
            }

            // 2. Check total capacity (max 2 global slots)
            if ($this->getLockedSlotsCount() < 2) {
                if (touch($user_lock)) {
                    $lock_file_path = $user_lock;
                }
            }

            flock($fh, LOCK_UN);
        }
        fclose($fh);
        return $lock_file_path;
    }

    public static function getLockPath(): string
    {
        $hash = substr(md5((string) DI::baseUrl()), 0, 8);
        $path = DI::system()->getTempPath() . '/ppex_locks_' . $hash;
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
        return $path;
    }

    private function getLockedSlotsCount(): int
    {
        $path = $this->getLockPath();
        if (!is_dir($path)) {
            return 0;
        }

        $files = glob($path . '/user_*.lock');
        $count = 0;
        $now = time();
        foreach ($files as $f) {
            if ($now - filemtime($f) > 1200) { // 20 minutes timeout for stale locks
                @unlink($f);
                continue;
            }
            $count++;
        }
        return $count;
    }

    /**
     * Checks how many export slots are currently locked.
     * 
     * Note: This method does not use a Mutex and is intended for UI display 
     * or preliminary checks. The actual atomic enforcement happens in acquireLock().
     */
    public function getLockedSlots(): int 
    {
        return $this->getLockedSlotsCount();
    }

    private function sanitizeUrl(string $url): string
    {
        if (empty($url)) return '';
        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
            return $url;
        }
        return '';
    }
}
