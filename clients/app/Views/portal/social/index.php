<?php
require_once BASE_PATH . '/app/Core/PlatformIconHelper.php';
use App\Core\PlatformIconHelper;
?>

<div class="page-header" style="margin-bottom:24px;">
    <div>
        <h1 class="page-title" style="font-size:26px; font-weight:800;">Social Media</h1>
        <p style="color: var(--text-muted); margin-top: 4px; font-size:13px;">Overview of your connected social channels, reach, and community metrics.</p>
    </div>
</div>

<!-- Score summary header -->
<?php if (isset($scoreRow) && $scoreRow): ?>
<div class="card" style="background:#f8fafc; border-left:4px solid var(--primary); margin-bottom:24px;">
    <div class="card-body" style="padding:20px 24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:24px;">
        <div>
            <span style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.02em;">Social Performance Score</span>
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

<!-- Channels List -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:24px;">
    <?php foreach ($byPlatform as $code => $platform): ?>
    <div class="card card-hover" style="margin-bottom:0;">
        <div class="card-header" style="background:#f8fafc; border-bottom:1px solid var(--border-light); padding:16px 20px;">
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="color:var(--primary); display:inline-flex;">
                    <?php echo PlatformIconHelper::getSvg($code, 'width="18" height="18"'); ?>
                </span>
                <span style="font-weight:700; font-size:14px; color:#1e293b;"><?php echo htmlspecialchars($platform['label']); ?></span>
            </div>
        </div>
        <div class="card-body" style="padding:16px 20px;">
            <?php if ($platform['data'] === null): ?>
                <p style="color:var(--text-muted); font-size:13px; font-style:italic; text-align:center; padding:20px 0;">No metrics tracked for this platform.</p>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <?php foreach ($fields as $field): ?>
                    <div style="display:flex; justify-content:space-between; font-size:12px; padding:6px 0; border-bottom:1px solid var(--border-light);">
                        <span style="color:var(--text-muted); font-weight:500;"><?php echo htmlspecialchars($field['label']); ?></span>
                        <strong style="color:var(--text-main);"><?php echo htmlspecialchars((string) ($platform['data'][$field['key']] ?? '--')); ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
