<?php
require_once __DIR__ . '/config/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getCurrentUser();
$redirectPage = '';

switch ($user['role']) {
    case 'professor':
        $redirectPage = 'professor/home.php';
        break;
    case 'student':
        $redirectPage = 'student/home.php';
        break;
    case 'administrator':
        $redirectPage = 'admin/home.php';
        break;
    default:
        $redirectPage = 'login.php';
}

header("Location: $redirectPage");
exit();

