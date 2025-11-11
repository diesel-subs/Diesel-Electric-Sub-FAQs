<?php
require_once 'config/database.php';

echo "<h1>Test Browse Tables</h1>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Tables found:</h2><ul>";
    foreach ($tables as $tableName) {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM `$tableName`");
        $rowCount = $countStmt->fetchColumn();
        
        echo "<li><a href='simple-db-admin.php?action=browse&table=$tableName'>$tableName</a> ($rowCount rows)</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<p><a href="simple-db-admin.php">Back to Database Admin</a></p>