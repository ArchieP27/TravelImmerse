<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lat = $_POST['lat'];
    $lon = $_POST['lon'];

    // Check if latitude and longitude are provided
    if (!$lat || !$lon) {
        echo json_encode(['error' => 'Coordinates not provided']);
        exit();
    }

    $apiKey = '3e7b0588371150c0abb133d0286569a3'; // Your OpenWeatherMap API key
    $weatherUrl = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&units=metric&appid={$apiKey}";

    // Fetch the weather data from OpenWeatherMap
    $weatherData = @file_get_contents($weatherUrl);

    // If we can't retrieve weather data
    if ($weatherData === FALSE) {
        echo json_encode(['error' => 'Error fetching weather data']);
        exit();
    }

    $weather = json_decode($weatherData, true);

    // Check if the weather data is valid
    if (isset($weather['weather'][0]['description'])) {
        $weatherDescription = $weather['weather'][0]['description'];
        $temperature = $weather['main']['temp']; // Temperature in Celsius

        // Send weather description and temperature as JSON
        echo json_encode(['weather' => ucfirst($weatherDescription) . ", " . $temperature . "°C"]);
    } else {
        echo json_encode(['error' => 'Invalid weather data']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
