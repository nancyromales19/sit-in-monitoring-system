<?php

ob_start();

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debugging function to help trace JSON issues
function debugJsonResponse($data) {
    // Attempt to JSON encode with error checking
    $json = json_encode($data);
    
    // Check for JSON encoding errors
    if ($json === false) {
        $errorCode = json_last_error();
        $errorMessage = json_last_error_msg();
        
        // Log the detailed error
        error_log("JSON Encoding Error: Code $errorCode - $errorMessage");
        error_log("Data causing the error: " . print_r($data, true));
        
        // Fallback response
        return json_encode([
            'success' => false, 
            'error' => 'JSON Encoding Failed',
            'debug_info' => [
                'error_code' => $errorCode,
                'error_message' => $errorMessage
            ]
        ]);
    }
    
    return $json;
}

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

// Predefined options for purpose and lab
$purposes = [
    'C Programming',
    'Java Programming',
    'C# Programming',
    'ASP Net',
    'PHP',
    'C Programming'
];

$labs = [];
$lab_query = "SELECT lab_room FROM laboratory";
$lab_result = $conn->query($lab_query);
if ($lab_result && $lab_result->num_rows > 0) {
    while ($row = $lab_result->fetch_assoc()) {
        $labs[] = $row['lab_room'];
    }
}

// Handle student search via AJAX
if (isset($_GET['search_student'])) {
    ob_clean();
    $search_term = $conn->real_escape_string($_GET['search_student']);
    
    // Search for student SPECIFICALLY by ID number
    $search_query = "SELECT * FROM students WHERE idno = '$search_term'";
    
    $search_result = $conn->query($search_query);
    
    $response = ['success' => false];
    if ($search_result && $search_result->num_rows > 0) {
        $student = $search_result->fetch_assoc();
        $response = [
            'success' => true,
            'idno' => $student['idno'],
            'full_name' => $student['firstname'] . ' ' . $student['lastname'],
            'sessions' => $student['sessions'] ?? ''
        ];
    }
    
    // Ensure proper JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    ob_end_flush();
    exit();
}

// Handle sit in processing via AJAX
if (isset($_POST['action']) && $_POST['action'] === 'sit_in') {
    ob_clean();

    $response = ['success' => false, 'error' => 'Sit-in processing failed'];

    // Remove the try-catch block and replace with direct error handling
    $stud_id = $conn->real_escape_string($_POST['stud_id']);
    $purpose = $conn->real_escape_string($_POST['purpose']);
    $lab = $conn->real_escape_string($_POST['lab']);
    $user_id = $_SESSION['user_id']; // Assuming you have user_id in session

    // Validate required fields
    if (empty($stud_id) || empty($purpose) || empty($lab)) {
        $response['error'] = 'Missing required fields';
        goto sendResponse;
    }

    // Begin transaction for atomic operations
    $conn->begin_transaction();

    // Check if student exists and has available sessions
    $student_check_query = "SELECT stud_id, sessions FROM students WHERE idno = '$stud_id'";
    $student_result = $conn->query($student_check_query);
    
    if (!$student_result || $student_result->num_rows === 0) {
        $response['error'] = 'Student not found';
        goto sendResponse;
    }

    $student = $student_result->fetch_assoc();
    $student_db_id = $student['stud_id'];
    $current_sessions = $student['sessions'] ?? 0;

    // Check for available sessions
    if ($current_sessions <= 0) {
        $response['error'] = 'No available sessions';
        goto sendResponse;
    }

    // Check for existing active sit-in
    $active_sitin_query = "SELECT * FROM sitin WHERE stud_id = '$student_db_id' AND time_out IS NULL";
    $active_sitin_result = $conn->query($active_sitin_query);
    
    if ($active_sitin_result && $active_sitin_result->num_rows > 0) {
        $response['error'] = 'Student already has an active sit-in';
        goto sendResponse;
    }

    // Get lab_id
    $lab_query = "SELECT lab_id FROM laboratory WHERE lab_room = '$lab'";
    $lab_result = $conn->query($lab_query);
    
    if (!$lab_result || $lab_result->num_rows === 0) {
        $response['error'] = 'Invalid lab selection';
        goto sendResponse;
    }

    $lab_data = $lab_result->fetch_assoc();
    $lab_id = $lab_data['lab_id'];

    // Deduct a session
    $update_sessions_query = "UPDATE students SET sessions = sessions - 1 WHERE stud_id = '$student_db_id'";
    if (!$conn->query($update_sessions_query)) {
        $response['error'] = 'Failed to update sessions';
        goto sendResponse;
    }

    // Insert sit in record
    $sit_in_query = "INSERT INTO sitin (stud_id, user_id, lab_id, purpose, time_in) 
                     VALUES ('$student_db_id', '$user_id', '$lab_id', '$purpose', NOW())";
    
    if ($conn->query($sit_in_query)) {
        // Commit the transaction
        $conn->commit();

        $response = [
            'success' => true,
            'sitin_id' => (string)$conn->insert_id,
            'time_in' => date('Y-m-d H:i:s'),
            'remaining_sessions' => (string)($current_sessions - 1)
        ];
    }

    // Label for error responses
    sendResponse:
    $jsonResponse = debugJsonResponse($response);
    
    // Ensure clean JSON response
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit();
}

