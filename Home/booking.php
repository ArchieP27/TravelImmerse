<?php
session_start();
require('../db.php'); // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user bookings from the database
$query = "SELECT id, ticket_path, booking_date FROM tickets WHERE user_id = ? ORDER BY booking_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | Travel Immerse</title>
    <link rel="stylesheet" href="stylehome.css">
    <link rel="stylesheet" href="footerdes.css">
    <link href="https://fonts.googleapis.com/css2?family=Girassol&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Girassol', sans-serif;
            background-color: #fff8f0;
            margin: 0;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }
        h2 {
            text-align: center;
            color: #d35400;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 18px;
        }
        th, td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #ff7f00;
            color: white;
            font-size: 20px;
        }
        tr:hover {
            background: #ffe6cc;
        }
        .download-btn {
            background: #ff6600;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: 0.3s;
        }
        .download-btn:hover {
            background: #cc5200;
        }
        .home-btn {
            display: block;
            width: 220px;
            margin: 30px auto;
            text-align: center;
            padding: 12px;
            background: #ff7f00;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
            transition: 0.3s;
        }
        .home-btn:hover {
            background: #cc6600;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h2>My Bookings</h2>
        <?php if ($result->num_rows > 0) : ?>
            <table>
                <tr>
                    <th>#</th>
                    <th>Date of Booking</th>
                    <th>Download Ticket</th>
                </tr>
                <?php 
                $sno = 1;
                while ($row = $result->fetch_assoc()) : 
                ?>
                    <tr>
                        <td><?= $sno++; ?></td>
                        <td><?= date("d M Y, h:i A", strtotime($row['booking_date'])); ?></td>
                        <td><a href="<?= $row['ticket_path']; ?>" class="download-btn" target="_blank">Download</a></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else : ?>
            <p style="text-align: center; color: #555; font-size: 18px;">No bookings found.</p>
        <?php endif; ?>
        
        <a href="home.php" class="home-btn">Return to Home</a>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
