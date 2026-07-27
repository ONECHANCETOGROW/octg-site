<?php
/**
 * Shared display template for single-metric manual-entry modules.
 */
?>
<div class="page-header" style="margin-bottom:24px;">
    <div>
        <h1 class="page-title" style="font-size:26px; font-weight:800;"><?php echo htmlspecialchars($title); ?></h1>
        <p style="color: var(--text-muted); margin-top: 4px; font-size:13px;">Performance details and raw metrics updated monthly by your account manager.</p>
    </div>
</div>

<?php if (isset($_GET['saved'])): ?>
<div class="card" style="border-left:4px solid var(--success); margin-bottom:20px; background:#f0fdf4;"><div class="card-body" style="padding:12px 20px; color:var(--success-text); font-weight:500;">Saved.</div></div>
<?php endif; ?>

<!-- Score summary header -->
<?php if (isset($scoreRow) && $scoreRow): ?>
<div class="card" style="background:#f8fafc; border-left:4px solid var(--primary); margin-bottom:24px;">
    <div class="card-body" style="padding:20px 24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:24px;">
        <div>
            <span style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.02em;">Module Performance Score</span>
            <div style="display:flex; align-items:baseline; gap:4px; margin-top:4px;">
                <span style="font-size:36px; font-weight:800; color:var(--text-main);"><?php echo $scoreRow['score']; ?></span>
                <span style="font-size:16px; color:var(--text-muted); font-weight:600;">/100</span>
                <?php if ($scoreRow['trend']): ?>
                    <span class="badge" style="font-size:11px; font-weight:700; background:var(--success-light); color:var(--success-text); margin-left:12px; padding:2px 8px; border-radius:10px;">
                        <?php echo htmlspecialchars($scoreRow['trend']); ?>
                    </span>
                <?php endif; ?>
            </div>
            <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--primary); margin-top:4px;">
                Status: <?php echo htmlspecialchars($scoreRow['health_status'] ?? 'Good'); ?>
            </div>
        </div>
        <div style="display:flex; gap:24px; flex:1; justify-content:flex-end; min-width:280px; flex-wrap:wrap;">
            <?php if ($scoreRow['biggest_win']): ?>
                <div style="font-size:13px; max-width:220px;">
                    <span style="display:block; font-size:10px; font-weight:700; color:var(--success-text); text-transform:uppercase; margin-bottom:4px; letter-spacing:0.02em;">Biggest Win</span>
                    <span style="color:var(--text-main); font-weight:500;"><?php echo htmlspecialchars($scoreRow['biggest_win']); ?></span>
                </div>
            <?php endif; ?>
            <?php if ($scoreRow['biggest_risk']): ?>
                <div style="font-size:13px; max-width:220px;">
                    <span style="display:block; font-size:10px; font-weight:700; color:var(--danger-text); text-transform:uppercase; margin-bottom:4px; letter-spacing:0.02em;">Biggest Risk</span>
                    <span style="color:var(--text-main); font-weight:500;"><?php echo htmlspecialchars($scoreRow['biggest_risk']); ?></span>
                </div>
            <?php endif; ?>
            <?php if ($scoreRow['priority_this_month']): ?>
                <div style="font-size:13px; max-width:220px;">
                    <span style="display:block; font-size:10px; font-weight:700; color:var(--primary); text-transform:uppercase; margin-bottom:4px; letter-spacing:0.02em;">Immediate Action</span>
                    <span style="color:var(--text-main); font-weight:500;"><?php echo htmlspecialchars($scoreRow['priority_this_month']); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($latestData === null): ?>
<div class="card"><div class="card-body empty-state" style="padding:48px 24px; text-align:center;">
    <i data-lucide="bar-chart-3" width="40" style="color:var(--text-muted); margin-bottom:12px;"></i>
    <p style="font-weight:600; margin-bottom:4px; color:var(--text-main);">No statistics recorded</p>
    <p style="color:var(--text-muted); font-size:13px;">Your account manager hasn't uploaded this month's snapshot metrics yet.</p>
</div></div>
<?php else: ?>

<!-- Visual KPI Cards Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <?php foreach ($fields as $field): if ($field['type'] === 'textarea') continue; ?>
    <div class="card card-hover" style="margin-bottom:0;"><div class="card-body" style="padding:20px;">
        <div style="color:var(--text-muted); font-size:13px; font-weight:500; margin-bottom:6px;"><?php echo htmlspecialchars($field['label']); ?></div>
        <div style="font-size:24px; font-weight:800; color:var(--text-main);"><?php echo htmlspecialchars((string) ($latestData[$field['key']] ?? '--')); ?></div>
        <?php if ($previousData !== null && isset($previousData[$field['key']]) && is_numeric($previousData[$field['key']] ?? null) && is_numeric($latestData[$field['key']] ?? null)):
            $prev = (float) $previousData[$field['key']]; $curr = (float) $latestData[$field['key']];
            $delta = $prev != 0 ? (($curr - $prev) / abs($prev)) * 100 : null;
        ?>
        <?php if ($delta !== null && abs($delta) > 0.05): ?>
        <div style="font-size:11px; margin-top:6px; font-weight:600; color:<?php echo $delta >= 0 ? 'var(--success-text)' : 'var(--danger-text)'; ?>;">
            <?php echo $delta >= 0 ? '▲ +' : '▼ '; ?><?php echo number_format(abs($delta), 1); ?>% vs last month
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div></div>
    <?php endforeach; ?>
</div>

<!-- Lists / Textarea sections -->
<?php foreach ($fields as $field): if ($field['type'] !== 'textarea' || empty($latestData[$field['key']])) continue; ?>
<div class="card">
    <div class="card-header" style="background:#f8fafc; border-bottom:1px solid var(--border-light); padding:16px 24px;">
        <div class="card-title" style="font-size:15px; font-weight:700; color:#1e293b;"><?php echo htmlspecialchars($field['label']); ?></div>
    </div>
    <div class="card-body" style="padding:20px 24px;">
        <ul style="margin:0; padding-left:20px; display:flex; flex-direction:column; gap:8px;">
            <?php foreach ((array) $latestData[$field['key']] as $line): ?>
            <li style="font-size:13px; color:var(--text-main); font-weight:500; line-height:1.5;"><?php echo htmlspecialchars($line); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>
