<?php
// Start session and include the database connection file
session_start();
include '../db.php'; // Ensure db.php does not echo "database connected successfully"

// Get the theme from the URL
$theme = mysqli_real_escape_string($conn, $_GET['theme']);

// Heading based on the theme
$headings = [
    'tropical escape' => '🌴 Tropical Escape : Explore Indian Beaches',
    'mountain calling' => '⛰️ Mountain Majesty : Discover Indian Peaks',
    'golden dunes' => '🏜 Desert Wonders : Explore the Sands of India',
    'urban explorer' => '🏙 Urban Adventures : Explore India’s Cities'
];

// Set the dynamic heading based on the theme, defaulting to "Destinations"
$pageHeading = isset($headings[$theme]) ? $headings[$theme] : 'Explore Indian Destinations';

// Fetch all destinations based on the selected theme
$query = "SELECT * FROM destinations WHERE theme='$theme'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($theme); ?> - Destinations</title>
    <link rel="icon" href="../images/Logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Girassol&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="footerdes.css">
</head>
<body>
    <?php include "header.php"; ?>

    <!-- Main Scrollable Content -->
    <div class="main">

        <!-- Page Heading -->
        <h1 style="text-align: center; color: #D35400; margin: 15px 0; font-size: 2rem;">
            <?php echo $pageHeading; ?>
        </h1>

        <!-- Destination Grid -->
        <div class="destination-grid">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <!-- Destination Item -->
                    <div class="destination-item">
                        <a href="place.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
                            <img src="../<?php echo $row['image_path']; ?>" alt="<?php echo $row['name']; ?>">
                            <div class="destination-info">
                                <h2><?php echo $row['name']; ?> 
                                    <span class="rating"><?php echo $row['rating']; ?> ★</span>
                                </h2>
                                <div class="location"><?php echo $row['location']; ?></div>
                                <p class="description"><?php echo $row['description']; ?></p>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: red;">No destinations found for <?php echo ucfirst($theme); ?>!</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include "footer.php"; ?>

</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
