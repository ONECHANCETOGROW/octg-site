<?php
require_once BASE_PATH . '/app/Services/Storage/Storage.php';
$manifestPath = "clients/{$audit['client_id']}/{$audit['id']}/manifest.json";
$hasManifest = Storage::exists($manifestPath);
$manifestData = $hasManifest ? json_decode(Storage::read($manifestPath), true) : null;

$logPath = "clients/{$audit['client_id']}/{$audit['id']}/05-logs/processing.log";
$logData = Storage::exists($logPath) ? Storage::read($logPath) : "Waiting for files...\n";
?>
<div class="page-header">
    <div>
        <h1 class="page-title"><?php echo htmlspecialchars($audit['name']); ?></h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Audit Workspace & Processing Pipeline</p>
    </div>
    <div class="page-actions">
        <a href="/audits/upload?id=<?php echo $audit['id']; ?>" class="btn btn-secondary">
            <i data-lucide="upload" width="16" style="margin-right: 8px;"></i> Upload More Files
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Processing Pipeline</div>
            </div>
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; position: relative;">
                    <?php 
                    $stages = [
                        'uploaded' => ['label' => 'Uploaded', 'icon' => 'upload-cloud'],
                        'validated' => ['label' => 'Validated', 'icon' => 'check-circle'],
                        'queued' => ['label' => 'Queued', 'icon' => 'list'],
                        'processing' => ['label' => 'Intelligence', 'icon' => 'cpu'],
                        'completed' => ['label' => 'Report', 'icon' => 'file-text']
                    ];
                    
                    // Map DB status to our pipeline index
                    $statusMap = ['setup' => -1, 'uploading' => -1, 'validated' => 1, 'queued' => 2, 'processing' => 3, 'completed' => 4, 'failed' => 99];
                    
                    // If we have uploads, we are at least at uploaded (0)
                    $currentIndex = $statusMap[$audit['status']] ?? -1;
                    if ($currentIndex === -1 && $hasManifest) $currentIndex = 0;
                    if ($audit['status'] === 'failed') $currentIndex = 99;

                    $percent = $currentIndex === 99 ? 100 : min(100, max(0, ($currentIndex / 4) * 100));
                    $barColor = $currentIndex === 99 ? 'var(--danger)' : 'var(--success)';
                    ?>
                    <!-- Timeline lines -->
                    <div style="position: absolute; top: 16px; left: 0; right: 0; height: 2px; background: var(--border); z-index: 1;"></div>
                    <div style="position: absolute; top: 16px; left: 0; width: <?php echo $percent; ?>%; height: 2px; background: <?php echo $barColor; ?>; z-index: 1; transition: width 0.5s;"></div>
                    
                    <!-- Steps -->
                    <?php 
                    $i = 0;
                    foreach ($stages as $key => $stage): 
                        if ($currentIndex === 99 && $i > 0) { // Failed state rendering
                            $bgColor = 'var(--bg-surface)'; $borderColor = 'var(--border)'; $color = 'var(--text-muted)'; $icon = $stage['icon'];
                            if ($i === 1) { // Show fail at step 1 for now or wherever it failed
                                $bgColor = 'var(--danger)'; $borderColor = 'var(--danger)'; $color = 'white'; $icon = 'alert-triangle';
                            }
                        } else if ($i < $currentIndex) {
                            // Completed
                            $bgColor = 'var(--success)'; $borderColor = 'var(--success)'; $color = 'white'; $icon = 'check';
                        } else if ($i === $currentIndex) {
                            // Running / Pending
                            $bgColor = 'var(--bg-surface)'; $borderColor = 'var(--primary)'; $color = 'var(--primary)'; $icon = 'loader';
                            $extraStyle = 'animation: spin 2s linear infinite;';
                        } else {
                            // Future
                            $bgColor = 'var(--bg-surface)'; $borderColor = 'var(--border)'; $color = 'var(--text-muted)'; $icon = $stage['icon'];
                            $extraStyle = '';
                        }
                    ?>
                    <div style="position: relative; z-index: 2; text-align: center;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: <?php echo $bgColor; ?>; border: 2px solid <?php echo $borderColor; ?>; color: <?php echo $color; ?>; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px;">
                            <?php if($icon === 'loader'): ?><style>@keyframes spin { 100% { transform: rotate(360deg); } }</style><?php endif; ?>
                            <i data-lucide="<?php echo $icon; ?>" width="16" style="<?php echo $extraStyle ?? ''; ?>"></i>
                        </div>
                        <div style="font-size: 12px; font-weight: <?php echo $i <= $currentIndex ? '600' : '500'; ?>; color: <?php echo $i <= $currentIndex ? 'var(--text-main)' : 'var(--text-muted)'; ?>;"><?php echo $stage['label']; ?></div>
                    </div>
                    <?php $i++; endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <div class="card-title">Uploaded Files Manifest</div>
            </div>
            <div class="table-container">
                <table>
                    <thead><tr><th>File Name</th><th>SHA-256</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if ($manifestData && isset($manifestData['files'])): ?>
                            <?php foreach ($manifestData['files'] as $f): ?>
                            <tr>
                                <td style="font-weight: 500;"><?php echo htmlspecialchars($f['name']); ?></td>
                                <td style="font-family: monospace; font-size: 12px; color: var(--text-muted);"><?php echo substr($f['sha256'], 0, 16); ?>...</td>
                                <td><span class="badge badge-success"><?php echo htmlspecialchars($f['status']); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" style="text-align: center; color: var(--text-muted);">No files uploaded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (in_array($audit['status'], ['validated', 'queued', 'failed'])): ?>
            <div class="card-header" style="background: var(--bg-body); border-top: 1px solid var(--border); display: flex; justify-content: flex-end;">
                <button id="processBtn" class="btn btn-primary" onclick="startProcessing()">
                    <i data-lucide="cpu" width="16" style="margin-right: 8px;"></i> Start Intelligence Engine
                </button>
            </div>
            <script>
            function startProcessing() {
                const btn = document.getElementById('processBtn');
                btn.disabled = true;
                btn.innerHTML = '<i data-lucide="loader" width="16" style="margin-right: 8px; animation: spin 2s linear infinite;"></i> Processing...';
                if(window.lucide) lucide.createIcons();
                
                let formData = new FormData();
                formData.append('audit_id', '<?php echo $audit['id']; ?>');
                
                fetch('/audits/process', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert("Processing Failed: " + data.error);
                        window.location.reload();
                    }
                })
                .catch(err => {
                    alert("Network error.");
                    window.location.reload();
                });
            }
            </script>
            <?php endif; ?>
            
            <?php if ($audit['status'] === 'completed'): ?>
            <div class="card-header" style="background: var(--bg-body); border-top: 1px solid var(--border); display: flex; justify-content: flex-end;">
                <a href="/reports/view?id=<?php echo $audit['id']; ?>" class="btn btn-success">
                    <i data-lucide="file-text" width="16" style="margin-right: 8px;"></i> View Report
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div>
        <div class="card" style="height: 100%;">
            <div class="card-header">
                <div class="card-title">Processing Log</div>
            </div>
            <div style="background: #0f172a; color: #38bdf8; padding: 16px; font-family: monospace; font-size: 12px; height: calc(100% - 61px); overflow-y: auto; white-space: pre-wrap; margin: 0;">
<?php echo htmlspecialchars($logData); ?>
            </div>
        </div>
    </div>
</div>
