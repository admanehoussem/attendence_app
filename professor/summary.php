<?php
require_once __DIR__ . '/../config/config.php';
requireRole(['professor']);
$user = getCurrentUser();
$course_id = $_GET['course_id'] ?? null;
$group_id = $_GET['group_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Summary - Attendance System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h2>Attendance Summary</h2>
            <div class="nav-user">
                <a href="home.php" class="btn btn-secondary">Back to Courses</a>
                <a href="../logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div id="summaryFilters" class="filters">
            <select id="courseFilter">
                <option value="">All Courses</option>
            </select>
            <select id="groupFilter">
                <option value="">All Groups</option>
            </select>
        </div>
        
        <div class="summary-stats">
            <div class="stat-card">
                <h3>Total Sessions</h3>
                <p id="totalSessions">0</p>
            </div>
            <div class="stat-card">
                <h3>Total Students</h3>
                <p id="totalStudents">0</p>
            </div>
            <div class="stat-card">
                <h3>Attendance Rate</h3>
                <p id="attendanceRate">0%</p>
            </div>
        </div>
        
        <div class="attendance-table-container">
            <table id="summaryTable" class="attendance-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Session Date</th>
                        <th>Status</th>
                        <th>Participation</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Summary data will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/common.js"></script>
    <script>
        const courseId = <?php echo $course_id ? "'$course_id'" : 'null'; ?>;
        const groupId = <?php echo $group_id ? "'$group_id'" : 'null'; ?>;
    </script>
    <script src="../assets/js/professor-summary.js"></script>
</body>
</html>

