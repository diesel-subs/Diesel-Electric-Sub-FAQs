<?php
require_once 'config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

function extractTags($question, $answer) {
    $tags = [];
    $text = stripMarkdown($question . ' ' . $answer);
    
    $terms = [
        'torpedo', 'engine', 'battery', 'compartment', 'hull', 'periscope',
        'crew', 'captain', 'officer', 'depth', 'submarine', 'boat', 'USS',
        'patrol', 'attack', 'battle', 'war', 'WWII', 'WW2', 'Japanese'
    ];
    
    foreach ($terms as $term) {
        if (stripos($text, $term) !== false) {
            $tags[] = $term;
        }
    }
    
    return array_slice(array_unique($tags), 0, 5);
}

function createShortAnswer($answer) {
    $text = stripMarkdown($answer);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    
    $sentences = preg_split('/(?<=[.!?])\s+(?=[A-Z])/', $text);
    
    $short = '';
    $count = 0;
    foreach ($sentences as $sentence) {
        if ($count >= 3) break;
        $sentence = trim($sentence);
        if (empty($sentence)) continue;
        
        if (strlen($short . ' ' . $sentence) > 300) break;
        $short .= ($short ? ' ' : '') . $sentence;
        $count++;
    }
    
    return $short ?: substr($text, 0, 300);
}

echo "<h1>FAQ Processor with Debug Output</h1>";

try {
    // Clear existing data
    echo "<p>Clearing database...</p>";
    $pdo->exec("DELETE FROM related_faqs");
    $pdo->exec("DELETE FROM faqs");
    $pdo->exec("DELETE FROM feedback");  
    $pdo->exec("DELETE FROM categories");
    $pdo->exec("ALTER TABLE categories AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE faqs AUTO_INCREMENT = 1");
    echo "<p>✓ Database cleared</p>";
    
    // Process just one file first for testing
    $testFile = '05-Hull-and-Compartments.md';
    
    if (!file_exists($testFile)) {
        echo "<p style='color: red;'>File not found: $testFile</p>";
        echo "<p>Current directory: " . getcwd() . "</p>";
        echo "<p>Files in directory:</p><ul>";
        foreach (glob('*.md') as $file) {
            echo "<li>$file</li>";
        }
        echo "</ul>";
        exit;
    }
    
    echo "<h2>Processing: $testFile</h2>";
    
    $content = file_get_contents($testFile);
    $lines = explode("\n", $content);
    
    // Get category from first line
    $category = stripMarkdown(trim($lines[0]));
    echo "<p>Category: <strong>" . htmlspecialchars($category) . "</strong></p>";
    
    // Insert category
    $slug = createSlug($category);
    $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    $result = $stmt->execute([$category, $slug, "Questions about " . $category]);
    
    if ($result) {
        echo "<p>✓ Category inserted</p>";
        $categoryId = $pdo->lastInsertId();
        echo "<p>Category ID: $categoryId</p>";
    } else {
        echo "<p style='color: red;'>Failed to insert category</p>";
        print_r($stmt->errorInfo());
    }
    
    // Find questions and answers
    $faqs = [];
    $currentQuestion = '';
    $currentAnswer = '';
    $inQuestion = false;
    
    foreach ($lines as $lineNum => $line) {
        $line = trim($line);
        
        if ($lineNum === 0) continue; // Skip category line
        
        // Look for questions
        if (preg_match('/^\*\*(.*?)\*\*\s*$/', $line, $matches)) {
            // Save previous FAQ
            if ($currentQuestion && $currentAnswer) {
                $question = stripMarkdown($currentQuestion);
                $question = substr($question, 0, 252);
                
                $faqs[] = [
                    'question' => $question,
                    'answer' => trim($currentAnswer)
                ];
            }
            
            // Start new question
            $currentQuestion = $matches[1];
            $currentAnswer = '';
            $inQuestion = true;
            continue;
        }
        
        // Add to answer if we're in a question block
        if ($inQuestion && !empty($line)) {
            $currentAnswer .= $line . "\n";
        }
    }
    
    // Don't forget last FAQ
    if ($currentQuestion && $currentAnswer) {
        $question = stripMarkdown($currentQuestion);
        $question = substr($question, 0, 252);
        
        $faqs[] = [
            'question' => $question,
            'answer' => trim($currentAnswer)
        ];
    }
    
    echo "<p>Found " . count($faqs) . " FAQs</p>";
    
    // Insert FAQs
    $insertCount = 0;
    foreach ($faqs as $faq) {
        $title = substr($faq['question'], 0, 100);
        $slug = createSlug($faq['question']);
        $shortAnswer = createShortAnswer($faq['answer']);
        $tags = implode(',', extractTags($faq['question'], $faq['answer']));
        
        $stmt = $pdo->prepare("
            INSERT INTO faqs (title, slug, question, content, short_answer, category_id, tags, is_published, view_count, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, 0, NOW(), NOW())
        ");
        
        $result = $stmt->execute([
            $title,
            $slug,
            $faq['question'],
            $faq['answer'],
            $shortAnswer,
            $categoryId,
            $tags
        ]);
        
        if ($result) {
            $insertCount++;
            echo "<p>✓ Inserted FAQ: " . htmlspecialchars(substr($title, 0, 50)) . "...</p>";
        } else {
            echo "<p style='color: red;'>Failed to insert FAQ: " . htmlspecialchars($title) . "</p>";
            print_r($stmt->errorInfo());
        }
    }
    
    echo "<h2>Result</h2>";
    echo "<p style='color: green; font-weight: bold;'>Successfully inserted $insertCount FAQs!</p>";
    
    // Verify the data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM faqs");
    $totalFaqs = $stmt->fetch()['count'];
    echo "<p>Total FAQs in database: $totalFaqs</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Stack trace:</p><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>

<p><a href="index.php">View Site</a> | <a href="simple-db-admin.php?action=browse&table=faqs">Browse FAQs</a></p>