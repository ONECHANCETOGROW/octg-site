<?php
require_once __DIR__ . '/api/_lib.php';
$pdo = octg_db();
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare('REPLACE INTO admin_users (id, name, email, password_hash, role, is_active, created_at) VALUES (1, "Admin", "admin", :h, "admin", 1, NOW())');
$stmt->execute([':h' => $hash]);
echo 'SUCCESS';
unlink(__FILE__); // self delete
?>
