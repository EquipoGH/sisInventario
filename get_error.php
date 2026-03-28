<?php
$logPath = __DIR__ . '/storage/logs/laravel.log';
if (!file_exists($logPath)) {
    echo "No log file found.";
    exit;
}
$log = file($logPath);
$errors = array_filter($log, function($line) {
    return strpos($line, 'ERROR:') !== false || strpos($line, 'Exception') !== false;
});
$lastError = array_slice($errors, -5);
foreach($lastError as $err) {
    echo trim($err) . "\n";
}
