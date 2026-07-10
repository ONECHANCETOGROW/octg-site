<?php
/* ==========================================================================
   SETUP.PHP — one-time first Super Admin account creation.
   Refuses to run once ANY admin_users row already exists, so this can't be
   used later to create a rogue account. Delete this file once you've used
   it, or leave it — it self-disables either way.
   ========================================================================== */
require_once __DIR__ . '/../api/_lib.php';

$pdo = octg_db();
if (!$pdo) {
    http_response_code(500);
    die('Database is not configured yet. Copy includes/db-config.example.php to includes/db-config.php and fill in your real Hostinger database credentials first, then import sql/005_admin_users.sql through sql/008_activity_log.sql via phpMyAdmin.');
}

$existingCount = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
if ($existingCount > 0) {
    http_response_code(403);
    die('Setup has already been completed — an admin account already exists. Go to <a href="/admin/login.php">/admin/login.php</a>. If you\'re locked out, an existing admin needs to reset your account directly in the database.');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid name and email address.';
    } elseif (strlen($password) < 10) {
        $error = 'Password must be at least 10 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO admin_users (name, email, password_hash, role) VALUES (:n, :e, :p, :r)');
        $stmt->execute([':n' => $name, ':e' => $email, ':p' => password_hash($password, PASSWORD_DEFAULT), ':r' => 'super_admin']);
        header('Location: /admin/login.php?created=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Setup | One Chance To Grow</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/admin/assets/admin.css">
</head>
<body class="admin-auth-page">
  <div class="admin-auth-card">
    <h1>Create Your Admin Account</h1>
    <p class="admin-auth-sub">This runs once. It will refuse to run again after this account is created.</p>
    <?php if ($error): ?><p class="admin-error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
    <form method="POST">
      <label>Full Name<input type="text" name="name" required></label>
      <label>Email<input type="email" name="email" required></label>
      <label>Password<input type="password" name="password" required minlength="10"></label>
      <label>Confirm Password<input type="password" name="confirm" required minlength="10"></label>
      <button type="submit" class="admin-btn admin-btn-primary">Create Super Admin Account</button>
    </form>
  </div>
</body>
</html>
