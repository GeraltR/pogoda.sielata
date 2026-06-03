<?php

$apiKey = 'b950b8b9c35c49cd90b8b9c35ca9cd84';
$stationId = 'IJAWOR79';

$cacheFile = __DIR__ . '/weather_cache.json';
$cacheTtl  = 300; // 5 minut

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Access-Control-Allow-Origin: https://sielata.com.pl');

// Jeśli cache jest świeży — użyj go
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
    $response = file_get_contents($cacheFile);
} else {

    $url = "https://api.weather.com/v2/pws/observations/current"
         . "?stationId={$stationId}"
         . "&format=json"
         . "&units=m"
         . "&apiKey={$apiKey}";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        die('cURL error: ' . curl_error($ch));
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        die("HTTP error: $httpCode<br>$response");
    }

    file_put_contents($cacheFile, $response);
}

$data = json_decode($response, true);

if (!isset($data['observations'][0])) {
    die('No weather data');
}

$obs = $data['observations'][0];

$result = [
    'temp'       => $obs['metric']['temp'],
    'humidity'   => $obs['humidity'],
    'pressure'   => $obs['metric']['pressure'],
    'wind'       => $obs['metric']['windSpeed'],
    'windDir'    => $obs['winddir'],
    'rain'       => $obs['metric']['precipRate'],
    'time'       => date('Y-m-d H:i', strtotime($obs['obsTimeUtc']))
];

header('Content-Type: application/json');
echo json_encode($result);
