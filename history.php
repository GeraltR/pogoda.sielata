<?php
$logFile = __DIR__ . '/history.json';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');


if (!file_exists($logFile)) {
    echo json_encode([]);
    exit;
}

echo file_get_contents($logFile);
