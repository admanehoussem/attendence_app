<?php
/**
 * Import/Export API Endpoint
 */

require_once __DIR__ . '/../config/config.php';
requireRole(['administrator']);

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$db = $database->getConnection();

if ($db === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

try {
    if ($action === 'export') {
        // Export students to Excel format (CSV for simplicity, compatible with Excel)
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=students_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        fputcsv($output, ['ID', 'Username', 'Email', 'First Name', 'Last Name', 'Created At']);
        
        $stmt = $db->query("SELECT id, username, email, first_name, last_name, created_at 
                           FROM users 
                           WHERE role = 'student' 
                           ORDER BY last_name, first_name");
        
        while ($row = $stmt->fetch()) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit();
        
    } elseif ($action === 'import' && $method === 'POST') {
        // Handle file upload
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File upload failed']);
            exit();
        }
        
        $file = $_FILES['file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        if ($handle === false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Could not read file']);
            exit();
        }
        
        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
            rewind($handle);
        }
        
        // Read header
        $header = fgetcsv($handle);
        $imported = 0;
        $errors = [];
        
        // Start transaction
        $db->beginTransaction();
        
        try {
            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) < 5) continue;
                
                $username = sanitizeInput($data[1] ?? '');
                $email = sanitizeInput($data[2] ?? '');
                $first_name = sanitizeInput($data[3] ?? '');
                $last_name = sanitizeInput($data[4] ?? '');
                $password = $data[5] ?? 'student123'; // Default password
                
                if (empty($username) || empty($email) || empty($first_name) || empty($last_name)) {
                    $errors[] = "Row skipped: Missing required fields";
                    continue;
                }
                
                // Check if user exists
                $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                if ($stmt->fetch()) {
                    $errors[] = "User $username already exists";
                    continue;
                }
                
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, 'student')");
                $stmt->execute([$username, $email, $password_hash, $first_name, $last_name]);
                $imported++;
            }
            
            $db->commit();
            fclose($handle);
            
            echo json_encode([
                'success' => true,
                'message' => "Imported $imported students successfully",
                'imported' => $imported,
                'errors' => $errors
            ]);
            
        } catch (Exception $e) {
            $db->rollBack();
            fclose($handle);
            throw $e;
        }
        
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (PDOException $e) {
    error_log("Import/Export API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Operation failed']);
}

