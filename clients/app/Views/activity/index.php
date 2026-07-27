<div class="page-header">
    <div>
        <h1 class="page-title">Activity Center</h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Track system actions and audit history.</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div style="display: flex; gap: 12px; align-items: center; width: 100%; max-width: 400px;">
            <div style="position: relative; flex: 1;">
                <i data-lucide="search" width="16" style="position: absolute; left: 12px; top: 12px; color: var(--text-muted);"></i>
                <input type="text" class="form-control" placeholder="Search activity logs..." style="padding-left: 36px;">
            </div>
            <button class="btn btn-secondary">
                <i data-lucide="filter" width="16" style="margin-right: 8px;"></i> Filter
            </button>
        </div>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Action</th>
                    <th>User</th>
                    <th>Client</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($activities)): ?>
                <tr>
                    <td colspan="4" style="text-align: center; color: var(--text-muted);">No activity recorded yet.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($activities as $log): ?>
                    <tr>
                        <td style="font-weight: 500;">
                            <?php echo htmlspecialchars($log['action']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></td>
                        <td><?php echo htmlspecialchars($log['business_name'] ?? '-'); ?></td>
                        <td style="color: var(--text-muted);">
                            <?php echo date('M d, Y h:i A', strtotime($log['created_at'])); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
