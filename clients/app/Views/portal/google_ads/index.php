<?php
/**
 * Google Ads Intelligence Report — Full tabbed viewer.
 *
 * Renders every section of intelligence.json interactively.
 * Uses Chart.js (CDN) for all charts and vanilla JS for tab switching.
 * Component patterns:
 *   insight_block()  — Data → Insight → Recommendation card
 *   kpi_card()       — single metric with label/value/trend
 *   entity_table()   — generic sortable data table
 *   opportunity_card() — opportunity with evidence + ROI
 */

// ── Severity colour helper ────────────────────────────────────────────
function severity_css(string $s): string {
    return match ($s) {
        'positive' => '#10b981',
        'warning'  => '#f59e0b',
        'critical' => '#ef4444',
        default    => '#64748b',
    };
}
function severity_bg(string $s): string {
    return match ($s) {
        'positive' => '#f0fdf4',
        'warning'  => '#fffbeb',
        'critical' => '#fef2f2',
        default    => '#f8fafc',
    };
}
function priority_badge(string $p): string {
    [$bg, $col] = match (strtolower($p)) {
        'high'   => ['#fef2f2','#dc2626'],
        'medium' => ['#fffbeb','#d97706'],
        default  => ['#f0fdf4','#16a34a'],
    };
    return "<span style='background:$bg;color:$col;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;'>$p</span>";
}
function effort_badge(string $e): string {
    [$bg, $col] = match (strtolower($e)) {
        'high'   => ['#fef2f2','#dc2626'],
        'medium' => ['#fffbeb','#d97706'],
        default  => ['#f0fdf4','#16a34a'],
    };
    return "<span style='background:$bg;color:$col;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;'>$e</span>";
}
?>
<?php if ($data === null): ?>
<!-- ── Empty State ──────────────────────────────────────────────────── -->
<div class="page-header"><div><h1 class="page-title">Google Ads</h1></div></div>
<div class="card" style="margin-top:24px;">
    <div class="card-body" style="text-align:center;padding:64px 24px;">
        <i data-lucide="target" width="48" style="color:#cbd5e1;margin-bottom:16px;"></i>
        <p style="font-weight:700;font-size:18px;margin-bottom:8px;">No Intelligence Report Yet</p>
        <p style="color:var(--text-muted);font-size:14px;max-width:400px;margin:0 auto;">Your account manager will run your first Google Ads audit shortly. All campaign insights, opportunities, and recommendations will appear here.</p>
    </div>
</div>
<?php else:
$d = $data;
$score    = $d['score']       ?? null;
$grade    = $d['grade']       ?? '--';
$health   = $d['health_status'] ?? 'Unknown';
$reportDate = $d['report_date'] ? date('F j, Y', strtotime((string)$d['report_date'])) : 'N/A';
$scoreColor = match(true) {
    $score >= 90 => '#10b981',
    $score >= 80 => '#3b82f6',
    $score >= 70 => '#f59e0b',
    default      => '#ef4444',
};
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
/* ── Tab system ──────────────────────────────────────────────────────── */
.report-tabs { display:flex; gap:4px; border-bottom:2px solid var(--border-light); margin-bottom:28px; overflow-x:auto; }
.report-tab  { padding:10px 18px; font-size:13px; font-weight:600; color:var(--text-muted); cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; white-space:nowrap; transition:color .15s,border-color .15s; border-radius:6px 6px 0 0; background:none; border-top:none; border-left:none; border-right:none; }
.report-tab:hover { color:var(--primary); background:var(--primary-light); }
.report-tab.active { color:var(--primary); border-bottom-color:var(--primary); }
.report-panel { display:none; }
.report-panel.active { display:block; }

