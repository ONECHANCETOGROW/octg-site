<div class="page-header">
    <div>
        <h1 class="page-title">Clients</h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Manage your agency clients and their marketing intelligence.</p>
    </div>
    <div class="page-actions">
        <a href="/clients/create" class="btn btn-primary">
            <i data-lucide="plus" width="16" style="margin-right: 8px;"></i> New Client
        </a>
    </div>
</div>

<?php if (isset($_GET['login_created'])): ?>
<div class="card" style="border-left: 4px solid var(--success,#10b981);">
    <div class="card-body">
        <strong>Portal login ready for <?php echo htmlspecialchars($_GET['email'] ?? ''); ?></strong>
        <p style="margin: 8px 0 0; font-size: 13px;">
            Login URL: <code><?php echo htmlspecialchars($_GET['login_url'] ?? '/portal/login'); ?></code><br>
            Temporary password: <code><?php echo htmlspecialchars($_GET['temp_password'] ?? ''); ?></code>
        </p>
        <p style="margin: 8px 0 0; font-size: 12px; color: var(--text-muted);">
            No email service is wired in yet -- relay these credentials to the client yourself. They'll be forced to set their own password on first login.
        </p>
    </div>
</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="card" style="border-left: 4px solid var(--danger,#ef4444);"><div class="card-body"><?php echo htmlspecialchars($_GET['error']); ?></div></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; gap: 12px; align-items: center; width: 100%; max-width: 400px;">
            <div style="position: relative; flex: 1;">
                <i data-lucide="search" width="16" style="position: absolute; left: 12px; top: 12px; color: var(--text-muted);"></i>
                <input type="text" class="form-control" placeholder="Search clients..." style="padding-left: 36px;">
            </div>
            <button class="btn btn-secondary">
                <i data-lucide="filter" width="16" style="margin-right: 8px;"></i> Filter
            </button>
        </div>
    </div>
    
    <?php if (empty($clients)): ?>
        <div class="empty-state">
            <i data-lucide="users" class="empty-icon"></i>
            <h3 class="empty-title">No clients found</h3>
            <p class="empty-desc">Get started by adding your first client to generate their marketing intelligence baseline.</p>
            <a href="/clients/create" class="btn btn-primary" style="margin-top: 16px;">Add Client</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Industry</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Client Management</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                    <?php $portalUsers = $portalAccessByClientId[$client['id']] ?? []; ?>
                    <tr>
                        <td>
                            <div style="font-weight: 500; color: var(--text-main);"><?php echo htmlspecialchars($client['business_name']); ?></div>
                            <?php if ($client['website']): ?>
                                <a href="<?php echo htmlspecialchars($client['website']); ?>" target="_blank" style="font-size: 12px; color: var(--primary); text-decoration: none;"><?php echo htmlspecialchars($client['website']); ?></a>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($client['industry'] ?? '-'); ?></td>
                        <td>
                            <div><?php echo htmlspecialchars($client['contact_person'] ?? '-'); ?></div>
                            <div style="font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($client['email'] ?? ''); ?></div>
                        </td>
                        <td>
                            <?php if ($client['status'] === 'active'): ?>
                                <span class="badge badge-success">Active</span>
                            <?php elseif ($client['status'] === 'onboarding'): ?>
                                <span class="badge badge-warning">Onboarding</span>
                            <?php else: ?>
                                <span class="badge badge-neutral">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td style="min-width: 320px;">
                            <?php 
                            $portalUrl = 'https://clients.onechancetogrow.com/client/' . htmlspecialchars($client['slug']) . '/dashboard';
                            ?>
                            <!-- URL row -->
                            <div style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-surface-hover); border: 1px solid var(--border); border-radius: 6px; padding: 4px 8px; margin-bottom: 12px;">
                                <div style="font-family: monospace; font-size: 11px; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 220px;">
                                    <?php echo $portalUrl; ?>
                                </div>
                                <div style="display: flex; gap: 4px;">
                                    <button onclick="copyToClipboard('<?php echo $portalUrl; ?>')" class="btn btn-secondary" style="padding: 2px 6px; font-size: 11px; height: 24px;" title="Copy Link">Copy</button>
                                    <a href="<?php echo $portalUrl; ?>" target="_blank" class="btn btn-primary" style="padding: 2px 8px; font-size: 11px; height: 24px;">Open Portal</a>
                                </div>
                            </div>

                            <!-- User row -->
                            <?php if (empty($portalUsers)): ?>
                                <form action="/clients/portal-login/create" method="POST" style="display:flex;gap:6px;">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                                    <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                                    <input type="email" name="email" placeholder="client@email.com" required class="form-control" style="font-size:12px;padding:4px 8px;height:28px;flex:1;">
                                    <button type="submit" class="btn btn-secondary" style="padding:4px 12px;font-size:12px;height:28px;">Create Login</button>
                                </form>
                            <?php else: foreach ($portalUsers as $pu): ?>
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                    <div style="font-size: 12px; font-weight: 500; display:flex; align-items:center; gap:6px; color: var(--text-main);">
                                        <?php echo htmlspecialchars($pu['email']); ?>
                                        <button class="btn btn-secondary" style="padding: 2px; border: none; background: transparent; color: var(--text-muted);" title="Edit Email" onclick="openEditEmailModal(<?php echo $pu['id']; ?>, '<?php echo htmlspecialchars($pu['email'], ENT_QUOTES); ?>')">
                                            <i data-lucide="edit-3" width="12"></i>
                                        </button>
                                        <span class="badge badge-<?php echo $pu['is_active'] ? 'success' : 'neutral'; ?>" style="font-size:10px; padding:2px 4px;"><?php echo $pu['is_active'] ? 'Active' : 'Disabled'; ?></span>
                                    </div>
                                    <div style="display: flex; gap: 4px;">
                                        <button onclick="openShareModal('<?php echo $pu['id']; ?>', '<?php echo $portalUrl; ?>', '<?php echo htmlspecialchars($pu['email'], ENT_QUOTES); ?>')" class="btn btn-secondary" style="padding: 2px 8px; font-size: 11px; height: 24px;">Share</button>
                                        <form action="/clients/portal-login/reset" method="POST" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                                            <input type="hidden" name="client_user_id" value="<?php echo $pu['id']; ?>">
                                            <button type="submit" class="btn btn-secondary" style="padding:2px 8px;font-size:11px;height:24px;">Reset PW</button>
                                        </form>
                                        <form action="/clients/portal-login/toggle" method="POST" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                                            <input type="hidden" name="client_user_id" value="<?php echo $pu['id']; ?>">
                                            <input type="hidden" name="active" value="<?php echo $pu['is_active'] ? '0' : '1'; ?>">
                                            <button type="submit" class="btn btn-secondary" style="padding:2px 8px;font-size:11px;height:24px;color:<?php echo $pu['is_active'] ? 'var(--danger-text)' : 'inherit'; ?>"><?php echo $pu['is_active'] ? 'Disable' : 'Enable'; ?></button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; endif; ?>
                            
                            <div style="margin-top: 12px; border-top: 1px solid var(--border); padding-top: 12px; display: flex; gap: 8px;">
                                <a href="/clients/portal-data?id=<?php echo $client['id']; ?>" class="btn btn-secondary" style="flex: 1; justify-content: center; font-size: 12px; padding: 4px; height: 28px;">
                                    <i data-lucide="database" width="14" style="margin-right:6px;"></i> Marketing Workspace
                                </a>
                                <a href="/clients/modules?id=<?php echo $client['id']; ?>" class="btn btn-secondary" style="font-size: 12px; padding: 4px 8px; height: 28px;" title="Manage Modules">
                                    <i data-lucide="settings" width="14"></i>
                                </a>
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 8px;">
                                <a href="/clients/edit?id=<?php echo $client['id']; ?>" class="btn btn-secondary" style="padding: 6px;">
                                    <i data-lucide="edit-2" width="14"></i>
                                </a>
                                <form action="/clients/delete" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this client?');">
                                    <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                                    <button type="submit" class="btn btn-secondary" style="padding: 6px; color: var(--danger-text);">
                                        <i data-lucide="trash-2" width="14"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div id="shareAccessModal" class="modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 50; align-items: center; justify-content: center;">
    <div class="modal-content card" style="width: 100%; max-width: 480px; margin: auto; position: relative;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 16px;">
            <h3 style="margin: 0; font-size: 16px;">Share Client Access</h3>
            <button class="btn btn-secondary" style="padding: 4px; border: none; background: transparent;" onclick="closeShareModal()">
                <i data-lucide="x" width="16"></i>
            </button>
        </div>
        <div class="card-body" style="padding-top: 24px;">
            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 500; color: var(--text-muted); margin-bottom: 6px; display: block;">Client Portal URL</label>
                <input type="text" id="sharePortalUrl" class="form-control" readonly style="font-family: monospace; font-size: 12px; width: 100%; background: var(--bg-surface-hover);">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 500; color: var(--text-muted); margin-bottom: 6px; display: block;">Login Email</label>
                <input type="text" id="shareLoginEmail" class="form-control" readonly style="font-family: monospace; font-size: 12px; width: 100%; background: var(--bg-surface-hover);">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 500; color: var(--text-muted); margin-bottom: 6px; display: block;">Temporary Password</label>
                <div style="font-size: 13px; color: var(--text-muted); background: var(--bg-surface-hover); padding: 12px; border-radius: 6px; line-height: 1.5; border: 1px solid var(--border);">
                    Passwords are not stored for security reasons. If the client needs a new password, use the <strong>Reset PW</strong> action on the client row.
                </div>
            </div>
            
            <div style="display: flex; gap: 12px; margin-top: 32px;">
                <button class="btn btn-primary" style="flex: 1; justify-content: center;" onclick="copyCredentials()">
                    <i data-lucide="copy" width="16" style="margin-right: 8px;"></i> Copy Credentials
                </button>
                <button class="btn btn-secondary" style="flex: 1; justify-content: center;" id="btnSendCredentials" onclick="sendCredentials()">
                    <i data-lucide="send" width="16" style="margin-right: 8px;"></i> Send Credentials
                </button>
            </div>
        </div>
    </div>
