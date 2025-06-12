<?php
session_start();

// Check if the user is logged in by checking the session variable
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: ../login/index.html");
    exit();
}

// Get the logged-in user's username from the session
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home - Travel Immerse</title>
    <link rel="icon" href="../images/Logo.png" type="image/png">
    <link rel="stylesheet" href="stylehome.css">
    <link rel="stylesheet" href="footerdes.css">
    <link href="https://fonts.googleapis.com/css2?family=Girassol&family=Sigmar&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'header.php'; ?>

<div class="main">
    <!-- Weather Section -->
    <section class="weather">
        <img src="../images/weather-icon.png" alt="Weather Icon">
        <span id="weatherText">Fetching weather...</span>
    </section>

    <section class="hero-section">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <p>Discover destinations that match your mood.</p>
    </section>

    <div class="mood-popup" id="moodPopup">🌍 Pick Your Mood!</div>

    <section class="mood-grid">
        <div class="mood-item" style="background-image: url('../images/beach.jpeg');" data-text="Tropical Escape" data-theme="tropical escape"></div>
        <div class="mood-item" style="background-image: url('../images/mountain.jpg');" data-text="Mountain Calling" data-theme="mountain calling"></div>
        <div class="mood-item" style="background-image: url('../images/snow.jpeg');" data-text="Snowy Wonderland" data-theme="snowy wonderland"></div>
        <div class="mood-item" style="background-image: url('../images/pilgrim.jpeg');" data-text="Spiritual Journey" data-theme="spiritual journey"></div>
        <div class="mood-item" style="background-image: url('../images/forest.jpeg');" data-text="Mystic Forest" data-theme="mystic forest"></div>
        <div class="mood-item" style="background-image: url('../images/desert.jpeg');" data-text="Golden Dunes" data-theme="golden dunes"></div>
        <div class="mood-item" style="background-image: url('../images/historical.jpeg');" data-text="Timeless History" data-theme="timeless history"></div>
        <div class="mood-item" style="background-image: url('../images/islands.jpeg');" data-text="Island Paradise" data-theme="island paradise"></div>
        <div class="mood-item" style="background-image: url('../images/adventure.jpeg');" data-text="Thrill Seeker" data-theme="thrill seeker"></div>
        <div class="mood-item" style="background-image: url('../images/city.jpg');" data-text="Urban Explorer" data-theme="urban explorer"></div>
    </section>
</div>

<?php include 'footer.php'; ?>

<script>
    // Make the mood items clickable and redirect to the destinations.php page with the selected theme
    document.querySelectorAll('.mood-item').forEach(item => {
        item.addEventListener('click', () => {
            const theme = item.getAttribute('data-theme');
            window.location.href = `destinations.php?theme=${encodeURIComponent(theme)}`;
        });
    });
    navigator.geolocation.getCurrentPosition(function(position) {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;

        // Send latitude and longitude to get_weather.php
        fetch('get_weather.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'lat=' + lat + '&lon=' + lon
        })
        .then(response => response.json())
        .then(data => {
            if (data.weather) {
                document.getElementById('weatherText').innerText = data.weather;
            } else {
                document.getElementById('weatherText').innerText = "Weather data unavailable";
            }
        })
        .catch(error => {
            document.getElementById('weatherText').innerText = "Error fetching weather";
            console.error('Error fetching weather:', error);
        });
    }, function(error) {
        document.getElementById('weatherText').innerText = "Unable to fetch location";
        console.error('Error fetching location:', error);
    });
</script>

</body>
</html>
