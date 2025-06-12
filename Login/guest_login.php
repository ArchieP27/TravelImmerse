<?php
session_start();
$_SESSION['user_id'] = 'guest'; // or 0, or any identifier you prefer
header("Location: ../Home/home.php");
exit();
?>
