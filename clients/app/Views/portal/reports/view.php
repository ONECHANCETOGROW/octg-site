<?php
$scorecard = $contract['scorecard'] ?? [];
$exec = $contract['executive_summary'] ?? [];
$recs = $contract['recommendations']['recommendations'] ?? [];
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Report</h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Generated <?php echo isset($contract['metadata']['generated_at']) ? date('F j, Y', strtotime($contract['metadata']['generated_at'])) : ''; ?></p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div style="font-size:28px;font-weight:700;margin-bottom:8px;"><?php echo $scorecard['overall_score'] ?? '--'; ?>/100
            <span class="badge badge-neutral"><?php echo htmlspecialchars($scorecard['grade'] ?? ''); ?></span>
        </div>
        <p><?php echo htmlspecialchars($exec['executive_summary'] ?? ''); ?></p>
    </div>
</div>

<div class="card">
    <div class="card-header"><div class="card-title">Recommendations from this report</div></div>
    <div class="table-container">
        <table>
            <thead><tr><th>Recommendation</th><th>Priority</th></tr></thead>
            <tbody>
                <?php foreach ($recs as $r): ?>
                <tr><td><?php echo htmlspecialchars($r['what_to_change'] ?? ''); ?></td><td><?php echo htmlspecialchars($r['priority'] ?? ''); ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
