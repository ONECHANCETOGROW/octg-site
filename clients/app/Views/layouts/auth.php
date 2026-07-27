<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'OCTG Intelligence'); ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        :root {
            --bg: #0a0a0a;
            --surface: #171717;
            --border: #262626;
            --text-main: #f5f5f5;
            --text-muted: #a3a3a3;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
        }
        
        body {
            background-color: var(--bg);
            color: var(--text-main);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-image: radial-gradient(circle at top, #1e3a8a33 0%, transparent 40%);
        }
        
        .auth-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 48px 40px;
            width: 100%;
            max-width: 360px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .logo {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.025em;
        }
        
        .subtitle {
            text-align: center;
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 32px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
        }
        
        input {
            width: 100%;
            padding: 10px 12px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text-main);
            font-size: 14px;
            box-sizing: border-box;
            transition: all 0.2s;
        }
        
        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 1px var(--primary);
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 8px;
        }
        
        button:hover {
            background: var(--primary-hover);
        }
        
        .error {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #ef4444;
            color: #fca5a5;
            padding: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .success {
            background: rgba(34, 197, 94, 0.1);
            border-left: 4px solid #22c55e;
            color: #86efac;
            padding: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .footer-link {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
        }
        .footer-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        .footer-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="logo">OCTG Intelligence</div>
        <div class="subtitle"><?php echo htmlspecialchars($subtitle ?? 'Sign in to your account'); ?></div>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php require BASE_PATH . "/app/Views/{$content_view}.php"; ?>
    </div>
</body>
</html>
