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
.step-label {
    font-size: 12px;
    font-weight: 500;
    color: var(--text-muted);
}
.step.active .step-label {
    color: var(--text-main);
}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">New Audit Workflow</h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Follow the steps to configure and initialize a new intelligence audit.</p>
    </div>
</div>

<div class="wizard-steps">
    <div class="step active">
        <div class="step-number">1</div>
        <div class="step-label">Setup</div>
    </div>
    <div class="step">
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

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <div class="card-title">Step 1 & 2: Client & Audit Details</div>
    </div>
    <form action="/audits/store" method="POST">
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Select Client *</label>
                <select class="form-control" name="client_id" required>
                    <option value="">-- Choose a Client --</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['business_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Audit Name *</label>
                <input type="text" class="form-control" name="name" placeholder="e.g. Q3 Performance Audit" required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Audit Month *</label>
                    <input type="month" class="form-control" name="audit_month" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Marketing Channel *</label>
                    <select class="form-control" name="channel" required>
                        <option value="Google Ads">Google Ads</option>
                        <option value="Meta Ads">Meta Ads</option>
                        <option value="SEO">SEO / Organic</option>
                        <option value="GA4">Google Analytics 4</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Internal Notes (Optional)</label>
                <textarea class="form-control" name="notes" rows="3" placeholder="Context or specific focus areas..."></textarea>
            </div>
        </div>
        <div class="card-header" style="background: var(--bg-body); border-top: 1px solid var(--border); display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary">Create Audit Workspace <i data-lucide="arrow-right" width="16" style="margin-left: 8px;"></i></button>
        </div>
    </form>
</div>
