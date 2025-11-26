<?php
/**
 * Database Setup Helper Script
 * Run this once to set up the database
 * 
 * Usage: php database/setup.php
 * Or access via browser: http://localhost/attendenceapp/database/setup.php
 */

// Database configuration
$host = 'localhost';
$dbname = 'attendance_system';
$username = 'root';
$password = '';

echo "=== Database Setup Script ===\n\n";

// Read schema file
$schemaFile = __DIR__ . '/schema.sql';

if (!file_exists($schemaFile)) {
    die("Error: schema.sql file not found!\n");
}

try {
    // Connect to MySQL (without database)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to MySQL server\n";
    
    // Read and execute schema
    $sql = file_get_contents($schemaFile);
    
    // Split by semicolons and execute each statement
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Ignore "database exists" errors
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "Warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "✓ Database schema imported successfully\n";
    echo "✓ Default administrator account created\n";
    echo "\n=== Setup Complete ===\n\n";
    echo "Default Login Credentials:\n";
    echo "Username: admin\n";
    echo "Password: admin123\n\n";
    echo "⚠️  IMPORTANT: Change the default password after first login!\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}

