<?php

session_start(); // If you're using sessions

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include('../db.php');

// Receive and sanitize POST data
$full_name = mysqli_real_escape_string($conn, $_POST['name']);
$username = mysqli_real_escape_string($conn, $_POST['username']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = mysqli_real_escape_string($conn, $_POST['password']);

// Check if all fields are filled
if ($full_name && $username && $email && $password) {
    // Insert query to add the user to the database
    $query = "INSERT INTO users (name, username, email, password) VALUES ('$full_name', '$username', '$email', '$password')";
    
    if (mysqli_query($conn, $query)) {
        // If the query is successful
        echo "
            <script>
                alert('Account created successfully!');
                window.opener.location.reload(); // Refresh the parent window (login page)
                window.close(); // Close the registration popup window
            </script>
        ";
    } else {
        echo "
            <script>
                alert('Error: Unable to create account. Please try again.');
                window.close(); // Still close the window to avoid keeping it open
            </script>
        ";
    }
} else {
    // If fields are missing
    echo "
        <script>
            alert('Please fill out all fields.');
        </script>
    ";
}

// Close the database connection
mysqli_close($conn);
?>
