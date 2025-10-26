#!/usr/bin/env php
<?php
/**
 * Local development server for testing the create-faq API
 * Run this script to start a simple PHP server on localhost:8080
 */

$port = 8080;
$host = '127.0.0.1';
$documentRoot = __DIR__;

echo "Starting PHP development server...\n";
echo "Server running at http://$host:$port\n";
echo "API endpoint: http://$host:$port/create-faq.php\n";
echo "Press Ctrl+C to stop the server\n\n";

// Start the built-in PHP server
$command = "php -S $host:$port -t $documentRoot";
passthru($command);
?>