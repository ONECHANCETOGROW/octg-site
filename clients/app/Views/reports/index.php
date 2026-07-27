<div class="page-header">
    <div>
        <h1 class="page-title">Report Library</h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Access generated marketing intelligence reports for all clients.</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div style="display: flex; gap: 12px; align-items: center; width: 100%; max-width: 400px;">
            <div style="position: relative; flex: 1;">
                <i data-lucide="search" width="16" style="position: absolute; left: 12px; top: 12px; color: var(--text-muted);"></i>
                <input type="text" class="form-control" placeholder="Search reports..." style="padding-left: 36px;">
            </div>
            <button class="btn btn-secondary">
                <i data-lucide="filter" width="16" style="margin-right: 8px;"></i> Filter
            </button>
        </div>
    </div>
    
    <?php if (empty($reports)): ?>
        <div class="empty-state">
            <i data-lucide="file-text" class="empty-icon"></i>
            <h3 class="empty-title">No reports available</h3>
            <p class="empty-desc">Create a new audit and complete the processing pipeline to generate a report.</p>
            <a href="/audits/create" class="btn btn-primary" style="margin-top: 16px;">Create Audit</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Audit Name</th>
                        <th>Client</th>
                        <th>Channel & Month</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                    <tr>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($report['name']); ?></td>
                        <td><?php echo htmlspecialchars($report['business_name']); ?></td>
                        <td>
                            <div><?php echo htmlspecialchars($report['channel']); ?></div>
                            <div style="font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($report['audit_month']); ?></div>
                        </td>
                        <td>
                            <?php if ($report['status'] === 'completed'): ?>
                                <span class="badge badge-success">Available</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Processing</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 8px;">
                                <a href="/reports/view?id=<?php echo $report['id']; ?>&type=<?php echo $report['audit_type']; ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;">
                                    View HTML
                                </a>
                                <button class="btn btn-secondary" style="padding: 6px;" title="Export PDF">
                                    <i data-lucide="download" width="14"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