/* ── Insight block ───────────────────────────────────────────────────── */
.insight-block { border-radius:12px; padding:20px 24px; margin:16px 0; display:flex; gap:16px; }
.insight-icon  { flex-shrink:0; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
.insight-body  { flex:1; }
.insight-label { font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.05em; margin-bottom:4px; }
.insight-text  { font-size:13px; line-height:1.6; margin-bottom:10px; color:var(--text-main); }
.rec-label     { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:var(--primary); margin-bottom:4px; }
.rec-text      { font-size:13px; line-height:1.5; color:var(--text-main); font-weight:500; }

/* ── KPI cards ───────────────────────────────────────────────────────── */
.kpi-grid      { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:16px; }
.kpi-card      { background:#fff; border:1px solid var(--border-light); border-radius:12px; padding:20px; }
.kpi-label     { font-size:12px; color:var(--text-muted); font-weight:600; margin-bottom:8px; }
.kpi-value     { font-size:28px; font-weight:800; letter-spacing:-.03em; color:var(--text-main); line-height:1; }
.kpi-sub       { font-size:11px; color:var(--text-muted); margin-top:6px; }

/* ── Entity table ────────────────────────────────────────────────────── */
.ent-table     { width:100%; border-collapse:collapse; font-size:13px; }
.ent-table th  { text-align:left; padding:10px 14px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:var(--text-muted); border-bottom:2px solid var(--border-light); background:var(--bg-subtle,#f8fafc); }
.ent-table td  { padding:12px 14px; border-bottom:1px solid var(--border-light); vertical-align:middle; }
.ent-table tbody tr:hover { background:var(--primary-light); }
.ent-table tbody tr:last-child td { border-bottom:none; }

/* ── Opportunity card ────────────────────────────────────────────────── */
.opp-card      { 
    background: #ffffff;
    border: 1px solid var(--border-light); 
    border-radius: 16px; 
    padding: 24px; 
    margin-bottom: 16px; 
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}
.opp-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
    border-color: #cbd5e1;
}
.opp-header    { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:16px; flex-wrap:wrap; }
.opp-title     { font-weight:800; font-size:16px; color:#0f172a; line-height: 1.4; display:flex; align-items:center; gap:8px; }
.opp-badges    { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
.opp-section   { font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px; display:flex; align-items:center; gap:6px;}
.opp-value     { font-size:14px; color:#334155; margin-bottom:16px; line-height:1.6; }
.opp-evidence  { background: linear-gradient(to right, #f8fafc, #ffffff); border: 1px solid #e2e8f0; border-radius:10px; padding:16px; font-size:13px; font-family:monospace; color:#475569; }

/* ── Score gauge ─────────────────────────────────────────────────────── */
.gauge-wrap    { text-align:center; padding:20px 0; }

/* ── Section header ──────────────────────────────────────────────────── */
.section-title { font-size:16px; font-weight:800; color:#1e293b; margin:0 0 16px; display:flex; align-items:center; gap:8px; }
.section-sub   { font-size:13px; color:var(--text-muted); margin-top:-12px; margin-bottom:16px; }
</style>

<!-- ── Page Header ───────────────────────────────────────────────────── -->
<div class="page-header" style="margin-bottom:8px;">
    <div>
        <h1 class="page-title">Google Ads Intelligence Report</h1>
        <p style="color:var(--text-muted);margin-top:4px;font-size:13px;">
            Generated <?php echo $reportDate; ?> &nbsp;·&nbsp;
            <span style="background:<?php echo $scoreColor; ?>;color:#fff;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:700;"><?php echo $score ?? '--'; ?>/100 &nbsp; <?php echo $grade; ?> &nbsp; <?php echo $health; ?></span>
        </p>
    </div>
</div>

<!-- ── Tab Navigation ────────────────────────────────────────────────── -->
<nav class="report-tabs" id="reportTabs">
    <button class="report-tab active" data-tab="tab-summary">
        <i data-lucide="sparkles" width="14" style="vertical-align:middle;margin-right:4px;"></i> Executive Summary
    </button>
    <button class="report-tab" data-tab="tab-performance">
        <i data-lucide="bar-chart-2" width="14" style="vertical-align:middle;margin-right:4px;"></i> Performance
    </button>
    <button class="report-tab" data-tab="tab-campaigns">
        <i data-lucide="megaphone" width="14" style="vertical-align:middle;margin-right:4px;"></i> Campaigns
    </button>
    <button class="report-tab" data-tab="tab-keywords">
        <i data-lucide="search" width="14" style="vertical-align:middle;margin-right:4px;"></i> Keywords &amp; Search Terms
    </button>
    <button class="report-tab" data-tab="tab-opportunities">
        <i data-lucide="zap" width="14" style="vertical-align:middle;margin-right:4px;"></i> Opportunities
    </button>
    <button class="report-tab" data-tab="tab-recommendations">
        <i data-lucide="list-checks" width="14" style="vertical-align:middle;margin-right:4px;"></i> Recommendations
    </button>
    <button class="report-tab" data-tab="tab-breakdown">
        <i data-lucide="layers" width="14" style="vertical-align:middle;margin-right:4px;"></i> Data Breakdown
    </button>
</nav>

<!-- ════════════════════════════════════════════════════════════════════ -->
<!-- TAB 1 — EXECUTIVE SUMMARY                                           -->
<!-- ════════════════════════════════════════════════════════════════════ -->
<div class="report-panel active" id="tab-summary">

    <?php if ($d['exec_summary_text']): ?>
    <div class="card" style="background:linear-gradient(135deg,#1e293b,#0f172a);color:#fff;border:none;margin-bottom:24px;">
        <div class="card-body" style="padding:32px;">
            <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:12px;">AI Intelligence Summary</div>
            <p style="font-size:16px;line-height:1.75;font-weight:400;color:#f1f5f9;margin:0;"><?php echo htmlspecialchars($d['exec_summary_text']); ?></p>
            <?php if ($d['exec_business_assessment']): ?>
            <div style="margin-top:20px;padding-top:20px;border-top:1px solid #334155;font-size:14px;color:#94a3b8;font-style:italic;">
                <?php echo htmlspecialchars($d['exec_business_assessment']); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:24px;">

        <!-- Biggest Wins -->
        <?php if (!empty($d['exec_biggest_wins'])): ?>
        <div class="card" style="margin:0;border-top:3px solid #10b981;">
            <div class="card-body" style="padding:20px;">
                <div class="section-title" style="font-size:13px;color:#10b981;">
                    <i data-lucide="trending-up" width="16"></i> Biggest Wins
                </div>
                <ul style="margin:0;padding-left:18px;font-size:13px;color:var(--text-main);line-height:1.8;">
                    <?php foreach ($d['exec_biggest_wins'] as $win): ?>
                    <li><?php echo htmlspecialchars($win); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- Biggest Risks -->
        <?php if (!empty($d['exec_biggest_risks'])): ?>
        <div class="card" style="margin:0;border-top:3px solid #ef4444;">
            <div class="card-body" style="padding:20px;">
                <div class="section-title" style="font-size:13px;color:#ef4444;">
                    <i data-lucide="alert-triangle" width="16"></i> Biggest Risks
                </div>
                <ul style="margin:0;padding-left:18px;font-size:13px;color:var(--text-main);line-height:1.8;">
                    <?php foreach ($d['exec_biggest_risks'] as $risk): ?>
                    <li><?php echo htmlspecialchars($risk); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- Immediate Actions -->
        <?php if (!empty($d['exec_immediate_actions'])): ?>
        <div class="card" style="margin:0;border-top:3px solid #f59e0b;">
            <div class="card-body" style="padding:20px;">
                <div class="section-title" style="font-size:13px;color:#d97706;">
                    <i data-lucide="zap" width="16"></i> Immediate Actions
                </div>
                <ol style="margin:0;padding-left:18px;font-size:13px;color:var(--text-main);line-height:1.8;">
                    <?php foreach ($d['exec_immediate_actions'] as $action): ?>
                    <li><?php echo htmlspecialchars($action); ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($d['exec_long_term_strategy']): ?>
    <div class="card" style="margin-bottom:0;border-left:4px solid var(--primary);">
        <div class="card-body" style="padding:20px 24px;">
            <div class="section-title" style="font-size:13px;color:var(--primary);margin-bottom:8px;">
                <i data-lucide="compass" width="15"></i> Long-Term Strategy
            </div>
            <p style="font-size:14px;line-height:1.65;color:var(--text-main);margin:0;"><?php echo htmlspecialchars($d['exec_long_term_strategy']); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Overall insight block -->
    <?php if (!empty($d['insights']['overall'])): ?>
    <?php $ins = $d['insights']['overall']; ?>
    <div class="insight-block" style="background:<?php echo severity_bg($ins['severity']); ?>;border:1px solid <?php echo severity_css($ins['severity']); ?>20;margin-top:20px;">
        <div class="insight-icon" style="background:<?php echo severity_css($ins['severity']); ?>20;">
            <i data-lucide="cpu" width="18" style="color:<?php echo severity_css($ins['severity']); ?>;"></i>
        </div>
        <div class="insight-body">
            <div class="insight-label" style="color:<?php echo severity_css($ins['severity']); ?>;">AI Analysis</div>
            <div class="insight-text"><?php echo htmlspecialchars($ins['insight']); ?></div>
            <?php if ($ins['recommendation']): ?>
            <div class="rec-label">Recommendation</div>
            <div class="rec-text"><?php echo htmlspecialchars($ins['recommendation']); ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ════════════════════════════════════════════════════════════════════ -->
<!-- TAB 2 — PERFORMANCE OVERVIEW                                        -->
<!-- ════════════════════════════════════════════════════════════════════ -->
<div class="report-panel" id="tab-performance">

    <!-- KPI Cards -->
    <div class="kpi-grid" style="margin-bottom:24px;">
        <div class="kpi-card">
            <div class="kpi-label">Total Spend</div>
            <div class="kpi-value">$<?php echo number_format($d['spend'], 0); ?></div>
            <?php if ($d['spend_trend']): ?><div class="kpi-sub"><?php echo htmlspecialchars($d['spend_trend']); ?></div><?php endif; ?>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Conversions</div>
            <div class="kpi-value"><?php echo number_format((float)$d['conversions'], 0); ?></div>
            <?php if ($d['conversions_trend']): ?><div class="kpi-sub"><?php echo htmlspecialchars($d['conversions_trend']); ?></div><?php endif; ?>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Cost Per Conversion</div>
            <div class="kpi-value"><?php echo $d['cpa'] !== null ? '$' . number_format($d['cpa'], 2) : '--'; ?></div>
            <div class="kpi-sub">Account average</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Cost Per Click</div>
            <div class="kpi-value"><?php echo $d['cpc'] !== null ? '$' . number_format($d['cpc'], 2) : '--'; ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Total Clicks</div>
            <div class="kpi-value"><?php echo number_format((float)($d['statistics']['total_clicks'] ?? 0), 0); ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Conversion Rate</div>
            <div class="kpi-value"><?php echo $d['conversion_rate'] !== null ? number_format($d['conversion_rate'], 1) . '%' : '--'; ?></div>
        </div>
        <?php
        // Transparency/cross-check card: shows clicks x conversion rate so
        // the Conversion Rate % above is independently verifiable against
        // the raw click/conversion counts rather than taken on faith.
        $totalClicksForCheck = (float) ($d['statistics']['total_clicks'] ?? 0);
        $calculatedConversions = ($d['conversion_rate'] !== null)
            ? $totalClicksForCheck * ($d['conversion_rate'] / 100)
            : null;
        ?>
        <div class="kpi-card">
            <div class="kpi-label">Calculated Conversions</div>
            <div class="kpi-value"><?php echo $calculatedConversions !== null ? number_format($calculatedConversions, 2) : '--'; ?></div>
            <div class="kpi-sub">Total Clicks &times; Conversion Rate</div>
        </div>
        <div class="kpi-card" style="background:linear-gradient(135deg,#1e293b,#0f172a);color:#fff;border-color:transparent;">
            <div class="kpi-label" style="color:#94a3b8;">Health Score</div>
            <div class="kpi-value" style="color:<?php echo $scoreColor; ?>;"><?php echo $score ?? '--'; ?></div>
            <div class="kpi-sub" style="color:#64748b;"><?php echo $grade; ?> &middot; <?php echo $health; ?></div>
        </div>
    </div>

    <!-- Narrative -->
    <?php if ($d['narrative']): ?>
    <div class="card" style="background:var(--primary-light,#e0f2fe);border:none;margin-bottom:24px;">
        <div class="card-body" style="padding:20px 24px;">
            <p style="font-size:15px;line-height:1.7;margin:0;color:var(--text-main);"><?php echo htmlspecialchars($d['narrative']); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Campaign Comparison Chart -->
    <?php if (!empty($d['chart_campaigns'])): ?>
    <div class="card" style="margin-bottom:24px;">
        <div class="card-header" style="padding:18px 24px;border-bottom:1px solid var(--border-light);">
            <div class="card-title">Campaign Performance Comparison</div>
        </div>
        <div class="card-body" style="padding:24px;">
            <canvas id="chartCampaigns" height="120"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Budget Waste Alert -->
    <?php if ($d['budget_waste'] > 0): ?>
    <div class="card" style="border-left:4px solid #f59e0b;margin-bottom:24px;">
        <div class="card-body" style="padding:16px 20px;display:flex;align-items:center;gap:12px;">
            <i data-lucide="alert-triangle" width="20" style="color:#f59e0b;flex-shrink:0;"></i>
            <div>
                <strong style="font-size:14px;">$<?php echo number_format($d['budget_waste'], 2); ?> in Budget Waste Detected</strong>
                <p style="font-size:13px;color:var(--text-muted);margin:4px 0 0;">Campaigns with zero conversions are still spending. See the Campaigns tab for details.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Score Category Breakdown -->
    <?php if (!empty($d['scorecard_categories'])): ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
        <?php if (!empty($d['chart_score_categories'])): ?>
        <div class="card" style="margin:0;">
            <div class="card-header" style="padding:14px 20px;border-bottom:1px solid var(--border-light);">
                <div class="card-title" style="font-size:13px;">Score by Category</div>
            </div>
            <div class="card-body" style="padding:20px;">
                <canvas id="chartScoreRadar" height="200"></canvas>
            </div>
        </div>
        <?php endif; ?>
        <div class="card" style="margin:0;">
            <div class="card-header" style="padding:14px 20px;border-bottom:1px solid var(--border-light);">
                <div class="card-title" style="font-size:13px;">Category Breakdown</div>
            </div>
            <div class="card-body" style="padding:0;">
                <table class="ent-table">
                    <thead><tr><th>Category</th><th>Score</th><th>Grade</th><th>Penalties</th></tr></thead>
                    <tbody>
                    <?php foreach ($d['scorecard_categories'] as $catName => $cat): ?>
                    <tr>
                        <td style="font-weight:600;"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $catName))); ?></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="width:60px;height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;">
                                    <div style="width:<?php echo (int)($cat['score'] ?? 0); ?>%;height:100%;background:<?php
                                        $s = (int)($cat['score'] ?? 0);
                                        echo $s >= 80 ? '#10b981' : ($s >= 70 ? '#f59e0b' : '#ef4444');
                                    ?>;border-radius:3px;"></div>
                                </div>
                                <span style="font-weight:700;"><?php echo (int)($cat['score'] ?? 0); ?></span>
                            </div>
                        </td>
                        <td><span style="font-weight:700;"><?php echo htmlspecialchars($cat['grade'] ?? '—'); ?></span></td>
                        <td><span style="color:#ef4444;font-weight:600;">-<?php echo (int)($cat['penalties_applied'] ?? 0); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ════════════════════════════════════════════════════════════════════ -->
<!-- TAB 3 — CAMPAIGNS DEEP DIVE                                         -->
<!-- ════════════════════════════════════════════════════════════════════ -->
<div class="report-panel" id="tab-campaigns">

    <!-- Campaign Insight Block -->
    <?php if (!empty($d['insights']['campaigns'])): ?>
    <?php $ins = $d['insights']['campaigns']; ?>
    <div class="insight-block" style="background:<?php echo severity_bg($ins['severity']); ?>;border:1px solid <?php echo severity_css($ins['severity']); ?>20;">
        <div class="insight-icon" style="background:<?php echo severity_css($ins['severity']); ?>20;">
            <i data-lucide="megaphone" width="18" style="color:<?php echo severity_css($ins['severity']); ?>;"></i>
        </div>
        <div class="insight-body">
            <div class="insight-label" style="color:<?php echo severity_css($ins['severity']); ?>;">Campaign Analysis</div>
            <div class="insight-text"><?php echo htmlspecialchars($ins['insight']); ?></div>
            <?php if ($ins['recommendation']): ?>
            <div class="rec-label">Recommendation</div>
            <div class="rec-text"><?php echo htmlspecialchars($ins['recommendation']); ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- All Campaigns Table -->
    <?php if (!empty($d['all_campaigns'])): ?>
    <div class="card" style="margin-bottom:24px;">
        <div class="card-header" style="padding:18px 24px;border-bottom:1px solid var(--border-light);">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div class="card-title">All Campaigns (<?php echo count($d['all_campaigns']); ?>)</div>
                <span style="font-size:12px;color:var(--text-muted);">Sorted by conversions</span>
            </div>
        </div>
        <div style="overflow-x:auto;">
        <table class="ent-table">
            <thead>
                <tr>
                    <th>Campaign</th>
                    <th>Spend</th>
                    <th>Conversions</th>
                    <th>CPA</th>
                    <?php if (isset($d['all_campaigns'][0]['clicks'])): ?><th>Clicks</th><?php endif; ?>
                    <?php if (isset($d['all_campaigns'][0]['ctr'])): ?><th>CTR</th><?php endif; ?>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($d['all_campaigns'] as $camp):
                $campConv  = (float)($camp['conversions'] ?? 0);
                $campSpend = (float)($camp['spend'] ?? 0);
                $campCpa   = $campConv > 0 ? $campSpend / $campConv : null;
                $isWaste   = $campConv === 0.0 && $campSpend > 0;
                $isTop     = $campConv === (float)($d['all_campaigns'][0]['conversions'] ?? -1);
            ?>
            <tr style="<?php echo $isWaste ? 'background:#fef2f2;' : ''; ?>">
                <td style="font-weight:600;">
                    <?php echo htmlspecialchars($camp['campaign_name'] ?? '—'); ?>
                    <?php if ($isTop): ?><span style="font-size:10px;background:#f0fdf4;color:#16a34a;padding:2px 6px;border-radius:10px;margin-left:6px;font-weight:700;">TOP</span><?php endif; ?>
                    <?php if ($isWaste): ?><span style="font-size:10px;background:#fef2f2;color:#dc2626;padding:2px 6px;border-radius:10px;margin-left:6px;font-weight:700;">WASTE</span><?php endif; ?>
                </td>
                <td>$<?php echo number_format($campSpend, 2); ?></td>
                <td><?php echo (int)$campConv; ?></td>
                <td><?php echo $campCpa !== null ? '$' . number_format($campCpa, 2) : '<span style="color:#ef4444;">∞</span>'; ?></td>
                <?php if (isset($camp['clicks'])): ?><td><?php echo number_format((float)($camp['clicks'] ?? 0)); ?></td><?php endif; ?>
                <?php if (isset($camp['ctr'])): ?><td><?php echo htmlspecialchars((string)($camp['ctr'] ?? '—')); ?></td><?php endif; ?>
                <td>
                    <?php if ($isWaste): ?>
                        <span style="background:#fef2f2;color:#dc2626;padding:3px 8px;border-radius:20px;font-size:11px;font-weight:700;">No Conversions</span>
                    <?php elseif ($campConv > 0): ?>
                        <span style="background:#f0fdf4;color:#16a34a;padding:3px 8px;border-radius:20px;font-size:11px;font-weight:700;">Converting</span>
                    <?php else: ?>
                        <span style="background:#f8fafc;color:#64748b;padding:3px 8px;border-radius:20px;font-size:11px;font-weight:700;">No Data</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Scorecard Penalties -->
    <?php if (!empty($d['scorecard_penalties'])): ?>
    <div class="card">
        <div class="card-header" style="padding:18px 24px;border-bottom:1px solid var(--border-light);">
            <div class="card-title" style="color:#ef4444;">
                <i data-lucide="alert-circle" width="16" style="vertical-align:middle;margin-right:6px;"></i>
                Rule Violations (<?php echo count($d['scorecard_penalties']); ?> found)
            </div>
        </div>
        <div style="overflow-x:auto;">
        <table class="ent-table">
            <thead><tr><th>Rule ID</th><th>Category</th><th>Penalty</th><th>Evidence</th></tr></thead>
            <tbody>
            <?php foreach ($d['scorecard_penalties'] as $pen): ?>
            <tr>
                <td><span style="font-family:monospace;font-weight:700;font-size:12px;"><?php echo htmlspecialchars($pen['rule_id'] ?? '—'); ?></span></td>
                <td><?php echo htmlspecialchars(ucwords($pen['category'] ?? '—')); ?></td>
                <td><span style="color:#ef4444;font-weight:700;">-<?php echo (int)($pen['penalty'] ?? 0); ?> pts</span></td>
                <td style="font-size:12px;font-family:monospace;color:var(--text-muted);">
                    <?php
                    $ev = $pen['evidence'] ?? [];
                    $parts = [];
                    foreach ($ev as $k => $v) {
                        $parts[] = htmlspecialchars("$k: $v");
                    }
                    echo implode(' &middot; ', array_slice($parts, 0, 4));
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ════════════════════════════════════════════════════════════════════ -->
<!-- TAB 4 — KEYWORDS & SEARCH TERMS                                     -->
<!-- ════════════════════════════════════════════════════════════════════ -->
<div class="report-panel" id="tab-keywords">

    <!-- Search Terms Insight -->
    <?php if (!empty($d['insights']['search_terms'])): ?>
    <?php $ins = $d['insights']['search_terms']; ?>
    <div class="insight-block" style="background:<?php echo severity_bg($ins['severity']); ?>;border:1px solid <?php echo severity_css($ins['severity']); ?>20;">
        <div class="insight-icon" style="background:<?php echo severity_css($ins['severity']); ?>20;">
            <i data-lucide="search" width="18" style="color:<?php echo severity_css($ins['severity']); ?>;"></i>
        </div>
        <div class="insight-body">
            <div class="insight-label" style="color:<?php echo severity_css($ins['severity']); ?>;">Search Term Analysis</div>
            <div class="insight-text"><?php echo htmlspecialchars($ins['insight']); ?></div>
            <?php if ($ins['recommendation']): ?>
            <div class="rec-label">Recommendation</div>
            <div class="rec-text"><?php echo htmlspecialchars($ins['recommendation']); ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Wasted Spend Chart -->
    <?php if (!empty($d['chart_waste_terms'])): ?>
    <div class="card" style="margin-bottom:24px;">
        <div class="card-header" style="padding:14px 20px;border-bottom:1px solid var(--border-light);">
            <div class="card-title" style="font-size:13px;color:#ef4444;">Wasted Spend — Zero-Conversion Search Terms</div>
        </div>
        <div class="card-body" style="padding:20px;">
            <canvas id="chartWasteTerms" height="100"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Search Terms Table -->
    <?php
    $nonEmptyTerms = array_values(array_filter(
        $d['search_terms'] ?? [],
        fn($t) => trim($t['search_term'] ?? '') !== ''
    ));
    ?>
    <?php if (!empty($nonEmptyTerms)): ?>
    <div class="card" style="margin-bottom:24px;">
        <div class="card-header" style="padding:18px 24px;border-bottom:1px solid var(--border-light);">
            <div class="card-title">Search Terms (<?php echo count($nonEmptyTerms); ?>)</div>
        </div>
        <div style="overflow-x:auto;">
        <table class="ent-table">
            <thead><tr><th>Search Term</th><th>Spend</th><th>Conversions</th><th>CPA</th><th>Flag</th></tr></thead>
            <tbody>
            <?php
            usort($nonEmptyTerms, fn($a,$b) => (float)($b['conversions']??0) <=> (float)($a['conversions']??0));
            foreach ($nonEmptyTerms as $term):
                $termConv  = (float)($term['conversions'] ?? 0);
                $termSpend = (float)($term['spend'] ?? 0);
                $termCpa   = $termConv > 0 ? $termSpend / $termConv : null;
                $isWaste   = $termConv === 0.0 && $termSpend > 0;
            ?>
            <tr style="<?php echo $isWaste ? 'background:#fef9f9;' : ''; ?>">
                <td style="font-weight:500;"><?php echo htmlspecialchars($term['search_term']); ?></td>
                <td>$<?php echo number_format($termSpend, 2); ?></td>
                <td><?php echo (int)$termConv; ?></td>
                <td><?php echo $termCpa !== null ? '$' . number_format($termCpa, 2) : '—'; ?></td>
                <td>
                    <?php if ($isWaste): ?>
                        <span style="background:#fef2f2;color:#dc2626;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;">NEGATIVE KW</span>
                    <?php elseif ($termConv >= 3): ?>
                        <span style="background:#f0fdf4;color:#16a34a;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;">SCALE</span>
                    <?php else: ?>
                        <span style="color:#94a3b8;font-size:11px;">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Keywords Table -->
    <?php if (!empty($d['keywords_all'])): ?>
    <div class="card">
        <div class="card-header" style="padding:18px 24px;border-bottom:1px solid var(--border-light);">
            <div class="card-title">Keywords (<?php echo count($d['keywords_all']); ?>)</div>
        </div>
        <div style="overflow-x:auto;">
        <table class="ent-table">
            <thead><tr><th>Keyword</th><th>Spend</th><th>Conversions</th><th>CPA</th></tr></thead>
            <tbody>
            <?php foreach (array_slice($d['keywords_all'], 0, 20) as $kw): ?>
            <tr>
                <td style="font-weight:500;"><?php echo htmlspecialchars($kw['keyword'] ?? $kw['search_term'] ?? '—'); ?></td>
                <td>$<?php echo number_format((float)($kw['spend'] ?? 0), 2); ?></td>
                <td><?php echo (int)($kw['conversions'] ?? 0); ?></td>
                <td><?php echo (float)($kw['conversions']??0) > 0
                    ? '$' . number_format((float)$kw['spend'] / (float)$kw['conversions'], 2)
                    : '—'; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php if (count($d['keywords_all']) > 20): ?>
        <div style="padding:12px 20px;text-align:center;color:var(--text-muted);font-size:12px;">
            Showing top 20 of <?php echo count($d['keywords_all']); ?> keywords
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($nonEmptyTerms) && empty($d['keywords_all'])): ?>
    <div class="card"><div class="card-body" style="text-align:center;padding:40px;color:var(--text-muted);">
        No keyword or search term data in this report. Upload a Keywords or Search Terms CSV to unlock this analysis.
    </div></div>
    <?php endif; ?>
</div>

<!-- ════════════════════════════════════════════════════════════════════ -->
<!-- TAB 5 — OPPORTUNITIES                                               -->
<!-- ════════════════════════════════════════════════════════════════════ -->
<div class="report-panel" id="tab-opportunities">

    <?php if (!empty($d['opportunities'])): ?>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 class="section-title" style="margin:0;"><?php echo count($d['opportunities']); ?> Opportunities Identified</h2>
        <span style="font-size:12px;color:var(--text-muted);">Sorted by priority</span>
    </div>
    <?php foreach ($d['opportunities'] as $opp):
        $oppPriority = $opp['priority']  ?? 'Medium';
        $oppDiff     = $opp['difficulty'] ?? 'Medium';
        $borderColor = match(strtolower($oppPriority)) { 'high' => '#ef4444', 'medium' => '#f59e0b', default => '#10b981' };
    ?>
    <div class="opp-card" style="border-left:4px solid <?php echo $borderColor; ?>;">
        <div class="opp-header">
            <div>
                <div style="font-size:10px;font-family:monospace;color:var(--text-muted);margin-bottom:6px;letter-spacing:0.05em;"><i data-lucide="hash" width="10" style="display:inline-block;vertical-align:middle;margin-right:2px;color:#94a3b8;"></i><?php echo htmlspecialchars($opp['opportunity_id'] ?? ''); ?></div>
                <div class="opp-title">
                    <div style="background:<?php echo $borderColor; ?>20; padding:6px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; color:<?php echo $borderColor; ?>;">
                        <i data-lucide="zap" width="18"></i>
                    </div>
                    <?php echo htmlspecialchars($opp['problem'] ?? ''); ?>
                </div>
            </div>
            <div class="opp-badges">
                <?php echo priority_badge($oppPriority); ?>
                <?php echo effort_badge($oppDiff); ?>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:20px;background:#f8fafc;padding:16px;border-radius:12px;border:1px solid #f1f5f9;">
            <div>
                <div class="opp-section"><i data-lucide="target" width="14" style="color:#94a3b8;"></i> Business Impact</div>
                <div class="opp-value"><?php echo htmlspecialchars($opp['business_impact'] ?? '—'); ?></div>
            </div>
            <div>
                <div class="opp-section"><i data-lucide="trending-up" width="14" style="color:#94a3b8;"></i> Estimated ROI</div>
                <div class="opp-value" style="color:var(--primary);font-weight:800;font-size:15px;"><?php echo htmlspecialchars($opp['estimated_roi'] ?? '—'); ?></div>
            </div>
        </div>
        <?php if (!empty($opp['evidence'])): ?>
        <div>
            <div class="opp-section"><i data-lucide="search" width="14" style="color:#94a3b8;"></i> Evidence</div>
            <div class="opp-evidence">
                <?php foreach ($opp['evidence'] as $k => $v): ?>
                <span style="margin-right:20px;display:inline-block;margin-bottom:4px;"><strong><?php echo htmlspecialchars(str_replace('_', ' ', $k)); ?>:</strong> <span style="color:#64748b;"><?php echo htmlspecialchars((string)$v); ?></span></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php else: ?>
    <div class="card"><div class="card-body" style="text-align:center;padding:48px;color:var(--text-muted);">
        <i data-lucide="check-circle-2" width="40" style="color:#10b981;margin-bottom:12px;"></i>
        <p style="font-weight:600;">No opportunities flagged — the account passed all evaluated rules.</p>
    </div></div>
    <?php endif; ?>
</div>

<!-- ════════════════════════════════════════════════════════════════════ -->
<!-- TAB 6 — RECOMMENDATIONS                                             -->
<!-- ════════════════════════════════════════════════════════════════════ -->
<div class="report-panel" id="tab-recommendations">

    <?php if (!empty($d['recommendations'])): ?>
    <div class="card">
        <div class="card-header" style="padding:18px 24px;border-bottom:1px solid var(--border-light);">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div class="card-title"><?php echo count($d['recommendations']); ?> Recommendations</div>
                <span style="font-size:12px;color:var(--text-muted);">Sorted by priority</span>
            </div>
        </div>
        <div style="overflow-x:auto;">
        <table class="ent-table">
            <thead>
                <tr>
                    <th>What to Change</th>
                    <th>Why It Matters</th>
                    <th>Expected Outcome</th>
                    <th>Priority</th>
                    <th>Effort</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($d['recommendations'] as $rec): ?>
            <tr>
                <td style="font-weight:600;max-width:240px;"><?php echo htmlspecialchars($rec['what_to_change'] ?? '—'); ?></td>
                <td style="font-size:12px;color:var(--text-muted);max-width:240px;"><?php echo htmlspecialchars($rec['why_it_matters'] ?? '—'); ?></td>
                <td style="font-size:12px;max-width:200px;"><?php echo htmlspecialchars($rec['expected_outcome'] ?? '—'); ?></td>
                <td><?php echo priority_badge($rec['priority'] ?? 'Medium'); ?></td>
                <td><?php echo effort_badge($rec['effort'] ?? 'Medium'); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php else: ?>
    <div class="card"><div class="card-body" style="text-align:center;padding:48px;color:var(--text-muted);">
        No recommendations in this report.
    </div></div>
    <?php endif; ?>
</div>

<!-- ════════════════════════════════════════════════════════════════════ -->
<!-- TAB 7 — DATA BREAKDOWN                                              -->
<!-- ════════════════════════════════════════════════════════════════════ -->
<div class="report-panel" id="tab-breakdown">

    <!-- Entity counts from statistics -->
    <?php $stats = $d['statistics'] ?? []; ?>
    <div class="kpi-grid" style="margin-bottom:24px;">
        <?php
        $entCounts = [
            'Campaigns'     => (int)($stats['campaigns_count']    ?? count($d['all_campaigns'] ?? [])),
            'Search Terms'  => (int)($stats['searchTerms_count']  ?? count($d['search_terms'] ?? [])),
            'Keywords'      => (int)($stats['keywords_count']     ?? count($d['keywords_all'] ?? [])),
            'Devices'       => (int)($stats['devices_count']      ?? count($d['devices'] ?? [])),
            'Locations'     => (int)($stats['locations_count']    ?? count($d['locations'] ?? [])),
            'Landing Pages' => (int)($stats['landingPages_count'] ?? count($d['landing_pages'] ?? [])),
        ];
        foreach ($entCounts as $label => $count):
        ?>
        <div class="kpi-card">
            <div class="kpi-label"><?php echo $label; ?></div>
            <div class="kpi-value"><?php echo $count; ?></div>
            <div class="kpi-sub">entities tracked</div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Devices -->
    <?php if (!empty($d['devices'])): ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
        <?php if (!empty($d['chart_devices'])): ?>
        <div class="card" style="margin:0;">
            <div class="card-header" style="padding:14px 20px;border-bottom:1px solid var(--border-light);">
                <div class="card-title" style="font-size:13px;">Device Split — Conversions</div>
            </div>
            <div class="card-body" style="padding:20px;">
                <canvas id="chartDevices" height="200"></canvas>
            </div>
        </div>
        <?php endif; ?>
        <div class="card" style="margin:0;">
            <div class="card-header" style="padding:14px 20px;border-bottom:1px solid var(--border-light);">
                <div class="card-title" style="font-size:13px;">Device Performance</div>
            </div>
            <?php if (!empty($d['insights']['devices'])): ?>
            <?php $dins = $d['insights']['devices']; ?>
            <div style="padding:12px 16px;background:<?php echo severity_bg($dins['severity']); ?>;border-bottom:1px solid var(--border-light);font-size:13px;color:var(--text-main);">
                <?php echo htmlspecialchars($dins['insight']); ?>
                <?php if ($dins['recommendation']): ?><br><strong style="color:var(--primary);">↳</strong> <?php echo htmlspecialchars($dins['recommendation']); ?><?php endif; ?>
            </div>
            <?php endif; ?>
            <div style="overflow-x:auto;">
            <table class="ent-table">
                <thead><tr><th>Device</th><th>Spend</th><th>Conversions</th></tr></thead>
                <tbody>
                <?php foreach ($d['devices'] as $dev): ?>
                <tr>
                    <td><?php echo htmlspecialchars($dev['device'] ?? $dev['device_type'] ?? '—'); ?></td>
                    <td>$<?php echo number_format((float)($dev['spend'] ?? 0), 2); ?></td>
                    <td><?php echo (int)($dev['conversions'] ?? 0); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Locations -->
    <?php if (!empty($d['locations'])): ?>
    <div class="card" style="margin-bottom:24px;">
        <div class="card-header" style="padding:14px 20px;border-bottom:1px solid var(--border-light);">
            <div class="card-title" style="font-size:13px;">Location Performance (<?php echo count($d['locations']); ?>)</div>
        </div>
        <div style="overflow-x:auto;">
        <table class="ent-table">
            <thead><tr><th>Location</th><th>Spend</th><th>Conversions</th><th>CPA</th></tr></thead>
            <tbody>
            <?php
            usort($d['locations'], fn($a,$b) => (float)($b['conversions']??0) <=> (float)($a['conversions']??0));
            foreach (array_slice($d['locations'], 0, 15) as $loc):
                $lConv = (float)($loc['conversions'] ?? 0);
                $lSpend = (float)($loc['spend'] ?? 0);
            ?>
            <tr>
                <td><?php echo htmlspecialchars($loc['location'] ?? $loc['location_name'] ?? '—'); ?></td>
                <td>$<?php echo number_format($lSpend, 2); ?></td>
                <td><?php echo (int)$lConv; ?></td>
                <td><?php echo $lConv > 0 ? '$' . number_format($lSpend / $lConv, 2) : '—'; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Landing Pages -->
    <?php if (!empty($d['landing_pages'])): ?>
    <div class="card" style="margin-bottom:24px;">
        <div class="card-header" style="padding:14px 20px;border-bottom:1px solid var(--border-light);">
            <div class="card-title" style="font-size:13px;">Landing Pages (<?php echo count($d['landing_pages']); ?>)</div>
        </div>
        <div style="overflow-x:auto;">
        <table class="ent-table">
            <thead><tr><?php
                $lpCols = !empty($d['landing_pages']) ? array_keys($d['landing_pages'][0]) : [];
                foreach (array_slice($lpCols, 0, 6) as $col):
            ?><th><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $col))); ?></th><?php endforeach; ?></tr></thead>
            <tbody>
            <?php foreach (array_slice($d['landing_pages'], 0, 10) as $lp): ?>
            <tr><?php foreach (array_slice($lpCols, 0, 6) as $col): ?>
                <td><?php echo htmlspecialchars((string)($lp[$col] ?? '—')); ?></td>
            <?php endforeach; ?></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Audiences / Extensions / Budget (if present) -->
    <?php foreach ([
        'audiences'      => ['Audiences', $d['audiences'] ?? []],
        'extensions'     => ['Ad Extensions', $d['extensions'] ?? []],
        'budget_entities'=> ['Budget', $d['budget_entities'] ?? []],
    ] as $key => [$label, $rows]):
        if (empty($rows)) continue;
    ?>
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header" style="padding:14px 20px;border-bottom:1px solid var(--border-light);">
            <div class="card-title" style="font-size:13px;"><?php echo $label; ?> (<?php echo count($rows); ?>)</div>
        </div>
        <div style="overflow-x:auto;">
        <table class="ent-table">
            <thead><tr><?php
                $cols = !empty($rows) ? array_keys($rows[0]) : [];
                foreach (array_slice($cols, 0, 6) as $col):
            ?><th><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $col))); ?></th><?php endforeach; ?></tr></thead>
            <tbody>
            <?php foreach (array_slice($rows, 0, 10) as $row): ?>
            <tr><?php foreach (array_slice($cols, 0, 6) as $col): ?>
                <td><?php echo htmlspecialchars((string)($row[$col] ?? '—')); ?></td>
            <?php endforeach; ?></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($d['devices']) && empty($d['locations']) && empty($d['landing_pages']) && empty($d['audiences']) && empty($d['extensions']) && empty($d['budget_entities'])): ?>
    <div class="card"><div class="card-body" style="text-align:center;padding:48px;color:var(--text-muted);">
        <i data-lucide="database" width="36" style="margin-bottom:12px;color:#cbd5e1;"></i>
        <p>No additional data breakdowns in this report. Upload Devices, Locations, or Landing Page CSVs to unlock this section.</p>
    </div></div>
    <?php endif; ?>
