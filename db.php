<?php
// Direct phpMyAdmin access
// This file provides clean access to phpMyAdmin without application conflicts

// Suppress any application-level includes
define('PMA_DIRECT_ACCESS', true);

// Change working directory to phpMyAdmin
chdir(__DIR__ . '/phpmyadmin');

// Set up proper environment variables
$_SERVER['SCRIPT_NAME'] = '/db.php';
$_SERVER['PHP_SELF'] = '/db.php';

// Include phpMyAdmin
require_once 'index.php';
?>