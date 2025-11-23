# Railway Deployment Guide

# Perfect for PHP + MySQL development with Git integration

## Why Railway is Perfect for Your Project

✅ **Git Integration**: Push code → Automatic deployment
✅ **MySQL Database**: Built-in database with automatic backups  
✅ **PHP 8.4 Support**: Matches your current setup exactly
✅ **Environment Variables**: Easy config management
✅ **Custom Domains**: Free SSL certificates
✅ **Affordable**: $5/month for hobby projects
✅ **Easy Database Management**: Built-in phpMyAdmin alternative

## Setup Instructions

### 1. Prepare Your Project for Railway

Create `railway.json`:

```json
{
  "build": {
    "builder": "NIXPACKS"
  },
  "deploy": {
    "startCommand": "php -S 0.0.0.0:$PORT -t ."
  }
}
```

Create `Procfile`:

```
web: php -S 0.0.0.0:$PORT -t .
```

Update `config/database.php`:

```php
<?php
// Railway Database Configuration
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
define('SITE_NAME', 'Diesel-Electric Submarine FAQs');

// Database connection
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
    die("Database connection failed");
}

function sanitize_input($data) {
    return htmlspecialchars(trim(stripslashes($data)));
}

function format_date($date) {
    return date('F j, Y', strtotime($date));
}
?>
```

### 2. Deploy to Railway

1. **Push to GitHub:**

   ```bash
   git add .
   git commit -m "Prepare for Railway deployment"
   git push origin main
   ```

2. **Connect to Railway:**
   - Go to railway.app
   - Sign up with GitHub
   - Create new project from GitHub repo
   - Railway automatically detects PHP and deploys

3. **Add MySQL Database:**
   - In Railway dashboard, click "Add Service"
   - Select "MySQL"
   - Railway automatically sets environment variables

4. **Import Your Database:**
   - Railway provides database connection details
   - Use their web interface or connect via MySQL client
   - Import your submarine_faqs_backup.sql

### 3. Development Workflow

```bash
# Make changes locally
git add .
git commit -m "Update FAQ system"
git push origin main
# → Railway automatically deploys!
```

## Comparison: Railway vs Others

| Feature | Railway | Vercel | Netlify | HostGator |
|---------|---------|--------|---------|-----------|
| Git Integration | ✅ Auto | ✅ Auto | ✅ Auto | ❌ Manual |
| PHP Support | ✅ Native | ⚠️ Functions | ❌ No | ✅ Full |
| MySQL Database | ✅ Managed | ❌ No | ❌ No | ✅ Basic |
| Deploy Speed | ⚡ 30s | ⚡ 10s | ⚡ 15s | 🐌 Manual |
| Cost | $5/mo | Free | Free | $10/mo |
| Custom Domain | ✅ Free SSL | ✅ Free SSL | ✅ Free SSL | ✅ Paid SSL |
| Environment Variables | ✅ Easy | ✅ Easy | ✅ Easy | ❌ Manual |

## Why Railway Wins for Your Project

1. **Perfect PHP Match**: Native PHP 8.4 support
2. **Database Included**: MySQL with automatic backups
3. **Git Integration**: Push → Deploy automatically  
4. **Development Friendly**: Easy rollbacks, preview environments
5. **Affordable**: $5/month vs $10+ for traditional hosting
6. **Modern Workflow**: Made for developers, not just hosting

## Alternative: PlanetScale + Vercel

If you want to separate database from hosting:

1. **PlanetScale**: Managed MySQL with branches (like Git for databases)
2. **Vercel**: Host your PHP site
3. **GitHub**: Source code management

This gives you database branching - you can test changes without affecting production data!
