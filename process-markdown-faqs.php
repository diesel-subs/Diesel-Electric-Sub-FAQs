<?php
require_once 'config/database.php';

function createSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^\w\s-]/', '', $text);
    $text = preg_replace('/[\s_-]+/', '-', $text);
    $text = trim($text, '-');
    return substr($text, 0, 50);
}

function extractTags($question, $answer) {
    $tags = [];
    $text = $question . ' ' . $answer;
    
    // Common submarine terms
    $terms = [
        'torpedo', 'engine', 'battery', 'compartment', 'hull', 'periscope',
        'crew', 'captain', 'officer', 'depth', 'submarine', 'boat', 'USS',
        'patrol', 'attack', 'battle', 'war', 'WWII', 'Japanese', 'navigation',
        'escape', 'diving', 'surface', 'bunks', 'galley', 'maneuvering'
    ];
    
    foreach ($terms as $term) {
        if (stripos($text, $term) !== false) {
            $tags[] = $term;
        }
    }
    
    return array_slice(array_unique($tags), 0, 5); // Max 5 tags
}

function createShortAnswer($answer) {
    // Remove markdown formatting
    $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $answer);
    $text = preg_replace('/\*(.*?)\*/', '$1', $text);
    $text = preg_replace('/>\s*(.*)/', '$1', $text);
    $text = preg_replace('/\n+/', ' ', $text);
    
    // Split into sentences
    $sentences = preg_split('/(?<=[.!?])\s+/', trim($text));
    
    // Take first 1-3 sentences, max 300 characters
    $short = '';
    $count = 0;
    foreach ($sentences as $sentence) {
        if ($count >= 3) break;
        if (strlen($short . $sentence) > 300) break;
        $short .= ($short ? ' ' : '') . trim($sentence);
        $count++;
    }
    
    return $short ?: substr(trim($text), 0, 300);
}

function processMarkdownFile($filePath) {
    echo "<h2>Processing: " . basename($filePath) . "</h2>";
    
    $content = file_get_contents($filePath);
    if (!$content) {
        echo "<p style='color: red;'>Could not read file: $filePath</p>";
        return [];
    }
    
    // Extract category from first line
    $lines = explode("\n", $content);
    $category = trim($lines[0]);
    if (empty($category)) {
        $category = "General";
    }
    
    echo "<p>Category: <strong>$category</strong></p>";
    
    // Find all questions (lines starting with **)
    $faqs = [];
    $currentQuestion = '';
    $currentAnswer = '';
    $inAnswer = false;
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Check if this is a question (starts with **)
        if (preg_match('/^\*\*(.*?)\*\*$/', $line, $matches)) {
            // Save previous FAQ if exists
            if ($currentQuestion && $currentAnswer) {
                $faqs[] = [
                    'category' => $category,
                    'question' => substr(trim($currentQuestion), 0, 252),
                    'answer' => trim($currentAnswer)
                ];
            }
            
            // Start new question
            $currentQuestion = trim($matches[1]);
            $currentAnswer = '';
            $inAnswer = true;
        } else if ($inAnswer && !empty($line)) {
            // Continue building answer
            $currentAnswer .= $line . "\n";
        }
    }
    
    // Don't forget the last FAQ
    if ($currentQuestion && $currentAnswer) {
        $faqs[] = [
            'category' => $category,
            'question' => substr(trim($currentQuestion), 0, 252),
            'answer' => trim($currentAnswer)
        ];
    }
    
    echo "<p>Found " . count($faqs) . " FAQs</p>";
    return $faqs;
}

// File paths (adjust these to match your actual file locations)
$files = [
    '/Users/irving/Desktop/_dwight/05-Hull-and-Compartments.md',
    '/Users/irving/Desktop/_dwight/08-US-WW2-Subs-in-General.md',
    '/Users/irving/Desktop/_dwight/10-Operating-US-WW2-Subs.md',
    '/Users/irving/Desktop/_dwight/12-Crews-Aboard-US-WW2-Subs.md',
    '/Users/irving/Desktop/_dwight/15-Life-Aboard-US-WW2-Subs.md',
    '/Users/irving/Desktop/_dwight/20-Attacks-and-Battles-Small-and-Large.md'
];

try {
    echo "<h1>Processing Markdown Files</h1>";
    
    $allFaqs = [];
    $categories = [];
    
    // Process each file
    foreach ($files as $file) {
        if (file_exists($file)) {
            $faqs = processMarkdownFile($file);
            $allFaqs = array_merge($allFaqs, $faqs);
            
            // Collect unique categories
            foreach ($faqs as $faq) {
                $categories[$faq['category']] = $faq['category'];
            }
        } else {
            echo "<p style='color: orange;'>File not found: $file</p>";
        }
    }
    
    echo "<h2>Summary</h2>";
    echo "<p>Total FAQs: " . count($allFaqs) . "</p>";
    echo "<p>Categories: " . implode(', ', array_keys($categories)) . "</p>";
    
    // Insert categories first
    echo "<h2>Inserting Categories</h2>";
    foreach ($categories as $categoryName) {
        $slug = createSlug($categoryName);
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->execute([$categoryName, $slug, "Questions about " . $categoryName]);
        echo "<p>✓ Inserted category: $categoryName (slug: $slug)</p>";
    }
    
    // Get category IDs
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
        
        $insertCount++;
        
        if ($insertCount % 10 == 0) {
            echo "<p>Inserted $insertCount FAQs...</p>";
        }
    }
    
    echo "<p style='color: green; font-weight: bold;'>✓ Successfully inserted $insertCount FAQs!</p>";
    
    // Show final stats
    echo "<h2>Final Database Stats</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $catCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM faqs");
    $faqCount = $stmt->fetch()['count'];
    
    echo "<p>Categories in database: $catCount</p>";
    echo "<p>FAQs in database: $faqCount</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<p><a href="index.php">View Updated Site</a> | <a href="simple-db-admin.php">Database Admin</a></p>