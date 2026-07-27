<div class="page-header">
    <div>
        <h1 class="page-title">Notifications</h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Your alerts and system messages.</p>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Alert</th>
                    <th>Timestamp</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($notifications)): ?>
                <tr>
                    <td colspan="3" style="text-align: center; color: var(--text-muted);">You have no notifications.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($notifications as $n): ?>
                    <tr <?php echo $n['is_read'] ? 'style="opacity: 0.6;"' : ''; ?>>
                        <td>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($n['title']); ?></div>
                            <div style="color: var(--text-muted); font-size: 13px;"><?php echo htmlspecialchars($n['message']); ?></div>
                        </td>
                        <td style="color: var(--text-muted);"><?php echo date('M d, h:i A', strtotime($n['created_at'])); ?></td>
                        <td style="text-align: right;">
                            <?php if (!$n['is_read']): ?>
                            <button class="btn btn-secondary" onclick="markRead(<?php echo $n['id']; ?>)">Mark Read</button>
                            <?php else: ?>
                            <span class="badge badge-neutral">Read</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function markRead(id) {
    let fd = new FormData();
    fd.append('id', id);
    fetch('/notifications/read', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
        if(d.success) location.reload();
    });
}
</script>
