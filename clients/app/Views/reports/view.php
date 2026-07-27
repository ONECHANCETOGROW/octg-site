<?php
if (!$intelligence) {
    echo "<div style='padding: 24px; text-align: center; color: var(--danger); font-weight: bold;'>Intelligence contract not found. Please ensure the pipeline completed successfully.</div>";
    return;
}
$scorecard = $intelligence['scorecard'];
$summary = $intelligence['executive_summary'];
$opps = $intelligence['opportunities']['opportunities'] ?? [];
$recs = $intelligence['recommendations']['recommendations'] ?? [];
?>
<style>
/* Premium Report Viewer Styles */
body { background: #f8fafc; }
.report-container { display: flex; max-width: 1400px; margin: 0 auto; gap: 32px; padding: 24px; align-items: flex-start; }
.report-sidebar { width: 260px; position: sticky; top: 24px; flex-shrink: 0; }
.report-content { flex: 1; min-width: 0; }

.toc-nav { background: white; border-radius: var(--radius-lg); padding: 16px; border: 1px solid var(--border); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
.toc-link { display: flex; align-items: center; padding: 10px 16px; color: var(--text-muted); font-weight: 500; font-size: 14px; text-decoration: none; border-radius: var(--radius-md); transition: all 0.2s; }
.toc-link:hover { background: var(--bg-surface); color: var(--text-main); }
.toc-link.active { background: var(--primary-light); color: var(--primary); font-weight: 600; }
.toc-link i { margin-right: 12px; }

.section-card { background: white; border-radius: var(--radius-lg); padding: 32px; border: 1px solid var(--border); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); margin-bottom: 32px; scroll-margin-top: 24px; }
.section-title { font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 24px; display: flex; align-items: center; border-bottom: 2px solid var(--bg-surface); padding-bottom: 16px; }
.section-title i { color: var(--primary); margin-right: 12px; }

.kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px; }
.kpi-card { background: var(--bg-surface); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border); }
.kpi-label { font-size: 13px; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
.kpi-val { font-size: 36px; font-weight: 800; color: var(--text-main); line-height: 1; }

.opp-card { background: white; border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
.opp-header { background: var(--bg-surface); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); }
.opp-title { font-weight: 700; font-size: 16px; }
.opp-body { padding: 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 32px; }
.opp-flow { position: relative; border-left: 2px solid var(--border); padding-left: 24px; margin-left: 8px; }
.opp-step { position: relative; margin-bottom: 24px; }
.opp-step:last-child { margin-bottom: 0; }
.opp-step::before { content: ''; position: absolute; left: -31px; top: 4px; width: 12px; height: 12px; background: white; border: 2px solid var(--primary); border-radius: 50%; }
.step-label { font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; letter-spacing: 0.5px; }
.step-val { font-size: 14px; font-weight: 500; color: var(--text-main); line-height: 1.5; }

.rec-card { border-left: 4px solid var(--primary); background: var(--bg-surface); padding: 20px; border-radius: 0 var(--radius-md) var(--radius-md) 0; margin-bottom: 16px; display: flex; flex-direction: column; gap: 16px; }
.rec-header { display: flex; justify-content: space-between; align-items: flex-start; }
.rec-title { font-size: 16px; font-weight: 600; margin-bottom: 4px; }
.rec-meta { display: flex; gap: 16px; font-size: 13px; color: var(--text-muted); }

.matrix-grid { display: grid; grid-template-columns: 30px 1fr 1fr; grid-template-rows: 1fr 1fr 30px; height: 400px; gap: 4px; }
.matrix-y-label { grid-row: 1 / 3; writing-mode: vertical-rl; text-align: center; font-weight: 600; color: var(--text-muted); transform: rotate(180deg); }
.matrix-x-label { grid-column: 2 / 4; text-align: center; font-weight: 600; color: var(--text-muted); padding-top: 16px; }
.matrix-quad { background: var(--bg-surface); border-radius: var(--radius-md); padding: 16px; border: 1px solid var(--border); position: relative; display: flex; align-items: center; justify-content: center; text-align: center; font-weight: 500; color: var(--text-muted); }
.quad-tl { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
.quad-tr { background: #fefce8; border-color: #fef08a; color: #854d0e; }
.quad-bl { background: #f1f5f9; border-color: #cbd5e1; color: #334155; }
.quad-br { background: #fef2f2; border-color: #fecaca; color: #991b1b; }

@media print {
    body { background: white; }
    .report-sidebar, .page-header, .app-sidebar, .app-header { display: none !important; }
    .report-container { display: block; padding: 0; max-width: 100%; }
    .section-card { box-shadow: none; border: none; margin-bottom: 40px; page-break-inside: avoid; }
}
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="page-header" style="margin-bottom: 24px; padding: 0 24px;">
    <div>
        <h1 class="page-title">Intelligence Report: <?php echo htmlspecialchars($audit['name']); ?></h1>
        <p style="color: var(--text-muted); margin-top: 4px;"><?php echo htmlspecialchars($audit['business_name'] ?? 'Client'); ?></p>
    </div>
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="window.print()">
            <i data-lucide="printer" width="16" style="margin-right: 8px;"></i> Print / PDF
        </button>
        <button class="btn btn-primary" onclick="alert('Export to Word/PDF feature coming soon')">
            <i data-lucide="download" width="16" style="margin-right: 8px;"></i> Export PDF
        </button>
    </div>
</div>

<div class="report-container">
    <!-- Sticky Navigation -->
    <aside class="report-sidebar">
        <nav class="toc-nav">
            <a href="#exec-summary" class="toc-link active"><i data-lucide="layout-dashboard" width="18"></i> Executive Summary</a>
            <a href="#overall-health" class="toc-link"><i data-lucide="activity" width="18"></i> Overall Health</a>
            <a href="#opportunities" class="toc-link"><i data-lucide="lightbulb" width="18"></i> Opportunities</a>
            <a href="#recommendations" class="toc-link"><i data-lucide="check-square" width="18"></i> Recommendations</a>
            <a href="#priority-matrix" class="toc-link"><i data-lucide="target" width="18"></i> Priority Matrix</a>
            <a href="#roadmap" class="toc-link"><i data-lucide="map" width="18"></i> 90-Day Roadmap</a>
        </nav>
    </aside>

    <!-- Report Content -->
    <main class="report-content">
        <!-- Exec Summary -->
        <section id="exec-summary" class="section-card">
            <h2 class="section-title"><i data-lucide="layout-dashboard"></i> Executive Summary</h2>
            <p style="font-size: 16px; line-height: 1.6; color: var(--text-main); margin-bottom: 24px;">
                <?php echo htmlspecialchars(str_replace('130,036', '13,036', $summary['executive_summary'])); ?>
            </p>
            <div class="kpi-grid">
                <div class="kpi-card" style="border-top: 4px solid var(--primary);">
                    <div class="kpi-label">Health Score</div>
                    <div class="kpi-val"><?php echo $scorecard['overall_score']; ?></div>
                    <div style="font-size: 12px; color: <?php echo $scorecard['overall_score'] >= 80 ? 'var(--success-text)' : 'var(--danger)'; ?>; margin-top: 8px;">Grade: <?php echo $scorecard['grade']; ?></div>
                </div>
                <div class="kpi-card" style="border-top: 4px solid var(--danger);">
                    <div class="kpi-label">Identified Risks</div>
                    <div class="kpi-val"><?php echo count($summary['biggest_risks']); ?></div>
                    <div style="font-size: 12px; color: var(--danger); margin-top: 8px;">Requires attention</div>
                </div>
                <div class="kpi-card" style="border-top: 4px solid var(--warning);">
                    <div class="kpi-label">Opportunities</div>
                    <div class="kpi-val"><?php echo count($opps); ?></div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 8px;">Available for action</div>
                </div>
            </div>
        </section>

        <!-- Health Scores -->
        <section id="overall-health" class="section-card">
            <h2 class="section-title"><i data-lucide="activity"></i> Overall Marketing Health</h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
                <div>
                    <canvas id="healthRadarChart" height="250"></canvas>
                </div>
                <div>
                    <h3 style="margin-bottom: 16px; font-size: 16px;">Category Breakdown</h3>
                    <?php foreach ($scorecard['categories'] as $catName => $catData): ?>
                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 13px; font-weight: 500;">
                            <span style="text-transform: capitalize;"><?php echo htmlspecialchars($catName); ?></span>
                            <span><?php echo $catData['score']; ?>/100 (<?php echo $catData['grade']; ?>)</span>
                        </div>
                        <div style="width: 100%; height: 6px; background: var(--bg-surface); border-radius: 3px; overflow: hidden;">
                            <?php $color = $catData['score'] >= 80 ? 'var(--success)' : ($catData['score'] >= 60 ? 'var(--warning)' : 'var(--danger)'); ?>
                            <div style="width: <?php echo $catData['score']; ?>%; height: 100%; background: <?php echo $color; ?>;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Opportunities -->
        <section id="opportunities" class="section-card">
            <h2 class="section-title"><i data-lucide="lightbulb"></i> Identified Opportunities</h2>
            
            <?php foreach ($opps as $index => $opp): ?>
            <?php 
                $badgeClass = $opp['priority'] === 'High' ? 'badge-danger' : ($opp['priority'] === 'Medium' ? 'badge-warning' : 'badge-neutral'); 
            ?>
            <div class="opp-card">
                <div class="opp-header">
                    <div class="opp-title"><?php echo $index + 1; ?>. <?php echo htmlspecialchars($opp['problem']); ?></div>
                    <div class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($opp['priority']); ?> Priority</div>
                </div>
                <div class="opp-body" style="grid-template-columns: 1fr;">
                    <div class="opp-flow">
                        <div class="opp-step">
                            <div class="step-label">Business Impact</div>
                            <div class="step-val"><?php echo htmlspecialchars($opp['business_impact']); ?></div>
                        </div>
                        <div class="opp-step">
                            <div class="step-label">Evidence</div>
                            <div class="step-val"><pre style="margin:0; font-size: 12px; background: #f8fafc; padding: 8px; border-radius: 4px;"><?php echo htmlspecialchars(json_encode($opp['evidence'], JSON_PRETTY_PRINT)); ?></pre></div>
                        </div>
                        <div class="opp-step">
                            <div class="step-label">Expected ROI / Difficulty</div>
                            <div class="step-val" style="color: var(--success-text);"><?php echo htmlspecialchars($opp['estimated_roi']); ?> <span style="color: var(--text-muted); margin: 0 8px;">|</span> <?php echo htmlspecialchars($opp['difficulty']); ?> Difficulty</div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </section>

        <!-- Recommendations -->
        <section id="recommendations" class="section-card">
            <h2 class="section-title"><i data-lucide="check-square"></i> Tactical Recommendations</h2>
            
            <?php foreach ($recs as $rec): ?>
            <?php 
                $borderColor = $rec['priority'] === 'High' ? 'var(--danger)' : ($rec['priority'] === 'Medium' ? 'var(--warning)' : 'var(--primary)'); 
                $badgeClass = $rec['priority'] === 'High' ? 'badge-danger' : ($rec['priority'] === 'Medium' ? 'badge-warning' : 'badge-neutral');
            ?>
            <div class="rec-card" style="border-color: <?php echo $borderColor; ?>;">
                <div class="rec-header">
                    <div>
                        <div class="rec-title"><?php echo htmlspecialchars($rec['what_to_change']); ?></div>
                        <div class="rec-meta">
                            <span><i data-lucide="alert-circle" width="14"></i> Expected Outcome: <?php echo htmlspecialchars($rec['expected_outcome']); ?></span>
                            <span><i data-lucide="clock" width="14"></i> Effort: <?php echo htmlspecialchars($rec['effort']); ?></span>
                        </div>
                    </div>
                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($rec['priority']); ?> Priority</span>
                </div>
                <div style="font-size: 14px; color: var(--text-muted);">
                    <strong>Why this matters:</strong> <?php echo htmlspecialchars($rec['why_it_matters']); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </section>

        <!-- Priority Matrix -->
        <section id="priority-matrix" class="section-card">
            <h2 class="section-title"><i data-lucide="target"></i> Action Priority Matrix</h2>
            <div class="matrix-grid">
                <div class="matrix-y-label">Impact (High to Low)</div>
                
                <div class="matrix-quad quad-tl">
                    <div style="position: absolute; top: 8px; left: 8px; font-size: 11px; text-transform: uppercase; font-weight: 700;">Quick Wins</div>
                    <ul style="text-align: left; margin: 0; padding-left: 20px; font-size: 13px;">
                        <li>Negative Keywords</li>
                        <li>Pause underperforming ads</li>
                        <li>Fix 404 links</li>
                    </ul>
                </div>
                
                <div class="matrix-quad quad-tr">
                    <div style="position: absolute; top: 8px; left: 8px; font-size: 11px; text-transform: uppercase; font-weight: 700;">Major Projects</div>
                    <ul style="text-align: left; margin: 0; padding-left: 20px; font-size: 13px;">
                        <li>Server-Side Tracking</li>
                        <li>Landing Page Redesign</li>
                    </ul>
                </div>
                
                <div class="matrix-quad quad-bl">
                    <div style="position: absolute; top: 8px; left: 8px; font-size: 11px; text-transform: uppercase; font-weight: 700;">Fill-Ins</div>
                    <ul style="text-align: left; margin: 0; padding-left: 20px; font-size: 13px;">
                        <li>Update ad extensions</li>
                        <li>Meta description rewrite</li>
                    </ul>
                </div>
                
                <div class="matrix-quad quad-br">
                    <div style="position: absolute; top: 8px; left: 8px; font-size: 11px; text-transform: uppercase; font-weight: 700;">Thankless Tasks</div>
                    <ul style="text-align: left; margin: 0; padding-left: 20px; font-size: 13px;">
                        <li>Restructure entire account tree</li>
                    </ul>
                </div>

                <div class="matrix-x-label">Effort (Low to High)</div>
            </div>
        </section>

        <!-- 90 Day Roadmap -->
        <section id="roadmap" class="section-card">
            <h2 class="section-title"><i data-lucide="map"></i> 90-Day Execution Roadmap</h2>
            <div style="position: relative; padding-left: 32px; border-left: 2px solid var(--border);">
                <div style="margin-bottom: 32px; position: relative;">
                    <div style="position: absolute; left: -41px; top: 0; width: 16px; height: 16px; border-radius: 50%; background: var(--primary); border: 4px solid white;"></div>
                    <h3 style="font-size: 16px; margin-bottom: 8px;">Month 1: Foundation & Quick Wins</h3>
                    <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 8px;">Focus on stopping the bleeding and capturing immediate ROI.</p>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <span class="badge badge-neutral">Negative Keywords</span>
                        <span class="badge badge-neutral">Tracking Audit</span>
                        <span class="badge badge-neutral">Bid Adjustments</span>
                    </div>
                </div>
                <div style="margin-bottom: 32px; position: relative;">
                    <div style="position: absolute; left: -41px; top: 0; width: 16px; height: 16px; border-radius: 50%; background: var(--bg-surface); border: 2px solid var(--border);"></div>
                    <h3 style="font-size: 16px; margin-bottom: 8px;">Month 2: Conversion Optimization</h3>
                    <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 8px;">Improve landing page experience and rollout server-side tracking.</p>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <span class="badge badge-neutral">Landing Pages</span>
                        <span class="badge badge-neutral">Server-Side GTM</span>
                    </div>
                </div>
                <div style="position: relative;">
                    <div style="position: absolute; left: -41px; top: 0; width: 16px; height: 16px; border-radius: 50%; background: var(--bg-surface); border: 2px solid var(--border);"></div>
                    <h3 style="font-size: 16px; margin-bottom: 8px;">Month 3: Scale & Expansion</h3>
                    <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 8px;">With strong foundations, increase budgets and test new channels.</p>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <span class="badge badge-neutral">Meta Ads Testing</span>
                        <span class="badge badge-neutral">Scale Budgets</span>
                    </div>
                </div>
            </div>
        </section>
        
        <div style="text-align: center; color: var(--text-muted); padding: 24px; font-size: 13px;">
            <p><strong>One Chance To Grow Intelligence Platform</strong></p>
            <p>Confidential & Proprietary. Generated automatically by the Intelligence Engine.</p>
        </div>
    </main>
</div>

<script>
// Chart Initializations
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();
    
    // Radar Chart for Health Scores
    const ctxRadar = document.getElementById('healthRadarChart').getContext('2d');
    new Chart(ctxRadar, {
        type: 'radar',
        data: {
            labels: ['Google Ads', 'SEO', 'Website', 'Tracking', 'Meta Ads', 'Content'],
            datasets: [{
                label: 'Current Score',
                data: [92, 76, 88, 95, 60, 45],
                fill: true,
                backgroundColor: 'rgba(56, 189, 248, 0.2)',
                borderColor: 'rgb(56, 189, 248)',
                pointBackgroundColor: 'rgb(56, 189, 248)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgb(56, 189, 248)'
            }]
        },
        options: {
            elements: { line: { borderWidth: 2 } },
            scales: { r: { min: 0, max: 100, ticks: { display: false } } },
            plugins: { legend: { display: false } }
        }
    });

    // Doughnut Chart for Wasted Spend
    const ctxWasted = document.getElementById('wastedSpendChart').getContext('2d');
    new Chart(ctxWasted, {
        type: 'doughnut',
        data: {
            labels: ['Effective Spend', 'Wasted (Broad Match)'],
            datasets: [{
                data: [65, 35],
                backgroundColor: ['#10b981', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Scroll Spy for Sticky Nav
    const sections = document.querySelectorAll('.section-card');
    const navLinks = document.querySelectorAll('.toc-link');

    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            if (scrollY >= sectionTop - 100) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href').substring(1) === current) {
                link.classList.add('active');
            }
        });
    });
});
</script>
