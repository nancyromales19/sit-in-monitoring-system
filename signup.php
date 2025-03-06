<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idno = $_POST['idno'];
    $lastname = $_POST['lastname'];
    $firstname = $_POST['firstname'];
    $midname = $_POST['midname'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $username = $_POST['username'];
    $password = $_POST['password']; // Store the password as plain text
    $email = $_POST['email'];
    $address = $_POST['address'];
    $role = 'student';

    $conn->begin_transaction(); // Start transaction

    try {
        // Insert into users table
        $sql_users = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
        $stmt_users = $conn->prepare($sql_users);
        $stmt_users->bind_param("sss", $username, $password, $role); // Use plain text $password
        $stmt_users->execute();

        $user_id = $conn->insert_id; // Get the ID of the newly inserted user

        // Insert into students table
        $sql_students = "INSERT INTO students (idno, user_id, lastname, firstname, midname, course, year_level, email, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_students = $conn->prepare($sql_students);
        $stmt_students->bind_param("iisssssss", $idno, $user_id, $lastname, $firstname, $midname, $course, $year_level, $email, $address);
        $stmt_students->execute();

        $conn->commit(); // Commit the transaction if both inserts succeed
        echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
    } catch (Exception $e) {
        $conn->rollback(); // Rollback the transaction on failure
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">
    <title>SIGN UP PAGE</title>
    <style>
        body {
            background: #cfe2f3;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Roboto Mono', monospace;
        }
        .container {
            width: 100%;
            max-width: 500px;
            padding: 25px;
        }
        .signup-form {
            background-color: #002244;
            border: 5px solid #0066b2;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        .signup-form h2 {
            margin-bottom: 20px;
            color: white;
            font-weight: 700;
        }
        .signup-form .input-group {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .signup-form input {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border: none;
            border-radius: 20px;
        }
        .input-group input {
            width: 48%;
        }
        .signup-form button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 9px;
            background-color: #cfe2f3;
            color: black;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .signup-form button:hover {
            background-color: #0039a6;
			color: white;
        }
		.signup-form select {
			width: 100%;
			margin-bottom: 10px;
			padding: 10px;
			border: none;
			border-radius: 20px;
			background-color: #fff;
			font-family: 'Roboto Mono', monospace;
			appearance: none;
			-webkit-appearance: none;
			-moz-appearance: none;
			text-align: center;
			cursor: pointer;
		}

		.input-group select {
			width: 48%;
		}

		/* Add an arrow icon to the dropdown */
		.signup-form select {
			background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='black'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
			background-repeat: no-repeat;
			background-position: right 10px center;
			background-size: 15px;
			padding-right: 30px;
		}
		.line {
				border-top: 2px solid #7DF9FF;
				margin-top: 10px; 
				margin-bottom: 10px; 
			}

    </style>
</head>
<body>
    <div class="container">
        <form method="POST" action="" class="signup-form">
            <h2>SIGN-UP</h2>
			<hr class="line"><br>
            <input type="number" name="idno" placeholder="ID No:" required><br>
            <div class="input-group">
                <input type="text" name="firstname" placeholder="Firstname:" required>
                <input type="text" name="lastname" placeholder="Lastname:" required>
            </div>
            <input type="text" name="midname" placeholder="Midname:"><br>
            <div class="input-group">
			<select name="course" id="course" required>
                <option value="">Course</option>
                <option value="BSIT">BSIT</option>
                <option value="BSCS">BSCS</option>
                <option value="BSIS">BSIS</option>
				<option value="BSHM">BSHM</option>
				<option value="BSCJ">BSCJ</option>
				<option value="BSEED">BSEED</option>
                </select>

            <select name="year_level" id="year_level" required>
                <option value="">Year Level</option>
                <option value="1">1st Year</option>
                <option value="2">2nd Year</option>
                <option value="3">3rd Year</option>
                <option value="4">4th Year</option>
                </select>
			</div><br>
			<input type="email" name="email" placeholder="Email:" required><br>
			<input type="text" name="address" placeholder="Address:" required><br>
            <input type="text" name="username" placeholder="Username:" required><br>
            <input type="password" name="password" placeholder="Password:" required><br><br>
            <button type="submit">REGISTER</button><br><br>
			</form>
    </div>
</body>
</html>
