<?php
require_once 'config/database.php';

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
        'patrol', 'attack', 'battle', 'war', 'WWII', 'WW2', 'Japanese',
        'navigation', 'escape', 'diving', 'surface', 'bunks', 'galley'
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

echo "<h1>Complete FAQ Import</h1>";

try {
    // Clear database
    echo "<p>Clearing database...</p>";
    $pdo->exec("DELETE FROM related_faqs");
    $pdo->exec("DELETE FROM faqs");
    $pdo->exec("DELETE FROM feedback");
    $pdo->exec("DELETE FROM categories");
    $pdo->exec("ALTER TABLE categories AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE faqs AUTO_INCREMENT = 1");
    echo "<p>✓ Database cleared</p>";
    
    // Process all files
    $files = [
        '05-Hull-and-Compartments.md',
        '08-US-WW2-Subs-in-General.md', 
        '10-Operating-US-WW2-Subs.md',
        '12-Crews-Aboard-US-WW2-Subs.md',
        '15-Life-Aboard-US-WW2-Subs.md',
        '20-Attacks-and-Battles-Small-and-Large.md'
    ];
    
    $totalInserted = 0;
    $categoryIds = [];
    
    foreach ($files as $file) {
        if (!file_exists($file)) {
            echo "<p style='color: orange;'>File not found: $file</p>";
            continue;
        }
        
        echo "<h2>Processing: $file</h2>";
        
        $content = file_get_contents($file);
        $lines = explode("\n", $content);
        
        // Get category
        $category = stripMarkdown(trim($lines[0]));
        echo "<p>Category: <strong>" . htmlspecialchars($category) . "</strong></p>";
        
        // Insert category if not exists
        if (!isset($categoryIds[$category])) {
            $slug = createSlug($category);
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
            $stmt->execute([$category, $slug, "Questions about " . $category]);
            $categoryIds[$category] = $pdo->lastInsertId();
            echo "<p>✓ Category created (ID: {$categoryIds[$category]})</p>";
        }
        
        $categoryId = $categoryIds[$category];
        
        // Parse FAQs
        $faqs = [];
        $currentQuestion = '';
        $currentAnswer = '';
        $inQuestion = false;
        
        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            
            if ($lineNum === 0) continue;
            
            if (preg_match('/^\*\*(.*?)\*\*\s*$/', $line, $matches)) {
                // Save previous
                if ($currentQuestion && $currentAnswer) {
                    $question = stripMarkdown($currentQuestion);
                    $question = substr($question, 0, 252);
                    
                    $faqs[] = [
                        'question' => $question,
                        'answer' => trim($currentAnswer)
                    ];
                }
                
                // Start new
                $currentQuestion = $matches[1];
                $currentAnswer = '';
                $inQuestion = true;
                continue;
            }
            
            if ($inQuestion && !empty($line)) {
                $currentAnswer .= $line . "\n";
            }
        }
        
        // Last FAQ
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
        foreach ($faqs as $faq) {
            $title = substr($faq['question'], 0, 100);
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
            
            $totalInserted++;
        }
        
        echo "<p>✓ Inserted " . count($faqs) . " FAQs from this file</p>";
    }
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h2 style='color: #155724; margin: 0 0 15px 0;'>✅ Import Complete!</h2>";
    echo "<p style='margin: 5px 0;'><strong>Total FAQs inserted:</strong> $totalInserted</p>";
    echo "<p style='margin: 5px 0;'><strong>Categories created:</strong> " . count($categoryIds) . "</p>";
    echo "</div>";
    
    // Final verification
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM faqs");
    $dbCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $catCount = $stmt->fetch()['count'];
    
    echo "<h3>Database Verification</h3>";
    echo "<p>FAQs in database: $dbCount</p>";
    echo "<p>Categories in database: $catCount</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<div style="margin-top: 30px; text-align: center;">
    <a href="index.php" style="background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 0 10px; display: inline-block;">🏠 View Website</a>
    <a href="simple-db-admin.php?action=browse&table=faqs" style="background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 0 10px; display: inline-block;">📊 Browse FAQs</a>
    <a href="simple-db-admin.php?action=browse&table=categories" style="background: #17a2b8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 0 10px; display: inline-block;">📁 Browse Categories</a>
</div>