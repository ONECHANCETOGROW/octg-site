<?php
require_once BASE_PATH . '/app/Core/PlatformIconHelper.php';
use App\Core\PlatformIconHelper;

$slug = $client['slug'] ?? '';
?>

<!-- Dashboard Header -->
<div class="page-header" style="margin-bottom:24px;">
    <div>
        <h1 class="page-title" style="font-size:28px; font-weight:800; letter-spacing:-0.03em;">Business Command Center</h1>
        <p style="color: var(--text-muted); margin-top: 4px; font-size:14px;">Real-time business performance snapshot for <strong><?php echo date('F Y', strtotime($period)); ?></strong></p>
    </div>
</div>

<?php if (!empty($latestReportExecSummary) && !empty($latestReportMeta)): ?>
<?php
    $rMeta   = $latestReportMeta;
    $rExec   = $latestReportExecSummary;
    $rScore  = (int)($rMeta['score'] ?? 0);
    $rScoreColor = match(true) {
        $rScore >= 90 => '#10b981',
        $rScore >= 80 => '#3b82f6',
        $rScore >= 70 => '#f59e0b',
        default       => '#ef4444',
    };
    $rDate = $rMeta['date'] ? date('F j, Y', strtotime((string)$rMeta['date'])) : '';
?>
<!-- ── Intelligence Report Banner ─────────────────────────────────── -->
<div style="background:linear-gradient(135deg,#0f172a 0%,#1e293b 60%,#0f2044 100%);border-radius:16px;padding:28px 32px;margin-bottom:28px;color:#fff;position:relative;overflow:hidden;">
    <div style="position:absolute;top:-40px;right:-40px;width:200px;height:200px;background:rgba(59,130,246,.08);border-radius:50%;"></div>
    <div style="position:absolute;bottom:-60px;right:80px;width:140px;height:140px;background:rgba(16,185,129,.06);border-radius:50%;"></div>
    <div style="position:relative;z-index:1;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:20px;margin-bottom:20px;">
            <div>
                <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#94a3b8;margin-bottom:8px;">
                    <i data-lucide="cpu" width="12" style="vertical-align:middle;margin-right:4px;"></i> AI Intelligence Report
                    <?php if ($rDate): ?>&nbsp;·&nbsp; <?php echo $rDate; ?><?php endif; ?>
                </div>
                <p style="font-size:15px;line-height:1.75;color:#e2e8f0;margin:0;max-width:680px;"><?php echo htmlspecialchars($rExec['executive_summary']); ?></p>
            </div>
            <div style="text-align:center;flex-shrink:0;">
                <div style="font-size:48px;font-weight:900;letter-spacing:-.04em;color:<?php echo $rScoreColor; ?>;line-height:1;"><?php echo $rScore; ?></div>
                <div style="font-size:12px;font-weight:700;color:#64748b;">/100 &nbsp;·&nbsp; <?php echo htmlspecialchars($rMeta['grade'] ?? ''); ?></div>
                <div style="font-size:11px;font-weight:600;color:<?php echo $rScoreColor; ?>;margin-top:4px;"><?php echo htmlspecialchars($rMeta['health'] ?? ''); ?></div>
            </div>
        </div>
        <div style="display:flex;gap:20px;flex-wrap:wrap;margin-bottom:20px;">
            <?php if (!empty($rExec['biggest_wins'][0])): ?>
            <div style="background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.25);border-radius:10px;padding:12px 16px;flex:1;min-width:200px;">
                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#10b981;margin-bottom:6px;">
                    <i data-lucide="trending-up" width="11" style="vertical-align:middle;margin-right:3px;"></i> Biggest Win
                </div>
                <div style="font-size:13px;color:#d1fae5;"><?php echo htmlspecialchars($rExec['biggest_wins'][0]); ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($rExec['biggest_risks'][0])): ?>
            <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:12px 16px;flex:1;min-width:200px;">
                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#f87171;margin-bottom:6px;">
                    <i data-lucide="alert-triangle" width="11" style="vertical-align:middle;margin-right:3px;"></i> Key Risk
                </div>
                <div style="font-size:13px;color:#fecaca;"><?php echo htmlspecialchars($rExec['biggest_risks'][0]); ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($rExec['immediate_actions'][0])): ?>
            <div style="background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.2);border-radius:10px;padding:12px 16px;flex:1;min-width:200px;">
                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#fbbf24;margin-bottom:6px;">
                    <i data-lucide="zap" width="11" style="vertical-align:middle;margin-right:3px;"></i> #1 Action
                </div>
                <div style="font-size:13px;color:#fde68a;"><?php echo htmlspecialchars($rExec['immediate_actions'][0]); ?></div>
            </div>
            <?php endif; ?>
        </div>
        <a href="<?php echo htmlspecialchars($rMeta['report_url']); ?>" style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);color:#fff;text-decoration:none;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:600;transition:background .15s;">
            <i data-lucide="file-bar-chart-2" width="15"></i>
            View Full Intelligence Report →
        </a>
    </div>
