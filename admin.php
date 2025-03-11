<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
header("Location: login.php");
exit();
}
if ($_SESSION['role'] !== 'admin') {
header("Location: homepage.php");
exit();
}
?>
<!DOCTYPE html>
<html>
    <head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <style>
        body {
            height: 100%;
            margin: 0;
            background-color: #cfe2f3;
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
        .container { 
            display: flex;
            justify-content: space-between; 
            margin: 20px;
         }
        .box {
             width: 48%;
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1); 
        }
        </style>
    </head>
    <body>

    <div class="bar w3-container w3-padding w3-bar w3-blue">
        <div class="w3-bar-item"><b>DASHBOARD</b></div>
		<div class="w3-bar w3-right">
        <a href="logout.php" class="w3-button w3-hover-red w3-right">LOG OUT</a>
		<a class="w3-bar-item w3-button w3-hover-white w3-right">NOTIFICATION</a>
		<a class="w3-bar-item w3-button w3-hover-white w3-right">HISTORY</a>
		<a href="reservation.php" class="w3-bar-item w3-button w3-hover-white w3-right">RESERVATION</a>
		<a href="profile.php" class="w3-bar-item w3-button w3-hover-white w3-right">PROFILE</a>
		<a class="w3-bar-item w3-button w3-hover-white w3-right">HOME</a>
		</div>
    </div>
    <br><br>
            <div class="w3-container w3-center">
                <img src="blue.jfif" alt="Dashboard Banner" class="dashboard-banner">
            </div>

        <div class="container">
            <div class="box">
                <h3>Statistics</h3>
                    <p>Students Registered: 183</p>
                    <p>Currently Sit-in: 0</p>
                    <p>Total Sit-in: 79</p>
                <canvas id="pieChart"></canvas>
            </div>
            <div class="box">
                <h3>Announcement</h3>
                    <form>
                        <textarea class="w3-input w3-border" placeholder="New Announcement"></textarea>
                        <button class="w3-button w3-green w3-margin-top">Submit</button>
                    </form>
            <h4>Posted Announcement</h4>
                <p><strong>CCS Admin | 2025-Feb-25</strong><br>UC did it again.</p>
                <p><strong>CCS Admin | 2025-Feb-03</strong><br>The College of Computer Studies...</p>
            </div>
        </div>
            <script>
            const ctx = document.getElementById('pieChart').getContext('2d');
            new Chart(ctx, {
            type: 'pie',
            data: {
            labels: ['C#', 'C', 'Java', 'ASP.Net', 'PHP'],
            datasets: [{
            data: [40, 20, 10, 15, 15],
            backgroundColor: ['blue', 'red', 'orange', 'yellow', 'green']
            }]
            }
            });
            </script>
    </body>
</html>