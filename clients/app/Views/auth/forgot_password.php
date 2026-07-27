<form action="/forgot-password" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required autofocus>
    </div>
    <button type="submit">Send Reset Link</button>
</form>
<div class="footer-link">
    <a href="/login">Return to Sign In</a>
</div>
