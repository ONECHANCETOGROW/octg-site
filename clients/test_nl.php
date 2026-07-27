<?php
define('BASE_PATH', __DIR__);
require_once __DIR__ . '/config/config.php';
// require_once __DIR__ . '/index.php'; 
require_once __DIR__ . '/app/Services/AIParser/NLParser.php';
$parser = new \App\Services\AIParser\NLParser();
$text = "Total Spend (Budget Used): $12,486.44\nTotal Clicks (Traffic Received): 1,188\nImpressions: 13385\nConversions: 211.28\nCpa: 11.67\nConversion Rate: 17.78";
print_r($parser->parse($text));
