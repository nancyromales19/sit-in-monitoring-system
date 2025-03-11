<?php
session_start();
include 'database.php';

$errorMessage = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

	if (empty($username) || empty($password)) {
		$errorMessage = "Please ENTER username and password.";
	} else {
		$sql = "SELECT user_id, username, password, role FROM users WHERE username = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows == 1) {
			$row = $result->fetch_assoc();
			if ($password == $row['password']) {
				session_regenerate_id(true);
				$_SESSION['username'] = $username;
				$_SESSION['role'] = $row['role'];
				
				if ($row['role'] == 'admin') {
					header("Location: admin.php");
				} else {
					header("Location: homepage.php");
				}
				exit();
			} else {
				$errorMessage = "Incorrect password.";
			}
		} else {
			$errorMessage = "Username not found.";
			}
		}

	}
?>


 <!DOCTYPE>
	<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="ie-edge">
		<meta name="viewport" content="width=device-width,initial-scale=1.0">
		<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
	</head>
	 <style>
			body {
				height: 100%;
				margin: 0;
				background-color: #cfe2f3;
			}
			.w3-card-4 {
				background-color: #002244; 
				color: #ffffff; /* white text */
				border: 3px solid #0066b2; 
				border-radius: 10px;
			}
			.user {
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
			.line {
				border-top: 2px solid #7DF9FF;
				margin-top: 10px; 
				margin-bottom: 10px; 
			}
			.w3-button {
				background-color: #cfe2f3;
				color: #000000;
				border-radius: 4px; 
				padding: 8px 16px; 
			}
			.modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
			background-color: #fefefe;
			margin: 15% auto;  /* Center on page */
			padding: 20px;
			border: 1px solid #888;
			/* width: 80%;  Remove fixed width */
			max-width: 400px; /* Set a maximum width */
			border-radius: 10px;
			box-sizing: border-box; /* Include padding and border in width */
		}

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .error-message {
            color: red; 
        }
        .success-message {
            color: green; 
        }
		.modal-content {
			background-color: #fff; /* White background */
			color: #333; /* Dark gray text */
		}

		.modal-content p { /* Style the message paragraph */
			margin: 0; /* Remove default paragraph margins */
			padding: 10px; /* Add some padding around the text */
			text-align: center; /* Center the text */
		}

		.modal-content .close {
			color: #888; /* Slightly lighter gray for close button */
		}
		</style>
		<body>
		<div class="w3-left w3-half" style="width:40px; margin-top: 150px; margin-left: 90px;">
		<img src="uc_logo.png" alt="uc logo">
		</div>
		<div class="w3-right w3-half" style="width:40px; margin-top: 150px; margin-right: 300px;">
		<img src="ccs_logo.png" alt="ccs logo">
		</div>
		<div class="w3-mobile w3-container w3-padding w3-animate-top w3-half" style="width:40%; margin-top: 150px; margin-left: 400px;" id="login-form">
			<div class="w3-card-4 w3-round-xlarge w3-container w3-padding"><br>
				<div class="user"><b><center>CCS Sit-in Monitoring System</center></b></div><br>
				<hr class="line">
				<form method="POST" action="">
					<p>
						<label for="username"><b>USERNAME:</b></label><br>
						<input type="text" class="w3-input w3-border w3-round" name="username" autocomplete="username" placeholder="Enter your username..." required>
					</p>
					<p>
						<label for="password"><b>PASSWORD:</b></label><br>
						<input type="password" class="w3-input w3-border w3-round" name="password" autocomplete="current-password" placeholder="Enter your password..." required>
					</p>
					<p>
						<button type="submit" class="w3-left w3-button"><b>LOGIN</b></button>
					</p>
					<a href="signup.php" class="w3-right">Register</a><br><br>
				</form>

			</div>
		</div>
		
			<div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <p id="modalMessage"></p>  </div>
    </div>


    <script>
        const modal = document.getElementById("messageModal");
        const modalMessage = document.getElementById("modalMessage");
        const span = document.getElementsByClassName("close")[0];

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        <?php if (!empty($errorMessage)) { ?>
            modalMessage.textContent = "<?php echo $errorMessage; ?>";
            modal.style.display = "block";
        <?php } ?>
    </script>
		</body>
	</html>