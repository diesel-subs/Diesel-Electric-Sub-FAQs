#!/bin/bash
# Railway Deployment Setup Script
# Prepares your submarine FAQ site for Railway deployment

echo "🚂 Railway Deployment Setup"
echo "=========================="

# Check if we're in a git repository
if [ ! -d ".git" ]; then
    echo "📁 Initializing Git repository..."
    git init
    echo "node_modules/" > .gitignore
    echo ".env" >> .gitignore
    echo "*.log" >> .gitignore
fi

# Create Railway configuration
echo "⚙️ Creating Railway configuration files..."

# Create railway.json
cat > railway.json << 'EOF'
{
  "build": {
    "builder": "NIXPACKS"
  },
  "deploy": {
    "startCommand": "php -S 0.0.0.0:$PORT -t ."
  }
}
EOF

# Create Procfile for web process
cat > Procfile << 'EOF'
web: php -S 0.0.0.0:$PORT -t .
EOF

# Create Railway-compatible database config
echo "🗄️ Creating Railway database configuration..."
cat > config/database-railway.php << 'EOF'
<?php
// Railway Database Configuration
// Environment variables are automatically set by Railway

$railway_host = $_ENV['MYSQLHOST'] ?? 'localhost';
$railway_port = $_ENV['MYSQLPORT'] ?? '3306'; 
$railway_user = $_ENV['MYSQLUSER'] ?? 'submarine_user';
$railway_pass = $_ENV['MYSQLPASSWORD'] ?? 'submarine2024!';
$railway_db = $_ENV['MYSQLDATABASE'] ?? 'submarine_faqs';

define('DB_HOST', $railway_host . ':' . $railway_port);
define('DB_USERNAME', $railway_user);
define('DB_PASSWORD', $railway_pass);
define('DB_NAME', $railway_db);
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_NAME', 'Diesel-Electric Submarine FAQs');

// Production settings
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Sorry, we're experiencing technical difficulties. Please try again later.");
}

// Helper functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function format_date($date) {
    return date('F j, Y', strtotime($date));
}
?>
EOF

# Create deployment script
echo "🚀 Creating deployment instructions..."
cat > RAILWAY_DEPLOY.md << 'EOF'
# Deploy to Railway - Quick Start

## 1. Commit Your Code
```bash
git add .
git commit -m "Prepare for Railway deployment"
```

## 2. Push to GitHub
```bash
# Create GitHub repository first, then:
git remote add origin https://github.com/yourusername/submarine-faqs.git
git push -u origin main
```

## 3. Deploy on Railway
1. Go to https://railway.app
2. Sign up with GitHub
3. Click "New Project" → "Deploy from GitHub repo"
4. Select your submarine-faqs repository
5. Railway automatically detects PHP and starts building!

## 4. Add MySQL Database
1. In Railway dashboard, click "Add Service"
2. Select "MySQL" 
3. Railway automatically connects it to your app

## 5. Import Database
1. Click on MySQL service → "Connect"
2. Use provided connection details to connect via MySQL client
3. Import your submarine_faqs_backup.sql file

## 6. Switch to Railway Database Config
```bash
# Backup current config
cp config/database.php config/database-local.php

# Use Railway config  
cp config/database-railway.php config/database.php

# Commit and push
git add .
git commit -m "Use Railway database config"
git push origin main
```

## 7. Your Site is Live! 🎉
Railway provides a URL like: `https://submarine-faqs-production.up.railway.app`

## Development Workflow
```bash
# Make changes locally
vim index.php

# Test locally (optional)
php -S localhost:8082

# Deploy to Railway
git add .
git commit -m "Updated homepage"
git push origin main
# → Railway automatically deploys in ~30 seconds!
```

## Environment Setup for Local Development
```bash
# Create .env file for local development
echo "MYSQLHOST=localhost" > .env
echo "MYSQLPORT=3306" >> .env  
echo "MYSQLUSER=submarine_user" >> .env
echo "MYSQLPASSWORD=submarine2024!" >> .env
echo "MYSQLDATABASE=submarine_faqs" >> .env
```

## Benefits You Get
✅ **Push to Deploy**: No more manual file uploads
✅ **Automatic Backups**: Database backed up automatically  
✅ **SSL Certificate**: HTTPS enabled automatically
✅ **Rollback**: Easy to revert if something breaks
✅ **Monitoring**: See logs and metrics in dashboard
✅ **Custom Domain**: Easy to add your own domain

## Cost
- **Development**: Free for small projects
- **Production**: ~$5/month (much cheaper than traditional hosting)
- **Database**: Included in the price
- **SSL**: Included
- **Backups**: Included

Your submarine FAQ site will be much easier to maintain! 🚂
EOF

# Create GitHub setup helper
echo "📚 Creating GitHub repository setup..."
cat > setup-github.sh << 'EOF'
#!/bin/bash
echo "🐙 GitHub Repository Setup"
echo "========================="
echo ""
echo "1. Go to https://github.com/new"
echo "2. Repository name: submarine-faqs (or your preferred name)"
echo "3. Description: Comprehensive diesel-electric submarine FAQ collection"
echo "4. Make it Public (or Private if you prefer)"
echo "5. Don't initialize with README (we already have files)"
echo "6. Click 'Create repository'"
echo ""
echo "Then run these commands:"
echo ""
echo "git remote add origin https://github.com/YOURUSERNAME/submarine-faqs.git"
echo "git branch -M main"  
echo "git push -u origin main"
echo ""
echo "Replace YOURUSERNAME with your actual GitHub username"
EOF

chmod +x setup-github.sh

# Update .gitignore
echo "📝 Updating .gitignore..."
cat >> .gitignore << 'EOF'

# Railway
.railway/

# Local development
.env
*.log
vendor/

# Database backups (don't commit large SQL files)
*.sql

# IDE files
.vscode/
.idea/

# OS files
.DS_Store
Thumbs.db
EOF

# Summary
echo ""
echo "✅ Railway setup complete!"
echo ""
echo "📁 Files created:"
echo "   - railway.json (Railway configuration)"
echo "   - Procfile (Process definition)"
echo "   - config/database-railway.php (Railway database config)"
echo "   - RAILWAY_DEPLOY.md (Deployment instructions)"
echo "   - setup-github.sh (GitHub setup helper)"
echo ""
echo "🚀 Next steps:"
echo "1. Create GitHub repository (run ./setup-github.sh for instructions)"
echo "2. Push your code to GitHub" 
echo "3. Deploy on Railway (https://railway.app)"
echo "4. Add MySQL service in Railway dashboard"
echo "5. Import your database"
echo ""
echo "⚡ After setup, deploying is just:"
echo "   git add . && git commit -m 'changes' && git push"
echo ""
echo "💰 Cost: ~$5/month (vs $10+ for traditional hosting)"
echo "🎯 Deploy time: ~30 seconds (vs hours with FTP uploads)"