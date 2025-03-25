<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}
include('database.php');

$username = $_SESSION['username'];
$role = $_SESSION['role'];
$user_id = null;


$query = "SELECT user_id, role FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();


if ($row = $result->fetch_assoc()) {
    $user_id = $row['user_id'];
} 


$query = "SELECT idno, lastname, firstname, midname, course, year_level, sessions, image_link, email, address FROM students WHERE user_id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $row['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

$current_sessions = $student['sessions'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'];
    $midname = $_POST['midname'];
    $lastname = $_POST['lastname'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
	$email = $_POST['email'];
	$address = $_POST['address'];
    $idno = $student['idno'];


    // Handle image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $image_name = time() . '_' . $_FILES['profile_image']['name'];
        $image_tmp_name = $_FILES['profile_image']['tmp_name'];
        $image_folder = 'images/';
        $image_link = $image_folder . basename($image_name);

        if (!move_uploaded_file($image_tmp_name, $image_link)) {
            $image_link = $student['image_link']; // Keep old image if upload fails
        }
    } else {
        $image_link = $student['image_link'];
    }

    // Update the database
    $update_query = "UPDATE students SET firstname=?, midname=?, lastname=?, course=?, year_level=?, image_link=?, email=?, address=? WHERE idno=?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssssssssi", $firstname, $midname, $lastname, $course, $year_level, $image_link, $email, $address, $idno);
    $stmt->execute();

    // Refresh the page to show updated data
    header("Location: profile.php");
    exit();
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
            height: 100%;
            margin: 0;
            background-color: #cfe2f3;
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
        .profile-form {
            width: 1700px;
            padding: 20px;
            margin-top: 40px;
            border-radius: 10px;
			border: 1px solid black;
            background-color: #94D1FF;
            box-shadow: 14px 13px 4px 0px rgba(0, 0, 0, 0.25);
            text-align: left;
        }
        h1 {
            margin-top: 0;
        }
        .profile-image {
            width: 200px;
            height: 200px;
            border-radius: 10%;
			border: 1px solid black;
            background-color: gray;
            display: inline-block;
            background-size: cover;
            background-position: center;
            float: left;
            margin-right: 20px;
        }
        .profile-details {
            display: inline-block;
			width: 1650px;
            height: 500px;
			border-radius: 10px;
			border: 1px solid black;
			background-color: white;
			color: black;
			font-size: 18px;
        }
        .profile-details p {
            margin: 50px 0;
        }
		.id-sessions {
			display: inline-flex;
			justify-content: flex-start;
			width: 100%;
		}
        .edit-button {
			background-color: green;
			color: white;
			padding: 10px;
			border: none;
			border-radius: 5px;
			cursor: pointer;
			position: absolute;
			width: 100px;
			height: 50px;
			top: 280px; /* Adjust this value to move the button down */
			right: 200px; /* Adjust this value to move the button left */
			box-shadow: 6px 9px 4px 0px rgba(0, 0, 0, 0.25);
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
            background-color: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 3px solid black;
            width: 80%;
            max-width: 800px;
            border-radius: 10px;
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
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
        <div class="w3-bar-item"><b>PROFILE</b></div>
		<div class="w3-bar w3-right">
		<a href="logout.php" class="w3-button w3-hover-red w3-right">LOG OUT</a>
		<a class="w3-bar-item w3-button w3-hover-white w3-right">NOTIFICATION</a>
		<a class="w3-bar-item w3-button w3-hover-white w3-right">HISTORY</a>
		<a href="reservation.php" class="w3-bar-item w3-button w3-hover-white w3-right">RESERVATION</a>
		<a href="profile.php" class="w3-bar-item w3-button w3-hover-white w3-right">PROFILE</a>
		<a href="homepage.php" class="w3-bar-item w3-button w3-hover-white w3-right">HOME</a>
		</div>
    </div>

    <div class="profile-form w3-container">
        <form action="profile.php" method="post" enctype="multipart/form-data">
            <h1 class="w3-center"><b>STUDENT PROFILE</b></h1>
            <div class="profile-image" style="background-image: url('<?php echo !empty($student['image_link']) ? $student['image_link'] : 'images/default.png'; ?>');">
			</div>
			<br><br><br><br><br><br><br><br><br><br>
            <div class="profile-details w3-container">
				<p>
					<div class="id-sessions">
						<strong><i class="fa fa-id-card"></i>&nbsp;&nbsp;ID Number :</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo $student['idno']; ?>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<strong># of Sessions :</strong> &nbsp;&nbsp;&nbsp;  <?php echo $current_sessions; ?>
					</div>
				</p>
				<p><strong><i class="fa fa-user"></i>&nbsp;&nbsp;Student Name : </strong> &nbsp;&nbsp;&nbsp; <?php echo $student['firstname'] . ' ' . $student['midname'] . ' ' . $student['lastname']; ?></p>
				<p><strong><i class="fa fa-graduation-cap"></i>&nbsp;&nbsp;Course :</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo $student['course']; ?></p>
				<p><strong><i class="material-icons">assistant_navigation</i>&nbsp;&nbsp;Year Level :</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo preg_replace('/[^0-9]/', '', $student['year_level']); ?></p>
				<p><strong><i class="fa fa-envelope"></i>&nbsp;&nbsp;Email :</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $student['email']; ?></p>
				<p><strong><i class="fa fa-map-marker-alt"></i>&nbsp;&nbsp;Address :</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $student['address']; ?></p>
			</div>

            <button type="button" class="edit-button" onclick="showModal()"><b>E D I T</b></button>
        </form>
    </div>

    <!-- Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal()">&times;</span>
            <form action="profile.php" method="post" enctype="multipart/form-data">
                <h3 class="w3-center"><b>EDIT STUDENT INFO</b></h3>
                <p>
                    <label>Upload Image</label>
                    <input class="w3-input w3-border" type="file" name="profile_image" accept="image/*">
                </p>
				 <p>
                    <label>ID Number</label>
                    <input class="w3-input w3-border" type="text" name="idno" value="<?php echo $student['idno']; ?>" readonly>
                </p>
                <p>
					<label>First Name</label>
					<input class="w3-input w3-border" type="text" name="firstname" value="<?php echo $student['firstname']; ?>">
				</p>
				<p>
					<label>Middle Name</label>
					<input class="w3-input w3-border" type="text" name="midname" value="<?php echo $student['midname']; ?>">
				</p>
				<p>
					<label>Last Name</label>
					<input class="w3-input w3-border" type="text" name="lastname" value="<?php echo $student['lastname']; ?>">
				</p>
                <p>
                    <label>Course</label>
                    <select class="w3-select w3-border" name="course">
					<option value="BSIT" <?php if ($student['course'] == 'BSIT') echo 'selected'; ?>>Bachelor of Science in Information Technology</option>
					<option value="BSCS" <?php if ($student['course'] == 'BSCS') echo 'selected'; ?>>Bachelor of Science in Computer Science</option>
					<option value="BSIS" <?php if ($student['course'] == 'BSIS') echo 'selected'; ?>>Bachelor of Science in Information Systems</option>
					<option value="BSHM" <?php if ($student['course'] == 'BSHM') echo 'selected'; ?>>Bachelor of Science in Hospitality Management</option>
					<option value="BSCJ" <?php if ($student['course'] == 'BSCJ') echo 'selected'; ?>>Bachelor of Science in Criminal Justice</option>
					<option value="BSEED" <?php if ($student['course'] == 'BSEED') echo 'selected'; ?>>Bachelor of Secondary Education</option>
					</select>	
                </p>
                <p>
                    <label>Year Level</label>
                    <select class="w3-select w3-border" name="year_level">
					<option value="1st Year" <?php if ($student['year_level'] == '1st Year') echo 'selected'; ?>>1st Year</option>
					<option value="2nd Year" <?php if ($student['year_level'] == '2nd Year') echo 'selected'; ?>>2nd Year</option>
					<option value="3rd Year" <?php if ($student['year_level'] == '3rd Year') echo 'selected'; ?>>3rd Year</option>
					<option value="4th Year" <?php if ($student['year_level'] == '4th Year') echo 'selected'; ?>>4th Year</option>
					</select>
                </p>
                <p>
				<p>
					<label>Email</label>
					<input class="w3-input w3-border" type="text" name="email" value="<?php echo $student['email']; ?>">
				</p>
				<p>
					<label>Address</label>
					<input class="w3-input w3-border" type="text" name="address" value="<?php echo $student['address']; ?>">
				</p>
                    <button type="submit" class="w3-button w3-blue">Save</button>
                </p>
            </form>
        </div>
    </div>

    <script>
        function showModal() {
            document.getElementById('editModal').style.display = 'block';
        }

        function hideModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                hideModal();
            }
        }
		document.querySelector('input[name="profile_image"]').addEventListener('change', function(event) {
			const file = event.target.files[0];
			if (file) {
				const reader = new FileReader();
				reader.onload = function(e) {
					document.querySelector('.profile-image').style.backgroundImage = `url('${e.target.result}')`;
				};
				reader.readAsDataURL(file);
			}
		});
    </script>
</body>
</html>
