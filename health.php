<?php
// Simple health check for Railway
echo "PHP is working!";
echo "\nPHP Version: " . phpversion();
echo "\nServer: " . $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
echo "\nPort: " . $_ENV['PORT'] ?? 'Not set';

// Test database connection
try {
    $host = $_ENV['MYSQLHOST'] ?? 'localhost';
    $port = $_ENV['MYSQLPORT'] ?? '3306';
    $database = $_ENV['MYSQLDATABASE'] ?? 'railway';
    $username = $_ENV['MYSQLUSER'] ?? 'root';
    $password = $_ENV['MYSQLPASSWORD'] ?? '';
    
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4",
        $username,
        $password
    );
    echo "\n✅ Database connection: SUCCESS";
    
    $stmt = $pdo->query("SELECT COUNT(*) as faq_count FROM faqs");
    $count = $stmt->fetchColumn();
    echo "\n📊 FAQs in database: " . $count;
} catch (Exception $e) {
    echo "\n❌ Database connection: " . $e->getMessage();
}

echo "\n\n🚀 Submarine FAQ App Status: READY";
?>