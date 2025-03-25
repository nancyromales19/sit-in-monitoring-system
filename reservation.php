<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include('database.php');

$username = $_SESSION['username'];

$query = "SELECT user_id, role FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$role = $row['role'];

if ($role === 'admin') {
    header("Location: admin.php");
    exit();
} else if ($role = 'student'){
    
} else {
    header("Location: login.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="ie-edge">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <style>
        body {
            background-color: #cfe2f3;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .bar {
            width: 100%;
            background-color: #002244;
            padding: 10px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }
        .bar .w3-bar-item, .bar a {
            color: white;
        }
		.bar {
			display: flex;
			align-items: center;
			height: 70px;
			padding: 0 20px;
		}
    </style>
</head>
<body>
    <div class="bar w3-container w3-bar w3-blue">
        <div class="w3-bar-item"><b>RESERVATION</b></div>
		<div class="w3-bar w3-right">
		<a href="logout.php" class="w3-button w3-hover-red w3-right">LOG OUT</a>
		<a class="w3-bar-item w3-button w3-hover-white w3-right">NOTIFICATION</a>
		<a href="history.php" class="w3-bar-item w3-button w3-hover-white w3-right">HISTORY</a>
		<a class="w3-bar-item w3-button w3-hover-white w3-right">RESERVATION</a>
		<a href="profile.php" class="w3-bar-item w3-button w3-hover-white w3-right">PROFILE</a>
		<a href="homepage.php" class="w3-bar-item w3-button w3-hover-white w3-right">HOME</a>
		</div>
    </div>
	<br><br>
	<div class="w3-center"><h2><b>RESERVATION</b></h2></div>

    
</body>
</html>
