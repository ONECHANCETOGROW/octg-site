<form action="/portal/login" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autofocus>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary">Sign In</button>
</form>
<div class="portal-footer-link">
    <a href="/portal/forgot-password">Forgot password?</a>
</div>
