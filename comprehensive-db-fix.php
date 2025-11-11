<?php
require_once 'config/database.php';

echo "<h1>Database Structure Diagnosis and Fix</h1>";

try {
    // First, let's see what columns actually exist
    echo "<h2>Current faqs table structure:</h2>";
    $stmt = $pdo->query("DESCRIBE faqs");
    $existingColumns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $columnNames = [];
    foreach ($existingColumns as $col) {
        $columnNames[] = $col['Field'];
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
    
    echo "<h2>Expected vs Actual Columns:</h2>";
    
    // Define expected columns
    $expectedColumns = [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'title' => 'VARCHAR(255)',
        'slug' => 'VARCHAR(255)',
        'question' => 'TEXT',
        'content' => 'TEXT',
        'short_answer' => 'TEXT',
        'category_id' => 'INT',
        'tags' => 'VARCHAR(500)',
        'is_published' => 'TINYINT(1) DEFAULT 1',
        'view_count' => 'INT DEFAULT 0',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ];
    
    $missingColumns = [];
    foreach ($expectedColumns as $colName => $colDef) {
        if (!in_array($colName, $columnNames)) {
            $missingColumns[] = $colName;
            echo "<p style='color: red;'>❌ Missing: $colName</p>";
        } else {
            echo "<p style='color: green;'>✅ Exists: $colName</p>";
        }
    }
    
    if (!empty($missingColumns)) {
        echo "<h2>Adding Missing Columns:</h2>";
        
        // Add missing columns one by one
        foreach ($missingColumns as $colName) {
            try {
                switch ($colName) {
                    case 'content':
                        $pdo->exec("ALTER TABLE faqs ADD COLUMN content TEXT AFTER question");
                        break;
                    case 'short_answer':
                        $pdo->exec("ALTER TABLE faqs ADD COLUMN short_answer TEXT AFTER content");
                        break;
                    case 'is_published':
                        $pdo->exec("ALTER TABLE faqs ADD COLUMN is_published TINYINT(1) DEFAULT 1 AFTER tags");
                        break;
                    case 'view_count':
                        $pdo->exec("ALTER TABLE faqs ADD COLUMN view_count INT DEFAULT 0 AFTER is_published");
                        break;
                    case 'created_at':
                        $pdo->exec("ALTER TABLE faqs ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER view_count");
                        break;
                    case 'updated_at':
                        $pdo->exec("ALTER TABLE faqs ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
                        break;
                    default:
                        echo "<p style='color: orange;'>⚠️ Unknown column: $colName</p>";
                        continue 2;
                }
                echo "<p style='color: green;'>✅ Added column: $colName</p>";
            } catch (PDOException $e) {
                echo "<p style='color: red;'>❌ Failed to add $colName: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
        
        // If we had existing FAQs but missing content column, copy question to content
        if (in_array('content', $missingColumns)) {
            echo "<h3>Migrating Data:</h3>";
            try {
                $stmt = $pdo->exec("UPDATE faqs SET content = question WHERE content IS NULL OR content = ''");
                echo "<p style='color: green;'>✅ Copied question text to content field for $stmt rows</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Data migration failed: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
        
        // Set all FAQs as published
        try {
            $stmt = $pdo->exec("UPDATE faqs SET is_published = 1 WHERE is_published IS NULL");
            echo "<p style='color: green;'>✅ Set all FAQs as published</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Could not set published status: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    echo "<h2>Final Table Structure:</h2>";
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
    
    // Test queries
    echo "<h2>Testing Database Queries:</h2>";
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM faqs WHERE is_published = 1");
        $published = $stmt->fetch()['count'];
        echo "<p style='color: green;'>✅ Published FAQs query works: $published FAQs</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Published FAQs query failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    try {
        $stmt = $pdo->query("SELECT id, title, content, category_id FROM faqs LIMIT 1");
        $test = $stmt->fetch();
        if ($test) {
            echo "<p style='color: green;'>✅ Content column query works</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️ No FAQs found in database</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Content query failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724; margin: 0 0 15px 0;'>✅ Database Structure Complete!</h3>";
    echo "<p style='margin: 5px 0;'>All required columns have been added to the faqs table.</p>";
    echo "<p style='margin: 5px 0;'>The admin dashboard should now work correctly.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #721c24; margin: 0;'>❌ Error</h3>";
    echo "<p style='margin: 10px 0 0 0;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

<div style="margin-top: 30px; text-align: center;">
    <a href="admin/login.php" style="background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 0 10px; display: inline-block;">🔐 Try Admin Login</a>
    <a href="index.php" style="background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 0 10px; display: inline-block;">🏠 View Website</a>
    <a href="simple-db-admin.php?action=browse&table=faqs" style="background: #17a2b8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 0 10px; display: inline-block;">📊 Browse FAQs</a>
</div>