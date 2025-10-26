#!/usr/bin/env php
<?php
/**
 * Local development server for testing FAQ APIs
 * Run this script to start a simple PHP server on localhost:8080
 */

$port = 8080;
$host = '127.0.0.1';
$documentRoot = dirname(__DIR__);  // Go up one level to project root

echo "Starting PHP development server...\n";
echo "Server running at http://$host:$port\n";
echo "API endpoints:\n";
echo "  - http://$host:$port/api/create-faq.php\n";
echo "  - http://$host:$port/api/list-faqs.php\n";
echo "  - http://$host:$port/api/update-faq.php\n";
echo "Press Ctrl+C to stop the server\n\n";

// Start the built-in PHP server with router
$router = __DIR__ . '/router.php';
$command = "php -S $host:$port -t $documentRoot $router";
passthru($command);
?>