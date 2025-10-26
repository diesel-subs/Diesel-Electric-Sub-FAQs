<?php
/**
 * create-faq.php - API endpoint for creating new FAQ entries
 * Receives JSON data from the create-faq form and processes it
 */

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

// Get the JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

// Validate required fields
$required_fields = ['category', 'question', 'short_answer', 'detailed_answer', 'filename'];
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

// Sanitize and validate data
$category = trim($data['category']);
$question = trim($data['question']);
$short_answer = trim($data['short_answer']);
$detailed_answer = trim($data['detailed_answer']);
$filename = trim($data['filename']);
$author = trim($data['author'] ?? '');
$source = trim($data['source'] ?? '');
$tags = trim($data['tags'] ?? '');
$created_at = $data['created_at'] ?? date('c');

// Validate category against allowed categories
$allowed_categories = [
    'Battles, Small and Large',
    'Crews Aboard US WW2 Subs',
    'Hull and Compartments',
    'Life Aboard US WW2 Subs',
    'Operating US WW2 Subs',
    'US WW2 Subs in General'
];

if (!in_array($category, $allowed_categories)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid category']);
    exit();
}

// Validate filename format
if (!preg_match('/^Q-[a-zA-Z0-9-]+\.md$/', $filename)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid filename format']);
    exit();
}

// Basic spam/honeypot check (if website field is filled, it's likely spam)
if (!empty($data['website'])) {
    // Log but don't reject - just return success
    error_log("Potential spam FAQ submission blocked: " . json_encode($data));
    echo json_encode(['success' => true, 'message' => 'FAQ created successfully']);
    exit();
}

// Generate the Markdown content
$markdown_content = generateMarkdownContent($question, $short_answer, $detailed_answer, $author, $source, $tags, $created_at);

