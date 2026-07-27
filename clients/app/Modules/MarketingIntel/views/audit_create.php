<?php
/** @var array<int,array<string,mixed>> $clients */
/** @var array<int,array<string,mixed>> $channels */
/** @var string|null $error */
?>
<style>
/* Premium Form Styling */
.intel-page-wrapper {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem 1rem;
    font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.intel-header {
    margin-bottom: 2.5rem;
    text-align: center;
}

.intel-header h1 {
    font-size: 2.25rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.75rem;
    letter-spacing: -0.025em;
}

.intel-header p {
    color: #6b7280;
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.5;
}

.intel-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.05), 0 0 10px rgba(0,0,0,0.01);
    border: 1px solid rgba(229, 231, 235, 0.8);
    overflow: hidden;
    position: relative;
}

.intel-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
}

.intel-card-body {
    padding: 2.5rem 3rem;
}

.intel-form-group {
    margin-bottom: 1.75rem;
}

.intel-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.intel-input, .intel-select {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 1rem;
    color: #1f2937;
    background-color: #f9fafb;
    transition: all 0.2s ease;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.025);
    box-sizing: border-box;
}

.intel-input:focus, .intel-select:focus {
    outline: none;
    border-color: #3b82f6;
    background-color: #ffffff;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
}

/* Custom Channel Cards */
.channel-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-top: 0.5rem;
}

.channel-card {
    position: relative;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    padding: 1.25rem;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #ffffff;
    display: flex;
    flex-direction: column;
}

.channel-card:hover {
    border-color: #d1d5db;
    background: #f9fafb;
}

.channel-card input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.channel-card:has(input[type="checkbox"]:checked) {
    border-color: #3b82f6;
    background: #eff6ff;
    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1), 0 2px 4px -1px rgba(59, 130, 246, 0.06);
}

.channel-icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}

.channel-title {
    font-weight: 600;
    color: #111827;
    font-size: 1.05rem;
}

.channel-desc {
    font-size: 0.85rem;
    color: #6b7280;
    line-height: 1.4;
}

.check-indicator {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid #d1d5db;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.channel-card:has(input[type="checkbox"]:checked) .check-indicator {
    background-color: #3b82f6;
    border-color: #3b82f6;
}

.check-indicator::after {
    content: "";
    width: 10px;
    height: 10px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='white'%3E%3Cpath fill-rule='evenodd' d='M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z' clip-rule='evenodd'/%3E%3C/svg%3E");
    background-size: contain;
    background-repeat: no-repeat;
    opacity: 0;
    transition: opacity 0.2s;
}

.channel-card:has(input[type="checkbox"]:checked) .check-indicator::after {
    opacity: 1;
}

.intel-help-text {
    font-size: 0.85rem;
    color: #6b7280;
    margin-top: 0.5rem;
    display: block;
    line-height: 1.4;
}

.intel-btn-primary {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    border: none;
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    width: 100%;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2), 0 2px 4px -1px rgba(37, 99, 235, 0.1);
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
}

.intel-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3), 0 4px 6px -2px rgba(37, 99, 235, 0.15);
    background: linear-gradient(135deg, #4f8cf6, #1d4ed8);
}

.intel-btn-primary:active {
    transform: translateY(0);
}

.intel-alert-danger {
    background-color: #fef2f2;
    border-left: 4px solid #ef4444;
    color: #991b1b;
    padding: 1rem 1.5rem;
    border-radius: 6px;
    margin-bottom: 2rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
    text-decoration: none;
    font-weight: 500;
    margin-bottom: 1rem;
    transition: color 0.2s;
}

.back-link:hover {
    color: #111827;
}

@media (max-width: 640px) {
    .intel-card-body {
        padding: 1.5rem;
    }
}
</style>

<div class="intel-page-wrapper">
    <a href="/dashboard" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        Back to Dashboard
    </a>

    <div class="intel-header">
        <h1>New AI-Assisted Audit</h1>
        <p>Pick the client and channels you want to audit. We'll guide you through collecting the necessary data for deeper AI-driven insights.</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="intel-alert-danger">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <div class="intel-card">
        <div class="intel-card-body">
            <form method="post" action="/audits/intel-store">
                <?php echo $csrfField ?? ''; ?>

                <div class="intel-form-group">
                    <label for="client_id" class="intel-label">Client</label>
                    <select name="client_id" id="client_id" class="intel-select" required>
                        <option value="" disabled selected>Select a client...</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= (int) $client['id'] ?>"><?= htmlspecialchars($client['business_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="intel-form-group">
                    <label for="title" class="intel-label">Audit Title</label>
                    <input type="text" class="intel-input" id="title" name="title" required
                           placeholder="e.g. Q3 Comprehensive Marketing Audit">
                </div>

                <div class="intel-form-group">
                    <label class="intel-label" style="margin-bottom: 1rem;">Channels in Scope</label>
                    <div class="channel-grid">
                        <?php foreach ($channels as $channel): ?>
                        <label class="channel-card">
                            <input type="checkbox" name="channel_ids[]" value="<?= (int) $channel['id'] ?>">
                            <div class="channel-content">
                                <div class="channel-icon-wrapper">
                                    <span class="channel-title"><?= htmlspecialchars($channel['name']) ?></span>
                                    <div class="check-indicator"></div>
                                </div>
                                <div class="channel-desc">
                                    <?= htmlspecialchars($channel['description'] ?? 'Connect and collect intelligence data for ' . $channel['name'] . '.') ?>
                                </div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="intel-form-group" style="margin-bottom: 2.5rem;">
                    <label for="known_entity_names" class="intel-label">Real Campaign/Account Names <span style="font-weight:400; color:#6b7280;">(Optional)</span></label>
                    <input type="text" class="intel-input" id="known_entity_names" name="known_entity_names"
                           placeholder="e.g. Brand - Search, Remarketing - Q3">
                    <span class="intel-help-text">
                        Used to sanity-check AI responses. If a pasted response never mentions any of these real names, we flag it to ensure it's grounded in your actual account data.
                    </span>
                </div>

                <button type="submit" class="intel-btn-primary">
                    Start Audit 
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>
