<form action="/portal/change-password" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <div class="form-group">
        <label for="current_password">Current Password</label>
        <input type="password" id="current_password" name="current_password">
        <div style="font-size:12px;color:var(--text-muted,#64748b);margin-top:4px;">Leave blank if this is your first login with a temporary password.</div>
    </div>
    <div class="form-group">
        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required minlength="8" autofocus>
    </div>
    <div class="form-group">
        <label for="confirm_password">Confirm New Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
    </div>
    <button type="submit" class="btn btn-primary">Update Password</button>
</form>
