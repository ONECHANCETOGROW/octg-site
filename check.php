<?php
$issues = [];
$dir = new RecursiveDirectoryIterator(__DIR__);
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/\.php$/', RegexIterator::GET_MATCH);
foreach($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    if(preg_match_all("/(?:include|require)(?:_once)?\s*(?:__DIR__\s*\.\s*)?['\"]([^'\"]+)['\"]/", $content, $matches)) {
        foreach($matches[1] as $inc) {
            $checkPath = dirname($path) . '/' . $inc;
            if(strpos($inc, '/') === 0) {
                 $checkPath = __DIR__ . $inc;
            }
            if (!file_exists($checkPath)) {
                $issues[] = "BROKEN INCLUDE: $inc in $path (Resolved to: $checkPath)";
            } else {
                // Check case sensitivity
                $real = realpath($checkPath);
                $basename = basename($inc);
                if (basename($real) !== $basename) {
                    $issues[] = "CASE SENSITIVITY: $inc in $path (Actual: " . basename($real) . ")";
                }
            }
        }
    }
}
print_r($issues);
