<?php
// Custom phpMyAdmin wrapper to fix display issues
ob_start();
include __DIR__ . '/phpmyadmin/index.php';
$content = ob_get_clean();

// Remove the problematic CSS that hides the page
$content = str_replace('<style id="cfs-style">html{display: none;}</style>', '', $content);

echo $content;
?>