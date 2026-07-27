<?php
$moduleNames = [
    'google_ads' => 'Google Ads',
    'seo' => 'Website SEO',
    'gbp' => 'Google Business Profile',
    'social' => 'Social Media',
    'website_performance' => 'Website Performance',
];
?>
<div class="page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <h1 class="page-title">Marketing Workspace</h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Operational center for <strong><?php echo htmlspecialchars($client['business_name']); ?></strong></p>
    </div>
    
    <!-- Month Snapshot Selector -->
    <div style="display:flex; align-items:center; gap:8px;">
        <label for="period-select" style="font-weight:600; font-size:13px; color:var(--text-muted);">Snapshot Month:</label>
        <select id="period-select" class="form-control" style="width:160px; padding:6px 10px; border-radius:6px; border:1px solid var(--border);" onchange="window.location.href='/clients/portal-data?id=<?php echo $client['id']; ?>&period=' + this.value">
            <?php
            $currentMonth = date('Y-m-01');
            for ($i = -6; $i <= 3; $i++) {
                $m = date('Y-m-01', strtotime("$currentMonth +$i month"));
                $selected = ($m === $period) ? 'selected' : '';
                echo '<option value="' . $m . '" ' . $selected . '>' . date('F Y', strtotime($m)) . '</option>';
            }
            ?>
        </select>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="card" style="border-left:4px solid var(--success); margin-bottom:20px; background:#f0fdf4;"><div class="card-body" style="padding:12px 20px; color:var(--success-text); font-weight:500;">Workspace updated successfully.</div></div>
<?php endif; ?>

<!-- Tabs Navigation -->
<div style="display:flex; border-bottom:1px solid var(--border); margin-bottom:20px; gap:8px; overflow-x:auto;">
    <button class="tab-btn active" onclick="switchTab('scores')" id="tab-btn-scores" style="padding:10px 16px; background:none; border:none; border-bottom:2px solid var(--primary); font-weight:600; color:var(--primary); cursor:pointer;">Scores & Health</button>
    <button class="tab-btn" onclick="switchTab('metrics')" id="tab-btn-metrics" style="padding:10px 16px; background:none; border:none; border-bottom:2px solid transparent; font-weight:500; color:var(--text-muted); cursor:pointer;">AI Ingestion</button>
    <button class="tab-btn" onclick="switchTab('recs')" id="tab-btn-recs" style="padding:10px 16px; background:none; border:none; border-bottom:2px solid transparent; font-weight:500; color:var(--text-muted); cursor:pointer;">Recommendations</button>
    <button class="tab-btn" onclick="switchTab('timeline')" id="tab-btn-timeline" style="padding:10px 16px; background:none; border:none; border-bottom:2px solid transparent; font-weight:500; color:var(--text-muted); cursor:pointer;">Business Timeline</button>
    <button class="tab-btn" onclick="switchTab('notes')" id="tab-btn-notes" style="padding:10px 16px; background:none; border:none; border-bottom:2px solid transparent; font-weight:500; color:var(--text-muted); cursor:pointer;">Notes & Goals</button>
</div>

