<?php
// Fix existing FAQ titles with markdown formatting
require_once 'config/database.php';

echo "Fixing FAQ titles with markdown formatting...\n";

function improvedStripMarkdown($text) {
    // Remove markdown formatting
    $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text); // Bold **text**
    $text = preg_replace('/\*(.*?)\*/', '$1', $text); // Italic *text*
    $text = preg_replace('/#{1,6}\s*/', '', $text); // Headers ### 
    $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $text); // Links [text](url)
    $text = preg_replace('/\[([^\]]+)\]\{[^}]+\}/', '$1', $text); // [text]{.class}
    $text = preg_replace('/`([^`]+)`/', '$1', $text); // Inline code `text`
    $text = preg_replace('/^>\s*/', '', $text); // Blockquotes > text
    $text = preg_replace('/^\s*[-*+]\s+/', '', $text); // List items - item
    $text = preg_replace('/\{\.underline\}/', '', $text); // Remove {.underline}
    return trim($text);
}

// Get all FAQs with problematic titles
$stmt = $pdo->query("SELECT id, title, question FROM faqs WHERE title LIKE '%{%' OR title LIKE '%]%' OR title LIKE '%[%'");
$faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($faqs) . " FAQs with markdown formatting in titles\n";

$updated = 0;
foreach ($faqs as $faq) {
    $oldTitle = $faq['title'];
    $newTitle = improvedStripMarkdown($oldTitle);
    
    if ($oldTitle !== $newTitle) {
        echo "Updating: '$oldTitle' -> '$newTitle'\n";
        
        // Create new slug
        $slug = strtolower($newTitle);
        $slug = preg_replace('/[^\w\s-]/', '', $slug);
        $slug = preg_replace('/[\s_-]+/', '-', $slug);
        $slug = trim($slug, '-');
        $slug = substr($slug, 0, 50);
        
        // Update the database
        $updateStmt = $pdo->prepare("UPDATE faqs SET title = ?, slug = ? WHERE id = ?");
        $updateStmt->execute([$newTitle, $slug, $faq['id']]);
        
        $updated++;
    }
}

echo "\nUpdated $updated FAQ titles\n";

// Also fix questions that might have markdown
echo "\nChecking questions for markdown formatting...\n";

$stmt = $pdo->query("SELECT id, question FROM faqs WHERE question LIKE '%{%' OR question LIKE '%]%' OR question LIKE '%[%'");
$faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($faqs) . " FAQs with markdown formatting in questions\n";

$updatedQuestions = 0;
foreach ($faqs as $faq) {
    $oldQuestion = $faq['question'];
    $newQuestion = improvedStripMarkdown($oldQuestion);
    
    if ($oldQuestion !== $newQuestion) {
        echo "Updating question: '" . substr($oldQuestion, 0, 50) . "...' -> '" . substr($newQuestion, 0, 50) . "...'\n";
        
        // Update the database
        $updateStmt = $pdo->prepare("UPDATE faqs SET question = ? WHERE id = ?");
        $updateStmt->execute([$newQuestion, $faq['id']]);
        
        $updatedQuestions++;
    }
}

echo "\nUpdated $updatedQuestions FAQ questions\n";

echo "\n=== CLEANUP COMPLETE ===\n";

// Show sample of cleaned titles
$stmt = $pdo->query("SELECT title FROM faqs f JOIN categories c ON f.category_id = c.id WHERE c.name = \"Attacks and Battles, Small and Large\" LIMIT 10");
echo "\nSample cleaned titles from 'Attacks and Battles, Small and Large':\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- " . $row['title'] . "\n";
}
?>