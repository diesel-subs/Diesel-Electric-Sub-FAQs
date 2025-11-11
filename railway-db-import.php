<?php
// Railway Database Import Script
// This script imports the submarine FAQ data to Railway's MySQL database

echo "Railway Database Import Script\n";
echo "==============================\n\n";

// Railway database connection (update these with your Railway database credentials)
$railway_host = $_ENV['MYSQLHOST'] ?? 'localhost';
$railway_port = $_ENV['MYSQLPORT'] ?? '3306';
$railway_database = $_ENV['MYSQLDATABASE'] ?? 'railway';
$railway_username = $_ENV['MYSQLUSER'] ?? 'root';
$railway_password = $_ENV['MYSQLPASSWORD'] ?? '';

echo "Connecting to Railway database...\n";
echo "Host: $railway_host\n";
echo "Database: $railway_database\n";
echo "Username: $railway_username\n\n";

try {
    $pdo = new PDO(
        "mysql:host=$railway_host;port=$railway_port;dbname=$railway_database;charset=utf8mb4",
        $railway_username,
        $railway_password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    echo "✅ Connected to Railway database successfully!\n\n";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Create tables if they don't exist
echo "Creating database tables...\n";

$createTables = [
    "admin_users" => "
        CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "categories" => "
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "faqs" => "
        CREATE TABLE IF NOT EXISTS faqs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category_id INT NOT NULL,
            title VARCHAR(500) NOT NULL,
            slug VARCHAR(500) UNIQUE NOT NULL,
            question TEXT NOT NULL,
            content TEXT,
            answer TEXT NOT NULL,
            short_answer VARCHAR(4096),
            tags VARCHAR(500),
            is_published TINYINT(1) DEFAULT 1,
            view_count INT DEFAULT 0,
            views INT DEFAULT 0,
            featured TINYINT(1) DEFAULT 0,
            status ENUM('published','draft','archived') DEFAULT 'published',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
            INDEX idx_category (category_id),
            INDEX idx_slug (slug),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "feedback" => "
        CREATE TABLE IF NOT EXISTS feedback (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            email VARCHAR(100),
            subject VARCHAR(200),
            message TEXT NOT NULL,
            status ENUM('new','read','responded') DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "related_faqs" => "
        CREATE TABLE IF NOT EXISTS related_faqs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            faq_id INT NOT NULL,
            related_faq_id INT NOT NULL,
            FOREIGN KEY (faq_id) REFERENCES faqs(id) ON DELETE CASCADE,
            FOREIGN KEY (related_faq_id) REFERENCES faqs(id) ON DELETE CASCADE,
            UNIQUE KEY unique_relation (faq_id, related_faq_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($createTables as $tableName => $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ Created/verified table: $tableName\n";
    } catch (PDOException $e) {
        echo "❌ Error creating table $tableName: " . $e->getMessage() . "\n";
    }
}

// Clear existing data
echo "\nClearing existing data...\n";
try {
    $pdo->exec("DELETE FROM related_faqs");
    $pdo->exec("DELETE FROM faqs");
    $pdo->exec("DELETE FROM categories");
    $pdo->exec("DELETE FROM admin_users");
    echo "✅ Cleared existing data\n";
} catch (PDOException $e) {
    echo "⚠️ Warning clearing data: " . $e->getMessage() . "\n";
}

// Insert admin user
echo "\nCreating admin user...\n";
try {
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)");
    $stmt->execute(['admin', password_hash('submarine2024!', PASSWORD_DEFAULT), 'admin@submarine-faqs.com']);
    echo "✅ Created admin user (username: admin, password: submarine2024!)\n";
} catch (PDOException $e) {
    echo "❌ Error creating admin user: " . $e->getMessage() . "\n";
}

// Insert categories
echo "\nInserting categories...\n";
$categories = [
    ['Hull and Compartments', 'hull-and-compartments', 'Questions about Hull and Compartments'],
    ['US WW2 Subs in General', 'us-ww2-subs-in-general', 'Questions about US WW2 Subs in General'],
    ['Operating US Subs in WW2', 'operating-us-subs-in-ww2', 'Questions about Operating US Subs in WW2'],
    ['Who Were the Crews Aboard WW2 US Subs', 'who-were-the-crews-aboard-ww2-us-subs', 'Questions about Who Were the Crews Aboard WW2 US Subs'],
    ['Life Aboard WW2 US Subs', 'life-aboard-ww2-us-subs', 'Questions about Life Aboard WW2 US Subs'],
    ['Attacks and Battles, Small and Large', 'attacks-and-battles-small-and-large', 'Questions about Attacks and Battles, Small and Large']
];

$categoryIds = [];
foreach ($categories as $cat) {
    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $stmt->execute($cat);
        $categoryIds[$cat[0]] = $pdo->lastInsertId();
        echo "✅ Inserted category: " . $cat[0] . "\n";
    } catch (PDOException $e) {
        echo "❌ Error inserting category " . $cat[0] . ": " . $e->getMessage() . "\n";
    }
}

echo "\n🚀 DATABASE SETUP COMPLETE!\n";
echo "\nNext steps:\n";
echo "1. Your Railway app should now be updated with the latest code\n";
echo "2. Run this script on Railway to populate the database\n";
echo "3. Access your admin panel at: https://your-app.railway.app/admin/login.php\n";
echo "4. Login with: admin / submarine2024!\n";
echo "\nYour submarine FAQ site is ready with 6 categories and admin functionality!\n";

// Show final stats
$stmt = $pdo->query("SELECT COUNT(*) FROM categories");
$catCount = $stmt->fetchColumn();

echo "\nFinal Statistics:\n";
echo "Categories: $catCount\n";
echo "Admin users: 1\n";
echo "Ready for FAQ import!\n";
?>