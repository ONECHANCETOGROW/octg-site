<?php
$moduleLabels = [
    'google-ads' => 'Google Ads',
    'seo' => 'Website SEO',
    'gbp' => 'Google Business Profile',
    'social' => 'Social Media',
    'website-performance' => 'Website Performance',
    'reports' => 'Reports',
    'recommendations' => 'Recommendations',
    'timeline' => 'Timeline',
];
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Portal Modules - <?php echo htmlspecialchars($client['business_name']); ?></h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Choose which dashboard sections this client can see.</p>
    </div>
</div>

<?php if (isset($_GET['saved'])): ?>
<div class="card" style="border-left:4px solid var(--success,#10b981);"><div class="card-body">Saved.</div></div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="/clients/modules">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <input type="hidden" name="client_id" value="<?php echo (int) $client['id']; ?>">
        <div class="card-body">
            <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">Executive Summary is always visible. Everything below is optional -- a client with no social media presence, for example, shouldn't see an empty Social Media card.</p>
            <?php foreach ($moduleLabels as $code => $label): ?>
            <label style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border,#e2e8f0);cursor:pointer;">
                <input type="checkbox" name="enabled[]" value="<?php echo $code; ?>" <?php echo !in_array($code, $disabled, true) ? 'checked' : ''; ?>>
                <?php echo htmlspecialchars($label); ?>
            </label>
            <?php endforeach; ?>
        </div>
        <div class="card-body" style="border-top:1px solid var(--border,#e2e8f0);">
            <button type="submit" class="btn btn-primary">Save Modules</button>
        </div>
    </form>
</div>
