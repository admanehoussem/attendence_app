<?php
/**
 * Authentication API Endpoint
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$db = $database->getConnection();

if ($db === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

switch ($method) {
    case 'POST':
        if ($action === 'login') {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            // Fallback to form-encoded POST when JSON body is not provided
            if (!is_array($data) || empty($data)) {
                $data = $_POST;
            }

            $username = sanitizeInput($data['username'] ?? '');
            $password = $data['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Username and password are required']);
                exit();
            }
            
            try {
                $stmt = $db->prepare("SELECT id, username, email, password_hash, first_name, last_name, role FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login successful',
                        'user' => [
                            'id' => $user['id'],
                            'username' => $user['username'],
                            'role' => $user['role'],
                            'first_name' => $user['first_name'],
                            'last_name' => $user['last_name']
                        ]
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
                }
            } catch (PDOException $e) {
                error_log("Login Error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Login failed']);
            }
        } elseif ($action === 'logout') {
            session_destroy();
            echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
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

