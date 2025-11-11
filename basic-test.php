<!DOCTYPE html>
<html>
<head>
    <title>Simple Test</title>
</head>
<body>
    <h1>Basic Test Page</h1>
    
    <?php
    echo "<h2>PHP is working!</h2>";
    echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
    
    // Test database
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=submarine_faqs', 'submarine_user', 'submarine2024!');
        echo "<h2 style='color: green;'>✅ Database Connected</h2>";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM faqs");
        $result = $stmt->fetch();
        echo "<p>FAQ Count: " . $result['count'] . "</p>";
        
        $stmt = $pdo->query("SELECT id, title FROM faqs LIMIT 3");
        $faqs = $stmt->fetchAll();
        
        echo "<h3>Sample FAQs:</h3><ul>";
        foreach ($faqs as $faq) {
            echo "<li>ID: {$faq['id']} - {$faq['title']}</li>";
        }
        echo "</ul>";
        
    } catch (Exception $e) {
        echo "<h2 style='color: red;'>❌ Database Error</h2>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>
    
    <h3>Navigation Links:</h3>
    <ul>
        <li><a href="index.php">Main Site</a></li>
        <li><a href="admin-login.php">Admin Login</a></li>
        <li><a href="edit-faq-wysiwyg.php">WYSIWYG Editor</a></li>
        <li><a href="simple-db-admin.php">Database Admin</a></li>
    </ul>
</body>
</html>