// Save the FAQ entry
try {
    $result = saveFaqEntry($category, $filename, $markdown_content, $data);
    
    if ($result['success']) {
        // Log successful creation
        error_log("FAQ created successfully: $category/$filename by " . ($author ?: 'anonymous'));
        
        echo json_encode([
            'success' => true,
            'message' => 'FAQ created successfully',
            'filename' => $filename,
            'category' => $category,
            'url' => $result['url'] ?? null
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to save FAQ'
        ]);
    }
} catch (Exception $e) {
    error_log("Error creating FAQ: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

/**
 * Generate Markdown content for the FAQ
 */
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

/**
 * Save the FAQ entry to the appropriate location
 */
function saveFaqEntry($category, $filename, $content, $originalData) {
    // Configuration - path to the local repository docs folder
    $base_docs_path = '/Users/irving/Documents/GitHub/Diesel-Electric-Submarine-FAQs/docs';
    $category_path = $base_docs_path . '/categories/' . $category;
    $file_path = $category_path . '/' . $filename;
    
    // Create category directory if it doesn't exist
    if (!is_dir($category_path)) {
        if (!mkdir($category_path, 0755, true)) {
            return ['success' => false, 'message' => 'Failed to create category directory'];
        }
    }
    
    // Check if file already exists
    if (file_exists($file_path)) {
        return ['success' => false, 'message' => 'FAQ with this filename already exists'];
    }
    
    // Write the file
    if (file_put_contents($file_path, $content) === false) {
        return ['success' => false, 'message' => 'Failed to write FAQ file'];
    }
    
    // Optional: Log the submission to a separate file for review
    $log_entry = [
        'timestamp' => date('c'),
        'category' => $category,
        'filename' => $filename,
        'question' => $originalData['question'],
        'author' => $originalData['author'] ?? 'anonymous',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    $log_file = $base_docs_path . '/submissions.log';
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
    
    // Update the category index file
    $index_updated = updateCategoryIndex($category_path, $filename, $originalData['question']);
    if (!$index_updated) {
        error_log("Warning: Failed to update category index for $category/$filename");
    }
    
    // Trigger a git commit for the new FAQ
    triggerGitCommit($category, $filename, $originalData);
    
    return [
        'success' => true,
        'url' => "/categories/$category/" . urlencode($filename)
    ];
}

/**
 * Update the category index file to include the new FAQ
 */
function updateCategoryIndex($category_path, $filename, $question) {
    $index_file = $category_path . '/index.md';
    
    // Read existing index file
    if (file_exists($index_file)) {
        $content = file_get_contents($index_file);
    } else {
        // Create basic index structure without "## Questions" header
        $category_name = basename($category_path);
        $content = "# $category_name\n\n";
    }
    
    // Create the new FAQ entry using the actual question as display text
    $new_entry = "- [$question](./$filename)\n";
    
    // Check if entry already exists
    if (strpos($content, $new_entry) !== false || strpos($content, "./$filename") !== false) {
        return true; // Already exists, nothing to do
    }
    
    // Find the last bullet point line and add the new entry after it
    $lines = explode("\n", $content);
    $last_bullet_index = -1;
    
    // Find the last line that starts with "- ["
    for ($i = count($lines) - 1; $i >= 0; $i--) {
        if (preg_match('/^- \[/', trim($lines[$i]))) {
            $last_bullet_index = $i;
            break;
        }
    }
    
    if ($last_bullet_index >= 0) {
        // Insert after the last bullet point
        array_splice($lines, $last_bullet_index + 1, 0, trim($new_entry));
    } else {
        // No existing bullet points found, add after the header
        // Find the first non-empty line after the title
        $insert_index = 1; // Default to after title
        for ($i = 1; $i < count($lines); $i++) {
            if (trim($lines[$i]) !== '') {
                $insert_index = $i + 1;
                break;
            }
        }
        array_splice($lines, $insert_index, 0, '', trim($new_entry));
    }
    
    $content = implode("\n", $lines);
    
    // Write the updated content
    return file_put_contents($index_file, $content) !== false;
}

/**
 * Trigger a git commit for the new FAQ
 */
function triggerGitCommit($category, $filename, $data) {
    $docs_path = '/Users/irving/Documents/GitHub/Diesel-Electric-Submarine-FAQs';
    $author = $data['author'] ?? 'FAQ Contributor';
    $question = $data['question'];
    
    $commands = [
        "cd $docs_path",
        "git add docs/categories/" . escapeshellarg($category) . "/" . escapeshellarg($filename),
        "git add docs/categories/" . escapeshellarg($category) . "/index.md",
        "git commit -m " . escapeshellarg("Add FAQ: $question") . " --author=" . escapeshellarg("$author <faq@dieselsubs.com>"),
        "git push origin main"
    ];
    
    $command = implode(' && ', $commands);
    exec($command . ' 2>&1', $output, $return_code);
    
    if ($return_code !== 0) {
        error_log("Git commit failed for FAQ $filename: " . implode("\n", $output));
    } else {
        error_log("Git commit successful for FAQ $filename");
    }
}

/**
 * Optional: Send notification email about new FAQ
 * Uncomment and configure if you want email notifications
 */
/*
function sendNotificationEmail($category, $filename, $data) {
    $to = 'admin@dieselsubs.com';
    $subject = 'New FAQ Submitted: ' . $data['question'];
    $message = "A new FAQ has been submitted:\n\n";
    $message .= "Category: $category\n";
    $message .= "Question: " . $data['question'] . "\n";
    $message .= "Author: " . ($data['author'] ?: 'Anonymous') . "\n";
    $message .= "Filename: $filename\n\n";
    $message .= "Short Answer:\n" . $data['short_answer'] . "\n\n";
    $message .= "Detailed Answer:\n" . $data['detailed_answer'] . "\n";
    
    $headers = 'From: noreply@dieselsubs.com' . "\r\n" .
               'Reply-To: noreply@dieselsubs.com' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    
    mail($to, $subject, $message, $headers);
}
*/
?>