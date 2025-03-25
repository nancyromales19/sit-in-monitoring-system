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

// Fetch announcements from the database
$query = "SELECT a.announce_id, a.content, a.date, u.username 
          FROM announcement a 
          JOIN users u ON a.user_id = u.user_id 
          ORDER BY a.date DESC";
$result = $conn->query($query);
$announcements = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <style>
        body {
            height: 100%;
            margin: 0;
            background-color: #cfe2f3;
        }
        .line {
            border-top: 2px solid #7DF9FF;
            margin-top: 10px; 
            margin-bottom: 10px; 
        }
        .underline {
            border-top: 1px solid #7DF9FF;
            margin-top: 10px; 
            margin-bottom: 10px; 
        }
        .regulations {
            background-color: #0039a6;
            color: white; 
            padding: 10px; 
            font-weight: bold; 
            font-size: 18px; 
            width: 300px;
            margin: 0 auto;
            border: 2px solid #7DF9FF; 
            border-radius: 10px;
        }
        .w3-card-4 {
            background-color: #002244; 
            color: #ffffff; /* white text */
            border: 3px solid #0066b2; 
            border-radius: 10px;
        }
        .reg  {
            height: 450px;
            overflow-y: scroll; 
        }
        .reg ::-webkit-scrollbar {
            display: none;
        }
        .reg {
            -ms-overflow-style: auto;  
            scrollbar-width: auto;  
        }
        .notified1  {
            height: 450px;
            overflow-y: scroll; 
        }
        .notified ::-webkit-scrollbar {
            display: block;
        }
        .notified {
            -ms-overflow-style: auto;  
            scrollbar-width: auto;  
        }
        .announce {
            background-color: #0039a6;
            color: white; 
            padding: 10px; 
            font-weight: bold; 
            font-size: 18px; 
            width: 300px;
            margin: 0 auto;
            border: 2px solid #7DF9FF; 
            border-radius: 10px;
        }
        .username {
            font-weight: bold;
            font-size: 18px;
        }
		.dashboard-banner {
			width: 100%; /* Makes the image full-width */
			max-height: 80px; /* Adjust height as needed */
			object-fit: cover; /* Ensures the image covers the space properly */
			border-radius: 10px; /* Adds rounded corners */
			border: 3px solid #7DF9FF; /* Matches the design */
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
    <div class="bar w3-container w3-padding w3-bar w3-blue">
        <div class="w3-bar-item"><b>HISTORY</b></div>
		<div class="w3-bar w3-right">
        <a href="logout.php" class="w3-button w3-hover-red w3-right">LOG OUT</a>
		<a class="w3-bar-item w3-button w3-hover-white w3-right">NOTIFICATION</a>
		<a href="history.php" class="w3-bar-item w3-button w3-hover-white w3-right">HISTORY</a>
		<a href="reservation.php" class="w3-bar-item w3-button w3-hover-white w3-right">RESERVATION</a>
		<a href="profile.php" class="w3-bar-item w3-button w3-hover-white w3-right">PROFILE</a>
		<a href="homepage.php" class="w3-bar-item w3-button w3-hover-white w3-right">HOME</a>
		</div>
    </div>
    <br><br>
    <div class="w3-center"><h2><b>HISTORY INFORMATION</b></h2></div>
</body>
</html>