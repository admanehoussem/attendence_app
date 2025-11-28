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
    <title>Course Management - Attendance System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h2>Course Management</h2>
            <div class="nav-user">
                <a href="home.php" class="btn btn-secondary">Back to Admin</a>
                <a href="../logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="management-header">
            <h1>All Courses</h1>
        </div>
        
        <div id="coursesList" class="courses-grid">
            <!-- Courses will be loaded here -->
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/common.js"></script>
    <script>
        $(document).ready(function() {
            loadCourses();
        });
        
        function loadCourses() {
            apiCall('courses.php')
                .then(data => {
                    if (data.success) {
                        displayCourses(data.data);
                    } else {
                        $('#coursesList').html('<p>Failed to load courses</p>');
                    }
                })
                .catch(error => {
                    $('#coursesList').html('<p>Error loading courses: ' + error.message + '</p>');
                });
        }
        
        function displayCourses(courses) {
            const container = $('#coursesList');
            container.empty();
            
            if (courses.length === 0) {
                container.html('<p>No courses found.</p>');
                return;
            }
            
            courses.forEach(course => {
                const card = $(`
                    <div class="course-card">
                        <h3>${course.name}</h3>
                        <p><strong>Code:</strong> ${course.code}</p>
                        <p><strong>Professor:</strong> ${course.first_name || 'N/A'} ${course.last_name || ''}</p>
                        <p><strong>Students:</strong> ${course.student_count || 0}</p>
                    </div>
                `);
                container.append(card);
            });
        }
    </script>
</body>
</html>

