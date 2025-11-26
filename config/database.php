<?php
/**
 * Database Configuration File
 * Handles database connection with proper error handling
 */

class Database {
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $charset;
    private $conn;
    
    public function __construct() {
        // Database configuration - Update these values according to your setup
        $this->host = 'localhost';
        $this->dbname = 'attendance_system';
        $this->username = 'root';
        $this->password = '';
        $this->charset = 'utf8mb4';
    }
    
    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection() {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
                
            } catch (PDOException $e) {
                // Log error
                error_log("Database Connection Error: " . $e->getMessage());
                
                // Return null on error (handle gracefully in application)
                return null;
            }
        }
        
        return $this->conn;
    }
    
    /**
     * Close database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }
}

// Create global database instance
$database = new Database();

