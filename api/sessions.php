<?php
/**
 * Attendance Sessions API Endpoint
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
            $course_id = $_GET['course_id'] ?? null;
            $group_id = $_GET['group_id'] ?? null;
            
            if ($user['role'] === 'professor') {
                $sql = "SELECT s.*, c.name as course_name, g.name as group_name 
                       FROM attendance_sessions s 
                       JOIN courses c ON s.course_id = c.id 
                       LEFT JOIN groups g ON s.group_id = g.id 
                       WHERE c.professor_id = ?";
                $params = [$user['id']];
                
                if ($course_id) {
                    $sql .= " AND s.course_id = ?";
                    $params[] = $course_id;
                }
                if ($group_id) {
                    $sql .= " AND s.group_id = ?";
                    $params[] = $group_id;
                }
                
                $sql .= " ORDER BY s.session_date DESC, s.session_time DESC";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
            } else {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit();
            }
            
            $sessions = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $sessions]);
            break;
            
        case 'POST':
            requireRole(['professor']);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $course_id = $data['course_id'] ?? null;
            $group_id = $data['group_id'] ?? null;
            $session_date = $data['session_date'] ?? date('Y-m-d');
            $session_time = $data['session_time'] ?? date('H:i:s');
            
            if (!$course_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Course ID is required']);
                exit();
            }
            
            // Verify professor owns the course
            $stmt = $db->prepare("SELECT id FROM courses WHERE id = ? AND professor_id = ?");
            $stmt->execute([$course_id, $user['id']]);
            if (!$stmt->fetch()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit();
            }
            
            $stmt = $db->prepare("INSERT INTO attendance_sessions (course_id, group_id, session_date, session_time, created_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$course_id, $group_id, $session_date, $session_time, $user['id']]);
            $session_id = $db->lastInsertId();
            
            // Initialize attendance records for enrolled students
            $stmt = $db->prepare("INSERT INTO attendance_records (session_id, student_id, status) 
                                 SELECT ?, e.student_id, 'absent' 
                                 FROM enrollments e 
                                 WHERE e.course_id = ? AND (? IS NULL OR e.group_id = ?)");
            $stmt->execute([$session_id, $course_id, $group_id, $group_id]);
            
            echo json_encode(['success' => true, 'message' => 'Session created successfully', 'id' => $session_id]);
            break;
            
        case 'PUT':
            requireRole(['professor']);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $session_id = $data['session_id'] ?? null;
            $action = $data['action'] ?? '';
            
            if (!$session_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Session ID is required']);
                exit();
            }
            
            // Verify professor owns the session
            $stmt = $db->prepare("SELECT s.id FROM attendance_sessions s 
                                 JOIN courses c ON s.course_id = c.id 
                                 WHERE s.id = ? AND c.professor_id = ?");
            $stmt->execute([$session_id, $user['id']]);
            if (!$stmt->fetch()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit();
            }
            
            if ($action === 'close') {
                $stmt = $db->prepare("UPDATE attendance_sessions SET status = 'closed', closed_at = NOW() WHERE id = ?");
                $stmt->execute([$session_id]);
                echo json_encode(['success' => true, 'message' => 'Session closed successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (PDOException $e) {
    error_log("Sessions API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Operation failed']);
}

