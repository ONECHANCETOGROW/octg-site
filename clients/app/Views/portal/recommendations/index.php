<div class="page-header">
    <div>
        <h1 class="page-title">Recommendations</h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Everything we've suggested, and where it stands.</p>
    </div>
</div>

<?php foreach (['High' => 'danger', 'Medium' => 'warning', 'Low' => 'neutral'] as $priority => $badgeClass): ?>
<div class="card">
    <div class="card-header"><div class="card-title"><span class="badge badge-<?php echo $badgeClass; ?>"><?php echo $priority; ?></span> Priority</div></div>
    <div class="table-container">
        <table>
            <thead><tr><th>Recommendation</th><th>Why</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($grouped[$priority])): ?>
                <tr><td colspan="4" style="text-align:center;color:var(--text-muted);">None.</td></tr>
                <?php else: foreach ($grouped[$priority] as $rec): ?>
                <tr>
                    <td style="font-weight:500;"><?php echo htmlspecialchars($rec['what_to_change']); ?></td>
                    <td style="color:var(--text-muted);font-size:13px;"><?php echo htmlspecialchars($rec['why_it_matters'] ?? ''); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $rec['status'] === 'completed' ? 'success' : ($rec['status'] === 'in_progress' ? 'warning' : ($rec['status'] === 'ignored' ? 'neutral' : 'danger')); ?>">
                            <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $rec['status']))); ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" action="<?php echo $this->clientUrl($client['slug'] ?? '', 'recommendations/' . $rec['id'] . '/status'); ?>" style="display:flex;gap:4px;">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <select name="status" onchange="this.form.submit()" class="form-control" style="font-size:12px;padding:4px;">
                                <option value="open" <?php echo $rec['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                <option value="in_progress" <?php echo $rec['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo $rec['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="ignored" <?php echo $rec['status'] === 'ignored' ? 'selected' : ''; ?>>Ignored</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; ?>
