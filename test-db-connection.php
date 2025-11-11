<?php
// Database Connection Test
echo "<h2>Database Connection Test</h2>";

try {
    // Test basic connection
    $pdo = new PDO('mysql:host=localhost;dbname=submarine_faqs', 'submarine_user', 'submarine_password');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div style='color: green;'>✅ Database connection successful!</div>";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM faqs");
    $result = $stmt->fetch();
    echo "<div>📊 Found {$result['count']} FAQs in database</div>";
    
    // Test database version
    $stmt = $pdo->query("SELECT VERSION() as version");
    $result = $stmt->fetch();
    echo "<div>🔧 MySQL Version: {$result['version']}</div>";
    
    // Show all tables
    echo "<h3>Available Tables:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    echo "<ul>";
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "<li>$tableName</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<h3>phpMyAdmin Access Links:</h3>";
echo "<ul>";
echo "<li><a href='phpmyadmin/' target='_blank'>Direct phpMyAdmin Access</a></li>";
echo "<li><a href='pma.php' target='_blank'>phpMyAdmin via pma.php</a></li>";
echo "<li><a href='db.php' target='_blank'>phpMyAdmin via db.php</a></li>";
echo "</ul>";

echo "<h3>System Info:</h3>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>Current Directory: " . getcwd() . "</li>";
echo "<li>Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</li>";
echo "</ul>";
?>