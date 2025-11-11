<?php
require_once 'config/database.php';

echo "<h1>FAQ Import Debug</h1>";

// Clear existing data
try {
    $pdo->exec("DELETE FROM faqs");
    $pdo->exec("DELETE FROM categories");
    echo "<p>✅ Cleared existing data</p>";
} catch (Exception $e) {
    echo "<p>❌ Error clearing data: " . $e->getMessage() . "</p>";
}

// Check markdown files
$files = glob('*.md');
$faqFiles = array_filter($files, function($file) {
    return preg_match('/^\d+/', basename($file));
});

echo "<h2>Found Files:</h2>";
foreach ($faqFiles as $file) {
    echo "<p>📄 " . htmlspecialchars($file) . "</p>";
}

if (empty($faqFiles)) {
    echo "<p>❌ No FAQ markdown files found!</p>";
    exit;
}

echo "<h2>Processing Files:</h2>";

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

// Process each file
foreach ($faqFiles as $file) {
    echo "<h3>Processing: " . htmlspecialchars($file) . "</h3>";
    
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    
    // Extract category from first line
    $firstLine = trim($lines[0]);
    $category = stripMarkdown(preg_replace('/^#+\s*/', '', $firstLine));
    
    echo "<p><strong>Category:</strong> " . htmlspecialchars($category) . "</p>";
    
    // Insert category
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $categorySlug = createSlug($category);
        $stmt->execute([$category, $categorySlug, "Questions about $category"]);
        
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$category]);
        $categoryId = $stmt->fetchColumn();
        
        echo "<p>✅ Category ID: $categoryId</p>";
        
    } catch (Exception $e) {
        echo "<p>❌ Category error: " . $e->getMessage() . "</p>";
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
                    echo "<p>✅ Q&A $qaCount: " . htmlspecialchars(substr($question, 0, 50)) . "...</p>";
                    
                } catch (Exception $e) {
                    echo "<p>❌ Q&A insert error: " . $e->getMessage() . "</p>";
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
            echo "<p>✅ Final Q&A $qaCount: " . htmlspecialchars(substr($question, 0, 50)) . "...</p>";
            
        } catch (Exception $e) {
            echo "<p>❌ Final Q&A insert error: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<p><strong>Total Q&As processed for this file: $qaCount</strong></p>";
}

// Final count
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM faqs");
    $totalFaqs = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    $totalCategories = $stmt->fetchColumn();
    
    echo "<h2>Final Results:</h2>";
    echo "<p>✅ Total FAQs imported: $totalFaqs</p>";
    echo "<p>✅ Total Categories: $totalCategories</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Final count error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='/admin/simple-db-admin.php'>View Database Admin</a></p>";
?>