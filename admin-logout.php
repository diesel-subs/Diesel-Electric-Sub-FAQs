<?php
require_once 'config/database.php';

// Clear admin session
unset($_SESSION['admin_logged_in']);
session_destroy();

// Redirect to home page
header('Location: index.php?logged_out=1');
exit;
?>