</div>

<!-- ── Report Footer ─────────────────────────────────────────────────── -->
<div style="margin-top:32px;padding-top:20px;border-top:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:12px;color:var(--text-muted);">
    <span>Report generated <?php echo $reportDate; ?> by OCTG Intelligence Engine</span>
    <span>Score: <?php echo $score ?? '--'; ?>/100 &middot; Grade: <?php echo $grade; ?></span>
</div>

<!-- ── JavaScript: tabs + Chart.js ───────────────────────────────────── -->
<script>
// Tab switching
document.querySelectorAll('.report-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.report-tab').forEach(function(t) { t.classList.remove('active'); });
        document.querySelectorAll('.report-panel').forEach(function(p) { p.classList.remove('active'); });
        tab.classList.add('active');
        document.getElementById(tab.dataset.tab).classList.add('active');
        // Re-render any charts in the newly visible tab
        if (window.chartInstances) {
            window.chartInstances.forEach(function(c) { c.resize(); });
        }
    });
});

window.chartInstances = [];

// ── Campaign Comparison Chart ─────────────────────────────────────────
<?php if (!empty($d['chart_campaigns'])): ?>
(function() {
    var ctx = document.getElementById('chartCampaigns');
    if (!ctx) return;
    var chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($d['chart_campaigns']['labels']); ?>,
            datasets: [
                {
                    label: 'Spend ($)',
                    data: <?php echo json_encode($d['chart_campaigns']['spend']); ?>,
                    backgroundColor: 'rgba(59,130,246,0.7)',
                    borderRadius: 6,
                    yAxisID: 'ySpend',
                },
                {
                    label: 'Conversions',
                    data: <?php echo json_encode($d['chart_campaigns']['conversions']); ?>,
                    backgroundColor: 'rgba(16,185,129,0.7)',
                    borderRadius: 6,
                    yAxisID: 'yConv',
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top' } },
            scales: {
                ySpend: { type: 'linear', position: 'left', ticks: { callback: function(v) { return '$' + v.toLocaleString(); } } },
                yConv:  { type: 'linear', position: 'right', grid: { drawOnChartArea: false } }
            }
        }
    });
    window.chartInstances.push(chart);
})();
<?php endif; ?>

