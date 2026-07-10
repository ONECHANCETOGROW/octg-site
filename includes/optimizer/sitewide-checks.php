<?php
/* ==========================================================================
   SITEWIDE-CHECKS.PHP — checks that apply once to the whole site rather
   than per-page: static performance proxies (present in every page's HTML
   by shared architecture), skip-link presence, and duplicate title/
   description detection across the full crawled set.
   ========================================================================== */

/* Performance: static proxies only, checked against ONE representative
   page's HTML (since these come from shared header.php/footer.php and are
   therefore identical site-wide) — see scorer.php's file header for why
   this is not the same thing as real Core Web Vitals. */
function octg_check_performance_proxies(string $sampleHtml): array {
    $score = 0;
    $issues = [];

    $hasDeferredScripts = (bool) preg_match('/<script\s+src="[^"]+"\s+defer/i', $sampleHtml);
    if ($hasDeferredScripts) $score += 35;
    else $issues[] = ['category'=>'performance','severity'=>'warning','issue'=>'Scripts not deferred','reason'=>'Shared scripts should load with the defer attribute so they don\'t block HTML parsing.','fix'=>'Add defer to shared script tags in includes/footer.php.'];

    $hasLazyImages = (bool) preg_match('/loading="lazy"/i', $sampleHtml);
    if ($hasLazyImages) $score += 35;
    else $issues[] = ['category'=>'performance','severity'=>'info','issue'=>'No lazy-loaded images detected on sample page','reason'=>'Below-the-fold images should use loading="lazy".','fix'=>'Verify octg_media() is being used for images on this page.'];

    $hasFontSwap = (bool) preg_match('/display=swap/i', $sampleHtml);
    if ($hasFontSwap) $score += 30;
    else $issues[] = ['category'=>'performance','severity'=>'warning','issue'=>'Font loading may block rendering','reason'=>'Google Fonts URL should include &display=swap.','fix'=>'Add display=swap to the Google Fonts stylesheet URL in includes/header.php.'];

    return ['score' => $score, 'issues' => $issues];
}

function octg_check_skip_link(string $sampleHtml): bool {
    return (bool) preg_match('/class="skip-link"/i', $sampleHtml);
}

/* Cross-page duplicate detection — needs every page's extracted facts. */
function octg_find_duplicates(array $allPageFacts): array {
    $issues = [];
    $titleMap = [];
    $descMap = [];

    foreach ($allPageFacts as $url => $facts) {
        if ($facts['title']) $titleMap[$facts['title']][] = $url;
        if ($facts['description']) $descMap[$facts['description']][] = $url;
    }

    foreach ($titleMap as $title => $urls) {
        if (count($urls) > 1) {
            foreach ($urls as $url) {
                $issues[] = ['url' => $url, 'category'=>'seo','severity'=>'critical','issue'=>'Duplicate title tag','reason'=>'This title is identical to ' . (count($urls)-1) . ' other page(s): ' . implode(', ', array_diff($urls, [$url])),'fix'=>'Write a unique title for this page.'];
            }
        }
    }
    foreach ($descMap as $desc => $urls) {
        if (count($urls) > 1) {
            foreach ($urls as $url) {
                $issues[] = ['url' => $url, 'category'=>'seo','severity'=>'critical','issue'=>'Duplicate meta description','reason'=>'This description is identical to ' . (count($urls)-1) . ' other page(s): ' . implode(', ', array_diff($urls, [$url])),'fix'=>'Write a unique meta description for this page.'];
            }
        }
    }
    return $issues;
}
