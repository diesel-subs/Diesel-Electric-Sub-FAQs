#!/bin/bash

# start-api-server.sh - Start PHP development server for API endpoints

echo "Starting PHP API server on port 8080..."

cd "$(dirname "$0")/.."

# Start PHP development server
php -S localhost:8080 -t . api/server.php
