<?php
/* ==========================================================================
   FETCHER.PHP — fetches a real HTTP response for a page, exactly like any
   real crawler (Screaming Frog, Googlebot, etc.) would see it. This is why
   the optimizer needs a live, deployed site to produce real data — reading
   PHP source directly would tell us what the code SAYS, not what a browser
   actually RECEIVES, and this system's whole premise is measurable,
   non-fabricated results.
   ========================================================================== */

function octg_optimizer_base_url(): string {
    $pdo = octg_db();
    if ($pdo) {
        try {
            $val = $pdo->query("SELECT base_url FROM optimizer_settings WHERE id = 1 LIMIT 1")->fetchColumn();
            if ($val) return rtrim($val, '/');
        } catch (Throwable $e) {}
    }
    return 'https://onechancetogrow.com'; // fallback if the DB/table isn't available yet
}

function octg_fetch_url(string $url, int $timeoutSeconds = 10): array {
    if (!function_exists('curl_init')) {
        return ['html' => null, 'http_code' => 0, 'error' => 'cURL is not available on this server.'];
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => $timeoutSeconds,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'OCTG-Website-Optimizer/1.0 (internal audit crawler)',
    ]);
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'html' => $html !== false ? $html : null,
        'http_code' => $httpCode,
        'error' => $error ?: null,
    ];
}
