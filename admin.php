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

include('database.php');

// Make sure you have the user_id in the session
if (!isset($_SESSION['user_id'])) {
    // If for some reason it's not in the session, fetch it again
    $username = $_SESSION['username'];
    $query = "SELECT user_id FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $_SESSION['user_id'] = $row['user_id'];
}

// Handle announcement submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['announcement'])) {
    $announcement_content = $_POST['announcement'];
    $user_id = $_SESSION['user_id']; 
    $date = date('Y-m-d');
    
    // Insert the announcement into the database
    $stmt = $conn->prepare("INSERT INTO announcement (user_id, content, date) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $announcement_content, $date);
    
    if ($stmt->execute()) {
        $successMessage = "Announcement posted successfully!";
    } else {
        $errorMessage = "Error: " . $stmt->error;
    }
    
    $stmt->close();
}

// Fetch announcements for display - join with users table to get admin username
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

// Fetch total registered students
$total_students_query = "SELECT COUNT(*) as total_students FROM students";
$total_students_result = $conn->query($total_students_query);
$total_students = $total_students_result->fetch_assoc()['total_students'];

// Fetch total currently sit-in students (modify this query based on your actual table structure)
$current_sitin_query = "SELECT COUNT(*) as current_sitin FROM sitin WHERE time_out IS NULL";
$current_sitin_result = $conn->query($current_sitin_query);
$current_sitin = $current_sitin_result->fetch_assoc()['current_sitin'];

// Fetch total overall sit-in students (modify this query based on your actual table structure)
$total_sitin_query = "SELECT COUNT(*) as total_sitin FROM sitin";
$total_sitin_result = $conn->query($total_sitin_query);
$total_sitin = $total_sitin_result->fetch_assoc()['total_sitin'];

// Fetch sit-in purpose distribution
$purpose_query = "SELECT purpose, COUNT(*) as count 
                  FROM sitin 
                  WHERE purpose IS NOT NULL 
                  GROUP BY purpose";
$purpose_result = $conn->query($purpose_query);

$purposes = [];
$counts = [];
$colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']; // Pink, Blue, Yellow, Teal

while ($row = $purpose_result->fetch_assoc()) {
    $purposes[] = $row['purpose'];
    $counts[] = $row['count'];
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
			width: 100%; 
			max-height: 80px; 
			object-fit: cover; 
			border-radius: 10px; 
			border: 3px solid #7DF9FF; 
		} 
		.bar { 
			display: flex; 
			align-items: center; 
			height: 70px; 
			padding: 0 20px; 
            overflow: hidden;
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
         .success-message {
             background-color: #4CAF50;
             color: white;
             padding: 10px;
             margin-bottom: 10px;
             border-radius: 5px;
         }
         .error-message {
             background-color: #f44336;
             color: white;
             padding: 10px;
             margin-bottom: 10px;
             border-radius: 5px;
         }
         .announcement-item {
             border-bottom: 1px solid #eee;
             padding: 10px 0;
         }
        </style>
    </head>
    <body>
     <div class="bar w3-container w3-padding w3-bar w3-blue">
        <div class="w3-bar-item"><b>DASHBOARD</b></div>
		<div class="w3-bar w3-right">
        <a href="logout.php" class="w3-button w3-hover-red w3-right">LOG OUT</a>
        <a class="w3-bar-item w3-button w3-hover-white w3-right">RESERVATION</a>
        <a class="w3-bar-item w3-button w3-hover-white w3-right">FEEDBACK REPORTS</a>
        <a class="w3-bar-item w3-button w3-hover-white w3-right">SIT-IN REPORTS</a>
        <a href="sitin_records.php" class="w3-bar-item w3-button w3-hover-white w3-right">SIT IN RECORDS</a>
		<a href="sitin.php" class="w3-bar-item w3-button w3-hover-white w3-right">SIT IN</a>
		<a href="students.php" class="w3-bar-item w3-button w3-hover-white w3-right">STUDENTS</a>
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
                    <p>Students Registered: <?php echo $total_students; ?></p>
                    <p>Currently Sit-in: <?php echo $current_sitin; ?></p>
                    <p>Total Sit-in: <?php echo $total_sitin; ?></p>
                <canvas id="pieChart"></canvas>
                <div class="purpose-stats">
                    <?php
                    foreach ($purposes as $index => $purpose) {
                        echo "<p>" . htmlspecialchars($purpose) . ": " . $counts[$index] . "</p>";
                    }
                    ?>
                </div>
            </div>
            <div class="box">
                <h3>Announcement</h3>
                <?php if (isset($successMessage)): ?>
                    <div class="success-message"><?php echo $successMessage; ?></div>
                <?php endif; ?>
                
                <?php if (isset($errorMessage)): ?>
                    <div class="error-message"><?php echo $errorMessage; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <textarea name="announcement" class="w3-input w3-border" placeholder="New Announcement" required></textarea>
                    <button type="submit" class="w3-button w3-green w3-margin-top">Submit</button>
                </form>
                
                <h4>Posted Announcement</h4>
                <?php if (empty($announcements)): ?>
                    <p>No announcements yet.</p>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="announcement-item">
                            <p>
                                <strong><?php echo htmlspecialchars($announcement['username']); ?> | 
                                <?php echo date('Y-M-d', strtotime($announcement['date'])); ?></strong><br>
                                <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
            <script>
                const ctx = document.getElementById('pieChart').getContext('2d');
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: <?php echo json_encode($purposes); ?>,
                        datasets: [{
                            data: <?php echo json_encode($counts); ?>,
                            backgroundColor: <?php echo json_encode($colors); ?>
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                        }
                    }
                });
            </script>
    </body>
</html>