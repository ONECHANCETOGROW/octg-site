<form action="/login" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <div class="form-group">
        <label for="identifier">Email or Username</label>
        <input type="text" id="identifier" name="identifier" required autofocus>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit">Sign In</button>
</form>
<div class="footer-link">
    <a href="/forgot-password">Forgot password?</a>
</div>
