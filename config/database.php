<?php
// Database configuration
define('DB_HOST', $_ENV['MYSQLHOST'] ?? 'localhost');
define('DB_PORT', $_ENV['MYSQLPORT'] ?? '3306');
define('DB_USERNAME', $_ENV['MYSQLUSER'] ?? 'dieselsu_dbuser');
define('DB_PASSWORD', $_ENV['MYSQLPASSWORD'] ?? 'codjuw-xojWo6-datqem');
define('DB_NAME', $_ENV['MYSQLDATABASE'] ?? 'dieselsu_faqs');
define('DB_CHARSET', 'utf8mb4');
// define('DB_USERNAME', $_ENV['MYSQLUSER'] ?? 'dieselsu_user_faqs');
// define('DB_PASSWORD', $_ENV['MYSQLPASSWORD'] ?? 'qipCu9-ramwos-bubfoq');

// Site configuration
define('SITE_NAME', 'Diesel-Electric Submarine FAQs');
define('SITE_URL', 'http://localhost');
define('ADMIN_EMAIL', 'irving.greisman@gmail.com');

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USERNAME,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please check your configuration.");
}

// Session configuration - only start if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper functions
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function get_category_slug($name) {
    return str_replace([' ', '/', '&'], ['-', '-', 'and'], strtolower($name));
}

function format_date($date) {
    return date('F j, Y', strtotime($date));
}

// Related FAQs functions
function get_related_faqs($pdo, $faq_id, $limit = 5) {
    $stmt = $pdo->prepare("
        SELECT f.id, f.title, f.slug, rf.relationship_type,
               f.category_id, c.name as category_name
        FROM related_faqs rf
        JOIN faqs f ON (rf.related_faq_id = f.id OR rf.faq_id = f.id)
        LEFT JOIN categories c ON f.category_id = c.id
        WHERE (rf.faq_id = ? OR rf.related_faq_id = ?) 
        AND f.id != ? 
        AND f.status = 'published'
        ORDER BY rf.relationship_type, f.title
        LIMIT ?
    ");
    $stmt->execute([$faq_id, $faq_id, $faq_id, $limit]);
    return $stmt->fetchAll();
}

function add_related_faq($pdo, $faq_id, $related_faq_id, $relationship_type = 'similar') {
    // Prevent self-reference
    if ($faq_id == $related_faq_id) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO related_faqs (faq_id, related_faq_id, relationship_type) 
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$faq_id, $related_faq_id, $relationship_type]);
    } catch (PDOException $e) {
        return false;
    }
}

function remove_related_faq($pdo, $faq_id, $related_faq_id) {
    $stmt = $pdo->prepare("
        DELETE FROM related_faqs 
        WHERE (faq_id = ? AND related_faq_id = ?) 
        OR (faq_id = ? AND related_faq_id = ?)
    ");
    return $stmt->execute([$faq_id, $related_faq_id, $related_faq_id, $faq_id]);
}

?>
