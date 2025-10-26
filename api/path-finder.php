<?php
/**
 * path-finder.php - Helper script to discover server paths
 */

header('Content-Type: text/plain');

echo "=== SERVER PATH DISCOVERY ===\n\n";

echo "Current working directory: " . getcwd() . "\n";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "Server name: " . $_SERVER['SERVER_NAME'] . "\n";
echo "PHP version: " . phpversion() . "\n\n";

echo "=== SUGGESTED PATHS ===\n\n";

// Common web hosting patterns
$doc_root = $_SERVER['DOCUMENT_ROOT'];
$suggestions = [
    "Web root docs: $doc_root/docs",
    "Public HTML docs: $doc_root/public_html/docs", 
    "Home directory docs: " . dirname($doc_root) . "/docs",
    "One level up: " . dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . "/docs"
];

foreach ($suggestions as $suggestion) {
    echo $suggestion . "\n";
}

echo "\n=== DIRECTORY PERMISSIONS ===\n\n";

// Check if common paths exist and are writable
$paths_to_check = [
    $doc_root,
    $doc_root . '/docs',
    dirname($doc_root) . '/docs',
    getcwd() . '/docs'
];

foreach ($paths_to_check as $path) {
    if (is_dir($path)) {
        $writable = is_writable($path) ? 'WRITABLE' : 'NOT WRITABLE';
        echo "✓ $path exists - $writable\n";
    } else {
        echo "✗ $path does not exist\n";
    }
}

echo "\n=== GIT CHECK ===\n\n";

// Check if git is available
exec('which git 2>&1', $git_output, $git_return);
if ($git_return === 0) {
    echo "✓ Git is available at: " . implode("\n", $git_output) . "\n";
    
    // Check git version
    exec('git --version 2>&1', $version_output);
    echo "Git version: " . implode("\n", $version_output) . "\n";
} else {
    echo "✗ Git is not available or not in PATH\n";
}

echo "\n=== ENVIRONMENT INFO ===\n\n";
echo "User: " . get_current_user() . "\n";
echo "User ID: " . getmyuid() . "\n";
echo "Group ID: " . getmygid() . "\n";

// Show current directory contents
echo "\nCurrent directory contents:\n";
$files = scandir('.');
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        $type = is_dir($file) ? '[DIR]' : '[FILE]';
        echo "$type $file\n";
    }
}
?>