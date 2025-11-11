<?php
echo "🚀 PHP is working on Railway!\n";
echo "Environment: " . ($_ENV['RAILWAY_ENVIRONMENT'] ?? 'local') . "\n";
echo "Port: " . ($_ENV['PORT'] ?? 'not set') . "\n";
?>