</div>
<?php endif; ?>


<div style="display: grid; grid-template-columns: 1.2fr 1.8fr; gap: 24px; margin-bottom: 24px; align-items: start; flex-wrap: wrap;">
    
    <!-- Health Score Card -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-body" style="padding: 28px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <span style="font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--primary-light);">Overall Marketing Health</span>
                <?php if ($overallTrend): ?>
                    <span class="trend-badge <?php echo strpos($overallTrend, '+') !== false ? 'trend-up' : 'trend-down'; ?>" style="font-size: 11px; padding: 4px 8px; border-radius: 20px; font-weight: 700;">
                        <?php echo $overallTrend; ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <div style="display: flex; align-items: baseline; gap: 8px; margin-bottom: 12px;">
                <span style="font-size: 54px; font-weight: 800; line-height: 1; letter-spacing: -0.04em; background: linear-gradient(135deg, #ffffff, #94a3b8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <?php echo $overallScore !== null ? $overallScore : '--'; ?>
                </span>
                <span style="font-size: 20px; color: #64748b; font-weight: 600;">/100</span>
            </div>

            <div style="margin-bottom: 20px;">
                <span class="badge" style="background:<?php 
                    echo $overallStatus === 'Excellent' ? '#10b981' : ($overallStatus === 'Good' ? '#3b82f6' : ($overallStatus === 'Needs Attention' ? '#f59e0b' : '#ef4444')); 
                ?>; color:white; font-weight:700; font-size:11px; text-transform:uppercase; padding: 4px 10px; border-radius:12px;">
                    <?php echo $overallStatus; ?>
                </span>
            </div>

            <div style="border-top: 1px solid var(--portal-border); padding-top: 16px; font-size: 13px; display: flex; flex-direction: column; gap: 10px; color: var(--portal-text-muted);">
                <div>
                    <strong style="color:var(--success); display:block; font-size:11px; text-transform:uppercase; margin-bottom:2px;">Biggest Win</strong>
                    <?php echo htmlspecialchars($overallWin); ?>
                </div>
                <div>
                    <strong style="color:var(--danger); display:block; font-size:11px; text-transform:uppercase; margin-bottom:2px;">Biggest Risk</strong>
                    <?php echo htmlspecialchars($overallRisk); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Summary Note -->
    <div class="card" style="margin-bottom: 0; min-height: 254px; display: flex; flex-direction: column;">
        <div class="card-header" style="border-bottom: 1px solid var(--portal-border); padding: 18px 24px;">
            <div class="card-title" style="font-size: 15px; font-weight: 700; display:flex; align-items:center; gap:8px;">
                <i data-lucide="sparkles" width="18" style="color:var(--primary);"></i> This Month's Executive Summary
            </div>
        </div>
        <div class="card-body" style="padding: 24px; flex: 1; display:flex; flex-direction:column; gap:16px;">
            <?php if (empty($monthlyNote) && empty($monthlyGoal)): ?>
                <div style="color:var(--text-muted); font-size: 13px; font-style:italic; text-align:center; padding: 40px 0;">
                    Your account manager is currently preparing this month's custom report summary.
                </div>
            <?php else: ?>
                <?php if ($monthlyNote): ?>
                    <div>
                        <strong style="font-size:12px; color:var(--text-muted); display:block; text-transform:uppercase; margin-bottom:6px; letter-spacing:0.02em;">Account Manager Notes</strong>
                        <p style="font-size: 14px; line-height: 1.6; color: var(--text-main); font-weight: 500;"><?php echo nl2br(htmlspecialchars($monthlyNote)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($monthlyGoal): ?>
                    <div style="border-top:1px dashed var(--border); padding-top:14px;">
                        <strong style="font-size:12px; color:var(--text-muted); display:block; text-transform:uppercase; margin-bottom:6px; letter-spacing:0.02em;">Month Focus & Goals</strong>
                        <p style="font-size: 13px; line-height: 1.5; color: var(--text-main);"><?php echo nl2br(htmlspecialchars($monthlyGoal)); ?></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Active Modules KPI Cards -->
<?php
// FOCUS MODE: while $portalFocusModeGoogleAdsOnly is true (set in
// app/Views/portal/layouts/main.php), only the Google Ads card is shown
// here. $dashboardModules itself is untouched -- the controller still
// computes every module's data -- this is purely a display filter, so
// flipping that one flag back to false restores the full grid instantly.
$visibleDashboardModules = (!empty($portalFocusModeGoogleAdsOnly))
    ? array_intersect_key($dashboardModules, ['google_ads' => true])
    : $dashboardModules;
?>
<h3 style="font-size: 16px; font-weight: 700; color:var(--portal-text-main); margin-bottom: 16px;">Marketing Channels</h3>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 32px;">
    <?php foreach ($visibleDashboardModules as $code => $mod): ?>
        <div class="card card-hover" style="margin-bottom: 0; display:flex; flex-direction:column; justify-content:space-between;">
            <div class="card-body" style="padding: 20px;">
                <!-- Header -->
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="display:inline-flex; padding:8px; border-radius:8px; background:var(--primary-light); color:var(--primary);">
                            <?php echo PlatformIconHelper::getSvg($code === 'website_performance' ? 'website' : $code, 'width="18" height="18"'); ?>
                        </span>
                        <span style="font-weight:700; font-size:14px; color:var(--portal-text-main);"><?php echo htmlspecialchars($mod['name']); ?></span>
                    </div>
                    <?php if ($mod['score'] !== null): ?>
                        <div style="text-align:right;">
                            <span style="font-size: 20px; font-weight: 800; color:var(--text-main);"><?php echo $mod['score']; ?></span>
                            <span style="font-size: 11px; color:var(--text-muted); font-weight: 600;">/100</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Score Trend/Status -->
                <?php if ($mod['score'] !== null): ?>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; font-size:11px;">
                        <span style="font-weight:700; text-transform:uppercase; color:<?php 
                            echo $mod['health_status'] === 'Excellent' || $mod['health_status'] === 'Good' ? 'var(--success-text)' : 'var(--danger-text)'; 
                        ?>;"><?php echo $mod['health_status']; ?></span>
                        
                        <?php if ($mod['trend']): ?>
                            <span style="font-weight:600; color:<?php echo strpos($mod['trend'], '+') !== false ? 'var(--success-text)' : 'var(--danger-text)'; ?>;">
                                <?php echo $mod['trend']; ?> vs prev
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Raw KPIs list -->
                <div style="display:flex; flex-direction:column; gap:8px; border-top:1px solid var(--portal-border); padding-top:12px; margin-bottom:16px;">
                    <?php if ($code === 'google_ads'): ?>
                        <div style="display:flex; justify-content:space-between; font-size:12px;">
                            <span style="color:var(--text-muted);">Spend</span>
                            <strong style="color:var(--text-main);">$<?php echo number_format((float)($mod['metrics']['spend'] ?? 0), 2); ?></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:12px;">
                            <span style="color:var(--text-muted);">Conversions</span>
                            <strong style="color:var(--text-main);"><?php echo number_format((float)($mod['metrics']['conversions'] ?? 0)); ?></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:12px;">
                            <span style="color:var(--text-muted);">Cost Per Conversion</span>
                            <strong style="color:var(--text-main);">$<?php echo number_format((float)($mod['metrics']['cpa'] ?? 0), 2); ?></strong>
                        </div>
                    <?php elseif ($code === 'seo'): ?>
                        <div style="display:flex; justify-content:space-between; font-size:12px;">
                            <span style="color:var(--text-muted);">Domain Authority</span>
                            <strong style="color:var(--text-main);"><?php echo (int)($mod['metrics']['authority_score'] ?? 0); ?> /100</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:12px;">
                            <span style="color:var(--text-muted);">Total Backlinks</span>
                            <strong style="color:var(--text-main);"><?php echo number_format((float)($mod['metrics']['backlinks_count'] ?? 0)); ?></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:12px;">
                            <span style="color:var(--text-muted);">Pages Indexed</span>
                            <strong style="color:var(--text-main);"><?php echo number_format((float)($mod['metrics']['pages_indexed'] ?? 0)); ?></strong>
                        </div>
                    <?php elseif ($code === 'gbp'): ?>
                        <div style="display:flex; justify-content:space-between; font-size:12px;">
                            <span style="color:var(--text-muted);">Phone Calls</span>
                            <strong style="color:var(--text-main);"><?php echo number_format((float)($mod['metrics']['calls'] ?? 0)); ?></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:12px;">
                            <span style="color:var(--text-muted);">Directions Requested</span>
                            <strong style="color:var(--text-main);"><?php echo number_format((float)($mod['metrics']['direction_requests'] ?? 0)); ?></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:12px;">
                            <span style="color:var(--text-muted);">New Reviews</span>
                            <strong style="color:var(--text-main);"><?php echo number_format((float)($mod['metrics']['reviews'] ?? 0)); ?> (★<?php echo number_format((float)($mod['metrics']['average_rating'] ?? 0.0), 1); ?>)</strong>
                        </div>
                    <?php elseif ($code === 'social'): ?>
                        <div style="display:flex; justify-content:space-between; font-size:12px;">
                            <span style="color:var(--text-muted);">Reach</span>
                            <strong style="color:var(--text-main);"><?php echo number_format((float)($mod['metrics']['reach'] ?? 0)); ?></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:12px;">
                            <span style="color:var(--text-muted);">Engagement</span>
                            <strong style="color:var(--text-main);"><?php echo number_format((float)($mod['metrics']['engagement'] ?? 0)); ?></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:12px;">
                            <span style="color:var(--text-muted);">Total Followers</span>
                            <strong style="color:var(--text-main);"><?php echo number_format((float)($mod['metrics']['followers'] ?? 0)); ?></strong>
                        </div>
                    <?php elseif ($code === 'website_performance'): ?>
                        <div style="display:flex; justify-content:space-between; font-size:12px;">
                            <span style="color:var(--text-muted);">Visitors</span>
                            <strong style="color:var(--text-main);"><?php echo number_format((float)($mod['metrics']['visitors'] ?? 0)); ?></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:12px;">
                            <span style="color:var(--text-muted);">Form Submissions</span>
                            <strong style="color:var(--text-main);"><?php echo number_format((float)($mod['metrics']['form_submissions'] ?? 0)); ?></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:12px;">
                            <span style="color:var(--text-muted);">Phone Calls</span>
                            <strong style="color:var(--text-main);"><?php echo number_format((float)($mod['metrics']['phone_calls'] ?? 0)); ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Footer CTA -->
            <div style="padding: 0 20px 20px 20px;">
                <a href="/client/<?php echo $slug; ?>/<?php echo str_replace('_', '-', $code); ?>" style="font-size:12px; font-weight:600; color:var(--primary); text-decoration:none; display:flex; align-items:center; gap:4px;">
                    View Details <i data-lucide="arrow-right" width="12"></i>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Recommendations & Open Tasks Section -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-light); padding: 18px 24px;">
        <div class="card-title" style="font-size: 15px; font-weight: 700; display:flex; align-items:center; gap:8px;">
            <i data-lucide="list-checks" width="18" style="color:var(--primary);"></i> Recommended Action Items
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php 
        $openRecs = array_filter($recommendations, fn($r) => $r['status'] === 'open' || $r['status'] === 'in_progress');
        if (empty($openRecs)): 
        ?>
            <div style="padding:32px; text-align:center; color:var(--text-muted); font-size:13px;">
                All actions completed! Your accounts are in optimal condition.
            </div>
        <?php else: ?>
            <div style="display:flex; flex-direction:column;">
                <?php foreach (array_slice($openRecs, 0, 5) as $rec): ?>
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 24px; border-bottom:1px solid var(--border-light); gap:20px;">
                        <div>
                            <span class="badge badge-<?php echo strtolower($rec['priority']) === 'high' ? 'danger' : (strtolower($rec['priority']) === 'medium' ? 'warning' : 'neutral'); ?>" style="font-size:10px; margin-right:8px; font-weight:700;">
                                <?php echo $rec['priority']; ?>
                            </span>
                            <span style="font-weight:600; font-size:13px; color:var(--text-main);"><?php echo htmlspecialchars($rec['what_to_change']); ?></span>
                            <?php if ($rec['why_it_matters']): ?>
                                <p style="font-size:11px; color:var(--text-muted); margin-top:4px; margin-left:52px;"><?php echo htmlspecialchars($rec['why_it_matters']); ?></p>
                            <?php endif; ?>
                        </div>
                        <span style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--primary); background:var(--primary-light); padding:4px 8px; border-radius:4px;">
                            <?php echo htmlspecialchars($rec['module_name']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="padding: 16px 24px; border-top:1px solid var(--border-light); text-align:center;">
                <a href="/client/<?php echo $slug; ?>/recommendations" style="font-size:12px; font-weight:600; color:var(--primary); text-decoration:none;">
                    View All Recommendations →
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
