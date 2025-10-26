<?php
/**
 * update-faq.php - API endpoint for updating existing FAQ entries
 * Handles the complex process of updating FAQ content, managing filename changes,
 * and updating all references throughout the documentation
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
$required_fields = ['oldFaq', 'category', 'question', 'short_answer', 'detailed_answer'];
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

// Sanitize and validate data
$oldFaq = $data['oldFaq'];
$category = trim($data['category']);
$question = trim($data['question']);
$short_answer = trim($data['short_answer']);
$detailed_answer = trim($data['detailed_answer']);
$related_topics = $data['related_topics'] ?? [];
$author = trim($data['author'] ?? '');
$create_backup = $data['create_backup'] ?? true;

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

// Basic spam/honeypot check
if (!empty($data['website'])) {
    error_log("Potential spam FAQ update blocked: " . json_encode($data));
    echo json_encode(['success' => true, 'message' => 'FAQ updated successfully']);
    exit();
}

try {
    $result = updateFaqEntry($oldFaq, $category, $question, $short_answer, $detailed_answer, $related_topics, $author, $create_backup);
    
    if ($result['success']) {
        error_log("FAQ updated successfully: {$result['oldFile']} -> {$result['newFile']}");
        echo json_encode($result);
    } else {
        http_response_code(500);
        echo json_encode($result);
    }
} catch (Exception $e) {
    error_log("Error updating FAQ: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()]);
}

/**
 * Main function to update FAQ entry
 */
function updateFaqEntry($oldFaq, $category, $question, $short_answer, $detailed_answer, $related_topics, $author, $create_backup) {
    $base_docs_path = '/Users/irving/Documents/GitHub/Diesel-Electric-Submarine-FAQs/docs';
    $repo_path = '/Users/irving/Documents/GitHub/Diesel-Electric-Submarine-FAQs';
    
    // Generate new filename from question
    $new_filename = generateFaqFilename($question);
    $old_file_path = $base_docs_path . '/categories/' . $oldFaq['category'] . '/' . $oldFaq['filename'];
    $new_file_path = $base_docs_path . '/categories/' . $category . '/' . $new_filename;
    
    // Check if old file exists
    if (!file_exists($old_file_path)) {
        return ['success' => false, 'message' => 'Original FAQ file not found: ' . $old_file_path];
    }
    
    // Check if new filename would conflict (unless it's the same file)
    if ($new_file_path !== $old_file_path && file_exists($new_file_path)) {
        return ['success' => false, 'message' => 'A FAQ with the new filename already exists'];
    }
    
    // Create backup branch if requested
    if ($create_backup) {
        $branch_result = createBackupBranch($repo_path, $question);
        if (!$branch_result['success']) {
            return $branch_result;
        }
    }
    
    // Generate new content
    $new_content = generateFaqContent($question, $short_answer, $detailed_answer, $related_topics, $author);
    
    // Create new category directory if needed
    $new_category_path = dirname($new_file_path);
    if (!is_dir($new_category_path)) {
        if (!mkdir($new_category_path, 0755, true)) {
            return ['success' => false, 'message' => 'Failed to create category directory'];
        }
    }
    
    // Write new file
    if (file_put_contents($new_file_path, $new_content) === false) {
        return ['success' => false, 'message' => 'Failed to write new FAQ file'];
    }
    
    // Update references if filename or category changed
    $references_updated = 0;
    if ($new_file_path !== $old_file_path) {
        $references_updated = updateAllReferences($base_docs_path, $oldFaq, $category, $new_filename, $question);
    }
    
    // Remove old file if it's different from new file
    if ($new_file_path !== $old_file_path) {
        if (!unlink($old_file_path)) {
            error_log("Warning: Failed to remove old FAQ file: $old_file_path");
        }
    }
    
    // Commit changes
    $commit_result = commitFaqUpdate($repo_path, $oldFaq, $new_filename, $category, $question, $author);
    
    return [
        'success' => true,
        'message' => 'FAQ updated successfully',
        'oldFile' => $oldFaq['filename'],
        'newFile' => $new_filename,
        'category' => $category,
        'referencesUpdated' => $references_updated,
        'backupBranch' => $create_backup ? generateBranchName($question) : null
    ];
}

/**
 * Generate filename from question
 */
function generateFaqFilename($question) {
    $slug = preg_replace('/[^a-zA-Z0-9\s-]/', '', $question);
    $slug = preg_replace('/\s+/', '-', trim($slug));
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    $slug = substr($slug, 0, 80);
    return 'Q-' . $slug . '.md';
}

/**
 * Generate FAQ content
 */
function generateFaqContent($question, $short_answer, $detailed_answer, $related_topics, $author) {
    $content = "# $question\n\n";
    
    // Add metadata if provided
    if ($author) {
        $content .= "---\n";
        $content .= "author: $author\n";
        $content .= "updated: " . date('Y-m-d') . "\n";
        $content .= "---\n\n";
    }
    
    // Add feedback link
    $content .= '!!! help-feedback ""' . "\n";
    $content .= '    <a href="/feedback/" data-feedback-link>Click here</a>' . "\n";
    $content .= '    if you have additional facts, records, or context about U.S. submarine design, production, or wartime operations.' . "\n\n";
    
    // Add anchor and tabbed content
    $content .= '<a id="summary"></a>' . "\n";
    $content .= '=== "Summary"' . "\n\n";
    $content .= "    " . str_replace("\n", "\n    ", $short_answer) . "\n\n";
    $content .= '=== "Detailed Answer"' . "\n\n";
    $content .= "    " . str_replace("\n", "\n    ", $detailed_answer) . "\n\n";
    
    // Add related topics if provided
    if (!empty($related_topics)) {
        $content .= '=== "Related Topics"' . "\n\n";
        foreach ($related_topics as $topic) {
            $topic = trim($topic);
            if ($topic) {
                $content .= "    - $topic\n";
            }
        }
        $content .= "\n";
    } else {
        $content .= '=== "Related Topics"' . "\n\n";
        $content .= "    \n\n";
    }
    
    return $content;
}

