<?php

// ===== WCZYTANIE .env DO TABLICY =====
$envPath = __DIR__ . '/../.env';
$ENV = [];

if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);

        // usuń BOM
        $line = preg_replace('/^\xEF\xBB\xBF/', '', $line);

        if ($line === '' || $line[0] === '#') continue;
        if (!str_contains($line, '=')) continue;

        [$k, $v] = explode('=', $line, 2);
        $ENV[trim($k)] = trim($v);
    }
}

// ===== WALIDACJA TOKENA =====
if (
    !isset($ENV['WEATHER_CRON_TOKEN']) ||
    !isset($_GET['key']) ||
    !hash_equals($ENV['WEATHER_CRON_TOKEN'], $_GET['key'])
) {
    http_response_code(403);
    echo 'WEATHER_CRON_TOKEN not set or invalid';
    exit;
}

// ===== RESZTA KODU (logger) =====
$weatherUrl  = 'https://pogoda.sielata.com.pl/weather.php';
$historyFile = __DIR__ . '/history.json';

$json = file_get_contents($weatherUrl);
if ($json === false) {
    exit;
}

$data = json_decode($json, true);
if (!$data || !isset($data['temp'])) {
    exit;
}

$history = [];
if (file_exists($historyFile)) {
    $history = json_decode(file_get_contents($historyFile), true) ?? [];
}

$history[] = [
    'time'     => time(),
    'temp'     => $data['temp'],
    'humidity' => $data['humidity'],
    'pressure' => $data['pressure'],
    'wind'     => $data['wind'],
    'rain'     => $data['rain']
];

// 24h = 288 wpisów
$history = array_slice($history, -288);

file_put_contents($historyFile, json_encode($history));
