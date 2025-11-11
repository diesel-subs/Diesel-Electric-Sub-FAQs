<?php
// Direct phpMyAdmin access with proper environment setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set proper working directory
chdir(__DIR__ . '/phpmyadmin');

// Add current directory to include path
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/phpmyadmin');

// Don't start session here - let phpMyAdmin handle its own sessions

// Check if phpMyAdmin files exist
if (!file_exists(__DIR__ . '/phpmyadmin/index.php')) {
    die('phpMyAdmin files not found. Please ensure phpMyAdmin is properly installed.');
}

// Include phpMyAdmin
try {
    include __DIR__ . '/phpmyadmin/index.php';
} catch (Exception $e) {
    echo '<div style="padding: 20px; font-family: Arial, sans-serif;">';
    echo '<h2 style="color: #d32f2f;">phpMyAdmin Error</h2>';
    echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<hr>';
    echo '<h3>Alternative Database Access:</h3>';
    echo '<ul>';
    echo '<li><a href="simple-db-admin.php">Simple Database Manager</a> - Built-in web interface</li>';
    echo '<li><a href="test-db-connection.php">Database Connection Test</a> - Check connectivity</li>';
    echo '</ul>';
    echo '<h3>Troubleshooting:</h3>';
    echo '<ol>';
    echo '<li>Check that MySQL is running</li>';
    echo '<li>Verify database credentials in config/database.php</li>';
    echo '<li>Ensure phpMyAdmin tmp directory has write permissions</li>';
    echo '</ol>';
    echo '</div>';
}
?>