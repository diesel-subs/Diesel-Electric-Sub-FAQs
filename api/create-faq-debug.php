<?php
/**
 * create-faq-debug.php - Debug version with detailed error reporting
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get the JSON input
    $input = file_get_contents('php://input');
    if (!$input) {
        throw new Exception('No input data received');
    }
    
    $data = json_decode($input, true);
    if (!$data) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    // Debug: Log received data
    error_log("FAQ Creation Debug - Received data: " . json_encode($data));

    // Validate required fields
    $required_fields = ['category', 'question', 'short_answer', 'detailed_answer', 'filename'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Basic spam/honeypot check
    if (!empty($data['website'])) {
        echo json_encode(['success' => true, 'message' => 'FAQ created successfully']);
        exit();
    }

    // Sanitize data
    $category = trim($data['category']);
    $question = trim($data['question']);
    $short_answer = trim($data['short_answer']);
    $detailed_answer = trim($data['detailed_answer']);
    $filename = trim($data['filename']);
    $author = trim($data['author'] ?? '');
    $source = trim($data['source'] ?? '');
    $tags = trim($data['tags'] ?? '');
    $created_at = $data['created_at'] ?? date('c');

    // Validate category
    $allowed_categories = [
        'Battles, Small and Large',
        'Crews Aboard US WW2 Subs',
        'Hull and Compartments',
        'Life Aboard US WW2 Subs',
        'Operating US WW2 Subs',
        'US WW2 Subs in General'
    ];

    if (!in_array($category, $allowed_categories)) {
        throw new Exception('Invalid category: ' . $category);
    }

    // Check if we're running on the server vs local
    $is_local = (strpos($_SERVER['SERVER_NAME'], 'localhost') !== false || 
                 strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false);
    
    if ($is_local) {
        // Local development path
        $base_docs_path = '/Users/irving/Documents/GitHub/Diesel-Electric-Submarine-FAQs/docs';
        $repo_path = '/Users/irving/Documents/GitHub/Diesel-Electric-Submarine-FAQs';
    } else {
        // Server path - YOU NEED TO UPDATE THESE PATHS!
        $base_docs_path = '/path/to/your/server/docs';  // UPDATE THIS!
        $repo_path = '/path/to/your/server/repo';       // UPDATE THIS!
    }

    error_log("FAQ Creation Debug - Using base path: $base_docs_path");

    // Check if base path exists
    if (!is_dir($base_docs_path)) {
        throw new Exception("Base docs path does not exist: $base_docs_path");
    }

    // Check if base path is writable
    if (!is_writable($base_docs_path)) {
        throw new Exception("Base docs path is not writable: $base_docs_path");
    }

    $category_path = $base_docs_path . '/categories/' . $category;
    
    // Create category directory if it doesn't exist
    if (!is_dir($category_path)) {
        if (!mkdir($category_path, 0755, true)) {
            throw new Exception("Failed to create category directory: $category_path");
        }
        error_log("FAQ Creation Debug - Created directory: $category_path");
    }

    $file_path = $category_path . '/' . $filename;

    // Check if file already exists
    if (file_exists($file_path)) {
        throw new Exception("FAQ with filename already exists: $filename");
    }

    // Generate markdown content
    $content = generateMarkdownContent($question, $short_answer, $detailed_answer, $author, $source, $tags, $created_at);
    
    error_log("FAQ Creation Debug - Generated content length: " . strlen($content));

    // Write the file
    $bytes_written = file_put_contents($file_path, $content);
    if ($bytes_written === false) {
        throw new Exception("Failed to write FAQ file: $file_path");
    }

    error_log("FAQ Creation Debug - File written successfully: $file_path ($bytes_written bytes)");

    // Try git operations (wrapped in try-catch to avoid breaking the main flow)
    $git_success = false;
    try {
        if ($is_local && is_dir($repo_path . '/.git')) {
            $git_result = performGitOperations($repo_path, $category, $filename, $question, $author);
            $git_success = $git_result['success'];
            error_log("FAQ Creation Debug - Git operations: " . ($git_success ? 'SUCCESS' : 'FAILED - ' . $git_result['error']));
        }
    } catch (Exception $git_error) {
        error_log("FAQ Creation Debug - Git error: " . $git_error->getMessage());
        // Don't fail the whole operation for git errors
    }

    // Log successful creation
    $log_entry = [
        'timestamp' => date('c'),
        'category' => $category,
        'filename' => $filename,
        'question' => $question,
        'author' => $author ?: 'anonymous',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'git_success' => $git_success
    ];
    
    $log_file = $base_docs_path . '/submissions.log';
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);

    echo json_encode([
        'success' => true,
        'message' => 'FAQ created successfully',
        'filename' => $filename,
        'category' => $category,
        'git_committed' => $git_success,
        'debug_info' => [
            'file_path' => $file_path,
            'bytes_written' => $bytes_written,
            'is_local' => $is_local
        ]
    ]);

} catch (Exception $e) {
    error_log("FAQ Creation Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'debug_info' => [
            'error_type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}

function generateMarkdownContent($question, $short_answer, $detailed_answer, $author, $source, $tags, $created_at) {
    $content = "# $question\n\n";
    
    // Add metadata if provided
    if ($author || $source || $tags) {
        $content .= "---\n";
        if ($author) $content .= "author: $author\n";
        if ($source) $content .= "source: $source\n";
        if ($tags) $content .= "tags: $tags\n";
        $content .= "created: $created_at\n";
        $content .= "---\n\n";
    }
    
    // Add tabbed content structure
    $content .= "=== \"Quick Answer\"\n\n";
    $content .= "    " . str_replace("\n", "\n    ", $short_answer) . "\n\n";
    
    $content .= "=== \"Detailed Answer\"\n\n";
    $content .= "    " . str_replace("\n", "\n    ", $detailed_answer) . "\n\n";
    
    return $content;
}

function performGitOperations($repo_path, $category, $filename, $question, $author) {
    $author_name = $author ?: 'FAQ Contributor';
    $file_relative_path = "docs/categories/$category/$filename";
    
    $commands = [
        "cd " . escapeshellarg($repo_path),
        "git add " . escapeshellarg($file_relative_path),
        "git commit -m " . escapeshellarg("Add FAQ: $question") . " --author=" . escapeshellarg("$author_name <faq@dieselsubs.com>"),
        "git push origin main"
    ];
    
    $command = implode(' && ', $commands);
    exec($command . ' 2>&1', $output, $return_code);
    
    return [
        'success' => $return_code === 0,
        'error' => $return_code !== 0 ? implode("\n", $output) : null,
        'output' => $output
    ];
}
?>