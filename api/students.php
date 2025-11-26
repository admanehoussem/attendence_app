<?php
/**
 * Students Management API Endpoint
 */

require_once __DIR__ . '/../config/config.php';
requireRole(['administrator']);

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$db = $database->getConnection();

if ($db === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

try {
    switch ($method) {
        case 'GET':
            $stmt = $db->query("SELECT id, username, email, first_name, last_name, created_at 
                               FROM users 
                               WHERE role = 'student' 
                               ORDER BY last_name, first_name");
            $students = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $students]);
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $username = sanitizeInput($data['username'] ?? '');
            $email = sanitizeInput($data['email'] ?? '');
            $password = $data['password'] ?? '';
            $first_name = sanitizeInput($data['first_name'] ?? '');
            $last_name = sanitizeInput($data['last_name'] ?? '');
            
            if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                exit();
            }
            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, 'student')");
            $stmt->execute([$username, $email, $password_hash, $first_name, $last_name]);
            
            echo json_encode(['success' => true, 'message' => 'Student created successfully', 'id' => $db->lastInsertId()]);
            break;
            
        case 'DELETE':
            $student_id = $_GET['id'] ?? null;
            if (!$student_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Student ID is required']);
                exit();
            }
            
            $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
            $stmt->execute([$student_id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Student not found']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (PDOException $e) {
    error_log("Students API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Operation failed']);
}

