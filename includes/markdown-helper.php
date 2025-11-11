<?php
// Markdown rendering helper for Submarine FAQ app
// Provides smart content rendering that handles both Markdown and plain text

// Include Parsedown library
require_once __DIR__ . '/Parsedown.php';

/**
 * Render content as Markdown with security features
 */
function render_markdown($text) {
    static $parsedown = null;
    
    if ($parsedown === null) {
        $parsedown = new Parsedown();
        
        // Security: escape HTML by default to prevent XSS
        $parsedown->setSafeMode(true);
        
        // Enable line breaks (GitHub-style)
        $parsedown->setBreaksEnabled(true);
    }
    
    return $parsedown->text($text);
}

/**
 * Render Markdown with HTML allowed (for trusted content)
 */
function render_markdown_unsafe($text) {
    static $parsedown = null;
    
    if ($parsedown === null) {
        $parsedown = new Parsedown();
        $parsedown->setSafeMode(false); // Allow HTML
        $parsedown->setBreaksEnabled(true);
    }
    
    return $parsedown->text($text);
}

/**
 * Detect if content contains Markdown syntax
 */
function is_markdown_content($text) {
    if (empty($text)) {
        return false;
    }
    
    // Common Markdown patterns
    $markdown_patterns = [
        '/^#{1,6}\s+.+$/m',      // Headers (# ## ###)
        '/\*\*[^*]+\*\*/',       // Bold (**text**)
        '/\*[^*\n]+\*/',         // Italic (*text*)
        '/^\s*[-*+]\s+.+$/m',    // Unordered lists (- * +)
        '/^\s*\d+\.\s+.+$/m',    // Ordered lists (1. 2. 3.)
        '/\[[^\]]+\]\([^)]+\)/', // Links [text](url)
        '/```[^`]*```/s',        // Code blocks
        '/`[^`\n]+`/',           // Inline code
        '/^\s*>\s+.+$/m',        // Blockquotes
        '/\|.*\|/',              // Tables
        '/^---+$/m',             // Horizontal rules
    ];
    
    foreach ($markdown_patterns as $pattern) {
        if (preg_match($pattern, $text)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Detect if content contains HTML tags
 */
function is_html_content($text) {
    if (empty($text)) {
        return false;
    }
    
    // Check for HTML tags
    return $text !== strip_tags($text);
}

/**
 * Sanitize HTML content to prevent XSS while preserving formatting
 */
function sanitize_html_content($html) {
    // Allowed HTML tags for FAQ content
    $allowed_tags = '<p><br><strong><b><em><i><u><strike><del><h1><h2><h3><h4><h5><h6><ul><ol><li><table><thead><tbody><tr><td><th><blockquote><pre><code><a><img><div><span><sub><sup>';
    
    // Strip disallowed tags
    $html = strip_tags($html, $allowed_tags);
    
    // Remove dangerous attributes
    $html = preg_replace('/on\w+="[^"]*"/i', '', $html); // Remove onclick, onload, etc.
    $html = preg_replace('/javascript:/i', '', $html);    // Remove javascript: URLs
    
    return $html;
}

/**
 * Smart content renderer - auto-detects HTML vs Markdown vs plain text
 */
function render_content($text) {
    if (empty($text)) {
        return '';
    }
    
    // Auto-detect content type
    if (is_html_content($text)) {
        // HTML content from WYSIWYG editor
        return sanitize_html_content($text);
    } elseif (is_markdown_content($text)) {
        // Markdown content 
        return render_markdown($text);
    } else {
        // Plain text - convert line breaks to HTML
        return nl2br(htmlspecialchars($text));
    }
}

/**
 * Render content with explicit type specification
 */
function render_content_typed($text, $type = 'auto') {
    if (empty($text)) {
        return '';
    }
    
    switch ($type) {
        case 'markdown':
            return render_markdown($text);
        case 'html':
            return render_markdown_unsafe($text);
        case 'text':
            return nl2br(htmlspecialchars($text));
        case 'auto':
        default:
            return render_content($text);
    }
}

/**
 * Convert plain text to Markdown (for migration)
 */
function text_to_markdown($text) {
    if (empty($text)) {
        return '';
    }
    
    // Basic conversions
    $markdown = $text;
    
    // Convert line breaks to Markdown line breaks
    $markdown = str_replace("\r\n", "\n", $markdown);
    $markdown = str_replace("\r", "\n", $markdown);
    
    // Convert double line breaks to paragraph breaks
    $markdown = preg_replace('/\n\n+/', "\n\n", $markdown);
    
    // Escape special Markdown characters if they appear to be literal
    $markdown = preg_replace('/([*_`#\[\](){}])/', '\\$1', $markdown);
    
    return $markdown;
}

/**
 * Get content preview (for search results, etc.)
 */
function get_content_preview($text, $length = 160) {
    if (empty($text)) {
        return '';
    }
    
    // Strip Markdown formatting for preview
    $preview = $text;
    
    // Remove Markdown syntax
    $preview = preg_replace('/#{1,6}\s+/', '', $preview);  // Headers
    $preview = preg_replace('/\*\*([^*]+)\*\*/', '$1', $preview);  // Bold
    $preview = preg_replace('/\*([^*\n]+)\*/', '$1', $preview);    // Italic
    $preview = preg_replace('/`([^`\n]+)`/', '$1', $preview);      // Code
    $preview = preg_replace('/\[[^\]]+\]\([^)]+\)/', '$1', $preview); // Links
    $preview = preg_replace('/^\s*[-*+]\s+/', '', $preview);       // Lists
    $preview = preg_replace('/^\s*\d+\.\s+/', '', $preview);       // Numbered lists
    
    // Clean up whitespace
    $preview = preg_replace('/\s+/', ' ', $preview);
    $preview = trim($preview);
    
    // Truncate to length
    if (strlen($preview) > $length) {
        $preview = substr($preview, 0, $length) . '...';
    }
    
    return htmlspecialchars($preview);
}
?>