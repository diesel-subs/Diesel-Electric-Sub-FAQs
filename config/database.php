<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'submarine_user');
define('DB_PASSWORD', 'submarine2024!');
define('DB_NAME', 'submarine_faqs');
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_NAME', 'Diesel-Electric Submarine FAQs');
define('SITE_URL', 'http://localhost');
define('ADMIN_EMAIL', 'admin@example.com');

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
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

// Session configuration
session_start();

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

// Short answer functions
function generate_short_answer($full_answer, $max_length = 4000) {
    // Ensure we don't exceed the 4096 byte limit (leaving room for encoding)
    if (strlen($full_answer) <= $max_length) {
        return $full_answer;
    }
    
    // Find the last complete sentence within the limit
    $truncated = substr($full_answer, 0, $max_length - 3);
    $last_period = strrpos($truncated, '.');
    $last_exclamation = strrpos($truncated, '!');
    $last_question = strrpos($truncated, '?');
    
    $last_sentence_end = max($last_period, $last_exclamation, $last_question);
    
    if ($last_sentence_end !== false && $last_sentence_end > ($max_length * 0.5)) {
        // If we found a sentence ending in the latter half, use it
        return substr($full_answer, 0, $last_sentence_end + 1);
    } else {
        // Otherwise, just truncate and add ellipsis
        return $truncated . '...';
    }
}

function update_faq_short_answer($pdo, $faq_id, $short_answer = null) {
    if ($short_answer === null) {
        // Auto-generate from full answer
        $stmt = $pdo->prepare("SELECT answer FROM faqs WHERE id = ?");
        $stmt->execute([$faq_id]);
        $faq = $stmt->fetch();
        
        if ($faq) {
            $short_answer = generate_short_answer($faq['answer']);
        } else {
            return false;
        }
    }
    
    // Ensure it fits in 4096 bytes
    if (strlen($short_answer) > 4096) {
        $short_answer = generate_short_answer($short_answer, 4000);
    }
    
    $stmt = $pdo->prepare("UPDATE faqs SET short_answer = ? WHERE id = ?");
    return $stmt->execute([$short_answer, $faq_id]);
}
?>