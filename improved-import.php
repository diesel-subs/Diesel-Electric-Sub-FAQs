<?php
// Improved FAQ Import Script with better parsing
require_once 'config/database.php';

echo "Starting Improved FAQ Import...\n";

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
    // Remove markdown formatting
    $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text); // Bold
    $text = preg_replace('/\*(.*?)\*/', '$1', $text); // Italic
    $text = preg_replace('/#{1,6}\s*/', '', $text); // Headers
    $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $text); // Links
    $text = preg_replace('/`([^`]+)`/', '$1', $text); // Inline code
    $text = preg_replace('/^>\s*/', '', $text); // Blockquotes
    $text = preg_replace('/^\s*[-*+]\s+/', '', $text); // List items
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

function createTitle($question) {
    $title = stripMarkdown($question);
    // Remove question markers if present
    $title = preg_replace('/^Q\d*[:\.]?\s*/', '', $title);
    return trim($title);
}

function createShortAnswer($answer) {
    $text = stripMarkdown($answer);
    $text = preg_replace('/\s+/', ' ', $text); // Normalize whitespace
    $text = trim($text);
    
    // Split into sentences
    $sentences = preg_split('/(?<=[.!?])\s+(?=[A-Z])/', $text);
    
    $short = '';
    $count = 0;
    foreach ($sentences as $sentence) {
        if ($count >= 3) break; // Maximum 3 sentences
        $sentence = trim($sentence);
        if (!empty($sentence)) {
            $short .= ($count > 0 ? ' ' : '') . $sentence;
            $count++;
        }
    }
    
    // If still too long, truncate at word boundary
    if (strlen($short) > 300) {
        $short = substr($short, 0, 297);
        $lastSpace = strrpos($short, ' ');
        if ($lastSpace !== false) {
            $short = substr($short, 0, $lastSpace) . '...';
        }
    }
    
    return $short;
}

function extractTags($question, $answer) {
    $tags = [];
    $text = stripMarkdown($question . ' ' . $answer);
    $text = strtolower($text);
    
    // Common submarine-related terms
    $terms = [
        'torpedo', 'torpedoes', 'engine', 'engines', 'battery', 'batteries',
        'compartment', 'compartments', 'hull', 'periscope', 'crew', 'crews',
        'captain', 'officer', 'officers', 'depth', 'submarine', 'submarines',
        'boat', 'boats', 'uss', 'patrol', 'patrols', 'attack', 'attacks',
        'battle', 'battles', 'war', 'wwii', 'ww2', 'japanese', 'japan',
        'navigation', 'escape', 'diving', 'surface', 'submerged', 'bunks',
        'galley', 'mess', 'diesel', 'electric', 'sonar', 'radar', 'bridge',
        'conning', 'tower', 'ballast', 'tank', 'tanks', 'valve', 'valves',
        'hatch', 'hatches', 'watertight', 'pressure', 'sea', 'ocean',
        'destroyer', 'destroyer', 'escort', 'convoy', 'fleet', 'navy'
    ];
    
    foreach ($terms as $term) {
        if (strpos($text, $term) !== false) {
            $tags[] = $term;
        }
    }
    
    return array_slice(array_unique($tags), 0, 8); // Max 8 tags
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
    
    // Extract category from first line (line 1)
    $firstLine = trim($lines[0]);
    $category = stripMarkdown($firstLine);
    // Remove any leading # symbols
    $category = preg_replace('/^#+\s*/', '', $category);
    
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
        
        // Look for questions - they start with ** and end with **
        if (preg_match('/^\*\*(.+?)\*\*\s*$/', $line, $matches)) {
            // Save previous Q&A if exists
            if (!empty($currentQ) && !empty($currentA)) {
                try {
                    // Process question - trim to 252 characters
                    $rawQuestion = trim($currentQ);
                    if (strlen($rawQuestion) > 252) {
                        $rawQuestion = substr($rawQuestion, 0, 249) . '...';
                    }
                    
                    // Create other fields
                    $title = createTitle($currentQ);
                    $slug = createSlug($title);
                    $answer = trim($currentA);
                    $shortAnswer = createShortAnswer($answer);
                    $tags = implode(',', extractTags($currentQ, $answer));
                    
                    $stmt = $pdo->prepare("INSERT INTO faqs (category_id, title, slug, question, content, answer, short_answer, tags, is_published, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 'published')");
                    $stmt->execute([$categoryId, $title, $slug, $rawQuestion, $answer, $answer, $shortAnswer, $tags]);
                    
                    $qaCount++;
                    $totalImported++;
                    echo "✅ Q&A $qaCount: " . substr($title, 0, 50) . "...\n";
                    echo "   Tags: $tags\n";
                    
                } catch (Exception $e) {
                    echo "❌ Q&A insert error: " . $e->getMessage() . "\n";
                }
            }
            
            // Start new question
            $currentQ = trim($matches[1]);
            $currentA = '';
            $inAnswer = false;
            
        } elseif (!empty($line) && !empty($currentQ) && !$inAnswer) {
            // First non-empty line after question starts the answer
            $currentA = $line;
            $inAnswer = true;
            
        } elseif ($inAnswer && !empty($currentQ)) {
            // Continue building the answer
            if (!empty($line)) {
                $currentA .= "\n" . $line;
            } else {
                $currentA .= "\n";
            }
        }
    }
    
    // Save final Q&A
    if (!empty($currentQ) && !empty($currentA)) {
        try {
            // Process question - trim to 252 characters
            $rawQuestion = trim($currentQ);
            if (strlen($rawQuestion) > 252) {
                $rawQuestion = substr($rawQuestion, 0, 249) . '...';
            }
            
            // Create other fields
            $title = createTitle($currentQ);
            $slug = createSlug($title);
            $answer = trim($currentA);
            $shortAnswer = createShortAnswer($answer);
            $tags = implode(',', extractTags($currentQ, $answer));
            
            $stmt = $pdo->prepare("INSERT INTO faqs (category_id, title, slug, question, content, answer, short_answer, tags, is_published, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 'published')");
            $stmt->execute([$categoryId, $title, $slug, $rawQuestion, $answer, $answer, $shortAnswer, $tags]);
            
            $qaCount++;
            $totalImported++;
            echo "✅ Final Q&A $qaCount: " . substr($title, 0, 50) . "...\n";
            echo "   Tags: $tags\n";
            
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
    
    // Show sample of imported data
    echo "\n=== SAMPLE DATA ===\n";
    $stmt = $pdo->query("SELECT c.name as category, f.title, LEFT(f.question, 60) as question_preview, f.tags FROM faqs f JOIN categories c ON f.category_id = c.id LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Category: " . $row['category'] . "\n";
        echo "Title: " . $row['title'] . "\n";
        echo "Question: " . $row['question_preview'] . "...\n";
        echo "Tags: " . $row['tags'] . "\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Final count error: " . $e->getMessage() . "\n";
}
?>