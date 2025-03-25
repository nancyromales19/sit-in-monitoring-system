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

// Handle student deletion if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stud_id = $_GET['delete'];
    
    // Delete the student record
    $stmt = $conn->prepare("DELETE FROM students WHERE stud_id = ?");
    $stmt->bind_param("i", $stud_id);
    
    if ($stmt->execute()) {
        $successMessage = "Student deleted successfully!";
    } else {
        $errorMessage = "Error deleting student: " . $stmt->error;
    }
    
    $stmt->close();
}

// Handle reset all sessions
if (isset($_GET['reset_all_sessions'])) {
    $reset_stmt = $conn->prepare("UPDATE students SET sessions = 30");
    
    if ($reset_stmt->execute()) {
        $successMessage = "All student sessions have been reset to 30!";
    } else {
        $errorMessage = "Error resetting sessions: " . $reset_stmt->error;
    }
    
    $reset_stmt->close();
}

// Handle search functionality
$search = '';
$where_clause = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $where_clause = " WHERE firstname LIKE ? OR lastname LIKE ? OR idno LIKE ? OR course LIKE ? OR email LIKE ?";
}

// Pagination setup
$results_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $results_per_page;

// Count total students for pagination
$count_query = "SELECT COUNT(*) as total FROM students" . $where_clause;
if (!empty($search)) {
    $stmt = $conn->prepare($count_query);
    $search_param = "%" . $search . "%";
    $stmt->bind_param("sssss", $search_param, $search_param, $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_students = $result->fetch_assoc()['total'];
    $stmt->close();
} else {
    $total_students = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
}

$total_pages = ceil($total_students / $results_per_page);

// Fetch students with pagination and search
$query = "SELECT * FROM students" . $where_clause . " ORDER BY lastname, firstname LIMIT ? OFFSET ?";

if (!empty($search)) {
    $stmt = $conn->prepare($query);
    $search_param = "%" . $search . "%";
    $stmt->bind_param("sssssii", $search_param, $search_param, $search_param, $search_param, $search_param, $results_per_page, $offset);
} else {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $results_per_page, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
$students = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Registered Students</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            height: 100%;
            margin: 0;
            background-color: #cfe2f3;
        } 
        .bar { 
            display: flex; 
            align-items: center; 
            height: 70px; 
            padding: 0 20px; 
        }
        .container {
            margin: 20px;
        }
        .student-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .search-container {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .search-container input {
            flex-grow: 1;
            margin-right: 10px;
            width: 20%;
        }
        .search-button {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .search-button i {
            font-size: 18px;
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
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            display: inline-block;
            padding: 8px 16px;
            text-decoration: none;
            background-color: #f1f1f1;
            color: black;
            border-radius: 5px;
            margin: 0 4px;
        }
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
        }
        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
        .student-actions {
            white-space: nowrap;
        }
        .reset-sessions-btn {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="bar w3-container w3-padding w3-bar w3-blue">
        <div href="admin.php" class="w3-bar-item"><b>STUDENTS</b></div>
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
    
    <div class="container">
        <h2><b><center>REGISTERED STUDENTS LIST</center></b></h2>
        
        <?php if (isset($successMessage)): ?>
            <div class="success-message"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        
        <?php if (isset($errorMessage)): ?>
            <div class="error-message"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        
        <div class="search-container">
            <form method="GET" action="" style="display: flex; align-items: center;">
                <input type="text" name="search" class="w3-input w3-border" placeholder="Search...." value="<?php echo htmlspecialchars($search); ?>" style="width: 20%; border-radius: 4px 0 0 4px; border-right: none;">
                <button type="submit" class="w3-button w3-blue search-button" style="border-radius: 0 4px 4px 0; height: 38px;">
                    <i class="fa fa-search"></i>
                </button>
            </form>
            <div>
                <a href="students.php?reset_all_sessions=1" class="w3-button w3-red reset-sessions-btn" onclick="return confirm('Are you sure you want to reset all student sessions to 30?')">Reset All Sessions</a>
            </div>
        </div>
        
        <div class="student-table">
            <table class="w3-table-all">
                <thead>
                    <tr class="w3-blue">
                        <th>ID No.</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Course</th>
                        <th>Year Level</th>
                        <th>Email</th>
                        <th>Remaining Sessions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="9" class="w3-center">No students found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['idno']); ?></td>
                                <td><?php echo htmlspecialchars($student['lastname']); ?></td>
                                <td><?php echo htmlspecialchars($student['firstname']); ?></td>
                                <td><?php echo htmlspecialchars($student['midname'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($student['course']); ?></td>
                                <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['sessions']); ?></td>
                                <td class="student-actions">
                                    <a href="students.php?delete=<?php echo $student['stud_id']; ?>" class="w3-button w3-small w3-red" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&laquo; First</a>
                    <a href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Prev</a>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" <?php echo $i == $current_page ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Next</a>
                    <a href="?page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Last &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>