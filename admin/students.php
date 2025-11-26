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
    <title>Student Management - Attendance System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h2>Student Management</h2>
            <div class="nav-user">
                <a href="home.php" class="btn btn-secondary">Back to Admin</a>
                <a href="../logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="management-header">
            <h1>Student Management</h1>
            <div class="action-buttons">
                <button id="addStudentBtn" class="btn btn-primary">Add Student</button>
                <button id="importBtn" class="btn btn-secondary">Import from Excel</button>
                <a href="../api/import_export.php?action=export" class="btn btn-secondary">Export to Excel</a>
            </div>
        </div>
        
        <div class="students-table-container">
            <table id="studentsTable" class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Students will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Add Student Modal -->
    <div id="addStudentModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add New Student</h2>
            <form id="addStudentForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Student</button>
            </form>
        </div>
    </div>
    
    <!-- Import Modal -->
    <div id="importModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Import Students from Excel</h2>
            <form id="importForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="importFile">Select CSV/Excel File</label>
                    <input type="file" id="importFile" accept=".csv,.xlsx,.xls" required>
                    <small>Format: ID, Username, Email, First Name, Last Name, Password (optional)</small>
                </div>
                <button type="submit" class="btn btn-primary">Import</button>
            </form>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/admin-students.js"></script>
</body>
</html>

