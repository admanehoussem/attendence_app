<?php
require_once __DIR__ . '/../config/config.php';
requireRole(['professor']);
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Home - Attendance System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h2>Attendance Management System</h2>
            <div class="nav-user">
                <span>Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                <a href="../logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="management-header">
            <h1>My Courses</h1>
            <button id="createSessionBtn" class="btn btn-primary">Create New Session</button>
        </div>
        <div id="coursesList" class="courses-grid">
            <!-- Courses will be loaded here -->
        </div>
    </div>
    
    <!-- Create Session Modal -->
    <div id="createSessionModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Create New Attendance Session</h2>
            <form id="createSessionForm">
                <div class="form-group">
                    <label for="sessionCourse">Course</label>
                    <select id="sessionCourse" required>
                        <option value="">Select a course</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sessionGroup">Group (Optional)</label>
                    <select id="sessionGroup">
                        <option value="">All Groups</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sessionDate">Date</label>
                    <input type="date" id="sessionDate" required>
                </div>
                <div class="form-group">
                    <label for="sessionTime">Time</label>
                    <input type="time" id="sessionTime" required>
                </div>
                <button type="submit" class="btn btn-primary">Create Session</button>
            </form>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/professor.js"></script>
</body>
</html>

