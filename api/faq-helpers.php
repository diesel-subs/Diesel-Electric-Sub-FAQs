<?php
/**
 * faq-helpers.php - Helper functions for FAQ management
 * Contains utility functions for finding, listing, and managing FAQ files
 */

/**
 * Get all FAQ files from the docs/categories directory
 * @param string $base_docs_path Base path to docs directory
 * @return array Array of FAQ file information
 */
function getAllFaqs($base_docs_path) {
    $faqs = [];
    $categories_path = $base_docs_path . '/categories';
    
    if (!is_dir($categories_path)) {
        return $faqs;
    }
    
    $category_dirs = scandir($categories_path);
    
    foreach ($category_dirs as $category) {
        if ($category === '.' || $category === '..' || !is_dir($categories_path . '/' . $category)) {
            continue;
        }
        
        $category_path = $categories_path . '/' . $category;
        $files = scandir($category_path);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === 'index.md' || substr($file, -3) !== '.md') {
                continue;
            }
            
            $file_path = $category_path . '/' . $file;
            $faq_data = parseFaqFile($file_path);
            
            if ($faq_data) {
                $faq_data['category'] = $category;
                $faq_data['filename'] = $file;
                $faq_data['path'] = $file_path;
                $faqs[] = $faq_data;
            }
        }
    }
    
    return $faqs;
}

/**
 * Parse FAQ file to extract question and content
 * @param string $file_path Path to FAQ file
 * @return array|null FAQ data or null if parsing fails
 */
function parseFaqFile($file_path) {
    if (!file_exists($file_path)) {
        return null;
    }
    
    $content = file_get_contents($file_path);
    if ($content === false) {
        return null;
    }
    
    // Extract question from filename
    $filename = basename($file_path, '.md');
    $question = convertFilenameToQuestion($filename);
    
    $lines = explode("\n", $content);
    $author = '';
    $updated = '';
    $short_answer = '';
    $detailed_answer = '';
    $related_topics = [];
    
    $in_frontmatter = false;
    $in_summary = false;
    $in_detailed = false;
    $in_related = false;
    $frontmatter_ended = false;
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        $trimmed = trim($line);
        
        // Handle frontmatter
        if ($i === 0 && $trimmed === '---') {
            $in_frontmatter = true;
            continue;
        }
        
        if ($in_frontmatter && $trimmed === '---') {
            $in_frontmatter = false;
            $frontmatter_ended = true;
            continue;
        }
        
        if ($in_frontmatter) {
            if (strpos($line, 'author:') === 0) {
                $author = trim(substr($line, 7));
            } elseif (strpos($line, 'updated:') === 0) {
                $updated = trim(substr($line, 8));
            }
            continue;
        }
        
        // Look for tabbed sections
        if (preg_match('/^=== "Summary"/', $trimmed)) {
            $in_summary = true;
            $in_detailed = false;
            $in_related = false;
            continue;
        }
        
        if (preg_match('/^=== "Detailed Answer"/', $trimmed)) {
            $in_summary = false;
            $in_detailed = true;
            $in_related = false;
            continue;
        }
        
        if (preg_match('/^=== "Related Topics"/', $trimmed)) {
            $in_summary = false;
            $in_detailed = false;
            $in_related = true;
            continue;
        }
        
        // Extract content based on current section
        if ($in_summary && !empty($trimmed) && !preg_match('/^(=== |!!! |<a id=)/', $trimmed)) {
            if (strpos($line, '    ') === 0) {
                $short_answer .= substr($line, 4) . "\n";
            } else {
                $short_answer .= $line . "\n";
            }
        }
        
        if ($in_detailed && !empty($trimmed) && !preg_match('/^(=== |!!! |<a id=)/', $trimmed)) {
            if (strpos($line, '    ') === 0) {
                $detailed_answer .= substr($line, 4) . "\n";
            } else {
                $detailed_answer .= $line . "\n";
            }
        }
        
        if ($in_related && !empty($trimmed) && !preg_match('/^(=== |!!! |<a id=)/', $trimmed)) {
            // Extract links and text from related topics
            if (preg_match('/\[([^\]]+)\]/', $line, $matches)) {
                $related_topics[] = trim($matches[1]);
            } elseif (preg_match('/^\s*-?\s*(.+)$/', $line, $matches)) {
                $topic = trim($matches[1]);
                if (!empty($topic) && !preg_match('/^<|^\[/', $topic)) {
                    $related_topics[] = $topic;
                }
            }
        }
    }
    
    return [
        'question' => $question,
        'author' => $author,
        'updated' => $updated,
        'short_answer' => trim($short_answer),
        'detailed_answer' => trim($detailed_answer),
        'related_topics' => $related_topics
    ];
}

/**
 * Convert filename to readable question format
 * @param string $filename Filename without extension
 * @return string Formatted question
 */
function convertFilenameToQuestion($filename) {
    // Remove any leading numbers or prefixes
    $question = preg_replace('/^(Q-|q-|\d+-?)/', '', $filename);
    
    // Replace hyphens with spaces
    $question = str_replace('-', ' ', $question);
    
    // Capitalize first letter of each word
    $question = ucwords($question);
    
    // Add question mark if not present
    if (!preg_match('/[.!?]$/', $question)) {
        $question .= '?';
    }
    
    return $question;
}

/**
 * Search FAQ files by text content
 * @param array $faqs Array of FAQ data
 * @param string $search_term Search term
 * @return array Filtered FAQ array
 */
