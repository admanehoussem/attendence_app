<?php
/**
 * Justifications API Endpoint
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
        case 'POST':
            requireRole(['student']);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $record_id = $data['record_id'] ?? null;
            $reason = sanitizeInput($data['reason'] ?? '');
            $file_path = $data['file_path'] ?? null;
            
            if (!$record_id || empty($reason)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Record ID and reason are required']);
                exit();
            }
            
            // Verify student owns the record
            $stmt = $db->prepare("SELECT ar.id FROM attendance_records ar WHERE ar.id = ? AND ar.student_id = ?");
            $stmt->execute([$record_id, $user['id']]);
            if (!$stmt->fetch()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit();
            }
            
            // Check if justification already exists
            $stmt = $db->prepare("SELECT id FROM justifications WHERE attendance_record_id = ?");
            $stmt->execute([$record_id]);
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Justification already submitted']);
                exit();
            }
            
            $stmt = $db->prepare("INSERT INTO justifications (attendance_record_id, student_id, reason, file_path) VALUES (?, ?, ?, ?)");
            $stmt->execute([$record_id, $user['id'], $reason, $file_path]);
            
            echo json_encode(['success' => true, 'message' => 'Justification submitted successfully', 'id' => $db->lastInsertId()]);
            break;
            
        case 'PUT':
            requireRole(['professor', 'administrator']);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $justification_id = $data['justification_id'] ?? null;
            $status = $data['status'] ?? null;
            $review_notes = sanitizeInput($data['review_notes'] ?? '');
            
            if (!$justification_id || !$status) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Justification ID and status are required']);
                exit();
            }
            
            if (!in_array($status, ['approved', 'rejected'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid status']);
                exit();
            }
            
            $stmt = $db->prepare("UPDATE justifications SET status = ?, reviewed_by = ?, reviewed_at = NOW(), review_notes = ? WHERE id = ?");
            $stmt->execute([$status, $user['id'], $review_notes, $justification_id]);
            
            // If approved, update attendance record status
            if ($status === 'approved') {
                $stmt = $db->prepare("UPDATE attendance_records ar 
                                     JOIN justifications j ON ar.id = j.attendance_record_id 
                                     SET ar.status = 'excused' 
                                     WHERE j.id = ?");
                $stmt->execute([$justification_id]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Justification reviewed successfully']);
            break;
            
        case 'GET':
            if ($user['role'] === 'student') {
                $course_id = $_GET['course_id'] ?? null;
                if ($course_id) {
                    $stmt = $db->prepare("SELECT j.*, ar.id as record_id, s.session_date, s.session_time 
                                         FROM justifications j 
                                         JOIN attendance_records ar ON j.attendance_record_id = ar.id 
                                         JOIN attendance_sessions s ON ar.session_id = s.id 
                                         WHERE j.student_id = ? AND s.course_id = ? 
                                         ORDER BY j.submitted_at DESC");
                    $stmt->execute([$user['id'], $course_id]);
                } else {
                    $stmt = $db->prepare("SELECT j.*, ar.id as record_id, s.session_date, s.session_time 
                                         FROM justifications j 
                                         JOIN attendance_records ar ON j.attendance_record_id = ar.id 
                                         JOIN attendance_sessions s ON ar.session_id = s.id 
                                         WHERE j.student_id = ? 
                                         ORDER BY j.submitted_at DESC");
                    $stmt->execute([$user['id']]);
                }
            } else {
                // Professor/Admin - all pending justifications
                $stmt = $db->query("SELECT j.*, u.first_name, u.last_name, u.username, s.session_date, c.name as course_name 
                                   FROM justifications j 
                                   JOIN attendance_records ar ON j.attendance_record_id = ar.id 
                                   JOIN users u ON j.student_id = u.id 
                                   JOIN attendance_sessions s ON ar.session_id = s.id 
                                   JOIN courses c ON s.course_id = c.id 
                                   WHERE j.status = 'pending' 
                                   ORDER BY j.submitted_at DESC");
            }
            
            $justifications = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $justifications]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (PDOException $e) {
    error_log("Justifications API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Operation failed']);
}

