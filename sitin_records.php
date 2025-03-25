<?php
session_start();

// Check if user is logged in and has admin role
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: homepage.php");
    exit();
}

include('database.php');

// Function to get sit-in statistics
function getSitInStatistics($conn) {
    // Purpose Statistics
    $purpose_query = "
        SELECT purpose, COUNT(*) as count 
        FROM sitin 
        GROUP BY purpose 
        ORDER BY count DESC
    ";
    $purpose_result = $conn->query($purpose_query);
    $purpose_stats = [];
    while ($row = $purpose_result->fetch_assoc()) {
        $purpose_stats[] = $row;
    }

    // Lab Statistics
    $lab_query = "
        SELECT l.lab_room, COUNT(s.sitin_id) as count 
        FROM laboratory l
        LEFT JOIN sitin s ON l.lab_id = s.lab_id
        GROUP BY l.lab_room 
        ORDER BY count DESC
    ";
    $lab_result = $conn->query($lab_query);
    $lab_stats = [];
    while ($row = $lab_result->fetch_assoc()) {
        $lab_stats[] = $row;
    }

    return [
        'purposes' => $purpose_stats,
        'labs' => $lab_stats
    ];
}

// Fetch sit-in records
$records_query = "
    SELECT 
        s.sitin_id, 
        st.idno, 
        CONCAT(st.firstname, ' ', st.lastname) AS full_name,
        s.purpose, 
        l.lab_room, 
        s.time_in, 
        s.time_out
    FROM sitin s
    JOIN students st ON s.stud_id = st.stud_id
    JOIN laboratory l ON s.lab_id = l.lab_id
    WHERE s.time_out IS NOT NULL
    ORDER BY s.time_out DESC
";
$records_result = $conn->query($records_query);

// Get statistics
$statistics = getSitInStatistics($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sit-In Records</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }
        .chart-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .chart-wrapper {
            width: 48%;
            background-color: white;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .records-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .records-table th, .records-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .records-table th {
            background-color: #2196F3;
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
<div class="bar w3-container w3-padding w3-bar w3-blue">
        <div class="w3-bar-item"><b>SITIN_RECORDS</b></div>
		<div class="w3-bar w3-right">
        <a href="logout.php" class="w3-button w3-hover-red w3-right">LOG OUT</a>
        <a class="w3-bar-item w3-button w3-hover-white w3-right">RESERVATION</a>
        <a class="w3-bar-item w3-button w3-hover-white w3-right">FEEDBACK REPORTS</a>
        <a class="w3-bar-item w3-button w3-hover-white w3-right">SIT-IN REPORTS</a>
        <a href="sitin_records.php" class="w3-bar-item w3-button w3-hover-white w3-right">SIT IN RECORDS</a>
		<a href="sitin.php" class="w3-bar-item w3-button w3-hover-white w3-right">SIT IN</a>
		<a href="students.php" class="w3-bar-item w3-button w3-hover-white w3-right">STUDENTS</a>
		<a href="admin.php" class="w3-bar-item w3-button w3-hover-white w3-right">HOME</a>
		</div>
    </div>
    <br><br>

    <div class="w3-container">
    
        <div class="chart-container">
            <div class="chart-wrapper">
                <h3>Sit in Purpose Distribution</h3>
                <canvas id="purposeChart"></canvas>
            </div>
            <div class="chart-wrapper">
                <h3>Laboratory Distribution</h3>
                <canvas id="labChart"></canvas>
            </div>
        </div>

        <table class="records-table">
            <thead>
                <tr>
                    <th>Sit-In ID</th>
                    <th>ID Number</th>
                    <th>Name</th>
                    <th>Purpose</th>
                    <th>Lab</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($records_result && $records_result->num_rows > 0) {
                    while ($record = $records_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($record['sitin_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($record['idno']) . "</td>";
                        echo "<td>" . htmlspecialchars($record['full_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($record['purpose']) . "</td>";
                        echo "<td>" . htmlspecialchars($record['lab_room']) . "</td>";
                        echo "<td>" . htmlspecialchars($record['time_in']) . "</td>";
                        echo "<td>" . htmlspecialchars($record['time_out']) . "</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        // Purpose Chart
        const purposeData = {
            labels: <?php echo json_encode(array_column($statistics['purposes'], 'purpose')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($statistics['purposes'], 'count')); ?>,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                    '#9966FF', '#FF9F40', '#FF6384'
                ]
            }]
        };

        // Lab Chart
        const labData = {
            labels: <?php echo json_encode(array_column($statistics['labs'], 'lab_room')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($statistics['labs'], 'count')); ?>,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                    '#9966FF', '#FF9F40', '#FF6384'
                ]
            }]
        };

        // Render charts
        new Chart(document.getElementById('purposeChart'), {
            type: 'pie',
            data: purposeData,
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: 'Sit-In Purposes'
                }
            }
        });

        new Chart(document.getElementById('labChart'), {
            type: 'pie',
            data: labData,
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: 'Laboratory Distribution'
                }
            }
        });
    </script>
</body>
</html>