function searchFaqs($faqs, $search_term) {
    if (empty($search_term)) {
        return $faqs;
    }
    
    $search_term = strtolower($search_term);
    $results = [];
    
    foreach ($faqs as $faq) {
        $score = 0;
        
        // Search in question (highest weight)
        if (stripos($faq['question'], $search_term) !== false) {
            $score += 10;
        }
        
        // Search in short answer
        if (stripos($faq['short_answer'], $search_term) !== false) {
            $score += 5;
        }
        
        // Search in detailed answer
        if (stripos($faq['detailed_answer'], $search_term) !== false) {
            $score += 3;
        }
        
        // Search in category
        if (stripos($faq['category'], $search_term) !== false) {
            $score += 2;
        }
        
        // Search in related topics
        foreach ($faq['related_topics'] as $topic) {
            if (stripos($topic, $search_term) !== false) {
                $score += 1;
                break;
            }
        }
        
        if ($score > 0) {
            $faq['search_score'] = $score;
            $results[] = $faq;
        }
    }
    
    // Sort by search score
    usort($results, function($a, $b) {
        return $b['search_score'] - $a['search_score'];
    });
    
    return $results;
}

/**
 * Get FAQ by filename and category
 * @param string $base_docs_path Base path to docs directory
 * @param string $category Category name
 * @param string $filename Filename
 * @return array|null FAQ data or null if not found
 */
function getFaqByFile($base_docs_path, $category, $filename) {
    $file_path = $base_docs_path . '/categories/' . $category . '/' . $filename;
    
    if (!file_exists($file_path)) {
        return null;
    }
    
    $faq_data = parseFaqFile($file_path);
    if ($faq_data) {
        $faq_data['category'] = $category;
        $faq_data['filename'] = $filename;
        $faq_data['path'] = $file_path;
    }
    
    return $faq_data;
}

/**
 * Validate FAQ data
 * @param array $faq_data FAQ data to validate
 * @return array Validation result with success boolean and errors array
 */
function validateFaqData($faq_data) {
    $errors = [];
    
    if (empty($faq_data['question'])) {
        $errors[] = 'Question is required';
    } elseif (strlen($faq_data['question']) > 200) {
        $errors[] = 'Question must be 200 characters or less';
    }
    
    if (empty($faq_data['short_answer'])) {
        $errors[] = 'Short answer is required';
    } elseif (strlen($faq_data['short_answer']) > 500) {
        $errors[] = 'Short answer must be 500 characters or less';
    }
    
    if (empty($faq_data['detailed_answer'])) {
        $errors[] = 'Detailed answer is required';
    }
    
    if (!empty($faq_data['category'])) {
        $allowed_categories = [
            'Battles, Small and Large',
            'Crews Aboard US WW2 Subs',
            'Hull and Compartments',
            'Life Aboard US WW2 Subs',
            'Operating US Subs in WW2',
            'US WW2 Subs in General'
        ];
        
        if (!in_array($faq_data['category'], $allowed_categories)) {
            $errors[] = 'Invalid category';
        }
    }
    
    return [
        'success' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Sanitize FAQ data for safe storage
 * @param array $faq_data FAQ data to sanitize
 * @return array Sanitized FAQ data
 */
function sanitizeFaqData($faq_data) {
    return [
        'question' => trim(strip_tags($faq_data['question'] ?? '')),
        'short_answer' => trim($faq_data['short_answer'] ?? ''),
        'detailed_answer' => trim($faq_data['detailed_answer'] ?? ''),
        'category' => trim($faq_data['category'] ?? ''),
        'author' => trim(strip_tags($faq_data['author'] ?? '')),
        'related_topics' => array_map('trim', array_filter($faq_data['related_topics'] ?? []))
    ];
}

/**
 * Get categories with FAQ counts
 * @param string $base_docs_path Base path to docs directory
 * @return array Categories with counts
 */
function getCategoriesWithCounts($base_docs_path) {
    $categories = [];
    $categories_path = $base_docs_path . '/categories';
    
    if (!is_dir($categories_path)) {
        return $categories;
    }
    
    $category_dirs = scandir($categories_path);
    
    foreach ($category_dirs as $category) {
        if ($category === '.' || $category === '..' || !is_dir($categories_path . '/' . $category)) {
            continue;
        }
        
        $category_path = $categories_path . '/' . $category;
        $files = scandir($category_path);
        $count = 0;
        
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && $file !== 'index.md' && substr($file, -3) === '.md') {
                $count++;
            }
        }
        
        $categories[$category] = $count;
    }
    
    return $categories;
}

/**
 * Check if filename already exists in category
 * @param string $base_docs_path Base path to docs directory
 * @param string $category Category name
 * @param string $filename Filename to check
 * @return bool True if file exists
 */
function faqFileExists($base_docs_path, $category, $filename) {
    $file_path = $base_docs_path . '/categories/' . $category . '/' . $filename;
    return file_exists($file_path);
}

/**
 * Get recent FAQs (by modification time)
 * @param array $faqs Array of FAQ data
 * @param int $limit Number of recent FAQs to return
 * @return array Recent FAQs
 */
function getRecentFaqs($faqs, $limit = 10) {
    // Add modification time to each FAQ
    foreach ($faqs as &$faq) {
        if (isset($faq['path']) && file_exists($faq['path'])) {
            $faq['modified'] = filemtime($faq['path']);
        } else {
            $faq['modified'] = 0;
        }
    }
    
    // Sort by modification time
    usort($faqs, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
    
    return array_slice($faqs, 0, $limit);
}

?>