<div class="page-header">
    <div>
        <h1 class="page-title">Marketing Health Dashboard</h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Overview of your digital marketing performance.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-secondary">
            <i data-lucide="download" width="16" style="margin-right: 8px;"></i> Export Report
        </button>
        <a href="/audits/create" class="btn btn-primary">
            <i data-lucide="plus" width="16" style="margin-right: 8px;"></i> Connect Data
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 24px;">
    <!-- Widgets -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-body">
            <div style="color: var(--text-muted); font-size: 13px; font-weight: 500; margin-bottom: 8px;">Overall Health Score</div>
            <div style="display: flex; align-items: end; gap: 12px;">
                <div style="font-size: 32px; font-weight: 700; color: var(--success-text); line-height: 1;">87<span style="font-size: 16px; color: var(--text-muted); font-weight: 500;">/100</span></div>
                <div class="badge badge-success">+4% this month</div>
            </div>
        </div>
    </div>
    
    <div class="card" style="margin-bottom: 0;">
        <div class="card-body">
            <div style="color: var(--text-muted); font-size: 13px; font-weight: 500; margin-bottom: 8px;">Active Clients</div>
            <div style="display: flex; align-items: end; gap: 12px;">
                <div style="font-size: 32px; font-weight: 700; line-height: 1;"><?php echo $stats['clients']; ?></div>
                <div class="badge badge-neutral">Stable</div>
            </div>
        </div>
    </div>
    
    <div class="card" style="margin-bottom: 0;">
        <div class="card-body">
            <div style="color: var(--text-muted); font-size: 13px; font-weight: 500; margin-bottom: 8px;">Reports Generated</div>
            <div style="display: flex; align-items: end; gap: 12px;">
                <div style="font-size: 32px; font-weight: 700; line-height: 1;"><?php echo $stats['reports']; ?></div>
                <div class="badge badge-warning">Requires Review (<?php echo max(0, $stats['audits'] - $stats['reports']); ?>)</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Recent Activity</div>
        <a href="/activity" class="btn btn-secondary" style="padding: 4px 12px; font-size: 12px;">View All</a>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($activities)): ?>
                <tr>
                    <td colspan="4" style="text-align: center; color: var(--text-muted);">No recent activity.</td>
                </tr>
                <?php else: ?>
                    <?php foreach($activities as $act): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center;">
                                <i data-lucide="zap" width="16" style="margin-right: 8px; color: var(--primary);"></i> 
                                <?php echo htmlspecialchars($act['action']); ?>
                            </div>
                        </td>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($act['business_name'] ?? '-'); ?></td>
                        <td style="color: var(--text-muted);"><?php echo date('M d, h:i A', strtotime($act['created_at'])); ?></td>
                        <td><span class="badge badge-success">Completed</span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
