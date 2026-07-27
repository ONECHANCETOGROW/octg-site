<?php
require_once __DIR__ . '/config/config.php';
$pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->query("SELECT id, created_at, status FROM mi_audits ORDER BY id DESC LIMIT 5");
$audits = $stmt->fetchAll(PDO::FETCH_ASSOC);
$latestId = $audits[0]['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM mi_knowledge_facts WHERE audit_id = ?");
$stmt->execute([$latestId]);
$facts = $stmt->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode([
    'audits' => $audits,
    'facts' => $facts
]);
