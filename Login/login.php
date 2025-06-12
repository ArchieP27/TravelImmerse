<?php
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['Username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']); // Plain-text password

    // Validate form input
    if (empty($username) || empty($password)) {
        // Redirect back to the login page with an error message
        header('Location: ../index.html?error=emptyfields');
        exit();
    }

    // Check if the user exists in the database
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Directly compare the password without hashing
        if ($password === $user['password']) {
            // Set session variables with user data
            $_SESSION['user_id'] = $user['id']; // Store user id
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];

            // Redirect to the homepage upon successful login
            header('Location: ../Home/home.php');
            exit();
        } else {
            // Redirect back with an invalid password error
            header('Location: ../index.html?error=invalidpassword');
            exit();
        }
    } else {
        // Redirect back with a "no account found" error
        header('Location: ../index.html?error=usernotfound');
        exit();
    }
}
?>
