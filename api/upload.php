<?php
/**
 * File Upload Handler for Justifications
 */

require_once __DIR__ . '/../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File upload failed']);
    exit();
}

$file = $_FILES['file'];
$fileSize = $file['size'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileError = $file['error'];

// Validate file size
if ($fileSize > MAX_FILE_SIZE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File size exceeds maximum allowed (5MB)']);
    exit();
}

// Validate file type
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
if (!in_array($fileExt, ALLOWED_FILE_TYPES)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: PDF, DOC, DOCX, JPG, JPEG, PNG']);
    exit();
}

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Generate unique filename
$newFileName = uniqid('justification_', true) . '.' . $fileExt;
$destination = UPLOAD_DIR . $newFileName;

// Move uploaded file
if (move_uploaded_file($fileTmpName, $destination)) {
    $relativePath = 'uploads/justifications/' . $newFileName;
    echo json_encode([
        'success' => true,
        'message' => 'File uploaded successfully',
        'file_path' => $relativePath
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
}

