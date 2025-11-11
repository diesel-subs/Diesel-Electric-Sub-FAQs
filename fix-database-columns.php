<?php
require_once 'config/database.php';

echo "<h1>Database Column Fix</h1>";

try {
    // Check if is_published column exists
    $stmt = $pdo->query("DESCRIBE faqs");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $hasPublished = in_array('is_published', $columns);
    
    echo "<h2>Current faqs table columns:</h2>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>" . htmlspecialchars($column) . "</li>";
    }
    echo "</ul>";
    
    if (!$hasPublished) {
        echo "<h2>Adding missing is_published column...</h2>";
        
        // Add the missing column
        $pdo->exec("ALTER TABLE faqs ADD COLUMN is_published TINYINT(1) DEFAULT 1 AFTER content");
        
        echo "<p style='color: green;'>✓ Added is_published column successfully!</p>";
        
        // Update all existing FAQs to be published
        $stmt = $pdo->exec("UPDATE faqs SET is_published = 1 WHERE is_published IS NULL");
        echo "<p style='color: green;'>✓ Set all existing FAQs as published ($stmt rows updated)</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ is_published column already exists!</p>";
    }
    
    // Check if we need to add any other missing columns that admin might expect
    $expectedColumns = ['view_count', 'created_at', 'updated_at'];
    
    foreach ($expectedColumns as $expectedCol) {
        if (!in_array($expectedCol, $columns)) {
            echo "<p style='color: orange;'>Adding missing column: $expectedCol</p>";
            
            switch ($expectedCol) {
                case 'view_count':
                    $pdo->exec("ALTER TABLE faqs ADD COLUMN view_count INT DEFAULT 0 AFTER is_published");
                    break;
                case 'created_at':
                    $pdo->exec("ALTER TABLE faqs ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER view_count");
                    break;
                case 'updated_at':
                    $pdo->exec("ALTER TABLE faqs ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
                    break;
            }
            echo "<p style='color: green;'>✓ Added $expectedCol column</p>";
        }
    }
    
    echo "<h2>Final table structure:</h2>";
    $stmt = $pdo->query("DESCRIBE faqs");
    $finalColumns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($finalColumns as $col) {
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
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724; margin: 0;'>✅ Database Structure Updated!</h3>";
    echo "<p style='margin: 10px 0 0 0;'>All required columns have been added to the faqs table.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #721c24; margin: 0;'>❌ Error</h3>";
    echo "<p style='margin: 10px 0 0 0;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

<div style="margin-top: 30px; text-align: center;">
    <a href="admin/dashboard.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;">Admin Dashboard</a>
    <a href="simple-db-admin.php?action=structure&table=faqs" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;">View Table Structure</a>
</div>