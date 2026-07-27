<div class="page-header" style="margin-bottom:24px;">
    <div>
        <h1 class="page-title" style="font-size:26px; font-weight:800;">Business Timeline</h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Milestones and operations history, month by month.</p>
    </div>
</div>

<?php if (empty($byMonth)): ?>
<div class="card">
    <div class="card-body empty-state" style="padding:48px 24px; text-align:center;">
        <i data-lucide="history" width="40" style="color:var(--text-muted); margin-bottom:12px;"></i>
        <p style="font-weight:600; color:var(--text-main); margin-bottom:4px;">Timeline is empty</p>
        <p style="color:var(--text-muted); font-size:13px;">System and manual timeline events will appear here once registered.</p>
    </div>
</div>
<?php else: foreach ($byMonth as $month => $events): ?>
<div class="card" style="margin-bottom:24px;">
    <div class="card-header" style="background:#f8fafc; border-bottom:1px solid var(--border-light); padding:16px 24px;">
        <div class="card-title" style="font-size:15px; font-weight:700; color:#1e293b;"><?php echo htmlspecialchars($month); ?></div>
    </div>
    <div class="card-body" style="padding:0;">
        <?php foreach ($events as $event): ?>
        <div style="display:flex; align-items:start; gap:16px; padding:18px 24px; border-bottom:1px solid var(--border-light);">
            <span style="display:inline-flex; padding:8px; border-radius:50%; background:var(--primary-light); color:var(--primary); margin-top:2px;">
                <i data-lucide="<?php echo htmlspecialchars($event['icon']); ?>" width="16" height="16"></i>
            </span>
            <div style="flex:1;">
                <h4 style="font-size:14px; font-weight:700; color:var(--text-main); margin-bottom:4px;"><?php echo htmlspecialchars($event['label']); ?></h4>
                <p style="font-size:13px; color:var(--text-muted); line-height:1.5;"><?php echo htmlspecialchars($event['description']); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; endif; ?>
