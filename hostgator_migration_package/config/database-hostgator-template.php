<?php
// HostGator Database configuration
// UPDATE THESE VALUES WITH YOUR HOSTGATOR DETAILS

define('DB_HOST', 'localhost');  // Usually localhost on HostGator
define('DB_USERNAME', 'YOUR_HOSTGATOR_USERNAME_dbuser');  // Replace with your HostGator DB user
define('DB_PASSWORD', 'YOUR_HOSTGATOR_DB_PASSWORD');     // Replace with your HostGator DB password
define('DB_NAME', 'YOUR_HOSTGATOR_USERNAME_submarine_faqs'); // Replace with your HostGator DB name
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_NAME', 'Diesel-Electric Submarine FAQs');

// Error reporting for production (disable debugging)
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Database connection with error handling
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database connection error: " . $e->getMessage());
    die("Sorry, we're experiencing technical difficulties. Please try again later.");
}

// Helper function for input sanitization
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Helper function for date formatting
function format_date($date) {
    return date('F j, Y', strtotime($date));
}
?>
