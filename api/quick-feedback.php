<?php
header('Content-Type: application/json');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$type = $input['type'] ?? '';
$value = $input['value'] ?? '';
$page = $input['page'] ?? '';
$faq_id = !empty($input['faq_id']) ? (int)$input['faq_id'] : null;

if (empty($type)) {
    echo json_encode(['success' => false, 'message' => 'Feedback type required']);
    exit;
}

try {
    // Handle different feedback types
    if ($type === 'exit_intent') {
        // Create quick_feedback table for exit intent data if it doesn't exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS quick_feedback (
                id INT AUTO_INCREMENT PRIMARY KEY,
                type VARCHAR(50) NOT NULL,
                value VARCHAR(100) NOT NULL,
                page VARCHAR(255),
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $stmt = $pdo->prepare("
            INSERT INTO quick_feedback (type, value, page, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $type,
            $value,
            $page,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        $message = "Exit intent feedback: " . $value;
    } else {
        // Handle thumbs up/down feedback for FAQs
        $message = $type === 'helpful' ? 'User found this FAQ helpful' : 'User found this FAQ not helpful';
    }
    
    $feedback_type = 'general';
    
    $stmt = $pdo->prepare("
        INSERT INTO feedback (feedback_type, faq_id, message, status, created_at) 
        VALUES (?, ?, ?, 'approved', NOW())
    ");
    
    $stmt->execute([$feedback_type, $faq_id, $message]);
    
    // Update FAQ views/helpfulness stats if needed
    if ($faq_id) {
        $stmt = $pdo->prepare("UPDATE faqs SET views = views + 1 WHERE id = ?");
        $stmt->execute([$faq_id]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your feedback!'
    ]);
    
} catch (Exception $e) {
    error_log("Quick feedback error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Unable to record feedback at this time'
    ]);
}
?>