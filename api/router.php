<?php
/**
 * router.php - Simple router for API endpoints
 * Handles routing for the PHP development server
 */

// Add CORS headers for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove leading slash
$path = ltrim($path, '/');

// Route API requests
if (strpos($path, 'api/') === 0) {
    $api_file = $path;
    
    if (file_exists($api_file)) {
        include $api_file;
        return true;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'API endpoint not found: ' . $path]);
        return true;
    }
}

// For other requests, return false to let the server handle them normally
return false;

?>