</div>

<div id="editEmailModal" class="modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 50; align-items: center; justify-content: center;">
    <div class="modal-content card" style="width: 100%; max-width: 480px; margin: auto; position: relative;">
        <form action="/clients/update-email" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
            <input type="hidden" name="client_user_id" id="editEmailUserId" value="">
            
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 16px;">
                <h3 style="margin: 0; font-size: 16px;">Edit Portal Email</h3>
                <button type="button" class="btn btn-secondary" style="padding: 4px; border: none; background: transparent;" onclick="closeEditEmailModal()">
                    <i data-lucide="x" width="16"></i>
                </button>
            </div>
            <div class="card-body" style="padding-top: 24px;">
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 500; color: var(--text-muted); margin-bottom: 6px; display: block;">Login Email</label>
                    <input type="email" name="new_email" id="editEmailInput" class="form-control" required style="width: 100%;">
                </div>
                
                <div style="display: flex; justify-content: flex-end; margin-top: 32px;">
                    <button type="submit" class="btn btn-primary">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let currentShareUserId = null;

function openShareModal(userId, url, email) {
    currentShareUserId = userId;
    document.getElementById('sharePortalUrl').value = url;
    document.getElementById('shareLoginEmail').value = email || 'No login created yet';
    document.getElementById('shareAccessModal').style.display = 'flex';
}