// ── Score Radar Chart ─────────────────────────────────────────────────
<?php if (!empty($d['chart_score_categories']['labels'])): ?>
(function() {
    var ctx = document.getElementById('chartScoreRadar');
    if (!ctx) return;
    var chart = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: <?php echo json_encode($d['chart_score_categories']['labels']); ?>,
            datasets: [{
                label: 'Score',
                data: <?php echo json_encode($d['chart_score_categories']['scores']); ?>,
                backgroundColor: 'rgba(59,130,246,0.15)',
                borderColor: '#3b82f6',
                borderWidth: 2,
                pointBackgroundColor: '#3b82f6',
            }]
        },
        options: {
            responsive: true,
            scales: { r: { min: 0, max: 100, ticks: { stepSize: 25 } } },
            plugins: { legend: { display: false } }
        }
    });
    window.chartInstances.push(chart);
})();
<?php endif; ?>

// ── Wasted Spend Bar Chart ────────────────────────────────────────────
<?php if (!empty($d['chart_waste_terms'])): ?>
(function() {
    var ctx = document.getElementById('chartWasteTerms');
    if (!ctx) return;
    var chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($d['chart_waste_terms']['labels']); ?>,
            datasets: [{
                label: 'Wasted Spend ($)',
                data: <?php echo json_encode($d['chart_waste_terms']['spend']); ?>,
                backgroundColor: 'rgba(239,68,68,0.7)',
                borderRadius: 6,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { x: { ticks: { callback: function(v) { return '$' + v; } } } }
        }
    });
    window.chartInstances.push(chart);
})();
<?php endif; ?>

// ── Device Doughnut Chart ─────────────────────────────────────────────
<?php if (!empty($d['chart_devices'])): ?>
(function() {
    var ctx = document.getElementById('chartDevices');
    if (!ctx) return;
    var chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($d['chart_devices']['labels']); ?>,
            datasets: [{
                data: <?php echo json_encode($d['chart_devices']['conversions']); ?>,
                backgroundColor: ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
    window.chartInstances.push(chart);
})();
<?php endif; ?>
</script>

<?php endif; ?>