<!-- Tab Content: Scores & Health -->
<div class="tab-content" id="tab-scores">
    <form method="POST" action="/clients/portal-data/save-scores">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
        <input type="hidden" name="period" value="<?php echo $period; ?>">

        <!-- Overall Marketing Health Summary Card -->
        <div class="card" style="background:#f8fafc; border:1px solid #cbd5e1; margin-bottom:24px;">
            <div class="card-body">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h3 style="font-size:16px; font-weight:700; color:#1e293b;">Overall Marketing Health Score</h3>
                        <p style="font-size:12px; color:var(--text-muted); margin-top:2px;">Calculated automatically as the average of active scores below.</p>
                    </div>
                    <div style="text-align:right;">
                        <span style="font-size:32px; font-weight:800; color:var(--primary);"><?php echo $overallScoreRow ? $overallScoreRow['score'] : '--'; ?></span>
                        <span style="font-size:16px; color:var(--text-muted); font-weight:600;">/100</span>
                        <?php if ($overallScoreRow && $overallScoreRow['health_status']): ?>
                            <div style="font-size:11px; font-weight:700; color:var(--success-text); margin-top:4px;"><?php echo $overallScoreRow['health_status']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="margin-top:16px; display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px;">
                    <div>
                        <label style="font-weight:600; font-size:12px; color:#475569; display:block; margin-bottom:6px;">Overall Biggest Win</label>
                        <input type="text" name="overall_biggest_win" class="form-control" style="width:100%;" value="<?php echo htmlspecialchars($overallScoreRow['biggest_win'] ?? ''); ?>" placeholder="e.g. Google Ads conversions increased by 15%">
                    </div>
                    <div>
                        <label style="font-weight:600; font-size:12px; color:#475569; display:block; margin-bottom:6px;">Overall Biggest Risk</label>
                        <input type="text" name="overall_biggest_risk" class="form-control" style="width:100%;" value="<?php echo htmlspecialchars($overallScoreRow['biggest_risk'] ?? ''); ?>" placeholder="e.g. Lead cost slightly higher due to seasonal keywords">
                    </div>
                    <div>
                        <label style="font-weight:600; font-size:12px; color:#475569; display:block; margin-bottom:6px;">Overall Priority Actions</label>
                        <input type="text" name="overall_priority" class="form-control" style="width:100%;" value="<?php echo htmlspecialchars($overallScoreRow['priority_this_month'] ?? ''); ?>" placeholder="e.g. Implement conversion tracking refinements">
                    </div>
                </div>
            </div>
        </div>

        <!-- Individual Module Scores -->
        <h3 style="font-size:15px; font-weight:700; margin-bottom:12px; color:#334155;">Module Scores (0-100)</h3>
        <div style="display:flex; flex-direction:column; gap:16px; margin-bottom:24px;">
            <?php foreach ($modules as $mod): 
                if ($mod['slug'] === 'marketing_health') continue;
                $slug = $mod['slug'];
                $sData = $scoresData[$slug] ?? null;
            ?>
                <div class="card" style="margin-bottom:0;">
                    <div class="card-body" style="padding:16px 20px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap;">
                            <div style="display:flex; align-items:center; gap:12px; min-width:200px;">
                                <i data-lucide="<?php echo $mod['icon']; ?>" style="color:var(--primary);" width="20"></i>
                                <span style="font-weight:600; font-size:14px; color:#1e293b;"><?php echo htmlspecialchars($mod['name']); ?></span>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <label style="font-size:12px; font-weight:600; color:#475569;">Score (0-100):</label>
                                <input type="number" name="scores[<?php echo $slug; ?>][score]" min="0" max="100" class="form-control" style="width:80px; text-align:center;" value="<?php echo $sData ? $sData['score'] : ''; ?>" placeholder="--">
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <label style="font-size:12px; font-weight:600; color:#475569;">Trend:</label>
                                <input type="text" name="scores[<?php echo $slug; ?>][trend]" class="form-control" style="width:120px;" value="<?php echo htmlspecialchars($sData['trend'] ?? ''); ?>" placeholder="e.g. +5% vs last month">
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <label style="font-size:12px; font-weight:600; color:#475569;">Status:</label>
                                <select name="scores[<?php echo $slug; ?>][health_status]" class="form-control" style="width:140px;">
                                    <option value="">-- Choose --</option>
                                    <option value="Excellent" <?php echo ($sData['health_status'] ?? '') === 'Excellent' ? 'selected' : ''; ?>>Excellent</option>
                                    <option value="Good" <?php echo ($sData['health_status'] ?? '') === 'Good' ? 'selected' : ''; ?>>Good</option>
                                    <option value="Needs Attention" <?php echo ($sData['health_status'] ?? '') === 'Needs Attention' ? 'selected' : ''; ?>>Needs Attention</option>
                                    <option value="Critical" <?php echo ($sData['health_status'] ?? '') === 'Critical' ? 'selected' : ''; ?>>Critical</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-primary" style="padding:10px 20px;">Save Module Scores & Recalculate Health</button>
    </form>
</div>

