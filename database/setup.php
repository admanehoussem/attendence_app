<?php
/**
 * Database Setup Helper Script
 * Run this once to set up the database
 * 
 * Usage: php database/setup.php
 * Or access via browser: http://localhost/attendenceapp/database/setup.php
 */

// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration - Try multiple common XAMPP configurations
$host = 'localhost';
$dbname = 'attendance_system';

// Try common password combinations for XAMPP
$password_options = ['', 'root', null];

// Try to read from config file first
$configFile = __DIR__ . '/../config/database.php';
if (file_exists($configFile)) {
    $configContent = file_get_contents($configFile);
    if (preg_match("/password\s*=\s*['\"](.*?)['\"]/", $configContent, $matches)) {
        $password_options = [$matches[1]];
    }
}

$username = 'root';
$password = null;
$pdo = null;

echo "<!DOCTYPE html>\n";
echo "<html lang='fr'>\n";
echo "<head><meta charset='UTF-8'><title>Database Setup</title>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
    .success { background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .error { background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .info { background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; }
    a { display: inline-block; margin: 10px 5px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
    a:hover { background: #0056b3; }
</style></head><body>\n";

echo "<h1>=== Configuration de la base de données ===</h1>\n\n";

// Try to connect
$connected = false;
foreach ($password_options as $pwd) {
    try {
        $pdo = new PDO("mysql:host=$host", $username, $pwd);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $password = $pwd;
        $connected = true;
        echo "<div class='success'>✓ Connecté au serveur MySQL</div>\n";
        break;
    } catch (PDOException $e) {
        continue;
    }
}

if (!$connected) {
    echo "<div class='error'>";
    echo "<strong>Erreur :</strong> Impossible de se connecter à MySQL.<br>\n";
    echo "Essayez de :<br>\n";
    echo "1. Démarrer MySQL dans XAMPP Control Panel<br>\n";
    echo "2. Vérifier le mot de passe dans config/database.php<br>\n";
    echo "3. Essayer de vous connecter manuellement : <code>/Applications/XAMPP/bin/mysql -u root</code><br>\n";
    echo "</div>\n";
    echo "<a href='../check.php'>Vérifier le système</a>\n";
    echo "</body></html>";
    exit(1);
}

// Read schema file
$schemaFile = __DIR__ . '/schema.sql';

if (!file_exists($schemaFile)) {
    echo "<div class='error'>Erreur : Fichier schema.sql introuvable !</div>\n";
    echo "</body></html>";
    exit(1);
}

try {
    echo "<div class='info'>Lecture du fichier schema.sql...</div>\n";
    
    // Read and execute schema
    $sql = file_get_contents($schemaFile);
    
    // Split by semicolons and execute each statement
    $statements = explode(';', $sql);
    $executed = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && !preg_match('/^--/', $statement) && !preg_match('/^\/\*/', $statement)) {
            try {
                $pdo->exec($statement);
                $executed++;
            } catch (PDOException $e) {
                // Ignore "database exists" and "table exists" errors
                $errorMsg = $e->getMessage();
                if (strpos($errorMsg, 'already exists') === false && 
                    strpos($errorMsg, 'Duplicate key name') === false) {
                    echo "<div class='error'>Avertissement : " . htmlspecialchars($errorMsg) . "</div>\n";
                }
            }
        }
    }
    
    echo "<div class='success'>✓ Schéma de base de données importé avec succès ($executed requêtes exécutées)</div>\n";
    
    // Verify database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE 'attendance_system'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>✓ Base de données 'attendance_system' créée</div>\n";
        
        // Check tables
        $pdo->exec("USE attendance_system");
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "<div class='success'>✓ " . count($tables) . " table(s) créée(s) : " . implode(', ', $tables) . "</div>\n";
        }
        
        // Check admin user
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'administrator'");
        $adminCount = $stmt->fetchColumn();
        
        if ($adminCount > 0) {
            echo "<div class='success'>✓ Compte administrateur par défaut créé</div>\n";
        }
    }
    
    echo "<div class='info'>";
    echo "<h2>=== Configuration terminée ===</h2>\n\n";
    echo "<strong>Identifiants de connexion par défaut :</strong><br>\n";
    echo "Username: <code>admin</code><br>\n";
    echo "Password: <code>admin123</code><br><br>\n";
    echo "⚠️  <strong>IMPORTANT :</strong> Changez le mot de passe après la première connexion !\n";
    echo "</div>\n";
    
    // Create uploads directory
    $uploadsDir = __DIR__ . '/../uploads/justifications';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
        echo "<div class='success'>✓ Dossier uploads/justifications créé</div>\n";
    }
    
    echo "<br>\n";
    echo "<a href='../index.php'>🚀 Accéder à l'application</a>\n";
    echo "<a href='../login.php'>🔐 Page de connexion</a>\n";
    echo "<a href='../check.php'>🔍 Vérifier le système</a>\n";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<strong>Erreur :</strong> " . htmlspecialchars($e->getMessage()) . "\n";
    echo "</div>\n";
    echo "<a href='../check.php'>Vérifier le système</a>\n";
}

echo "</body></html>";
