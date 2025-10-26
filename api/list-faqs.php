<?php
/**
 * list-faqs.php - API endpoint for listing all available FAQs
 * Returns a JSON array of all FAQ files with their metadata
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Include helper functions
require_once 'faq-helpers.php';

try {
    $base_docs_path = '/Users/irving/Documents/GitHub/Diesel-Electric-Submarine-FAQs/docs';
    
    // Get all FAQs
    $faqs = getAllFaqs($base_docs_path);
    
    // Sort by question for consistent ordering
    usort($faqs, function($a, $b) {
        return strcmp($a['question'], $b['question']);
    });
    
    // Return the FAQ list
    echo json_encode([
        'success' => true,
        'faqs' => $faqs,
        'count' => count($faqs)
    ]);
    
} catch (Exception $e) {
    error_log("Error listing FAQs: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}

?>