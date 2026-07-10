<?php
/* ==========================================================================
   LOGIN.PHP — admin authentication.
   ========================================================================== */
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../api/_lib.php';

if (!empty($_SESSION['admin_user'])) {
    header('Location: /admin/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $pdo = octg_db();

    if (!$pdo) {
        $error = 'The database isn\'t connected yet — see includes/db-config.example.php.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE email = :e AND is_active = 1 LIMIT 1');
        $stmt->execute([':e' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'Incorrect email or password.';
        } else {
            session_regenerate_id(true); // prevent session fixation
            $_SESSION['admin_user'] = ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']];
            $pdo->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = :id')->execute([':id' => $user['id']]);
            try {
                $pdo->prepare('INSERT INTO activity_log (action, description, actor) VALUES (:a, :d, :actor)')
                    ->execute([':a' => 'Admin login', ':d' => $user['email'], ':actor' => $user['name']]);
            } catch (Throwable $e) {}
            header('Location: /admin/index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | One Chance To Grow</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/admin/assets/admin.css">
</head>
<body class="admin-auth-page">
  <div class="admin-auth-card">
    <h1>Admin Login</h1>
    <p class="admin-auth-sub">One Chance To Grow</p>
    <?php if (isset($_GET['created'])): ?><p class="admin-success">Account created — sign in below.</p><?php endif; ?>
    <?php if ($error): ?><p class="admin-error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
    <form method="POST">
      <label>Username / Email<input type="text" name="email" required autofocus></label>
      <label>Password<input type="password" name="password" required></label>
      <button type="submit" class="admin-btn admin-btn-primary">Sign In</button>
    </form>
  </div>
</body>
</html>