<!-- Tab Content: AI Ingestion -->
<div class="tab-content" id="tab-metrics" style="display:none;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <p style="font-size:13px; color:var(--text-muted); margin:0;">Use AI to automatically ingest metrics, extract tables, and generate recommendations. Paste the AI's JSON output below.</p>
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('manual-fallback-container').style.display = document.getElementById('manual-fallback-container').style.display === 'none' ? 'block' : 'none';">Toggle Manual Fallback</button>
    </div>
    
    <!-- Step 0: Prime the AI Advisor -->
    <div class="card" style="border:2px solid #f59e0b; margin-bottom:24px; background:#fffbeb;">
        <div class="card-header" style="background:#fef3c7;">
            <div style="display:flex; align-items:center; gap:8px;">
                <i data-lucide="graduation-cap" width="18" style="color:#b45309;"></i>
                <span style="font-weight:700; font-size:14px; color:#78350f;">Step 0 — Prime Your AI Advisor First</span>
            </div>
        </div>
        <div class="card-body">
            <p style="font-size:12px; color:#78350f; margin:0 0 12px 0;">
                Paste these 3 prompts into your AI advisor (Google Ads Advisor, ChatGPT, Claude, or Gemini) <strong>in order, before</strong> any of the questions below. They set the account-analyst role, lock in the exact JSON-only format with a worked example, and confirm what data the advisor is actually working from — so the real questions below come back as clean, parseable JSON instead of prose the advisor improvises on the first try.
            </p>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:12px;">
                <?php
                $primingMap = [
                    'priming_1' => ['label' => '1. Role & Scope', 'desc' => 'Sets the conversation context.'],
                    'priming_2' => ['label' => '2. Format Contract', 'desc' => 'Locks in JSON-only, with an example.'],
                    'priming_3' => ['label' => '3. Data Source Check', 'desc' => 'Confirms real data vs. guessing.'],
                ];
                foreach ($primingMap as $pKey => $pInfo):
                ?>
                    <div style="border:1px solid #fde68a; padding:10px; border-radius:6px; background:#fff;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                            <label style="font-size:12px; font-weight:700; color:#78350f; margin:0;"><?php echo htmlspecialchars($pInfo['label']); ?></label>
                            <button type="button" class="btn btn-secondary" style="font-size:10px; padding:2px 6px;" onclick="copyPrompt('priming', '<?php echo $pKey; ?>')">Copy Prompt</button>
                        </div>
                        <div style="font-size:11px; color:#92400e;"><?php echo htmlspecialchars($pInfo['desc']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(400px, 1fr)); gap:24px; margin-bottom:32px;">
        <?php foreach ($modules as $mod):
            if ($mod['slug'] === 'marketing_health') continue;
            $slug = $mod['slug'];

            // Only show AI interface for Google Ads right now, or all modules? User wants a reusable framework.
            $promptMap = [
                'kpis' => 'Performance KPIs',
                'campaigns' => 'Campaign Table',
                'keywords' => 'Keywords',
                'search_terms' => 'Search Terms',
                'recommendations' => 'Recommendations',
                'opportunities' => 'Opportunities',
                'executive_summary' => 'Executive Summary'
            ];
        ?>
            <div class="card" style="border:2px solid var(--primary-light);">
                <div class="card-header" style="background:#f8fafc;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <i data-lucide="<?php echo $mod['icon']; ?>" width="18" style="color:var(--primary);"></i>
                        <span style="font-weight:700; font-size:14px;"><?php echo htmlspecialchars($mod['name']); ?> - AI Import</span>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="/clients/portal-data/ingest-ai">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                        <input type="hidden" name="period" value="<?php echo $period; ?>">
                        <input type="hidden" name="module" value="<?php echo $slug; ?>">
                        
                        <div style="display:flex; flex-direction:column; gap:16px; margin-bottom:16px;">
                            <?php foreach($promptMap as $key => $label): ?>
                                <div style="border:1px solid var(--border-light); padding:12px; border-radius:6px; background:#fafafa;">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                        <label style="font-size:12px; font-weight:700; color:#334155; margin:0;"><?php echo $label; ?></label>
                                        <button type="button" class="btn btn-secondary" style="font-size:10px; padding:2px 6px;" onclick="copyPrompt('<?php echo $slug; ?>', '<?php echo $key; ?>')">Copy Prompt</button>
                                    </div>
                                    <textarea name="<?php echo $key; ?>" class="form-control" rows="2" style="width:100%; font-family:monospace; font-size:11px;" placeholder="Paste the AI response from Google Ads Advisor, ChatGPT, Claude, or Gemini here..."></textarea>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width:100%; padding:12px;">Process AI Responses for <?php echo htmlspecialchars($mod['name']); ?></button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Manual Fallback Container -->
    <div id="manual-fallback-container" style="display:none; border-top:2px dashed var(--border); padding-top:24px;">
        <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--danger-text);">Manual Override (Fallback Only)</h3>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:24px;">
            <?php foreach ($modules as $mod): 
                if ($mod['slug'] === 'marketing_health') continue;
                $slug = $mod['slug'];
                $fields = \App\Modules\ClientPortal\ModuleFieldDefinitions::forModule($slug);
                if (empty($fields)) continue;
            ?>
                <div class="card" style="opacity:0.8;">
                    <div class="card-header" style="background:#f1f5f9;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-weight:700; font-size:14px;"><?php echo htmlspecialchars($mod['name']); ?> (Manual)</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($slug === 'social'): ?>
                            <!-- Social platform selector/tabs -->
                            <?php 
                            $platforms = \App\Modules\ClientPortal\ModuleFieldDefinitions::socialPlatforms(); 
                            foreach ($platforms as $platCode => $platLabel):
                                $pData = $metricsData['social'][$platCode] ?? [];
                            ?>
                                <form method="POST" action="/clients/portal-data/save-metrics" style="margin-bottom:24px; border-bottom:1px dashed var(--border); padding-bottom:16px;">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                    <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                                    <input type="hidden" name="period" value="<?php echo $period; ?>">
                                    <input type="hidden" name="module" value="social">
                                    <input type="hidden" name="platform" value="<?php echo $platCode; ?>">
                                    
                                    <h4 style="font-size:12px; font-weight:700; color:var(--primary); margin-bottom:12px;"><?php echo $platLabel; ?> Platform Data</h4>
                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                        <?php foreach ($fields as $field): ?>
                                            <div>
                                                <label style="font-size:11px; font-weight:600; color:#475569; display:block; margin-bottom:4px;"><?php echo $field['label']; ?></label>
                                                <input type="text" name="<?php echo $field['key']; ?>" class="form-control" style="width:100%;" value="<?php echo htmlspecialchars((string)($pData[$field['key']] ?? '')); ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button type="submit" class="btn btn-secondary" style="margin-top:10px; width:100%; font-size:11px; padding:4px 8px;">Save <?php echo $platLabel; ?></button>
                                </form>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Standard Modules Form -->
                            <form method="POST" action="/clients/portal-data/save-metrics">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                                <input type="hidden" name="period" value="<?php echo $period; ?>">
                                <input type="hidden" name="module" value="<?php echo $slug; ?>">
                                <input type="hidden" name="platform" value="">
                                
                                <div style="display:grid; grid-template-columns:1fr; gap:12px; margin-bottom:16px;">
                                    <?php foreach ($fields as $field): ?>
                                        <div>
                                            <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;"><?php echo $field['label']; ?></label>
                                            <?php if ($field['type'] === 'textarea'): ?>
                                                <textarea name="<?php echo $field['key']; ?>" class="form-control" rows="3" style="width:100%; font-family:monospace;"><?php echo htmlspecialchars(implode("\n", (array)($metricsData[$slug][$field['key']] ?? []))); ?></textarea>
                                            <?php else: ?>
                                                <input type="text" name="<?php echo $field['key']; ?>" class="form-control" style="width:100%;" value="<?php echo htmlspecialchars((string)($metricsData[$slug][$field['key']] ?? '')); ?>">
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="submit" class="btn btn-secondary" style="width:100%;">Save <?php echo htmlspecialchars($mod['name']); ?> (Manual)</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
const prompts = <?php echo json_encode($prompts ?? []); ?>;

