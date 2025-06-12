<?php
// db.php - Database connection using MySQLi
$host = 'localhost';
$dbname = 'travel_immerse';
$user = 'root';   // MySQL username (default for XAMPP is 'root')
$pass = '12345678';       // MySQL password (default for XAMPP is empty)

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    // echo "Database connected successfully";
}
?>
