<?php
/**
 * Statistics API Endpoint
 */

require_once __DIR__ . '/../config/config.php';
requireRole(['administrator']);

header('Content-Type: application/json');

$db = $database->getConnection();

if ($db === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

try {
    // Overall statistics
    $stats = [];
    
    // Total counts
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'student'");
    $stats['total_students'] = $stmt->fetch()['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'professor'");
    $stats['total_professors'] = $stmt->fetch()['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM courses");
    $stats['total_courses'] = $stmt->fetch()['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM attendance_sessions");
    $stats['total_sessions'] = $stmt->fetch()['total'];
    
    // Attendance statistics
    $stmt = $db->query("SELECT 
        COUNT(*) as total_records,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
        SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused_count
        FROM attendance_records");
    $attendance_stats = $stmt->fetch();
    $stats['attendance'] = $attendance_stats;
    
    // Attendance rate
    if ($attendance_stats['total_records'] > 0) {
        $stats['attendance_rate'] = round(($attendance_stats['present_count'] / $attendance_stats['total_records']) * 100, 2);
    } else {
        $stats['attendance_rate'] = 0;
    }
    
    // Justification statistics
    $stmt = $db->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
        FROM justifications");
    $stats['justifications'] = $stmt->fetch();
    
    // Attendance by course
    $stmt = $db->query("SELECT 
        c.id, c.name, c.code,
        COUNT(DISTINCT s.id) as session_count,
        COUNT(ar.id) as record_count,
        SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count
        FROM courses c
        LEFT JOIN attendance_sessions s ON c.id = s.course_id
        LEFT JOIN attendance_records ar ON s.id = ar.session_id
        GROUP BY c.id
        ORDER BY c.name");
    $stats['by_course'] = $stmt->fetchAll();
    
    // Monthly attendance trend
    $stmt = $db->query("SELECT 
        DATE_FORMAT(s.session_date, '%Y-%m') as month,
        COUNT(DISTINCT s.id) as session_count,
        COUNT(ar.id) as record_count,
        SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count
        FROM attendance_sessions s
        LEFT JOIN attendance_records ar ON s.id = ar.session_id
        GROUP BY month
        ORDER BY month DESC
        LIMIT 12");
    $stats['monthly_trend'] = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $stats]);
    
} catch (PDOException $e) {
    error_log("Statistics API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Operation failed']);
}

