<div class="page-header">
    <div>
        <h1 class="page-title"><?php echo isset($client) ? 'Edit Client' : 'Add Client'; ?></h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Provide the core details to set up the marketing baseline.</p>
    </div>
</div>

<div class="card" style="max-width: 800px;">
    <form action="<?php echo isset($client) ? '/clients/update' : '/clients/store'; ?>" method="POST">
        <?php if (isset($client)): ?>
            <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
        <?php endif; ?>
        
        <div class="card-body">
            <h3 style="margin-bottom: 24px; font-size: 16px;">Business Information</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="business_name">Business Name *</label>
                    <input type="text" class="form-control" id="business_name" name="business_name" value="<?php echo htmlspecialchars($client['business_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="website">Website</label>
                    <input type="url" class="form-control" id="website" name="website" placeholder="https://" value="<?php echo htmlspecialchars($client['website'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="industry">Industry</label>
                    <input type="text" class="form-control" id="industry" name="industry" value="<?php echo htmlspecialchars($client['industry'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="active" <?php echo (isset($client) && $client['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="onboarding" <?php echo (isset($client) && $client['status'] === 'onboarding') ? 'selected' : ''; ?>>Onboarding</option>
                        <option value="inactive" <?php echo (isset($client) && $client['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
            
            <hr style="border: none; border-top: 1px solid var(--border); margin: 32px 0;">
            <h3 style="margin-bottom: 24px; font-size: 16px;">Primary Contact</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label" for="contact_person">Contact Person</label>
                    <input type="text" class="form-control" id="contact_person" name="contact_person" value="<?php echo htmlspecialchars($client['contact_person'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($client['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($client['phone'] ?? ''); ?>">
                </div>
            </div>
        </div>
        
        <div class="card-header" style="background: var(--bg-body); border-top: 1px solid var(--border); border-bottom: none; display: flex; justify-content: flex-end; gap: 12px;">
            <a href="/clients" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><?php echo isset($client) ? 'Save Changes' : 'Create Client'; ?></button>
        </div>
    </form>
</div>
