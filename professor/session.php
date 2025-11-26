<?php
require_once __DIR__ . '/../config/config.php';
requireRole(['professor']);
$user = getCurrentUser();
$session_id = $_GET['id'] ?? null;
if (!$session_id) {
    header('Location: home.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - Attendance System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h2>Mark Attendance</h2>
            <div class="nav-user">
                <a href="home.php" class="btn btn-secondary">Back to Courses</a>
                <a href="../logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div id="sessionInfo" class="session-header">
            <!-- Session info will be loaded here -->
        </div>
        
        <div class="attendance-controls">
            <button id="closeSessionBtn" class="btn btn-danger">Close Session</button>
        </div>
        
        <div class="attendance-table-container">
            <table id="attendanceTable" class="attendance-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Status</th>
                        <th>Participation (0-10)</th>
                        <th>Behavior Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Attendance records will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/common.js"></script>
    <script>
        const sessionId = <?php echo $session_id; ?>;
    </script>
    <script src="../assets/js/professor-session.js"></script>
</body>
</html>

