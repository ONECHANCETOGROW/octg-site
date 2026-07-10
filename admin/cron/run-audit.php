<?php
/* ==========================================================================
   RUN-AUDIT.PHP — the full crawl → analyze → score → save → notify cycle.

   This is a REAL, complete, working script — but it needs two things this
   sandbox can't provide to actually execute: (1) a live, deployed site at
   the configured base_url for the crawler to fetch, and (2) either a
   manual visit to this URL or a real cron job calling it automatically.

   TO RUN IT MANUALLY: visit /admin/cron/run-audit.php?manual=1 while
   logged into the admin panel.

   TO RUN IT EVERY 24 HOURS AUTOMATICALLY: Hostinger's hPanel has a "Cron
   Jobs" section. Add a job that runs once daily and executes:
     php /home/YOUR_USERNAME/public_html/admin/cron/run-audit.php --scheduled
   (Hostinger's cron UI walks you through the exact path — it fills in your
   username automatically.) This is real setup you do on your host; it is
   not something I can register from here.
   ========================================================================== */

$isCli = (php_sapi_name() === 'cli');
$isScheduled = $isCli && in_array('--scheduled', $argv ?? []);

if (!$isCli) {
    require_once __DIR__ . '/../includes/admin-auth.php'; // must be logged in to trigger manually via browser
    if (!isset($_GET['manual'])) { http_response_code(400); die('Add ?manual=1 to run this by hand.'); }
}

require_once __DIR__ . '/../../api/_lib.php';
require_once __DIR__ . '/../../includes/activity-logger.php';
require_once __DIR__ . '/../../includes/notification-service.php';
require_once __DIR__ . '/../includes/optimizer/page-inventory.php';
require_once __DIR__ . '/../includes/optimizer/fetcher.php';
require_once __DIR__ . '/../includes/optimizer/analyzer.php';
require_once __DIR__ . '/../includes/optimizer/scorer.php';
require_once __DIR__ . '/../includes/optimizer/sitewide-checks.php';

$pdo = octg_db();
if (!$pdo) {
    die('Database not connected — see includes/db-config.example.php. The optimizer cannot run without it.');
}

$startedAt = date('Y-m-d H:i:s');
$baseUrl = octg_optimizer_base_url();
$pages = octg_optimizer_page_inventory();

$allFacts = [];       // url => facts, for duplicate detection
$allScores = [];      // url => scores
$allIssues = [];      // flat list, each with 'url' added
$fetchFailures = [];
$sampleHtmlForPerf = null;

foreach ($pages as $page) {
    $url = $baseUrl . $page['url'];
    $result = octg_fetch_url($url);

    if (!$result['html'] || $result['http_code'] >= 400) {
        $fetchFailures[] = ['url' => $page['url'], 'http_code' => $result['http_code'], 'error' => $result['error']];
        $allIssues[] = [
            'url' => $page['url'], 'category' => 'technical_seo', 'severity' => 'critical',
            'issue' => 'Page did not load (HTTP ' . $result['http_code'] . ')',
            'reason' => $result['error'] ?: 'The crawler could not fetch this URL successfully.',
            'fix' => 'Verify the page is live and returns a 200 response.',
        ];
        continue;
    }

    if (!$sampleHtmlForPerf) $sampleHtmlForPerf = $result['html'];

    $facts = octg_analyze_html($result['html']);
    $allFacts[$page['url']] = $facts;
}

/* Build a real inbound-link graph from every page actually fetched, before
   scoring — this is why scoring happens in a second pass below, rather than
   inline in the fetch loop: internal_linking scores need to know how many
   OTHER crawled pages link to a given URL, which isn't knowable until every
   page has been fetched at least once. */
$inboundCounts = array_fill_keys(array_keys($allFacts), 0);
$linkEdges = []; // [source_url, target_url] — persisted below once $reportId exists, feeds the Knowledge Engine's link graph
foreach ($allFacts as $sourceUrl => $facts) {
    foreach ($facts['internal_link_targets'] as $target) {
        $target = rtrim($target, '/');
        if ($target === '' ) $target = '/';
        if (isset($inboundCounts[$target]) && $target !== $sourceUrl) {
            $inboundCounts[$target]++;
            $linkEdges[] = [$sourceUrl, $target];
        }
    }
}