/**
 * Create backup branch
 */
function createBackupBranch($repo_path, $question) {
    $branch_name = generateBranchName($question);
    
    $commands = [
        "cd $repo_path",
        "git checkout -b " . escapeshellarg($branch_name),
        "git add .",
        "git commit -m " . escapeshellarg("Backup before updating FAQ: $question") . " || true"
    ];
    
    $command = implode(' && ', $commands);
    exec($command . ' 2>&1', $output, $return_code);
    
    if ($return_code !== 0) {
        return ['success' => false, 'message' => 'Failed to create backup branch: ' . implode("\n", $output)];
    }
    
    return ['success' => true, 'branch' => $branch_name];
}

/**
 * Generate branch name from question
 */
function generateBranchName($question) {
    $slug = preg_replace('/[^a-zA-Z0-9\s-]/', '', $question);
    $slug = preg_replace('/\s+/', '-', trim($slug));
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    $slug = substr(strtolower($slug), 0, 40);
    return 'update-faq-' . $slug . '-' . date('Ymd-His');
}

/**
 * Update all references to the FAQ
 */
function updateAllReferences($base_docs_path, $oldFaq, $new_category, $new_filename, $new_question) {
    $references_updated = 0;
    
    // Find all markdown files
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base_docs_path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'md') {
            $file_path = $file->getPathname();
            $content = file_get_contents($file_path);
            $original_content = $content;
            
            // Update filename references
            $old_filename = $oldFaq['filename'];
            $content = str_replace($old_filename, $new_filename, $content);
            
            // Update link text if it matches the old question
            if (isset($oldFaq['question'])) {
                $old_question = $oldFaq['question'];
                // Match markdown links with the old question text
                $pattern = '/\[' . preg_quote($old_question, '/') . '\]\([^)]*' . preg_quote($old_filename, '/') . '\)/';
                $replacement = "[$new_question](./$new_filename)";
                $content = preg_replace($pattern, $replacement, $content);
            }
            
            // If content changed, write it back
            if ($content !== $original_content) {
                file_put_contents($file_path, $content);
                $references_updated++;
            }
        }
    }
    
    // Update category index files
    updateCategoryIndexes($base_docs_path, $oldFaq, $new_category, $new_filename, $new_question);
    
    return $references_updated;
}

/**
 * Update category index files
 */
function updateCategoryIndexes($base_docs_path, $oldFaq, $new_category, $new_filename, $new_question) {
    // Remove from old category index if category changed
    if ($oldFaq['category'] !== $new_category) {
        $old_index_path = $base_docs_path . '/categories/' . $oldFaq['category'] . '/index.md';
        if (file_exists($old_index_path)) {
            $content = file_get_contents($old_index_path);
            // Remove the line containing the old filename
            $lines = explode("\n", $content);
            $lines = array_filter($lines, function($line) use ($oldFaq) {
                return strpos($line, $oldFaq['filename']) === false;
            });
            file_put_contents($old_index_path, implode("\n", $lines));
        }
    }
    
    // Add to new category index
    $new_index_path = $base_docs_path . '/categories/' . $new_category . '/index.md';
    if (file_exists($new_index_path)) {
        $content = file_get_contents($new_index_path);
        
        // Check if entry already exists
        if (strpos($content, $new_filename) === false) {
            // Find the last bullet point and add after it
            $lines = explode("\n", $content);
            $last_bullet_index = -1;
            
            for ($i = count($lines) - 1; $i >= 0; $i--) {
                if (preg_match('/^- \[/', trim($lines[$i]))) {
                    $last_bullet_index = $i;
                    break;
                }
            }
            
            $new_entry = "- [$new_question](./$new_filename)";
            
            if ($last_bullet_index >= 0) {
                array_splice($lines, $last_bullet_index + 1, 0, $new_entry);
            } else {
                // Add after description
                $lines[] = '';
                $lines[] = $new_entry;
            }
            
            file_put_contents($new_index_path, implode("\n", $lines));
        }
    }
}

/**
 * Commit FAQ update
 */
function commitFaqUpdate($repo_path, $oldFaq, $new_filename, $category, $question, $author) {
    $commit_author = $author ?: 'FAQ Updater';
    $commit_message = "Update FAQ: $question (was: {$oldFaq['filename']})";
    
    $commands = [
        "cd $repo_path",
        "git add .",
        "git commit -m " . escapeshellarg($commit_message) . " --author=" . escapeshellarg("$commit_author <faq@dieselsubs.com>"),
        "git push origin " . escapeshellarg(getCurrentBranch($repo_path))
    ];
    
    $command = implode(' && ', $commands);
    exec($command . ' 2>&1', $output, $return_code);
    
    if ($return_code !== 0) {
        error_log("Git commit failed for FAQ update: " . implode("\n", $output));
        return ['success' => false, 'message' => 'Failed to commit changes'];
    }
    
    return ['success' => true];
}

/**
 * Get current git branch
 */
function getCurrentBranch($repo_path) {
    $command = "cd $repo_path && git branch --show-current";
    exec($command, $output, $return_code);
    return $return_code === 0 ? trim($output[0]) : 'main';
}

?>