<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Core/Database.php';

\ = \Database::getInstance();
\ = \->query('SELECT overall_completeness, overall_confidence, status, reachable_tier FROM mi_audits WHERE id = 2');
\ = \->fetch(PDO::FETCH_ASSOC);
echo '<pre>'; print_r(\); echo '</pre>';

