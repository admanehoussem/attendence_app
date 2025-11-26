<?php
/**
 * Attendance Records API Endpoint
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
            $session_id = $_GET['session_id'] ?? null;
            $course_id = $_GET['course_id'] ?? null;
            $group_id = $_GET['group_id'] ?? null;
            
            if ($user['role'] === 'professor') {
                if ($session_id) {
                    // Get attendance for a specific session
                    $stmt = $db->prepare("SELECT ar.*, u.first_name, u.last_name, u.username 
                                         FROM attendance_records ar 
                                         JOIN users u ON ar.student_id = u.id 
                                         WHERE ar.session_id = ? 
                                         ORDER BY u.last_name, u.first_name");
                    $stmt->execute([$session_id]);
                    $records = $stmt->fetchAll();
                } elseif ($course_id) {
                    // Get attendance summary for a course
                    $sql = "SELECT ar.*, u.first_name, u.last_name, s.session_date, s.session_time, g.name as group_name 
                           FROM attendance_records ar 
                           JOIN users u ON ar.student_id = u.id 
                           JOIN attendance_sessions s ON ar.session_id = s.id 
                           LEFT JOIN groups g ON s.group_id = g.id 
                           WHERE s.course_id = ?";
                    $params = [$course_id];
                    
                    if ($group_id) {
                        $sql .= " AND s.group_id = ?";
                        $params[] = $group_id;
                    }
                    
                    $sql .= " ORDER BY s.session_date DESC, u.last_name";
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                    $records = $stmt->fetchAll();
                } else {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Session ID or Course ID is required']);
                    exit();
                }
            } elseif ($user['role'] === 'student') {
                if ($course_id) {
                    $stmt = $db->prepare("SELECT ar.*, s.session_date, s.session_time, s.status as session_status, 
                                         j.id as justification_id, j.status as justification_status 
                                         FROM attendance_records ar 
                                         JOIN attendance_sessions s ON ar.session_id = s.id 
                                         LEFT JOIN justifications j ON ar.id = j.attendance_record_id 
                                         WHERE s.course_id = ? AND ar.student_id = ? 
                                         ORDER BY s.session_date DESC");
                    $stmt->execute([$course_id, $user['id']]);
                    $records = $stmt->fetchAll();
                } else {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Course ID is required']);
                    exit();
                }
            } else {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit();
            }
            
            echo json_encode(['success' => true, 'data' => $records]);
            break;
            
        case 'PUT':
            requireRole(['professor']);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $record_id = $data['record_id'] ?? null;
            $status = $data['status'] ?? null;
            $participation_score = $data['participation_score'] ?? null;
            $behavior_notes = $data['behavior_notes'] ?? null;
            
            if (!$record_id || !$status) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Record ID and status are required']);
                exit();
            }
            
            // Verify professor owns the session
            $stmt = $db->prepare("SELECT s.id FROM attendance_records ar 
                                 JOIN attendance_sessions s ON ar.session_id = s.id 
                                 JOIN courses c ON s.course_id = c.id 
                                 WHERE ar.id = ? AND c.professor_id = ?");
            $stmt->execute([$record_id, $user['id']]);
            if (!$stmt->fetch()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit();
            }
            
            $updates = ["status = ?"];
            $params = [$status];
            
            if ($participation_score !== null) {
                $updates[] = "participation_score = ?";
                $params[] = $participation_score;
            }
            
            if ($behavior_notes !== null) {
                $updates[] = "behavior_notes = ?";
                $params[] = sanitizeInput($behavior_notes);
            }
            
            $params[] = $record_id;
            $sql = "UPDATE attendance_records SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode(['success' => true, 'message' => 'Attendance updated successfully']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (PDOException $e) {
    error_log("Attendance API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Operation failed']);
}

