<?php
require_once 'config/database.php';

echo "<h1>Quick Database Fix</h1>";

try {
    echo "<p>Checking and fixing faqs table structure...</p>";
    
    // Add is_published column if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE faqs ADD COLUMN is_published TINYINT(1) DEFAULT 1");
        echo "<p style='color: green;'>✓ Added is_published column</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "<p style='color: blue;'>ℹ️ is_published column already exists</p>";
        } else {
            throw $e;
        }
    }
    
    // Add view_count column if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE faqs ADD COLUMN view_count INT DEFAULT 0");
        echo "<p style='color: green;'>✓ Added view_count column</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "<p style='color: blue;'>ℹ️ view_count column already exists</p>";
        } else {
            throw $e;
        }
    }
    
    // Add timestamps if they don't exist
    try {
        $pdo->exec("ALTER TABLE faqs ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "<p style='color: green;'>✓ Added created_at column</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "<p style='color: blue;'>ℹ️ created_at column already exists</p>";
        } else {
            throw $e;
        }
    }
    
    try {
        $pdo->exec("ALTER TABLE faqs ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "<p style='color: green;'>✓ Added updated_at column</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "<p style='color: blue;'>ℹ️ updated_at column already exists</p>";
        } else {
            throw $e;
        }
    }
    
    // Update all existing FAQs to be published
    $stmt = $pdo->prepare("UPDATE faqs SET is_published = 1 WHERE is_published IS NULL OR is_published = 0");
    $updated = $stmt->execute();
    $rowCount = $stmt->rowCount();
    echo "<p style='color: green;'>✓ Updated $rowCount FAQs to published status</p>";
    
    // Test the admin query
    echo "<h2>Testing Admin Queries</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM faqs WHERE is_published = 1");
    $published = $stmt->fetch()['count'];
    echo "<p>✓ Published FAQs: $published</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM faqs WHERE is_published = 0");
    $drafts = $stmt->fetch()['count'];
    echo "<p>✓ Draft FAQs: $drafts</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $categories = $stmt->fetch()['count'];
    echo "<p>✓ Categories: $categories</p>";
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724; margin: 0;'>✅ Database Fixed Successfully!</h3>";
    echo "<p style='margin: 10px 0 0 0;'>All required columns are now present and the admin dashboard should work.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #721c24; margin: 0;'>❌ Error</h3>";
    echo "<p style='margin: 10px 0 0 0;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

<div style="margin-top: 30px; text-align: center;">
    <a href="admin/login.php" style="background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 0 10px; display: inline-block;">🔐 Admin Login</a>
    <a href="simple-db-admin.php?action=structure&table=faqs" style="background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 0 10px; display: inline-block;">📊 View Structure</a>
</div>