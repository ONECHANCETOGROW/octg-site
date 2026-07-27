<div class="page-header">
    <div>
        <h1 class="page-title">Reports</h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Every report we've ever generated for you, grouped by month.</p>
    </div>
</div>

<?php if (empty($byMonth)): ?>
<div class="card"><div class="card-body empty-state">
    <p style="font-weight:600;">No reports yet</p>
</div></div>
<?php else: foreach ($byMonth as $month => $reports): ?>
<div class="card">
    <div class="card-header"><div class="card-title"><?php echo htmlspecialchars($month); ?></div></div>
    <div class="table-container">
        <table>
            <thead><tr><th>Report</th><th>Channel</th><th>Date</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($reports as $r): ?>
                <tr>
                    <td style="font-weight:500;"><?php echo htmlspecialchars($r['title'] ?? $r['name'] ?? 'Report'); ?></td>
                    <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $r['channel']))); ?></td>
                    <td style="color:var(--text-muted);"><?php echo date('M j, Y', strtotime($r['created_at'])); ?></td>
                    <td><a class="btn btn-secondary" style="padding:4px 12px;font-size:12px;" href="<?php echo $this->clientUrl($client['slug'] ?? '', 'reports/' . $r['id']); ?>">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; endif; ?>
