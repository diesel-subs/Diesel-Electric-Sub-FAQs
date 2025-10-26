<?php
/**
 * test-faq-list.php - Test script to verify FAQ listing functionality
 */

// Include helper functions
require_once 'faq-helpers.php';

echo "Testing FAQ listing functionality...\n\n";

$base_docs_path = '/Users/irving/Documents/GitHub/Diesel-Electric-Submarine-FAQs/docs';

echo "Base docs path: $base_docs_path\n";
echo "Categories path: " . $base_docs_path . '/categories' . "\n\n";

// Check if categories directory exists
if (!is_dir($base_docs_path . '/categories')) {
    echo "ERROR: Categories directory not found!\n";
    exit(1);
}

echo "Scanning for FAQ files...\n";

// Get all FAQs
try {
    $faqs = getAllFaqs($base_docs_path);
    
    echo "Found " . count($faqs) . " FAQs:\n\n";
    
    if (count($faqs) > 0) {
        foreach (array_slice($faqs, 0, 5) as $i => $faq) {
            echo "FAQ " . ($i + 1) . ":\n";
            echo "  Question: " . substr($faq['question'], 0, 80) . (strlen($faq['question']) > 80 ? '...' : '') . "\n";
            echo "  Category: " . $faq['category'] . "\n";
            echo "  Filename: " . $faq['filename'] . "\n";
            echo "  Short Answer: " . substr($faq['short_answer'], 0, 50) . (strlen($faq['short_answer']) > 50 ? '...' : '') . "\n";
            echo "\n";
        }
        
        if (count($faqs) > 5) {
            echo "... and " . (count($faqs) - 5) . " more FAQs\n\n";
        }
        
        // Test search functionality
        echo "Testing search for 'submarine':\n";
        $searchResults = searchFaqs($faqs, 'submarine');
        echo "Found " . count($searchResults) . " results\n\n";
        
        echo "Testing search for 'torpedo':\n";
        $searchResults = searchFaqs($faqs, 'torpedo');
        echo "Found " . count($searchResults) . " results\n\n";
        
    } else {
        echo "No FAQs found. Let's check what's in the categories directory:\n";
        
        $categories_path = $base_docs_path . '/categories';
        $dirs = scandir($categories_path);
        
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            
            $dir_path = $categories_path . '/' . $dir;
            if (is_dir($dir_path)) {
                echo "Category: $dir\n";
                $files = scandir($dir_path);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..' && substr($file, -3) === '.md') {
                        echo "  - $file\n";
                    }
                }
                echo "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

?>