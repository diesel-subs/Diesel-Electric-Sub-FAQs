<?php
require_once 'config/database.php';

echo "<h1>Database Management Script</h1>";

try {
    // Clear existing data
    echo "<h2>Clearing existing data...</h2>";
    
    $tables = ['related_faqs', 'faqs', 'feedback', 'categories'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("DELETE FROM `$table`");
        $stmt->execute();
        echo "<p>✓ Cleared table: $table</p>";
    }
    
    // Reset auto-increment
    echo "<h2>Resetting auto-increment values...</h2>";
    $resetTables = ['categories', 'faqs', 'feedback'];
    foreach ($resetTables as $table) {
        $stmt = $pdo->prepare("ALTER TABLE `$table` AUTO_INCREMENT = 1");
        $stmt->execute();
        echo "<p>✓ Reset auto-increment for: $table</p>";
    }
    
    echo "<h2>Table Structures:</h2>";
    
    // Show categories structure
    echo "<h3>Categories Table:</h3>";
    $stmt = $pdo->query("DESCRIBE categories");
    $columns = $stmt->fetchAll();
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show faqs structure
    echo "<h3>FAQs Table:</h3>";
    $stmt = $pdo->query("DESCRIBE faqs");
    $columns = $stmt->fetchAll();
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p style='color: green; font-weight: bold;'>All operations completed successfully!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>