async function copyPrompt(module, section) {
    const fullPrompt = prompts[section] || 'Prompt not found.';
    
    try {
        await navigator.clipboard.writeText(fullPrompt);
        alert(`Copied optimized prompt for ${section}!`);
    } catch (err) {
        alert('Failed to copy. Please allow clipboard access.');
    }
}
</script>

<!-- Tab Content: Recommendations -->
<div class="tab-content" id="tab-recs" style="display:none;">
    <div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
        <!-- Left: List of Recommendations -->
        <div>
            <h3 style="font-size:15px; font-weight:700; margin-bottom:12px; color:#334155;">Active Recommendations</h3>
            <div class="card">
                <div class="card-body" style="padding:0;">
                    <?php if (empty($recommendations)): ?>
                        <div style="padding:32px; text-align:center; color:var(--text-muted);">No recommendations entered yet for this client.</div>
                    <?php else: ?>
                        <table style="width:100%; border-collapse:collapse; font-size:13px;">
                            <thead>
                                <tr style="background:#f8fafc; border-bottom:1px solid var(--border); text-align:left;">
                                    <th style="padding:10px 16px; font-weight:600;">Module</th>
                                    <th style="padding:10px 16px; font-weight:600;">What to Change</th>
                                    <th style="padding:10px 16px; font-weight:600;">Priority</th>
                                    <th style="padding:10px 16px; font-weight:600;">Status</th>
                                    <th style="padding:10px 16px; font-weight:600;">Source</th>
                                    <th style="padding:10px 16px; text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recommendations as $rec): ?>
                                    <tr style="border-bottom:1px solid var(--border);">
                                        <td style="padding:10px 16px; font-weight:600; color:var(--primary);"><?php echo htmlspecialchars($rec['module_name']); ?></td>
                                        <td style="padding:10px 16px;">
                                            <strong><?php echo htmlspecialchars($rec['what_to_change']); ?></strong>
                                            <?php if ($rec['why_it_matters']): ?>
                                                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;"><?php echo htmlspecialchars($rec['why_it_matters']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding:10px 16px;">
                                            <span class="badge badge-<?php echo strtolower($rec['priority']) === 'high' ? 'danger' : (strtolower($rec['priority']) === 'medium' ? 'warning' : 'neutral'); ?>">
                                                <?php echo $rec['priority']; ?>
                                            </span>
                                        </td>
                                        <td style="padding:10px 16px; text-transform:capitalize; font-weight:600;"><?php echo str_replace('_', ' ', $rec['status']); ?></td>
                                        <td style="padding:10px 16px; font-size:11px; color:var(--text-muted);"><?php echo htmlspecialchars($rec['source']); ?></td>
                                        <td style="padding:10px 16px; text-align:right;">
                                            <form method="POST" action="/clients/portal-data/delete-recommendation" style="display:inline;" onsubmit="return confirm('Delete this recommendation?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                                <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                                                <input type="hidden" name="period" value="<?php echo $period; ?>">
                                                <input type="hidden" name="recommendation_id" value="<?php echo $rec['id']; ?>">
                                                <button type="submit" class="btn btn-secondary" style="padding:4px 8px; font-size:11px; color:var(--danger-text);">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Add New Recommendation Form -->
        <div>
            <h3 style="font-size:15px; font-weight:700; margin-bottom:12px; color:#334155;">Add Recommendation</h3>
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/clients/portal-data/add-recommendation">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                        <input type="hidden" name="period" value="<?php echo $period; ?>">

                        <div style="margin-bottom:12px;">
                            <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;">Target Module</label>
                            <select name="module" class="form-control" style="width:100%;" required>
                                <?php foreach ($modules as $mod): 
                                    if ($mod['slug'] === 'marketing_health') continue; ?>
                                    <option value="<?php echo $mod['slug']; ?>"><?php echo htmlspecialchars($mod['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="margin-bottom:12px;">
                            <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;">What to Change</label>
                            <input type="text" name="what_to_change" class="form-control" style="width:100%;" placeholder="e.g. Optimize bids on high CPA keywords" required>
                        </div>

                        <div style="margin-bottom:12px;">
                            <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;">Why it Matters</label>
                            <textarea name="why_it_matters" class="form-control" rows="2" style="width:100%;" placeholder="e.g. Saves advertising budget and increases efficiency"></textarea>
                        </div>

                        <div style="margin-bottom:16px;">
                            <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;">Priority</label>
                            <select name="priority" class="form-control" style="width:100%;">
                                <option value="High">High</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="Low">Low</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%;">Add Recommendation</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab Content: Timeline -->
<div class="tab-content" id="tab-timeline" style="display:none;">
    <div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
        <!-- Left: Custom events list -->
        <div>
            <h3 style="font-size:15px; font-weight:700; margin-bottom:12px; color:#334155;">Custom Business Timeline Events</h3>
            <div class="card">
                <div class="card-body" style="padding:0;">
                    <?php if (empty($timelineEvents)): ?>
                        <div style="padding:32px; text-align:center; color:var(--text-muted);">No custom events added to the Business Timeline yet.</div>
                    <?php else: ?>
                        <table style="width:100%; border-collapse:collapse; font-size:13px;">
                            <thead>
                                <tr style="background:#f8fafc; border-bottom:1px solid var(--border); text-align:left;">
                                    <th style="padding:10px 16px; font-weight:600; width:120px;">Date</th>
                                    <th style="padding:10px 16px; font-weight:600;">Event Label</th>
                                    <th style="padding:10px 16px; font-weight:600;">Description</th>
                                    <th style="padding:10px 16px; font-weight:600; width:80px;">Icon</th>
                                    <th style="padding:10px 16px; text-align:right; width:100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($timelineEvents as $event): ?>
                                    <tr style="border-bottom:1px solid var(--border);">
                                        <td style="padding:10px 16px; white-space:nowrap;"><?php echo date('Y-m-d', strtotime($event['event_date'])); ?></td>
                                        <td style="padding:10px 16px; font-weight:600;"><?php echo htmlspecialchars($event['label']); ?></td>
                                        <td style="padding:10px 16px;"><?php echo htmlspecialchars($event['description']); ?></td>
                                        <td style="padding:10px 16px;">
                                            <i data-lucide="<?php echo htmlspecialchars($event['icon']); ?>" width="16"></i>
                                            <span style="font-size:11px; color:var(--text-muted); margin-left:4px;"><?php echo htmlspecialchars($event['icon']); ?></span>
                                        </td>
                                        <td style="padding:10px 16px; text-align:right;">
                                            <form method="POST" action="/clients/portal-data/delete-timeline-event" style="display:inline;" onsubmit="return confirm('Delete this event?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                                <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                                                <input type="hidden" name="period" value="<?php echo $period; ?>">
                                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                <button type="submit" class="btn btn-secondary" style="padding:4px 8px; font-size:11px; color:var(--danger-text);">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Add custom event form -->
        <div>
            <h3 style="font-size:15px; font-weight:700; margin-bottom:12px; color:#334155;">Add Custom Timeline Event</h3>
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/clients/portal-data/add-timeline-event">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                        <input type="hidden" name="period" value="<?php echo $period; ?>">

                        <div style="margin-bottom:12px;">
                            <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;">Event Date</label>
                            <input type="date" name="event_date" class="form-control" style="width:100%;" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div style="margin-bottom:12px;">
                            <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;">Event Label</label>
                            <input type="text" name="label" class="form-control" style="width:100%;" placeholder="e.g. Campaign optimized" required>
                        </div>

                        <div style="margin-bottom:12px;">
                            <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;">Description</label>
                            <textarea name="description" class="form-control" rows="3" style="width:100%;" placeholder="e.g. Reworked target locations and ad schedules for higher CTR." required></textarea>
                        </div>

                        <div style="margin-bottom:16px;">
                            <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;">Icon Shape</label>
                            <select name="icon" class="form-control" style="width:100%;">
                                <option value="calendar">Calendar (default)</option>
                                <option value="trending-up">Trending Up (win)</option>
                                <option value="alert-circle">Alert Circle (risk)</option>
                                <option value="check-circle">Check Circle (complete)</option>
                                <option value="sparkles">Sparkles (new launch)</option>
                                <option value="target">Target (ads)</option>
                                <option value="search">Search (SEO)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%;">Add Event</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab Content: Notes & Goals -->
<div class="tab-content" id="tab-notes" style="display:none;">
    <form method="POST" action="/clients/portal-data/save-notes">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
        <input type="hidden" name="period" value="<?php echo $period; ?>">

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:20px;">
            <div>
                <h3 style="font-size:14px; font-weight:700; margin-bottom:8px; color:#334155;">Monthly Note (This Month Summary)</h3>
                <p style="font-size:11px; color:var(--text-muted); margin-bottom:10px;">Brief plain English summary of what happened this month.</p>
                <textarea name="note" class="form-control" rows="8" style="width:100%; font-size:13px;" placeholder="e.g. This month, we focused on high-intent search terms. Lead counts grew while CPA decreased by 8%..."><?php echo htmlspecialchars($note['body'] ?? ''); ?></textarea>
            </div>
            <div>
                <h3 style="font-size:14px; font-weight:700; margin-bottom:8px; color:#334155;">Monthly Goal</h3>
                <p style="font-size:11px; color:var(--text-muted); margin-bottom:10px;">Primary goals set for this snapshot period.</p>
                <textarea name="goal" class="form-control" rows="8" style="width:100%; font-size:13px;" placeholder="e.g. 1. Increase backlinks count to 250+&#10;2. Launch summer retargeting campaign..."><?php echo htmlspecialchars($goal['body'] ?? ''); ?></textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="padding:10px 20px;">Save Notes & Goals</button>
    </form>
</div>

<script>
function switchTab(tabId) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(function(content) {
        content.style.display = 'none';
    });
    
    // Remove active styles from all buttons
    document.querySelectorAll('.tab-btn').forEach(function(btn) {
        btn.style.color = 'var(--text-muted)';
        btn.style.borderBottomColor = 'transparent';
        btn.style.fontWeight = '500';
    });
    
    // Show selected tab content
    document.getElementById('tab-' + tabId).style.display = 'block';
    
    // Apply active styles to selected button
    var activeBtn = document.getElementById('tab-btn-' + tabId);
    activeBtn.style.color = 'var(--primary)';
    activeBtn.style.borderBottomColor = 'var(--primary)';
    activeBtn.style.fontWeight = '600';
}
</script>
