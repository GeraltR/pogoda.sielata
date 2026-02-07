<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aktualna Pogoda SieLata</title>
</head>
<body>
<div id="weather">
  <div>🌡 Temperatura: <span id="temp"></span> °C</div>
  <div>💧 Wilgotność: <span id="hum"></span> %</div>
  <div>🔽 Ciśnienie: <span id="pres"></span> hPa</div>
  <div>🌬 Wiatr: <span id="wind"></span> km/h</div>
  <div>🌧 Opad: <span id="rain"></span> mm/h</div>
  <div>🕒 Aktualizacja: <span id="time"></span></div>
</div>

<script>
fetch('weather.php')
  .then(r => r.json())
  .then(d => {
    document.getElementById('temp').textContent = d.temp;
    document.getElementById('hum').textContent = d.humidity;
    document.getElementById('pres').textContent = d.pressure;
    document.getElementById('wind').textContent = d.wind;
    document.getElementById('rain').textContent = d.rain;
    document.getElementById('time').textContent = d.time;
  });
</script>
</body>
</html>