function closeShareModal() {
    document.getElementById('shareAccessModal').style.display = 'none';
    currentShareUserId = null;
}

function openEditEmailModal(userId, email) {
    document.getElementById('editEmailUserId').value = userId;
    document.getElementById('editEmailInput').value = email;
    document.getElementById('editEmailModal').style.display = 'flex';
}

function closeEditEmailModal() {
    document.getElementById('editEmailModal').style.display = 'none';
}

function copyCredentials() {
    const url = document.getElementById('sharePortalUrl').value;
    const email = document.getElementById('shareLoginEmail').value;
    const text = `Client Portal: ${url}\nEmail: ${email}\nPassword: Use the temporary password provided by your account manager.`;
    navigator.clipboard.writeText(text).then(() => {
        showToast('✓ Credentials Copied');
        closeShareModal();
    });
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('✓ Client Portal Link Copied');
    });
}

function sendCredentials() {
    if (!currentShareUserId) return;
    const btn = document.getElementById('btnSendCredentials');
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader" width="16" class="spin" style="margin-right: 8px;"></i> Sending...';
    lucide.createIcons();
    
    fetch('/clients/send-invite', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'csrf_token=<?php echo urlencode($csrf_token ?? ""); ?>&client_user_id=' + encodeURIComponent(currentShareUserId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('✓ Credentials Sent Successfully');
            closeShareModal();
        } else {
            alert('Error: ' + (data.error || 'Failed to send'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('An error occurred. Check the console.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="send" width="16" style="margin-right: 8px;"></i> Send Credentials';
        lucide.createIcons();
    });
}

function showToast(msg) {
    const toast = document.createElement('div');
    toast.textContent = msg;
    toast.style.position = 'fixed';
    toast.style.bottom = '24px';
    toast.style.right = '24px';
    toast.style.background = 'var(--success, #10b981)';
    toast.style.color = '#fff';
    toast.style.padding = '12px 24px';
    toast.style.borderRadius = '6px';
    toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    toast.style.zIndex = '9999';
    toast.style.fontWeight = '500';
    toast.style.transition = 'opacity 0.3s ease';
    document.body.appendChild(toast);
    setTimeout(() => { 
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>
