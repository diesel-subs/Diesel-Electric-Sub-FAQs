<?php
// Test Markdown rendering in your submarine FAQ app

require_once 'config/database.php';
require_once 'includes/markdown-helper.php';

echo "<h1>Markdown Test for Submarine FAQ App</h1>";

// Test content examples
$test_contents = [
    'plain_text' => "This is plain text.\nIt has line breaks.\n\nAnd paragraphs.",
    
    'markdown_basic' => "# Submarine Operations\n\nThis is a **bold** statement about *submarine* operations.\n\n- First point\n- Second point\n\n1. Numbered item\n2. Another item",
    
    'markdown_advanced' => "## Advanced Submarine Systems\n\n### Propulsion\n\nThe **diesel-electric** system works as follows:\n\n```\nDiesel Engine → Generator → Electric Motor → Propeller\n```\n\n> **Important**: Submarines could only use diesel engines on the surface.\n\n| System | Surface | Submerged |\n|--------|---------|----------|\n| Diesel | ✅ Active | ❌ Off |\n| Electric | ⚡ Charging | ✅ Active |\n\nFor more details, see [submarine operations](/category.php?cat=Operating%20US%20Subs%20in%20WW2).",
    
    'mixed_content' => "**Question**: How deep could submarines dive?\n\n*Answer*: Most US WW2 submarines had a test depth of around 300 feet, with a maximum operating depth of 400 feet.\n\nKey points:\n- Test depth: 300 feet\n- Emergency depth: 400+ feet  \n- Crush depth: ~500+ feet\n\n`Note: Depths varied by submarine class.`"
];

foreach ($test_contents as $type => $content) {
    echo "<div style='border: 1px solid #ccc; margin: 20px 0; padding: 15px;'>";
    echo "<h3>Test: " . ucfirst(str_replace('_', ' ', $type)) . "</h3>";
    
    echo "<h4>Raw Content:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px;'>" . htmlspecialchars($content) . "</pre>";
    
    echo "<h4>Rendered Output:</h4>";
    echo "<div style='border: 1px solid #eee; padding: 10px; background: white;'>";
    echo render_content($content);
    echo "</div>";
    
    echo "<h4>Detection Result:</h4>";
    echo "<p><strong>Is Markdown:</strong> " . (is_markdown_content($content) ? "Yes" : "No") . "</p>";
    
    echo "</div>";
}

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
pre { overflow-x: auto; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
blockquote { border-left: 4px solid #ddd; margin: 0; padding-left: 16px; }
</style>";
?>