<?php
/**
 * RealMember Domain Updater
 *
 * Fetches the latest list of disposable email domains from the
 * community-maintained GitHub repository and writes it to the
 * RealMember data directory.
 *
 * Hardening features:
 *  - HTTP timeout & explicit User-Agent (avoids hanging cron jobs and
 *    GitHub 403s for empty UAs).
 *  - Per-line domain validation (RFC-compliant, IDN/Punycode allowed)
 *    so corrupted upstream data cannot inject garbage into the list.
 *  - Sanity-check on the minimum number of domains to prevent
 *    accidentally replacing a healthy list with an empty one.
 *  - Atomic file replacement via tmp-file + rename(), so a crash mid-
 *    write cannot leave behind a corrupt PHP file that would break the
 *    dashboard at the next include.
 *  - CLI-only guard: refuses to run when invoked over HTTP, so a
 *    misconfigured webserver (Nginx without proper try_files, Apache
 *    without AllowOverride) cannot turn the script into an open download
 *    trigger or file-write vector.
 *
 * Usage: php update_domains.php
 */

// Defense-in-Depth: refuse execution from any non-CLI SAPI.
// In a default Friendica install this script is never reachable via the web
// (the front-controller .htaccess blocks it), but on misconfigured servers
// it could become callable — which would expose an unauthenticated GitHub
// download trigger, a file-write to data/disposable_domains.php, and an
// implicit DoS amplifier (every web hit fetches from upstream).
if (PHP_SAPI !== 'cli') {
	http_response_code(403);
	exit('This script is meant to be run from the command line only.');
}

$sourceUrl  = 'https://raw.githubusercontent.com/disposable-email-domains/disposable-email-domains/main/disposable_email_blocklist.conf';
$targetFile = __DIR__ . '/../data/disposable_domains.php';
$updateFile = __DIR__ . '/../data/last_update.txt';

// Minimum number of valid domains we expect upstream to deliver.
// If the response contains fewer entries, we abort to avoid replacing
// a healthy list with a truncated/empty one (defensive against upstream regressions).
const REALMEMBER_MIN_DOMAINS = 50;

/**
 * Abort with a non-zero exit code so cron monitors can detect failures.
 * `die()` alone exits with status 0, which would silently mask errors.
 */
function realmember_fail(string $message): void
{
    fwrite(STDERR, $message);
    exit(1);
}

echo "RealMember: Lade neueste Spam-Domains herunter...\n";

// HTTP context with timeout and User-Agent.
// Without timeout a stalled GitHub mirror could hang the cron job indefinitely;
// without UA some GitHub endpoints respond with 403.
$ctx = stream_context_create([
    'http' => [
        'timeout'    => 30,
        'user_agent' => 'RealMember-Updater/1.1 (Friendica Addon)',
        'header'     => "Accept: text/plain\r\n",
    ],
]);

$rawContent = @file_get_contents($sourceUrl, false, $ctx);

if ($rawContent === false) {
    realmember_fail("FEHLER: Konnte die Liste nicht von GitHub abrufen. Bitte Internetverbindung und Timeout prüfen.\n");
}

$lines   = explode("\n", $rawContent);
$domains = [];
$skipped = 0;

// RFC 1035 / 1123 conforming domain regex.
// Allows lowercase letters, digits, hyphens (not at start/end of label),
// IDN/Punycode (xn--*), labels up to 63 chars, full domain up to 253 chars.
$domainRegex = '/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)+$/';

foreach ($lines as $line) {
    $line = strtolower(trim($line));

    // Skip empty lines and comments
    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }

    // Validate per-line: reject anything that does not look like a domain.
    // This protects us if the upstream list is ever corrupted, mangled, or
    // tampered with — even though `var_export` would safely escape strings,
    // garbage entries would still pollute the in_array() lookup at runtime.
    if (strlen($line) > 253 || !preg_match($domainRegex, $line)) {
        fwrite(STDERR, "WARNUNG: Überspringe ungültige Zeile: " . substr($line, 0, 80) . "\n");
        $skipped++;
        continue;
    }

    $domains[] = $line;
}

$domains = array_values(array_unique($domains));
sort($domains);
$count = count($domains);

// Sanity check: refuse to overwrite a healthy list with a suspiciously short one.
// The real upstream list contains several thousand entries; anything below the
// floor strongly indicates a network glitch or an upstream regression.
if ($count < REALMEMBER_MIN_DOMAINS) {
    realmember_fail(sprintf(
        "FEHLER: Nur %d gültige Domains erhalten (Mindestmenge: %d). Update abgebrochen, bestehende Liste bleibt unverändert.\n",
        $count,
        REALMEMBER_MIN_DOMAINS
    ));
}

if ($skipped > 0) {
    echo "Hinweis: $skipped ungültige Zeilen wurden übersprungen.\n";
}

// Build the PHP output file content.
$output  = "<?php\n";
$output .= "/**\n";
$output .= " * Automatisch generiert von RealMember Updater am " . date('Y-m-d H:i:s') . "\n";
$output .= " * Quelle: $sourceUrl\n";
$output .= " * Anzahl gültiger Domains: $count\n";
$output .= " */\n\n";
$output .= "return [\n";
foreach ($domains as $domain) {
    $output .= "\t" . var_export($domain, true) . ",\n";
}
$output .= "];\n";

// Atomic write: write to a temp file first, then rename.
// rename() is atomic on POSIX filesystems, so a reader (the dashboard
// doing `include $targetFile`) will either see the old or the new file
// in full, never a half-written corrupt one.
$tmpFile = $targetFile . '.tmp';

if (file_put_contents($tmpFile, $output) === false) {
    realmember_fail("FEHLER: Konnte nicht in die temporäre Datei $tmpFile schreiben. Bitte Schreibrechte prüfen.\n");
}

if (!rename($tmpFile, $targetFile)) {
    @unlink($tmpFile);
    realmember_fail("FEHLER: Konnte temporäre Datei nicht nach $targetFile umbenennen.\n");
}

// Update timestamp file (best-effort; failure here is non-fatal).
@file_put_contents($updateFile, date('Y-m-d H:i:s'));

echo "ERFOLG: $count Domains wurden erfolgreich gespeichert.\n";
