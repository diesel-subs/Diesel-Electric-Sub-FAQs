<?php
echo "<h1>Database Connection Test</h1>";

try {
    require_once 'config/database.php';
    echo "<p>✅ Database config loaded successfully</p>";
    
    echo "<p><strong>PDO Object:</strong> " . (isset($pdo) ? "EXISTS" : "NOT SET") . "</p>";
    
    if (isset($pdo)) {
        echo "<p><strong>Connection Type:</strong> " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "</p>";
        
        // Test simple query
        $stmt = $pdo->query("SELECT DATABASE() as current_db, NOW() as current_time");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p><strong>Current Database:</strong> " . htmlspecialchars($result['current_db']) . "</p>";
        echo "<p><strong>Current Time:</strong> " . htmlspecialchars($result['current_time']) . "</p>";
        
        // Test tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p><strong>Available Tables:</strong> " . implode(', ', $tables) . "</p>";
        
        // Test insert
        $testName = "Test Category " . date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $result = $stmt->execute([$testName, 'test-category-' . time(), 'Test category description']);
        
        if ($result) {
            echo "<p>✅ Test insert successful - ID: " . $pdo->lastInsertId() . "</p>";
        } else {
            echo "<p>❌ Test insert failed</p>";
            print_r($stmt->errorInfo());
        }
        
        // Check categories count
        $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
        $count = $stmt->fetchColumn();
        echo "<p><strong>Categories Count:</strong> $count</p>";
        
    }
} catch (Exception $e) {
    echo "<p>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
}
?>