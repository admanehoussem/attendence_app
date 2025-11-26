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
    <title>Admin Home - Attendance System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h2>Administrator Dashboard</h2>
            <div class="nav-user">
                <span>Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                <a href="../logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="admin-menu">
            <a href="statistics.php" class="admin-card">
                <h3>Statistics</h3>
                <p>View system statistics and reports</p>
            </a>
            <a href="students.php" class="admin-card">
                <h3>Student Management</h3>
                <p>Manage student accounts and import/export</p>
            </a>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/common.js"></script>
</body>
</html>

