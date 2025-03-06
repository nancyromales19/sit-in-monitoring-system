<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
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
        <div class="w3-bar-item"><b>DASHBOARD</b></div>
		<div class="w3-bar w3-right">
        <a href="logout.php" class="w3-button w3-hover-red w3-right">LOG OUT</a>
		<a class="w3-bar-item w3-button w3-hover-white w3-right">NOTIFICATION</a>
		<a class="w3-bar-item w3-button w3-hover-white w3-right">HISTORY</a>
		<a class="w3-bar-item w3-button w3-hover-white w3-right">RESERVATION</a>
		<a href="profile.php" class="w3-bar-item w3-button w3-hover-white w3-right">PROFILE</a>
		<a class="w3-bar-item w3-button w3-hover-white w3-right">HOME</a>
		</div>
    </div>
    <br><br>
	
	<div class="w3-container w3-center">
    <img src="blue.jfif" alt="Dashboard Banner" class="dashboard-banner">
	</div>

    <div class="w3-row w3-border">
        <div class="w3-half w3-container w3-padding w3-animate-top" style="margin-top: 20px;" id="notify">
            <div class="w3-card-4 w3-round-xlarge w3-container w3-padding"><br>
                <div class="announce"><b><center><i class="fa fa-bullhorn"></i>&nbsp;&nbsp;ANNOUNCEMENT</center></b></div><br>
                <hr class="line">
                <form class="notified1">
                    <p><b>CCS Admin | 2025-Feb-03</b></p>
                    <p>The College of Computer Studies will open the<br>
                        registration of students for the Sit-in privilege starting<br>
                        tomorrow. Thank you ! Lab Supervisor</p>
                    <hr class="underline">
                    <p><b>CCS Admin | 2024-May-04</b></p>
                    <p>Important Announcement, We are excited to <br>
                        tell you that students who are enrolled in this<br>
                        semester can now have 30 sessions of laboratory sit-ins.</p>
                    <hr class="underline">
                </form>
            </div>
        </div>
		
        <div class="w3-half w3-container w3-padding w3-animate-top" style="margin-top: 20px;" id="rules">
            <div class="w3-card-4 w3-round-xlarge w3-container w3-padding"><br>
                <div class="regulations"><b><center>RULES AND REGULATIONS</center></b></div><br>
                <hr class="line">
                <form class="reg">
					<center><p><b>University Of Cebu - Main Campus</b></p>
					<p><b>COLLEGE OF INFORMATION & COMPUTER STUDIES</b></p></center><br>
					<p><b>LABORATORY RULES AND REGULATIONS</b></p>
                    <p>To avoid embarrassment and maintain camaraderie with your friends and superiors at our laboratories, please observe the following:</p><br>
                    <p>1. Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones, walkmans and other personal pieces of equipment must be switched off.</p><br>
                    <p>2. Games are not allowed inside the lab. This includes computer-related games, card games and other games that may disturb the operation of the lab.</p><br>
                    <p>3. Surfing the internet is allowed only with the permission of the instructor. Downloading and installing software are strictly prohibited.</p><br>
                    <p>4. Getting access to other websites not related to the course (especially pornographic and illicit sites) is strictly prohibited.</p><br>
                    <p>5. Deleting computer files and changing the set-up of the computer is a major offense.</p><br>
                    <p>6. Observe computer time usage carefully. A fifteen-minute allowance is given for each use. Otherwise, the unit will be given to those who wish to "sit-in".</p><br>
                    <p>7. Observe proper decorum while inside the laboratory.</p><br>
					<li>&nbsp;&nbsp;Do not get inside the lab unless the instructor is present.</li><br>
					<li>&nbsp;&nbsp;All bags, knapsacks, and the likes must be deposited at the counter.</li><br>
					<li>&nbsp;&nbsp;Follow the seating arrangement of your instructor.</li><br>
					<li>&nbsp;&nbsp;At the end of class, all software programs must be closed.</li><br>
					<li>&nbsp;&nbsp;Return all chairs to their proper places after using.</li><br>
					<p>8. Chewing gum, eating, drinking, smoking, and other forms of vandalism are prohibited inside the lab.</p><br>
					<p>9. Anyone causing a continual disturbance will be asked to leave the lab. Acts or gestures offensive to the members of the community, including public display of physical intimacy, are not tolerated.</p><br>
					<p>10. Persons exhibiting hostile or threatening behavior such as yelling, swearing, or disregarding requests made by lab personnel will be asked to leave the lab.</p><br>
					<p>11. For serious offense, the lab personnel may call the Civil Security Office (CSU) for assistance.</p><br>
					<p>12. Any technical problem or difficulty must be addressed to the laboratory supervisor, student assistant or instructor immediately.</p><br><br>
					<p><b>DISCIPLINARY ACTION</b></p><br>
					<li>First Offense - The Head or the Dean or OIC recommends to the Guidance Center for a suspension from classes for each offender.</li><br>
					<li>Second and Subsequent Offenses - A recommendation for a heavier sanction will be endorsed to the Guidance Center.</li><br>
                </form>
            </div>
        </div>
    </div>
    <br>
</body>
</html>
