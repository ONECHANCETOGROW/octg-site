<?php
$data = [];
// Try multiple possible storage paths
$paths = [
    __DIR__ . '/storage',
    dirname(__DIR__) . '/storage',
    dirname(dirname(__DIR__)) . '/storage'
];

$foundFiles = [];
foreach ($paths as $p) {
    $pattern = $p . '/clients/1/*/09-contract/intelligence.json';
    $files = glob($pattern);
    if ($files) {
        $foundFiles = array_merge($foundFiles, $files);
    }
}

foreach($foundFiles as $file) {
    $json = file_get_contents($file);
    $decoded = json_decode($json, true);
    $data[] = [
        'path' => $file,
        'statistics' => $decoded['knowledge']['statistics'] ?? null,
        'campaigns' => count($decoded['knowledge']['entities']['campaigns'] ?? [])
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'tried_paths' => $paths,
    'files_found' => $foundFiles,
    'data' => $data
], JSON_PRETTY_PRINT);
