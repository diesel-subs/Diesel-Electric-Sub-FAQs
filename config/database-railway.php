<?php
// Railway Database Configuration
// Environment variables are automatically set by Railway

$railway_host = $_ENV['MYSQLHOST'] ?? 'localhost';
$railway_port = $_ENV['MYSQLPORT'] ?? '3306'; 
$railway_user = $_ENV['MYSQLUSER'] ?? 'submarine_user';
$railway_pass = $_ENV['MYSQLPASSWORD'] ?? 'submarine2024!';
$railway_db = $_ENV['MYSQLDATABASE'] ?? 'submarine_faqs';

define('DB_HOST', $railway_host . ':' . $railway_port);
define('DB_USERNAME', $railway_user);
define('DB_PASSWORD', $railway_pass);
define('DB_NAME', $railway_db);
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_NAME', 'Diesel-Electric Submarine FAQs');

// Production settings
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Sorry, we're experiencing technical difficulties. Please try again later.");
}

// Helper functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function format_date($date) {
    return date('F j, Y', strtotime($date));
}
?>
