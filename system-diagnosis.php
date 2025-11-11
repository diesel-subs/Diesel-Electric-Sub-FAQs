<?php
echo "<h1>🔧 Submarine FAQ - System Diagnosis</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>";

echo "<h2>1. Basic PHP Test</h2>";
echo "<div class='success'>✅ PHP is working - Version: " . phpversion() . "</div>";

echo "<h2>2. File System Test</h2>";
$configFile = __DIR__ . '/config/database.php';
if (file_exists($configFile)) {
    echo "<div class='success'>✅ Config file exists: $configFile</div>";
} else {
    echo "<div class='error'>❌ Config file missing: $configFile</div>";
}

echo "<h2>3. Database Configuration Test</h2>";
try {
    require_once 'config/database.php';
    echo "<div class='success'>✅ Database config loaded successfully</div>";
    echo "<div>Database Host: " . DB_HOST . "</div>";
    echo "<div>Database Name: " . DB_NAME . "</div>";
    echo "<div>Database User: " . DB_USERNAME . "</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Config error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<h2>4. PDO Extension Test</h2>";
if (extension_loaded('pdo')) {
    echo "<div class='success'>✅ PDO extension loaded</div>";
} else {
    echo "<div class='error'>❌ PDO extension missing</div>";
}

if (extension_loaded('pdo_mysql')) {
    echo "<div class='success'>✅ PDO MySQL driver loaded</div>";
} else {
    echo "<div class='error'>❌ PDO MySQL driver missing</div>";
}

echo "<h2>5. Direct Database Connection Test</h2>";
try {
    $testPdo = new PDO(
        "mysql:host=localhost;dbname=submarine_faqs;charset=utf8mb4",
        'submarine_user',
        'submarine2024!',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "<div class='success'>✅ Direct database connection successful</div>";
    
    $count = $testPdo->query("SELECT COUNT(*) FROM faqs")->fetchColumn();
    echo "<div class='success'>✅ Query successful - Found $count FAQs</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<h2>6. Config Database Connection Test</h2>";
try {
    if (isset($pdo)) {
        $count2 = $pdo->query("SELECT COUNT(*) FROM faqs")->fetchColumn();
        echo "<div class='success'>✅ Config PDO connection successful - Found $count2 FAQs</div>";
    } else {
        echo "<div class='error'>❌ Config PDO variable not set</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Config PDO error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<h2>7. Session Test</h2>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<div class='success'>✅ Session is active</div>";
} else {
    echo "<div class='warning'>⚠️ Session not active</div>";
}

echo "<h2>8. File Permissions Test</h2>";
$dirs = ['includes', 'config', 'phpmyadmin/tmp'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        if (is_readable($dir)) {
            echo "<div class='success'>✅ Directory readable: $dir</div>";
        } else {
            echo "<div class='error'>❌ Directory not readable: $dir</div>";
        }
        if (is_writable($dir)) {
            echo "<div class='success'>✅ Directory writable: $dir</div>";
        } else {
            echo "<div class='warning'>⚠️ Directory not writable: $dir</div>";
        }
    } else {
        echo "<div class='error'>❌ Directory missing: $dir</div>";
    }
}

echo "<h2>9. Quick Navigation</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Main Site</a></li>";
echo "<li><a href='admin-login.php'>Admin Login</a></li>";
echo "<li><a href='simple-db-admin.php'>Simple Database Manager</a></li>";
echo "<li><a href='test-db-connection.php'>Database Test</a></li>";
echo "</ul>";
?>