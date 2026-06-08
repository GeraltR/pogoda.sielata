<?php

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/cron_error.log');
error_reporting(E_ALL);

echo "start\n";
// ... reszta kodu

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

$json = file_get_contents($weatherUrl);
if ($json === false) exit;

$data = json_decode($json, true);
if (!$data || !isset($data['temp'])) exit;

// zamiast file_put_contents...
$payload = json_encode([
    'temp'     => $data['temp'],
    'humidity' => $data['humidity'],
    'pressure' => $data['pressure'],
    'wind'     => $data['wind'],
    'rain'     => $data['rain'],
]);

$laravelUrl = 'https://api.sielata.com.pl/public/api/weather/log?key=' . $ENV['WEATHER_CRON_TOKEN'];

$ch = curl_init($laravelUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
]);
curl_exec($ch);
curl_close($ch);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP: $httpCode\n";
echo "Response: $response\n";