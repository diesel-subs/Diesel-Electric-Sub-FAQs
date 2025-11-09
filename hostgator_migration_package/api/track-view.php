<?php
header('Content-Type: application/json');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['faq_id']) || !is_numeric($input['faq_id'])) {
    echo json_encode(['success' => false, 'message' => 'Valid FAQ ID required']);
    exit;
}

$faqId = (int) $input['faq_id'];

try {
    // Update view count
    $updateQuery = "UPDATE faqs SET views = views + 1 WHERE id = ? AND status = 'published'";
    $stmt = $pdo->prepare($updateQuery);
    $result = $stmt->execute([$faqId]);

    if ($result && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'View tracked']);
    } else {
        echo json_encode(['success' => false, 'message' => 'FAQ not found']);
    }

} catch (Exception $e) {
    error_log("View tracking error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Tracking failed']);
}
?>