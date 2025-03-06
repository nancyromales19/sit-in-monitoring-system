<?php
session_start();
//Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// Check if the user has the admin role
if ($_SESSION['role'] !== 'admin') {
    header("Location: homepage.php"); // Redirect to homepage if not admin
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>
<body>
    <div class="w3-bar w3-blue">
        <a href="logout.php" class="w3-button w3-hover-red w3-right">LOG OUT</a>
        <span class="w3-bar-item">Admin Dashboard</span>
    </div>
    <div class="w3-container">
        <h1>Welcome, Admin!</h1>
        <p>This is the admin dashboard.</p>
    </div>
</body>
</html>
