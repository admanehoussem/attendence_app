<?php
/**
 * Courses API Endpoint
 */

require_once __DIR__ . '/../config/config.php';
requireLogin();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$db = $database->getConnection();

if ($db === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$user = getCurrentUser();

try {
    switch ($method) {
        case 'GET':
            if ($user['role'] === 'professor') {
                $stmt = $db->prepare("SELECT c.*, COUNT(DISTINCT e.student_id) as student_count 
                                     FROM courses c 
                                     LEFT JOIN enrollments e ON c.id = e.course_id 
                                     WHERE c.professor_id = ? 
                                     GROUP BY c.id 
                                     ORDER BY c.created_at DESC");
                $stmt->execute([$user['id']]);
                $courses = $stmt->fetchAll();
            } elseif ($user['role'] === 'student') {
                $stmt = $db->prepare("SELECT c.*, e.group_id, g.name as group_name 
                                     FROM enrollments e 
                                     JOIN courses c ON e.course_id = c.id 
                                     LEFT JOIN groups g ON e.group_id = g.id 
                                     WHERE e.student_id = ? 
                                     ORDER BY c.name");
                $stmt->execute([$user['id']]);
                $courses = $stmt->fetchAll();
            } else {
                // Administrator - all courses
                $stmt = $db->query("SELECT c.*, u.first_name, u.last_name, COUNT(DISTINCT e.student_id) as student_count 
                                   FROM courses c 
                                   LEFT JOIN users u ON c.professor_id = u.id 
                                   LEFT JOIN enrollments e ON c.id = e.course_id 
                                   GROUP BY c.id 
                                   ORDER BY c.created_at DESC");
                $courses = $stmt->fetchAll();
            }
            
            echo json_encode(['success' => true, 'data' => $courses]);
            break;
            
        case 'POST':
            requireRole(['professor', 'administrator']);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $code = sanitizeInput($data['code'] ?? '');
            $name = sanitizeInput($data['name'] ?? '');
            $professor_id = $data['professor_id'] ?? $user['id'];
            
            if (empty($code) || empty($name)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Course code and name are required']);
                exit();
            }
            
            $stmt = $db->prepare("INSERT INTO courses (code, name, professor_id) VALUES (?, ?, ?)");
            $stmt->execute([$code, $name, $professor_id]);
            
            echo json_encode(['success' => true, 'message' => 'Course created successfully', 'id' => $db->lastInsertId()]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (PDOException $e) {
    error_log("Courses API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Operation failed']);
}

