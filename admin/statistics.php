<?php
require_once __DIR__ . '/../config/config.php';
requireRole(['administrator']);
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - Attendance System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h2>Statistics Dashboard</h2>
            <div class="nav-user">
                <a href="home.php" class="btn btn-secondary">Back to Admin</a>
                <a href="../logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <h1>System Statistics</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Students</h3>
                <p id="totalStudents">0</p>
            </div>
            <div class="stat-card">
                <h3>Total Professors</h3>
                <p id="totalProfessors">0</p>
            </div>
            <div class="stat-card">
                <h3>Total Courses</h3>
                <p id="totalCourses">0</p>
            </div>
            <div class="stat-card">
                <h3>Total Sessions</h3>
                <p id="totalSessions">0</p>
            </div>
            <div class="stat-card">
                <h3>Overall Attendance Rate</h3>
                <p id="attendanceRate">0%</p>
            </div>
        </div>
        
        <div class="charts-container">
            <div class="chart-card">
                <h3>Attendance Status Distribution</h3>
                <canvas id="attendanceChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Monthly Attendance Trend</h3>
                <canvas id="monthlyChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Attendance by Course</h3>
                <canvas id="courseChart"></canvas>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/admin-statistics.js"></script>
</body>
</html>

