<?php
/**
 * System Check Script
 * Vérifie que tout est configuré correctement avant de lancer l'application
 * Accessible via: http://localhost/attendenceapp/check.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification du système - AttendanceApp</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .check-item.success {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        .check-item.error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .check-item.warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .icon {
            font-size: 24px;
        }
        .message {
            flex: 1;
        }
        .message strong {
            display: block;
            margin-bottom: 5px;
        }
        .action {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .action a {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
        }
        .action a:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Vérification du système</h1>
        
        <?php
        $allGood = true;
        
        // Vérifier PHP
        echo '<div class="check-item success">';
        echo '<span class="icon">✅</span>';
        echo '<div class="message">';
        echo '<strong>PHP Version</strong>';
        echo 'Version: ' . phpversion();
        echo '</div>';
        echo '</div>';
        
        // Vérifier PDO MySQL
        if (extension_loaded('pdo_mysql')) {
            echo '<div class="check-item success">';
            echo '<span class="icon">✅</span>';
            echo '<div class="message"><strong>Extension PDO MySQL</strong> Installée</div>';
            echo '</div>';
        } else {
            echo '<div class="check-item error">';
            echo '<span class="icon">❌</span>';
            echo '<div class="message"><strong>Extension PDO MySQL</strong> Manquante - Installez-la</div>';
            echo '</div>';
            $allGood = false;
        }
        
        // Vérifier la connexion MySQL
        require_once __DIR__ . '/config/database.php';
        $db = new Database();
        $conn = $db->getConnection();
        
        if ($conn) {
            echo '<div class="check-item success">';
            echo '<span class="icon">✅</span>';
            echo '<div class="message"><strong>Connexion MySQL</strong> Réussie</div>';
            echo '</div>';
            
            // Vérifier si la base de données existe
            try {
                $stmt = $conn->query("SELECT DATABASE()");
                $currentDb = $stmt->fetchColumn();
                
                if ($currentDb === 'attendance_system') {
                    echo '<div class="check-item success">';
                    echo '<span class="icon">✅</span>';
                    echo '<div class="message"><strong>Base de données</strong> attendance_system existe</div>';
                    echo '</div>';
                    
                    // Vérifier les tables
                    $stmt = $conn->query("SHOW TABLES");
                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    if (count($tables) > 0) {
                        echo '<div class="check-item success">';
                        echo '<span class="icon">✅</span>';
                        echo '<div class="message"><strong>Tables</strong> ' . count($tables) . ' table(s) trouvée(s)</div>';
                        echo '</div>';
                    } else {
                        echo '<div class="check-item warning">';
                        echo '<span class="icon">⚠️</span>';
                        echo '<div class="message"><strong>Tables</strong> Aucune table trouvée - Lancez database/setup.php</div>';
                        echo '</div>';
                        $allGood = false;
                    }
                } else {
                    echo '<div class="check-item warning">';
                    echo '<span class="icon">⚠️</span>';
                    echo '<div class="message"><strong>Base de données</strong> attendance_system n\'existe pas - Lancez database/setup.php</div>';
                    echo '</div>';
                    $allGood = false;
                }
            } catch (PDOException $e) {
                echo '<div class="check-item error">';
                echo '<span class="icon">❌</span>';
                echo '<div class="message"><strong>Erreur</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '</div>';
                $allGood = false;
            }
        } else {
            echo '<div class="check-item error">';
            echo '<span class="icon">❌</span>';
            echo '<div class="message"><strong>Connexion MySQL</strong> Échec - Vérifiez config/database.php</div>';
            echo '</div>';
            $allGood = false;
        }
        
        // Vérifier le dossier uploads
        $uploadsDir = __DIR__ . '/uploads/justifications';
        if (is_dir($uploadsDir)) {
            if (is_writable($uploadsDir)) {
                echo '<div class="check-item success">';
                echo '<span class="icon">✅</span>';
                echo '<div class="message"><strong>Dossier uploads/justifications</strong> Existe et est accessible en écriture</div>';
                echo '</div>';
            } else {
                echo '<div class="check-item warning">';
                echo '<span class="icon">⚠️</span>';
                echo '<div class="message"><strong>Dossier uploads/justifications</strong> Existe mais n\'est pas accessible en écriture</div>';
                echo '</div>';
            }
        } else {
            echo '<div class="check-item warning">';
            echo '<span class="icon">⚠️</span>';
            echo '<div class="message"><strong>Dossier uploads/justifications</strong> N\'existe pas - Créez-le avec: mkdir -p uploads/justifications</div>';
            echo '</div>';
        }
        ?>
        
        <div class="action">
            <?php if (!$allGood): ?>
                <p><strong>Actions requises :</strong></p>
                <ul style="margin: 10px 0 20px 20px;">
                    <?php if (!$conn): ?>
                        <li>Vérifiez que MySQL est démarré dans XAMPP</li>
                        <li>Vérifiez les identifiants dans <code>config/database.php</code></li>
                    <?php endif; ?>
                    <li><a href="database/setup.php">Configurer la base de données</a></li>
                    <li><a href="index.php">Accéder à l'application</a></li>
                </ul>
            <?php else: ?>
                <p><strong>✅ Tout est prêt !</strong></p>
                <p style="margin: 10px 0;">Vous pouvez maintenant accéder à l'application.</p>
            <?php endif; ?>
            
            <a href="database/setup.php">🔧 Configurer la base de données</a>
            <a href="index.php">🚀 Accéder à l'application</a>
            <a href="login.php">🔐 Page de connexion</a>
        </div>
    </div>
</body>
</html>

