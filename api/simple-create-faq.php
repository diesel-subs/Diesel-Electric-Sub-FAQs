<?php
/**
 * Simple FAQ Creator - Just create the file, let MkDocs do the rest
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST allowed']);
    exit();
}

try {
    // Get and validate input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON');
    }
    
    // Required fields
    if (empty($data['category']) || empty($data['question']) || 
        empty($data['short_answer']) || empty($data['detailed_answer'])) {
        throw new Exception('Missing required fields');
    }
    
    // Simple spam check
    if (!empty($data['website'])) {
        echo json_encode(['success' => true]);
        exit();
    }
    
    // Clean the data
    $category = trim($data['category']);
    $question = trim($data['question']);
    $short_answer = trim($data['short_answer']);
    $detailed_answer = trim($data['detailed_answer']);
    $author = trim($data['author'] ?? '');
    
    // Generate filename
    $filename = 'Q-' . preg_replace('/[^a-zA-Z0-9-]/', '-', $question);
    $filename = preg_replace('/-+/', '-', $filename);
    $filename = trim($filename, '-');
    $filename = substr($filename, 0, 80) . '.md';
    
    // Create the markdown content
    $content = "# $question\n\n";
    
    if ($author) {
        $content .= "---\n";
        $content .= "author: $author\n";
        $content .= "created: " . date('Y-m-d') . "\n";
        $content .= "---\n\n";
    }
    
    $content .= "=== \"Quick Answer\"\n\n";
    $content .= "    $short_answer\n\n";
    $content .= "=== \"Detailed Answer\"\n\n";
    $content .= "    $detailed_answer\n\n";
    
    // Determine the file path
    // For local testing, we'll try to detect if we're on your local machine
    $is_local = (php_uname('n') === 'Irvings-MacBook-Pro.local' || 
                 strpos($_SERVER['SERVER_NAME'], 'localhost') !== false ||
                 strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false);
    
    if ($is_local) {
        // Local development path
        $docs_path = '/Users/irving/Documents/GitHub/Diesel-Electric-Submarine-FAQs/docs';
    } else {
        // Server path - you'll need to update this based on your hosting
        $docs_path = $_SERVER['DOCUMENT_ROOT'] . '/docs';
    }
    
    $category_path = $docs_path . '/categories/' . $category;
    $file_path = $category_path . '/' . $filename;
    
    // Create category directory if needed
    if (!is_dir($category_path)) {
        if (!mkdir($category_path, 0755, true)) {
            throw new Exception('Cannot create category directory');
        }
    }
    
    // Write the file
    if (file_put_contents($file_path, $content) === false) {
        throw new Exception('Cannot write file');
    }
    
    // Success!
    echo json_encode([
        'success' => true,
        'filename' => $filename,
        'category' => $category,
        'path' => $file_path,
        'message' => 'FAQ created successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>