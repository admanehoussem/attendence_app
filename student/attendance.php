<?php
require_once __DIR__ . '/../config/config.php';
requireRole(['student']);
$user = getCurrentUser();
$course_id = $_GET['course_id'] ?? null;
if (!$course_id) {
    header('Location: home.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Attendance - Attendance System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h2>Course Attendance</h2>
            <div class="nav-user">
                <a href="home.php" class="btn btn-secondary">Back to Courses</a>
                <a href="../logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div id="courseInfo" class="course-header">
            <!-- Course info will be loaded here -->
        </div>
        
        <div class="attendance-stats">
            <div class="stat-card">
                <h3>Total Sessions</h3>
                <p id="totalSessions">0</p>
            </div>
            <div class="stat-card">
                <h3>Present</h3>
                <p id="presentCount">0</p>
            </div>
            <div class="stat-card">
                <h3>Absent</h3>
                <p id="absentCount">0</p>
            </div>
            <div class="stat-card">
                <h3>Attendance Rate</h3>
                <p id="attendanceRate">0%</p>
            </div>
        </div>
        
        <div class="attendance-table-container">
            <table id="attendanceTable" class="attendance-table">
                <thead>
                    <tr>
                        <th>Session Date</th>
                        <th>Status</th>
                        <th>Justification</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Attendance records will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Justification Modal -->
    <div id="justificationModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Submit Justification</h2>
            <form id="justificationForm">
                <input type="hidden" id="recordId">
                <div class="form-group">
                    <label for="reason">Reason</label>
                    <textarea id="reason" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="justificationFile">Upload Document (Optional)</label>
                    <input type="file" id="justificationFile" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/common.js"></script>
    <script>
        const courseId = <?php echo $course_id; ?>;
    </script>
    <script src="../assets/js/student-attendance.js"></script>
</body>
</html>

