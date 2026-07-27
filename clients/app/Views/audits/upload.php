<style>
.wizard-steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: 32px;
    position: relative;
    max-width: 600px;
    margin-inline: auto;
}
.wizard-steps::before {
    content: '';
    position: absolute;
    top: 15px;
    left: 30px;
    right: 30px;
    height: 2px;
    background: var(--border);
    z-index: 1;
}
.step {
    position: relative;
    z-index: 2;
    text-align: center;
    width: 60px;
}
.step-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--bg-surface);
    border: 2px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    font-weight: 600;
    color: var(--text-muted);
}
.step.active .step-number {
    border-color: var(--primary);
    background: var(--primary);
    color: white;
}
.step.completed .step-number {
    border-color: var(--success);
    background: var(--success);
    color: white;
}
.step-label {
    font-size: 12px;
    font-weight: 500;
    color: var(--text-muted);
}
.step.active .step-label, .step.completed .step-label {
    color: var(--text-main);
}
.dropzone {
    border: 2px dashed var(--border);
    border-radius: var(--radius-lg);
    padding: 48px;
    text-align: center;
    background: var(--bg-body);
    transition: all 0.2s;
    cursor: pointer;
}
.dropzone.dragover {
    border-color: var(--primary);
    background: var(--primary-light);
}
.dropzone-icon {
    width: 48px;
    height: 48px;
    color: var(--text-light);
    margin-bottom: 16px;
}
.upload-progress {
    margin-top: 24px;
    text-align: left;
}
.file-item {
    display: flex;
    align-items: center;
    padding: 12px;
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    margin-bottom: 8px;
}
.file-info { flex: 1; }
.file-name { font-weight: 500; font-size: 13px; }
.file-size { font-size: 12px; color: var(--text-muted); }
.file-status { margin-left: 12px; }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">Upload Data</h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Upload the raw exports required for this audit.</p>
    </div>
</div>

<div class="wizard-steps">
    <div class="step completed">
        <div class="step-number"><i data-lucide="check" width="16"></i></div>
        <div class="step-label">Setup</div>
    </div>
    <div class="step active">
        <div class="step-number">2</div>
        <div class="step-label">Upload</div>
    </div>
    <div class="step">
        <div class="step-number">3</div>
        <div class="step-label">Process</div>
    </div>
    <div class="step">
        <div class="step-number">4</div>
        <div class="step-label">Report</div>
    </div>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <div class="card-title">Upload Raw Reports for <?php echo htmlspecialchars($audit['name']); ?></div>
    </div>
    <div class="card-body">
        <div class="dropzone" id="dropzone">
            <input type="file" id="fileInput" multiple style="display: none;" accept=".csv,.pdf,.xlsx">
            <i data-lucide="upload-cloud" class="dropzone-icon"></i>
            <h3 style="margin-bottom: 8px;">Drag & Drop files here</h3>
            <p style="color: var(--text-muted); font-size: 13px;">Supports Google Ads exports (CSV), Google Analytics (PDF/CSV), and raw data (XLSX)</p>
            <button class="btn btn-secondary" style="margin-top: 16px;" onclick="document.getElementById('fileInput').click()">Browse Files</button>
        </div>

        <div class="upload-progress" id="uploadList"></div>
    </div>
    <div class="card-header" style="background: var(--bg-body); border-top: 1px solid var(--border); display: flex; justify-content: flex-end;">
        <a href="/audits/show?id=<?php echo $audit['id']; ?>" class="btn btn-primary" id="continueBtn" style="display: none;">Continue to Processing <i data-lucide="arrow-right" width="16" style="margin-left: 8px;"></i></a>
    </div>
</div>

<script>
const dropzone = document.getElementById('dropzone');
const fileInput = document.getElementById('fileInput');
const uploadList = document.getElementById('uploadList');
const continueBtn = document.getElementById('continueBtn');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropzone.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults (e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropzone.addEventListener(eventName, () => dropzone.classList.add('dragover'), false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropzone.addEventListener(eventName, () => dropzone.classList.remove('dragover'), false);
});

dropzone.addEventListener('drop', handleDrop, false);
fileInput.addEventListener('change', function(e) { handleFiles(this.files); });

function handleDrop(e) {
    let dt = e.dataTransfer;
    let files = dt.files;
    handleFiles(files);
}

function handleFiles(files) {
    ([...files]).forEach(uploadFile);
}

function uploadFile(file) {
    const fileId = 'file-' + Math.random().toString(36).substr(2, 9);
    const item = document.createElement('div');
    item.className = 'file-item';
    item.id = fileId;
    item.innerHTML = `
        <i data-lucide="file" width="20" style="color: var(--text-muted); margin-right: 12px;"></i>
        <div class="file-info">
            <div class="file-name">${file.name}</div>
            <div class="file-size">${(file.size / 1024).toFixed(1)} KB</div>
        </div>
        <div class="file-status">
            <span class="badge badge-warning">Uploading...</span>
        </div>
    `;
    uploadList.appendChild(item);
    if(window.lucide) lucide.createIcons();

    let formData = new FormData();
    formData.append('file', file);
    formData.append('audit_id', '<?php echo $audit['id']; ?>');
    formData.append('client_id', '<?php echo $audit['client_id']; ?>');

    fetch('/upload/process', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const statusDiv = item.querySelector('.file-status');
        if(data.success) {
            statusDiv.innerHTML = `<span class="badge badge-success">Validated</span>`;
            continueBtn.style.display = 'inline-flex';
        } else {
            statusDiv.innerHTML = `<span class="badge badge-danger">Error: ${data.error}</span>`;
        }
    })
    .catch(error => {
        item.querySelector('.file-status').innerHTML = `<span class="badge badge-danger">Upload Failed</span>`;
    });
}
</script>