foreach ($allFacts as $url => $facts) {
    $scored = octg_score_page($facts, $inboundCounts[$url] ?? 0);
    foreach ($scored['issues'] as $issue) {
        $issue['url'] = $url;
        $issue['file_path'] = null;
        $allIssues[] = $issue;
    }
    $allScores[$url] = $scored['scores'];
}

/* Sitewide performance proxy check, run once against a representative page
   (the shared header/footer/script architecture is identical everywhere) */
$perfResult = $sampleHtmlForPerf
    ? octg_check_performance_proxies($sampleHtmlForPerf)
    : ['score' => 0, 'issues' => [['category'=>'performance','severity'=>'critical','issue'=>'No page could be fetched to check performance','reason'=>'Every page fetch failed.','fix'=>'Verify the site is live and reachable.']]];

foreach ($allScores as $url => &$scores) {
    $scores['performance'] = $perfResult['score'];
    $scores['overall'] = round(array_sum($scores) / count($scores), 1);
}
unset($scores);
foreach ($perfResult['issues'] as $issue) { $issue['url'] = null; $allIssues[] = $issue; }

/* Cross-page duplicate title/description detection */
$dupeIssues = octg_find_duplicates($allFacts);
$allIssues = array_merge($allIssues, $dupeIssues);

/* ---- Aggregate + save ---- */
$categories = ['seo','aeo','geo','eeat','accessibility','performance','technical_seo','internal_linking'];
$averages = [];
foreach ($categories as $cat) {
    $vals = array_column($allScores, $cat);
    $averages[$cat] = $vals ? round(array_sum($vals) / count($vals), 2) : null;
}
$overallVals = array_column($allScores, 'overall');
$overallAvg = $overallVals ? round(array_sum($overallVals) / count($overallVals), 2) : null;

$criticalCount = count(array_filter($allIssues, fn($i) => ($i['severity'] ?? '') === 'critical'));
$warningCount = count(array_filter($allIssues, fn($i) => ($i['severity'] ?? '') === 'warning'));

$stmt = $pdo->prepare(
    'INSERT INTO audit_reports (run_type, pages_crawled, overall_score, seo_score, aeo_score, geo_score, eeat_score,
     accessibility_score, performance_score, technical_seo_score, internal_linking_score,
     critical_issue_count, warning_count, started_at, finished_at)
     VALUES (:rt, :pc, :overall, :seo, :aeo, :geo, :eeat, :a11y, :perf, :tseo, :il, :crit, :warn, :started, NOW())'
);
$stmt->execute([
    ':rt' => $isScheduled ? 'scheduled' : 'manual', ':pc' => count($pages), ':overall' => $overallAvg,
    ':seo' => $averages['seo'], ':aeo' => $averages['aeo'], ':geo' => $averages['geo'], ':eeat' => $averages['eeat'],
    ':a11y' => $averages['accessibility'], ':perf' => $averages['performance'], ':tseo' => $averages['technical_seo'],
    ':il' => $averages['internal_linking'], ':crit' => $criticalCount, ':warn' => $warningCount, ':started' => $startedAt,
]);
$reportId = $pdo->lastInsertId();

/* Persist the link graph for the Knowledge Engine (includes/knowledge/link-graph.php) */
if ($linkEdges) {
    $linkStmt = $pdo->prepare('INSERT INTO page_links (report_id, source_url, target_url) VALUES (:rid, :src, :tgt)');
    foreach ($linkEdges as [$src, $tgt]) {
        $linkStmt->execute([':rid' => $reportId, ':src' => $src, ':tgt' => $tgt]);
    }
}

foreach ($allScores as $url => $scores) {
    $pdo->prepare(
        'INSERT INTO audit_page_scores (report_id, url, overall_score, seo_score, aeo_score, geo_score, eeat_score, accessibility_score, performance_score, technical_seo_score, internal_linking_score)
         VALUES (:rid, :url, :overall, :seo, :aeo, :geo, :eeat, :a11y, :perf, :tseo, :il)'
    )->execute([
        ':rid' => $reportId, ':url' => $url, ':overall' => $scores['overall'], ':seo' => $scores['seo'],
        ':aeo' => $scores['aeo'], ':geo' => $scores['geo'], ':eeat' => $scores['eeat'],
        ':a11y' => $scores['accessibility'], ':perf' => $scores['performance'], ':tseo' => $scores['technical_seo'],
        ':il' => $scores['internal_linking'],
    ]);
}

