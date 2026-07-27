<form action="/portal/forgot-password" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autofocus>
    </div>
    <button type="submit" class="btn btn-primary">Send Reset Link</button>
</form>
<div class="portal-footer-link">
    <a href="/portal/login">Back to login</a>
</div>
