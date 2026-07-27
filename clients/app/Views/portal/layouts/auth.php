<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Client Portal - One Chance To Grow'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg-main, #f8fafc);
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh; margin: 0;
        }
        .portal-auth-card {
            background: var(--bg-surface, #fff);
            border: 1px solid var(--border, #e2e8f0);
            border-radius: var(--radius-lg, 12px);
            padding: 48px 40px; width: 100%; max-width: 380px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08);
        }
        .portal-logo { text-align: center; font-size: 20px; font-weight: 700; margin-bottom: 4px; color: var(--text-main, #0f172a); }
        .portal-subtitle { text-align: center; font-size: 14px; color: var(--text-muted, #64748b); margin-bottom: 32px; }
        .portal-auth-card input[type="email"], .portal-auth-card input[type="password"], .portal-auth-card input[type="text"] {
            width: 100%; padding: 10px 12px; border: 1px solid var(--border, #e2e8f0);
            border-radius: 6px; font-size: 14px; box-sizing: border-box; margin-top: 4px;
        }
        .portal-auth-card label { display: block; font-size: 13px; font-weight: 500; color: var(--text-muted, #64748b); margin-bottom: 4px; }
        .portal-auth-card .form-group { margin-bottom: 18px; }
        .portal-auth-card button { width: 100%; margin-top: 8px; }
        .portal-footer-link { text-align: center; margin-top: 20px; font-size: 13px; }
        .portal-footer-link a { color: var(--primary, #0284c7); text-decoration: none; font-weight: 500; }
        .portal-error { background: var(--danger-light, #fee2e2); color: var(--danger-text, #991b1b); padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 18px; }
        .portal-success { background: var(--success-light, #d1fae5); color: var(--success-text, #065f46); padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 18px; }
    </style>
</head>
<body>
    <div class="portal-auth-card">
        <div class="portal-logo">One Chance To Grow</div>
        <div class="portal-subtitle"><?php echo htmlspecialchars($subtitle ?? 'Client Portal'); ?></div>

        <?php if (!empty($error)): ?>
            <div class="portal-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="portal-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php require BASE_PATH . "/app/Views/{$content_view}.php"; ?>
    </div>
</body>
</html>
