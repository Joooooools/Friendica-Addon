<?php
/**
 * RealMember Domain Updater
 * 
 * This script fetches the latest list of disposable email domains from GitHub
 * and updates the RealMember database file.
 * 
 * Usage: php update_domains.php
 */

$sourceUrl = 'https://raw.githubusercontent.com/disposable-email-domains/disposable-email-domains/main/disposable_email_blocklist.conf';
$targetFile = __DIR__ . '/../data/disposable_domains.php';
$updateFile = __DIR__ . '/../data/last_update.txt';

echo "RealMember: Lade neueste Spam-Domains herunter...\n";

$rawContent = file_get_contents($sourceUrl);

if ($rawContent === false) {
    die("FEHLER: Konnte die Liste nicht von GitHub abrufen. Bitte Internetverbindung prüfen.\n");
}

$lines = explode("\n", $rawContent);
$domains = [];

foreach ($lines as $line) {
    $line = trim($line);
    // Skip empty lines and comments
    if (empty($line) || str_starts_with($line, '#')) {
        continue;
    }
    $domains[] = $line;
}

sort($domains);
$domains = array_unique($domains);

$count = count($domains);

// Prepare PHP file content
$output = "<?php\n";
$output .= "/**\n";
$output .= " * Automatisch generiert von RealMember Updater am " . date('Y-m-d H:i:s') . "\n";
$output .= " * Quelle: $sourceUrl\n";
$output .= " */\n\n";
$output .= "return [\n";

foreach ($domains as $domain) {
    $output .= "\t" . var_export($domain, true) . ",\n";
}

$output .= "];\n";

if (file_put_to_file($targetFile, $output)) {
    echo "ERFOLG: $count Domains wurden erfolgreich gespeichert.\n";
    file_put_contents($updateFile, date('Y-m-d H:i:s'));
} else {
    die("FEHLER: Konnte nicht in die Datei $targetFile schreiben. Bitte Schreibrechte prüfen.\n");
}

/**
 * Helper because write_to_file might be different in actual execution context
 * but here we use standard PHP.
 */
function file_put_to_file($file, $data) {
    return file_put_contents($file, $data) !== false;
}
