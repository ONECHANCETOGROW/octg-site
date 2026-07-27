<form action="/portal/reset-password" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">
    <div class="form-group">
        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required minlength="8" autofocus>
    </div>
    <div class="form-group">
        <label for="confirm_password">Confirm New Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
    </div>
    <button type="submit" class="btn btn-primary">Set New Password</button>
</form>
