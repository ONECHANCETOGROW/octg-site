<?php
/* ==========================================================================
   _LIB.PHP — shared helpers for every /api/*.php endpoint.
   Never included directly by a page — only by other api/*.php scripts.
   ========================================================================== */

function octg_db(): ?PDO {
    static $pdo = null;
    static $attempted = false;
    if ($attempted) return $pdo;
    $attempted = true;

    $configPath = __DIR__ . '/../includes/db-config.php';
    if (!file_exists($configPath)) return null;

    $cfg = require $configPath;
    try {
        $dsn = "mysql:host={$cfg['host']};dbname={$cfg['database']};charset={$cfg['charset']}";
        $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (Throwable $e) {
        $pdo = null;
    }
    return $pdo;
}

/* Fallback so a submission is never silently lost before the database is
   configured — writes one JSON line per submission into a directory the
   .htaccess above blocks from public access. */
function octg_log_fallback(string $type, array $data): void {
    $line = json_encode(array_merge($data, ['_type' => $type, '_logged_at' => date('c')])) . "\n";
    @file_put_contents(__DIR__ . '/logs/' . $type . '.log', $line, FILE_APPEND | LOCK_EX);
}

function octg_field(string $key, $default = ''): string {
    return isset($_POST[$key]) ? trim((string) $_POST[$key]) : $default;
}

function octg_send_json(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function octg_is_valid_email(string $email): bool {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function octg_get_setting(string $group, string $key, string $default = ''): string {
    $pdo = octg_db();
    if (!$pdo) return $default;
    $stmt = $pdo->prepare('SELECT setting_value FROM website_settings WHERE setting_group = ? AND setting_key = ?');
    $stmt->execute([$group, $key]);
    $val = $stmt->fetchColumn();
    return $val !== false ? $val : $default;
}

function octg_get_content(string $key, string $default = ''): string {
    $pdo = octg_db();
    if (!$pdo) return $default;
    $stmt = $pdo->prepare('SELECT content_value FROM cms_content WHERE content_key = ? AND status = \'published\'');
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    return $val !== false ? $val : $default;
}

function octg_get_json(string $key, array $default = []): array {
    $pdo = octg_db();
    if (!$pdo) return $default;
    $stmt = $pdo->prepare('SELECT content_value FROM cms_content WHERE content_key = ? AND type = "json"');
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    if ($val) {
        $decoded = json_decode($val, true);
        return is_array($decoded) ? $decoded : $default;
    }
    return $default;
}

/* Helper to get media data by looking up the content_key in cms_content,
   then fetching the associated media record by ID. */
function octg_get_media_data(string $contentKey): ?array {
    $pdo = octg_db();
    if (!$pdo) return null;
    
    // First, find the media ID assigned to this content slot
    $stmt = $pdo->prepare('SELECT content_value FROM cms_content WHERE content_key = ?');
    $stmt->execute([$contentKey]);
    $mediaId = $stmt->fetchColumn();
    
    if ($mediaId && is_numeric($mediaId)) {
        // Now fetch the media details
        $stmt2 = $pdo->prepare('SELECT file_path, alt_text, title FROM cms_media WHERE id = ?');
        $stmt2->execute([$mediaId]);
        $media = $stmt2->fetch();
        if ($media) {
            return $media;
        }
    }
    return null;
}
