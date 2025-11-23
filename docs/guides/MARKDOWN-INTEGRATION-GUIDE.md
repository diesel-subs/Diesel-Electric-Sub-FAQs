# Adding Markdown Support to Submarine FAQ App

## Installation Options

### Option 1: Parsedown (Lightweight, Recommended)

```bash
# Download Parsedown (single file, no dependencies)
curl -o includes/Parsedown.php https://raw.githubusercontent.com/erusev/parsedown/master/Parsedown.php
```

### Option 2: Composer + PHP Markdown

```bash
# If you want to use Composer
composer require michelf/php-markdown
```

## Implementation

### 1. Add Markdown Helper Function

Create: `includes/markdown-helper.php`

```php
<?php
// Markdown rendering helper

// Include Parsedown
require_once __DIR__ . '/Parsedown.php';

function render_markdown($text) {
    static $parsedown = null;
    
    if ($parsedown === null) {
        $parsedown = new Parsedown();
        
        // Security: escape HTML by default
        $parsedown->setSafeMode(true);
        
        // Enable line breaks
        $parsedown->setBreaksEnabled(true);
    }
    
    return $parsedown->text($text);
}

function render_markdown_safe($text) {
    // For content that might contain HTML you want to preserve
    static $parsedown = null;
    
    if ($parsedown === null) {
        $parsedown = new Parsedown();
        $parsedown->setSafeMode(false); // Allow HTML
        $parsedown->setBreaksEnabled(true);
    }
    
    return $parsedown->text($text);
}

// Detect if content is Markdown vs plain text
function is_markdown_content($text) {
    // Simple detection based on Markdown patterns
    $markdown_patterns = [
        '/^#{1,6}\s/',          // Headers
        '/\*\*.*\*\*/',         // Bold
        '/\*.*\*/',             // Italic  
        '/^\s*[-*+]\s/',        // Lists
        '/^\s*\d+\.\s/',        // Numbered lists
        '/\[.*\]\(.*\)/',       // Links
        '/```/',                // Code blocks
        '/`.*`/',               // Inline code
    ];
    
    foreach ($markdown_patterns as $pattern) {
        if (preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE, 0)) {
            return true;
        }
    }
    
    return false;
}
?>
```

### 2. Create Smart Content Renderer

```php
<?php
// Smart content renderer that handles both Markdown and plain text

function render_content($text) {
    if (empty($text)) {
        return '';
    }
    
    // Auto-detect if content is Markdown
    if (is_markdown_content($text)) {
        return render_markdown($text);
    } else {
        // Fallback to current method for plain text
        return nl2br(htmlspecialchars($text));
    }
}
?>
```

## Usage Examples

### Before (Plain Text)

```php
<?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
```

### After (Markdown + Plain Text)

```php
<?php echo render_content($faq['answer']); ?>
```

## Markdown Examples Your App Will Support

### Headers

```markdown
# Main Header
## Sub Header  
### Section Header
```

### Formatting

```markdown
**Bold text**
*Italic text*
`Code snippets`
```

### Lists

```markdown
- Bullet point 1
- Bullet point 2

1. Numbered item 1
2. Numbered item 2
```

### Links

```markdown
[Visit our homepage](/)
[Search FAQs](/search.php)
```

### Code Blocks

```markdown
```php
echo "Hello submarine!";
```

```

### Tables:
```markdown
| Feature | Status |
|---------|--------|
| Search | ✅ Done |
| Feedback | ✅ Done |
```

## Database Considerations

### Option A: Mixed Content (Recommended)

- Keep existing plain text FAQs as-is
- Add new Markdown content
- Auto-detection handles both formats

### Option B: Markdown-Only

- Convert existing content to Markdown
- All new content uses Markdown
- More consistent but requires migration

### Option C: Content Type Field

- Add `content_type` field to FAQs table
- Explicitly mark content as 'markdown' or 'text'
- Most precise control

## Security Features

✅ **XSS Protection**: Parsedown safe mode enabled by default
✅ **HTML Escaping**: Automatic for plain text content  
✅ **Input Validation**: Existing validation still applies
✅ **Backward Compatibility**: Plain text FAQs still work

## Performance

- **Parsedown**: Very fast, single file
- **Caching**: Consider caching rendered HTML for high traffic
- **Memory**: Minimal impact on existing app
