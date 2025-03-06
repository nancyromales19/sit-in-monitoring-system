<?php
session_start();
include('database.php');

$username = $_SESSION['username'];

$query = "SELECT idno, lastname, firstname, midname, course, year_level, sessions, image_link, email, address FROM users WHERE username='$username'";
$result = mysqli_query($conn, $query);
$student = mysqli_fetch_assoc($result);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'];
    $midname = $_POST['midname'];
    $lastname = $_POST['lastname'];
    $idno = $student['idno'];

}

// Split name into parts for form population
$name_parts = explode(' ', $student['firstname'] . ' ' . $student['midname'] . ' ' . $student['lastname']);
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
		.head{
			margin-top: 50px;
			font-size: 30px;
		}
		.reserve-button {
			background-color: green;
			color: white;
			padding: 10px;
			border: none;
			border-radius: 10px;
			cursor: pointer;
			position: absolute;
			width: 150px;
			height: 40px;
			box-shadow: 6px 9px 4px 0px rgba(0, 0, 0, 0.25);
			right: 20px;
		}
		.reservation-form {
            width: 1700px;
            padding: 20px;
            margin-top: 5px;
            border-radius: 10px;
			border: 1px solid black;
            background-color: #94D1FF;
            box-shadow: 14px 13px 4px 0px rgba(0, 0, 0, 0.25);
            text-align: left;
        }
		.reservation-details {
            display: inline-block;
			width: 1650px;
            height: 550px;
			border-radius: 10px;
			border: 1px solid black;
			background-color: white;
			color: black;
			font-size: 18px;
        }
        .reservation-details p {
            margin: 50px 0;
        }
    </style>
</head>
<body>
    <div class="bar w3-container w3-bar w3-blue">
        <div class="w3-bar-item"><b>RESERVATION</b></div>
		<div class="w3-bar w3-right">
		<a href="logout.php" class="w3-button w3-hover-red w3-right">LOG OUT</a>
		<a class="w3-bar-item w3-button w3-hover-white w3-right">NOTIFICATION</a>
		<a class="w3-bar-item w3-button w3-hover-white w3-right">HISTORY</a>
		<a class="w3-bar-item w3-button w3-hover-white w3-right">RESERVATION</a>
		<a href="profile.php" class="w3-bar-item w3-button w3-hover-white w3-right">PROFILE</a>
		<a href="homepage.php" class="w3-bar-item w3-button w3-hover-white w3-right">HOME</a>
		</div>
    </div>
	
	<p class="head"><b>Reservation</b></p>
	
	<div class="reservation-form w3-container">
        <form action="" method="post" enctype="multipart/form-data">
            <div class="reservation-details w3-container">
				<p><strong>ID number : </strong><?php echo $student['idno']; ?></p>
				<p><strong>Student Name : </strong><?php echo $student['firstname'] . ' ' . $student['midname'] . ' ' . $student['lastname']; ?></p>
				<p><strong>Purpose :</strong></p>
				<p><strong>Lab :</strong></p>
				<p><strong>Time in :</strong></p>
				<p><strong>Date :</strong></p>
				<p><strong>Remaining sessions :</p>
				<button type="button" class="reserve-button" ><b>RESERVE</b></button>
			</div>
        </form>
    </div>

    
</body>
</html>
