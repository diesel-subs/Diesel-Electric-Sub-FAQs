<?php
// render-markdown.php - AJAX endpoint for live preview
require_once 'includes/markdown-helper.php';

header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = $_POST['content'];
    echo render_content($content);
} else {
    echo '<p class="text-muted">No content provided</p>';
}
?>