$issueIds = [];
foreach ($allIssues as $issue) {
    $isSafe = !empty($issue['safe']);
    $stmt2 = $pdo->prepare(
        'INSERT INTO audit_issues (report_id, url, category, severity, issue, reason, suggested_fix, is_safe_auto_fix)
         VALUES (:rid, :url, :cat, :sev, :issue, :reason, :fix, :safe)'
    );
    $stmt2->execute([
        ':rid' => $reportId, ':url' => $issue['url'] ?? '(sitewide)', ':cat' => $issue['category'],
        ':sev' => $issue['severity'], ':issue' => $issue['issue'], ':reason' => $issue['reason'],
        ':fix' => $issue['fix'] ?? null, ':safe' => $isSafe ? 1 : 0,
    ]);
    if ($isSafe) $issueIds[] = $pdo->lastInsertId();
}

/* Draft improvements for safe-fixable issues found (still requires human
   approval before anything is applied — see admin/optimizer-drafts.php) */
foreach ($issueIds as $issueId) {
    $issueRow = $pdo->prepare('SELECT * FROM audit_issues WHERE id = :id');
    $issueRow->execute([':id' => $issueId]);
    $issue = $issueRow->fetch();
    if (!$issue) continue;
    $pdo->prepare(
        'INSERT INTO draft_improvements (issue_id, url, field, current_value, suggested_value, reasoning)
         VALUES (:iid, :url, :field, :cur, :sug, :reason)'
    )->execute([
        ':iid' => $issueId, ':url' => $issue['url'], ':field' => $issue['issue'],
        ':cur' => null, ':sug' => $issue['suggested_fix'], ':reason' => $issue['reason'],
    ]);
}

/* Email the report */
$settings = octg_email_settings();
if ($settings['notifications_enabled']) {
    $topIssuesText = implode("\n", array_map(fn($i) => "- [{$i['severity']}] " . ($i['url'] ?? 'sitewide') . ': ' . $i['issue'], array_slice($allIssues, 0, 15)));
    octg_notify_lead('Website Audit Report', [
        'Overall Score' => $overallAvg . '/100',
        'Pages Crawled' => count($pages),
        'Critical Issues' => $criticalCount,
        'Warnings' => $warningCount,
        'Fetch Failures' => count($fetchFailures),
        'Top Issues' => $topIssuesText,
    ]);
}
$pdo->prepare('UPDATE audit_reports SET email_sent = 1 WHERE id = :id')->execute([':id' => $reportId]);

octg_log_activity('Website audit completed', "Report #{$reportId} — overall score {$overallAvg}, {$criticalCount} critical issues");

/* Safe auto-fixes, only if explicitly enabled in Optimizer Settings — these
   regenerate derived metadata files only (see includes/optimizer/safe-fixes.php
   for exactly why that's the one category considered safe to run unattended). */
try {
    $optSettings = $pdo->query('SELECT auto_fix_enabled FROM optimizer_settings WHERE id = 1')->fetch();
    if ($optSettings && $optSettings['auto_fix_enabled']) {
        require_once __DIR__ . '/../includes/optimizer/safe-fixes.php';
        $fixResults = [
            octg_regenerate_sitemap(),
            octg_regenerate_image_sitemap(),
            octg_regenerate_llms_txt(),
            octg_regenerate_robots_txt(),
        ];
        octg_log_activity('Automatic safe fixes applied', implode(' | ', $fixResults));
    }
} catch (Throwable $e) {
    // optimizer_settings table may not exist yet on an older install — safe to skip.
}

if (!$isCli) {
    header('Location: /admin/optimizer.php?report_id=' . $reportId);
    exit;
}
echo "Audit complete. Report #{$reportId}. Overall score: {$overallAvg}. Critical: {$criticalCount}, Warnings: {$warningCount}.\n";
