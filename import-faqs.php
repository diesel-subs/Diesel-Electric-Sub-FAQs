<?php
require_once 'config/database.php';

function extract_title_from_filename($filename) {
    // Remove .html extension and convert dashes to spaces
    $title = str_replace(['.html', '-'], ['', ' '], $filename);
    // Capitalize words properly
    return ucwords($title);
}

function extract_question_from_title($title) {
    // Convert title to a proper question format
    $question = $title;
    if (!str_ends_with($question, '?') && !str_ends_with($question, '.') && !str_ends_with($question, '!')) {
        $question .= '?';
    }
    return $question;
}

function create_slug($title) {
    return strtolower(str_replace([' ', '/', '&', '?', '!', '.'], ['-', '-', 'and', '', '', ''], $title));
}

function extract_content_from_html($html_content) {
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    
    $summary = '';
    $detailed = '';
    
    // Extract Summary tab content
    $summaryDiv = $dom->getElementById('tabSummary');
    if ($summaryDiv) {
        $summary = trim(strip_tags($summaryDiv->textContent));
    }
    
    // Extract Detailed Answer tab content
    $detailedDiv = $dom->getElementById('tabDetailedAnswer');
    if ($detailedDiv) {
        $detailed = trim(strip_tags($detailedDiv->textContent));
    }
    
    return [$summary, $detailed];
}

function get_category_id_by_name($pdo, $category_name) {
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$category_name]);
    $result = $stmt->fetch();
    return $result ? $result['id'] : null;
}

// Main processing
try {
    echo "Starting FAQ import from categories folder...\n\n";
    
    $categories_dir = __DIR__ . '/categories';
    $total_imported = 0;
    $errors = [];
    
    // Get all category directories
    $category_dirs = glob($categories_dir . '/*', GLOB_ONLYDIR);
    
    foreach ($category_dirs as $category_path) {
        $category_name = basename($category_path);
        echo "Processing category: $category_name\n";
        
        // Get category ID
        $category_id = get_category_id_by_name($pdo, $category_name);
        if (!$category_id) {
            $errors[] = "Category '$category_name' not found in database";
            continue;
        }
        
        // Get all HTML files in this category (except index.html)
        $html_files = glob($category_path . '/*.html');
        $category_count = 0;
        
        foreach ($html_files as $file_path) {
            $filename = basename($file_path);
            
            // Skip index files
            if ($filename === 'index.html') {
                continue;
            }
            
            try {
                $html_content = file_get_contents($file_path);
                if (!$html_content) {
                    $errors[] = "Could not read file: $file_path";
                    continue;
                }
                
                // Extract content
                [$summary, $detailed] = extract_content_from_html($html_content);
                
                if (empty($summary) && empty($detailed)) {
                    $errors[] = "No content found in file: $filename";
                    continue;
                }
                
                // Generate FAQ data
                $title = extract_title_from_filename($filename);
                $question = extract_question_from_title($title);
                $slug = create_slug($title);
                $answer = $detailed ?: $summary; // Use detailed if available, otherwise summary
                $short_answer = $summary ?: substr($detailed, 0, 4000);
                
                // Check if FAQ already exists
                $stmt = $pdo->prepare("SELECT id FROM faqs WHERE slug = ?");
                $stmt->execute([$slug]);
                if ($stmt->fetch()) {
                    echo "  - Skipping '$title' (already exists)\n";
                    continue;
                }
                
                // Insert FAQ
                $stmt = $pdo->prepare("
                    INSERT INTO faqs (category_id, title, slug, question, answer, short_answer, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'published')
                ");
                
                $success = $stmt->execute([
                    $category_id,
                    $title,
                    $slug,
                    $question,
                    $answer,
                    $short_answer
                ]);
                
                if ($success) {
                    echo "  ✓ Imported: $title\n";
                    $total_imported++;
                    $category_count++;
                } else {
                    $errors[] = "Failed to insert FAQ: $title";
                }
                
            } catch (Exception $e) {
                $errors[] = "Error processing $filename: " . $e->getMessage();
            }
        }
        
        echo "  Category total: $category_count FAQs\n\n";
    }
    
    echo "===== IMPORT COMPLETE =====\n";
    echo "Total FAQs imported: $total_imported\n";
    
    if (!empty($errors)) {
        echo "\nErrors encountered:\n";
        foreach ($errors as $error) {
            echo "  - $error\n";
        }
    }
    
    // Show final count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM faqs");
    $result = $stmt->fetch();
    echo "\nTotal FAQs in database: " . $result['total'] . "\n";
    
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
}
?>