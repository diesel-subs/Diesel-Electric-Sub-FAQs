<?php
require_once 'config/database.php';

echo "<h1>Database Diagnostic</h1>";

try {
    // Check current FAQ count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM faqs");
    $faqCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $catCount = $stmt->fetch()['count'];
    
    echo "<h2>Current Database Status</h2>";
    echo "<p>FAQs: $faqCount</p>";
    echo "<p>Categories: $catCount</p>";
    
    // Check if files exist
    echo "<h2>File Check</h2>";
    $files = [
        '05-Hull-and-Compartments.md',
        '08-US-WW2-Subs-in-General.md',
        '10-Operating-US-WW2-Subs.md',
        '12-Crews-Aboard-US-WW2-Subs.md',
        '15-Life-Aboard-US-WW2-Subs.md',
        '20-Attacks-and-Battles-Small-and-Large.md'
    ];
    
    foreach ($files as $file) {
        if (file_exists($file)) {
            $size = filesize($file);
            echo "<p>✓ $file ($size bytes)</p>";
        } else {
            echo "<p>✗ $file (missing)</p>";
        }
    }
    
    // Test reading one file
    echo "<h2>File Content Test</h2>";
    $testFile = '05-Hull-and-Compartments.md';
    if (file_exists($testFile)) {
        $content = file_get_contents($testFile);
        $lines = explode("\n", $content);
        echo "<p>First line: " . htmlspecialchars($lines[0]) . "</p>";
        echo "<p>Total lines: " . count($lines) . "</p>";
        
        // Look for questions
        $questionCount = 0;
        foreach ($lines as $line) {
            if (preg_match('/^\*\*(.*?)\*\*\s*$/', trim($line))) {
                $questionCount++;
            }
        }
        echo "<p>Questions found: $questionCount</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<p><a href="improved-faq-processor.php">Run Processor Again</a></p>