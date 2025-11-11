<?php
require_once 'config/database.php';

function stripMarkdown($text) {
    // Remove bold/italic markdown
    $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
    $text = preg_replace('/\*(.*?)\*/', '$1', $text);
    // Remove other markdown elements
    $text = preg_replace('/#+\s*/', '', $text);
    $text = preg_replace('/>\s*/', '', $text);
    $text = preg_replace('/^\s*-\s*/', '', $text, -1, PREG_SPLIT_NO_EMPTY);
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
    
    // Common submarine terms
    $terms = [
        'torpedo', 'engine', 'battery', 'compartment', 'hull', 'periscope',
        'crew', 'captain', 'officer', 'depth', 'submarine', 'boat', 'USS',
        'patrol', 'attack', 'battle', 'war', 'WWII', 'WW2', 'Japanese', 'navigation',
        'escape', 'diving', 'surface', 'bunks', 'galley', 'maneuvering', 'conning',
        'pressure', 'ballast', 'diesel', 'electric', 'sonar', 'radar', 'watch'
    ];
    
    foreach ($terms as $term) {
        if (stripos($text, $term) !== false) {
            $tags[] = $term;
        }
    }
    
    return array_slice(array_unique($tags), 0, 5);
}

function createShortAnswer($answer) {
    // Clean up markdown formatting first
    $text = stripMarkdown($answer);
    
    // Remove line breaks and extra spaces
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    
    // Split into sentences (improved regex)
    $sentences = preg_split('/(?<=[.!?])\s+(?=[A-Z])/', $text);
    
    // Take first 1-3 sentences, max 300 characters
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

function processMarkdownFile($filePath) {
    echo "<h2>Processing: " . basename($filePath) . "</h2>";
    
    $content = file_get_contents($filePath);
    if (!$content) {
        echo "<p style='color: red;'>Could not read file: $filePath</p>";
        return [];
    }
    
    // Extract category from line 1 and strip markdown
    $lines = explode("\n", $content);
    $category = stripMarkdown(trim($lines[0]));
    if (empty($category)) {
        $category = "General";
    }
    
    echo "<p>Category: <strong>" . htmlspecialchars($category) . "</strong></p>";
    
    $faqs = [];
    $currentQuestion = '';
    $currentAnswer = '';
    $inQuestion = false;
    
    foreach ($lines as $lineNum => $line) {
        $line = trim($line);
        
        // Skip the category line (first line)
        if ($lineNum === 0) continue;
        
        // Check if this line starts a new question (starts with **)
        if (preg_match('/^\*\*(.*?)\*\*\s*$/', $line, $matches)) {
            // Save previous FAQ if exists
            if ($currentQuestion && $currentAnswer) {
                $question = stripMarkdown($currentQuestion);
                $question = substr($question, 0, 252); // Trim to 252 chars as requested
                
                $faqs[] = [
                    'category' => $category,
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
        
        // If we're in a question/answer block and this isn't empty
        if ($inQuestion && !empty($line)) {
            // If this line starts with **, it's a new question, so process it above
            if (strpos($line, '**') === 0) {
                continue; // This will be caught by the regex above
            }
            
            // Add to current answer
            $currentAnswer .= $line . "\n";
        }
    }
    
    // Don't forget the last FAQ
    if ($currentQuestion && $currentAnswer) {
        $question = stripMarkdown($currentQuestion);
        $question = substr($question, 0, 252);
        
        $faqs[] = [
            'category' => $category,
            'question' => $question,
            'answer' => trim($currentAnswer)
        ];
    }
    
    echo "<p>Found " . count($faqs) . " FAQs</p>";
    
    // Show first few questions for verification
    if (count($faqs) > 0) {
        echo "<h4>Sample Questions:</h4><ul>";
        for ($i = 0; $i < min(3, count($faqs)); $i++) {
            echo "<li>" . htmlspecialchars(substr($faqs[$i]['question'], 0, 100)) . "...</li>";
        }
        echo "</ul>";
    }
    
    return $faqs;
}

try {
    echo "<h1>Improved Markdown FAQ Processing</h1>";
    
    // Clear existing data first
    echo "<h2>Clearing existing data...</h2>";
    $pdo->exec("DELETE FROM related_faqs");
    $pdo->exec("DELETE FROM faqs");
    $pdo->exec("DELETE FROM feedback");
    $pdo->exec("DELETE FROM categories");
    $pdo->exec("ALTER TABLE categories AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE faqs AUTO_INCREMENT = 1");
    echo "<p>✓ Database cleared</p>";
    
    // Target markdown files
    $files = [
        '05-Hull-and-Compartments.md',
        '08-US-WW2-Subs-in-General.md',
        '10-Operating-US-WW2-Subs.md',
        '12-Crews-Aboard-US-WW2-Subs.md',
        '15-Life-Aboard-US-WW2-Subs.md',
        '20-Attacks-and-Battles-Small-and-Large.md'
    ];
    
    $allFaqs = [];
    $categories = [];
    
    // Process each file
    foreach ($files as $file) {
        $filePath = __DIR__ . '/' . $file;
        if (file_exists($filePath)) {
            $faqs = processMarkdownFile($filePath);
            $allFaqs = array_merge($allFaqs, $faqs);
            
            // Collect unique categories
            foreach ($faqs as $faq) {
                $categories[$faq['category']] = $faq['category'];
            }
        } else {
            echo "<p style='color: orange;'>File not found: $file</p>";
        }
    }
    
    echo "<h2>Processing Summary</h2>";
    echo "<p><strong>Total FAQs:</strong> " . count($allFaqs) . "</p>";
    echo "<p><strong>Categories:</strong> " . implode(', ', array_keys($categories)) . "</p>";
    
    // Insert categories first
    echo "<h2>Creating Categories</h2>";
    foreach ($categories as $categoryName) {
        $slug = createSlug($categoryName);
        $description = "Frequently asked questions about " . $categoryName . " for diesel-electric submarines in World War II.";
        
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->execute([$categoryName, $slug, $description]);
        echo "<p>✓ Category: <strong>" . htmlspecialchars($categoryName) . "</strong> (slug: $slug)</p>";
    }
    
    // Get category IDs for reference
    $categoryIds = [];
    $stmt = $pdo->query("SELECT id, name FROM categories");
    while ($row = $stmt->fetch()) {
        $categoryIds[$row['name']] = $row['id'];
    }
    
    // Insert FAQs
    echo "<h2>Inserting FAQs</h2>";
    $insertCount = 0;
    
    foreach ($allFaqs as $faq) {
        $categoryId = $categoryIds[$faq['category']] ?? 1;
        
        // Create title from question (first 100 chars)
        $title = substr($faq['question'], 0, 100);
        if (strlen($faq['question']) > 100) {
            $title .= '...';
        }
        
        $slug = createSlug($faq['question']);
        $shortAnswer = createShortAnswer($faq['answer']);
        $tags = implode(',', extractTags($faq['question'], $faq['answer']));
        
        $stmt = $pdo->prepare("
            INSERT INTO faqs (title, slug, question, content, short_answer, category_id, tags, is_published, view_count, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, 0, NOW(), NOW())
        ");
        
        $stmt->execute([
            $title,
            $slug,
            $faq['question'],
            $faq['answer'],
            $shortAnswer,
            $categoryId,
            $tags
        ]);
        
        $insertCount++;
        
        if ($insertCount % 5 == 0) {
            echo "<p>Inserted $insertCount FAQs...</p>";
        }
    }
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724; margin: 0 0 10px 0;'>✅ Processing Complete!</h3>";
    echo "<p style='margin: 5px 0;'><strong>FAQs inserted:</strong> $insertCount</p>";
    echo "<p style='margin: 5px 0;'><strong>Categories created:</strong> " . count($categories) . "</p>";
    echo "</div>";
    
    // Show final database stats
    echo "<h2>Final Database Statistics</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $catCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM faqs WHERE is_published = 1");
    $faqCount = $stmt->fetch()['count'];
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th style='padding: 8px; background: #f8f9fa;'>Metric</th><th style='padding: 8px; background: #f8f9fa;'>Count</th></tr>";
    echo "<tr><td style='padding: 8px;'>Categories in database</td><td style='padding: 8px; text-align: center;'>$catCount</td></tr>";
    echo "<tr><td style='padding: 8px;'>Published FAQs</td><td style='padding: 8px; text-align: center;'>$faqCount</td></tr>";
    echo "</table>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #721c24; margin: 0;'>❌ Error</h3>";
    echo "<p style='margin: 10px 0 0 0;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

<div style="margin-top: 30px; text-align: center;">
    <a href="index.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;">View Updated Site</a>
    <a href="simple-db-admin.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;">Database Admin</a>
    <a href="simple-db-admin.php?action=browse&table=faqs" style="background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;">Browse FAQs</a>
</div>