// Handle time out processing via AJAX
if (isset($_POST['action']) && $_POST['action'] === 'time_out') {
    ob_clean();
    $response = ['success' => false, 'error' => 'Time out processing failed'];

    try {
        // Sanitize and validate inputs
        $sitin_id = $conn->real_escape_string($_POST['sitin_id']);

        // Begin transaction
        $conn->begin_transaction();

        // First, verify the sit-in record exists and is not already timed out
        $verify_query = "SELECT stud_id FROM sitin WHERE sitin_id = '$sitin_id' AND time_out IS NULL";
        $verify_result = $conn->query($verify_query);

        if (!$verify_result || $verify_result->num_rows === 0) {
            throw new Exception('Invalid or already timed out sit-in record');
        }

        // Update sit in record with time out
        $time_out_query = "UPDATE sitin SET time_out = NOW() WHERE sitin_id = '$sitin_id'";
        
        if ($conn->query($time_out_query)) {
            // Commit the transaction
            $conn->commit();
            $response['success'] = true;
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        // Rollback the transaction
        $conn->rollback();
        $response['error'] = $e->getMessage();
    }
    $jsonResponse = debugJsonResponse($response);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit();
}

// Fetch current sit-in records
$current_sit_ins_query = "
    SELECT s.sitin_id, st.idno, 
           CONCAT(st.firstname, ' ', st.lastname) AS full_name, 
           s.purpose, l.lab_room, st.sessions, s.time_in
    FROM sitin s
    JOIN students st ON s.stud_id = st.stud_id
    JOIN laboratory l ON s.lab_id = l.lab_id
    WHERE s.time_out IS NULL
    ORDER BY s.time_in DESC
";
$current_sit_ins_result = $conn->query($current_sit_ins_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Current Sit In</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        .navigation {
            background-color: #2196F3;
            overflow: hidden;
        }
        .navigation a {
            float: right;
            display: block;
            color: white;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }
        .navigation a:hover {
            background-color: #1E88E5;
        }
        .container {
            padding: 20px;
        }
        .sit-in-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .sit-in-table th {
            background-color: #2196F3;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .sit-in-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .sit-in-button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
        }
        .sit-in-student-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            margin-bottom: 10px;
            cursor: pointer;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 500px;
            border-radius: 5px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .modal-input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
        }
        .modal-select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            background-color: white;
        }
        .modal-search-input {
            width: 70%;
            padding: 10px;
            margin-right: 10px;
        }
        .modal-search-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
        }
        .modal-sit-in-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            width: 100%;
            margin-top: 10px;
            cursor: pointer;
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
        <div class="w3-bar-item"><b>SITIN</b></div>
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
        <button class="sit-in-student-button" onclick="openSitInModal()">+ Sit In Student</button>
        
        <table class="sit-in-table">
            <thead>
                <tr>
                    <th>SIT IN ID NO.</th>
                    <th>ID NUMBER</th>
                    <th>NAME</th>
                    <th>PURPOSE</th>
                    <th>SIT LAB</th>
                    <th>SESSION</th>
                    <th>TIME IN</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody id="sitInTableBody">
                <?php 
                if ($current_sit_ins_result && $current_sit_ins_result->num_rows > 0) {
                    while ($sit_in = $current_sit_ins_result->fetch_assoc()) {
                        echo "<tr data-sitin-id='{$sit_in['sitin_id']}'>";
                        echo "<td>" . htmlspecialchars($sit_in['sitin_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($sit_in['idno']) . "</td>";
                        echo "<td>" . htmlspecialchars($sit_in['full_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($sit_in['purpose']) . "</td>";
                        echo "<td>" . htmlspecialchars($sit_in['lab_room']) . "</td>";
                        echo "<td>" . htmlspecialchars($sit_in['sessions'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($sit_in['time_in']) . "</td>";
                        echo "<td><button class='sit-in-button' onclick='timeOutStudent({$sit_in['sitin_id']})'>Time Out</button></td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

   <!-- Sit In Modal -->
   <div id="sitInModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeSitInModal()">&times;</span>
            <h2 class="w3-center"><b>SIT IN FORM</b></h2><br>
            <div>
                <input type="text" id="studentSearchInput" class="modal-search-input" placeholder="Search student ID">
                <button class="modal-search-button" onclick="searchStudent()">SEARCH</button>
    
            </div><br>
            <label><b>Student Id no.</b></label>
            <input type="text" id="idNumberInput" class="modal-input" placeholder="Student idno" readonly>
            <label><b>Full Name</b></label>
            <input type="text" id="fullNameInput" class="modal-input" placeholder="Student fullname" readonly>
            <label><b>Remaining Sessions</b></label>
            <input type="text" id="sessionsInput" class="modal-input" placeholder="Student remaining sessions" readonly>
            
            <label><b>Purpose</b></label>
            <select class="modal-select" id="purposeSelect" name="purpose">
                <option value=""></option>
                <?php foreach($purposes as $purpose): ?>
                    <option value="<?php echo htmlspecialchars($purpose); ?>">
                        <?php echo htmlspecialchars($purpose); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label><b>Laboratory</b></label>
            <select class="modal-select" id="labSelect" name="lab">
                <option value=""></option>
                 <?php foreach($labs as $lab): ?>
                    <option value="<?php echo htmlspecialchars($lab); ?>">
                        <?php echo htmlspecialchars($lab); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="modal-sit-in-button" onclick="submitSitIn()">SIT IN</button>
        </div>
    </div>

    <script>
         function openSitInModal() {
    // Reset modal fields
    document.getElementById('studentSearchInput').value = '';
    document.getElementById('idNumberInput').value = '';
    document.getElementById('fullNameInput').value = '';
    document.getElementById('sessionsInput').value = '';
    document.getElementById('purposeSelect').selectedIndex = 0;
    document.getElementById('labSelect').selectedIndex = 0;

    // Removed the line referencing non-existent 'studentSearchResults'
    document.getElementById('sitInModal').style.display = 'block';
}

function closeSitInModal() {
    document.getElementById('sitInModal').style.display = 'none';
}

function searchStudent() {
    const searchInput = document.getElementById('studentSearchInput');
    const idNumberInput = document.getElementById('idNumberInput');
    const fullNameInput = document.getElementById('fullNameInput');
    const sessionsInput = document.getElementById('sessionsInput');

    // Reset previous state
    idNumberInput.value = '';
    fullNameInput.value = '';
    sessionsInput.value = '';

    const searchTerm = searchInput.value.trim();
    if (searchTerm === '') {
        alert('Please enter a student ID number');
        return;
    }

    fetch(`sitin.php?search_student=${encodeURIComponent(searchTerm)}`)
        .then(response => {
            // Check if the response is OK
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Student found
                idNumberInput.value = data.idno;
                fullNameInput.value = data.full_name;
                sessionsInput.value = data.sessions;
            } else {
                // Handle unsuccessful search
                alert(data.error || 'Student not found');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error searching for student: ' + error.message);
        });
}

function submitSitIn() {
    const idNumber = document.getElementById('idNumberInput').value;
    const fullName = document.getElementById('fullNameInput').value;
    const purpose = document.getElementById('purposeSelect').value;
    const lab = document.getElementById('labSelect').value;

    // Enhanced validation
    if (!idNumber || !fullName || !purpose || !lab) {
        alert('Please fill in all required fields');
        return;
    }

    // Disable submit button during processing
    const submitButton = document.querySelector('.modal-sit-in-button');
    submitButton.disabled = true;
    submitButton.textContent = 'Processing...';

    // Create FormData to ensure proper encoding
    const formData = new FormData();
    formData.append('action', 'sit_in');
    formData.append('stud_id', idNumber);
    formData.append('purpose', purpose);
    formData.append('lab', lab);

    // Send AJAX request to save sit-in
    fetch('sitin.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Log full response for debugging
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        // Check content type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Response is not JSON');
        }
        
        return response.json();
    })
    .then(data => {
        // Reset button state
        submitButton.disabled = false;
        submitButton.textContent = 'SIT IN';

        if (data.success) {
            // Successful sit-in
            alert('Sit-in processed successfully');
            location.reload();
        } else {
            // Handle specific error scenarios
            throw new Error(data.error || 'Unknown error occurred');
        }
    })
    .catch(error => {
        console.error('Full Error:', error);
        
        // More detailed error logging
        if (error instanceof TypeError) {
            console.error('TypeError details:', error.message);
        }
        
        alert('Failed to process sit-in: ' + error.message);
        
        // Reset button state
        submitButton.disabled = false;
        submitButton.textContent = 'SIT IN';
    });
    
    // Close modal
    closeSitInModal();
}

        function timeOutStudent(sitinId) {
            fetch('sitin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=time_out&sitin_id=${encodeURIComponent(sitinId)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload the page to refresh the table
                    location.reload();
                } else {
                    alert('Failed to process time out: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
            console.error('Full Error:', error);
            alert('Failed to process sit-in: ' + error.message);
        });
        }

// Close modal if clicked outside
window.onclick = function(event) {
    var modal = document.getElementById('sitInModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
    </script>
</body>
</html>