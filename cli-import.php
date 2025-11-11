<?php
// Command line FAQ import script
require_once 'config/database.php';

echo "Starting FAQ import...\n";

// Clear existing data
try {
    $pdo->exec("DELETE FROM faqs");
    $pdo->exec("DELETE FROM categories");
    echo "✅ Cleared existing data\n";
} catch (Exception $e) {
    echo "❌ Error clearing data: " . $e->getMessage() . "\n";
}

// Get markdown files
$files = glob('*.md');
$faqFiles = array_filter($files, function($file) {
    return preg_match('/^\d+/', basename($file));
});

echo "Found " . count($faqFiles) . " FAQ files\n";

function stripMarkdown($text) {
    $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
    $text = preg_replace('/\*(.*?)\*/', '$1', $text);
    return trim($text);
}

function createSlug($text) {
    $text = stripMarkdown($text);
    $text = strtolower($text);
    $text = preg_replace('/[^\w\s-]/', '', $text);
    $text = preg_replace('/[\s_-]+/', '-', $text);
    $text = trim($text, '-');
    return substr($text, 0, 50);
}

$totalImported = 0;

foreach ($faqFiles as $file) {
    echo "\nProcessing: $file\n";
    
    $content = file_get_contents($file);
    if ($content === false) {
        echo "❌ Could not read file: $file\n";
        continue;
    }
    
    $lines = explode("\n", $content);
    
    // Extract category from first line
    $firstLine = trim($lines[0]);
    $category = stripMarkdown(preg_replace('/^#+\s*/', '', $firstLine));
    
    echo "Category: $category\n";
    
    // Insert category
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $categorySlug = createSlug($category);
        $stmt->execute([$category, $categorySlug, "Questions about $category"]);
        
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$category]);
        $categoryId = $stmt->fetchColumn();
        
        if (!$categoryId) {
            echo "❌ Could not get category ID for: $category\n";
            continue;
        }
        
        echo "Category ID: $categoryId\n";
        
    } catch (Exception $e) {
        echo "❌ Category error: " . $e->getMessage() . "\n";
        continue;
    }
    
    // Parse Q&A pairs
    $currentQ = '';
    $currentA = '';
    $inAnswer = false;
    $qaCount = 0;
    
    for ($i = 1; $i < count($lines); $i++) {
        $line = trim($lines[$i]);
        
        if (preg_match('/^Q\d*[:\.]?\s*(.+)$/i', $line, $matches)) {
            // Save previous Q&A if exists
            if (!empty($currentQ) && !empty($currentA)) {
                try {
                    $question = trim($currentQ);
                    $answer = trim($currentA);
                    $slug = createSlug($question);
                    $shortAnswer = substr(stripMarkdown($answer), 0, 250);
                    
                    $stmt = $pdo->prepare("INSERT INTO faqs (category_id, title, slug, question, content, answer, short_answer, is_published, status) VALUES (?, ?, ?, ?, ?, ?, ?, 1, 'published')");
                    $stmt->execute([$categoryId, $question, $slug, $question, $answer, $answer, $shortAnswer]);
                    
                    $qaCount++;
                    $totalImported++;
                    echo "✅ Q&A $qaCount: " . substr($question, 0, 50) . "...\n";
                    
                } catch (Exception $e) {
                    echo "❌ Q&A insert error: " . $e->getMessage() . "\n";
                }
            }
            
            // Start new question
            $currentQ = trim($matches[1]);
            $currentA = '';
            $inAnswer = false;
            
        } elseif (preg_match('/^A\d*[:\.]?\s*(.*)$/i', $line, $matches)) {
            $currentA = trim($matches[1]);
            $inAnswer = true;
            
        } elseif ($inAnswer && !empty($line) && !preg_match('/^[QA]\d*[:\.]/', $line)) {
            $currentA .= "\n" . $line;
        }
    }
    
    // Save final Q&A
    if (!empty($currentQ) && !empty($currentA)) {
        try {
            $question = trim($currentQ);
            $answer = trim($currentA);
            $slug = createSlug($question);
            $shortAnswer = substr(stripMarkdown($answer), 0, 250);
            
            $stmt = $pdo->prepare("INSERT INTO faqs (category_id, title, slug, question, content, answer, short_answer, is_published, status) VALUES (?, ?, ?, ?, ?, ?, ?, 1, 'published')");
            $stmt->execute([$categoryId, $question, $slug, $question, $answer, $answer, $shortAnswer]);
            
            $qaCount++;
            $totalImported++;
            echo "✅ Final Q&A $qaCount: " . substr($question, 0, 50) . "...\n";
            
        } catch (Exception $e) {
            echo "❌ Final Q&A insert error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "Processed $qaCount Q&As from $file\n";
}

// Final count
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM faqs");
    $totalFaqs = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    $totalCategories = $stmt->fetchColumn();
    
    echo "\n=== IMPORT COMPLETE ===\n";
    echo "Total FAQs imported: $totalFaqs\n";
    echo "Total Categories: $totalCategories\n";
    echo "Files processed: " . count($faqFiles) . "\n";
    
} catch (Exception $e) {
    echo "❌ Final count error: " . $e->getMessage() . "\n";
}
?>