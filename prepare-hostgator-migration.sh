#!/bin/bash
# HostGator Migration Preparation Script
# This script prepares your files for HostGator upload

echo "🚀 HostGator Migration Preparation"
echo "=================================="

# Create migration directory
MIGRATION_DIR="hostgator_migration_package"
echo "📁 Creating migration package directory..."
mkdir -p "$MIGRATION_DIR"

# Copy essential PHP files
echo "📄 Copying PHP files..."
cp index.php "$MIGRATION_DIR/"
cp category.php "$MIGRATION_DIR/"
cp faq.php "$MIGRATION_DIR/"
cp search.php "$MIGRATION_DIR/"
cp feedback.php "$MIGRATION_DIR/"
cp feedback-dashboard.php "$MIGRATION_DIR/"
cp about.php "$MIGRATION_DIR/"
cp under-construction.php "$MIGRATION_DIR/"
cp under-construction.html "$MIGRATION_DIR/"

# Copy directories
echo "📂 Copying directories..."
cp -r config "$MIGRATION_DIR/"
cp -r includes "$MIGRATION_DIR/"
cp -r admin "$MIGRATION_DIR/"
cp -r api "$MIGRATION_DIR/"

# Copy configuration files
echo "⚙️ Copying configuration files..."
cp .htaccess "$MIGRATION_DIR/"

# Copy database backup
echo "🗄️ Copying database backup..."
cp submarine_faqs_backup.sql "$MIGRATION_DIR/"

# Create HostGator-specific database config template
echo "🔧 Creating HostGator database config template..."
cat > "$MIGRATION_DIR/config/database-hostgator-template.php" << 'EOF'
<?php
// HostGator Database configuration
// UPDATE THESE VALUES WITH YOUR HOSTGATOR DETAILS

define('DB_HOST', 'localhost');  // Usually localhost on HostGator
define('DB_USERNAME', 'YOUR_HOSTGATOR_USERNAME_dbuser');  // Replace with your HostGator DB user
define('DB_PASSWORD', 'YOUR_HOSTGATOR_DB_PASSWORD');     // Replace with your HostGator DB password
define('DB_NAME', 'YOUR_HOSTGATOR_USERNAME_submarine_faqs'); // Replace with your HostGator DB name
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_NAME', 'Diesel-Electric Submarine FAQs');

// Error reporting for production (disable debugging)
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Database connection with error handling
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database connection error: " . $e->getMessage());
    die("Sorry, we're experiencing technical difficulties. Please try again later.");
}

// Helper function for input sanitization
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Helper function for date formatting
function format_date($date) {
    return date('F j, Y', strtotime($date));
}
?>
EOF

# Create migration instructions file
echo "📋 Creating migration instructions..."
cat > "$MIGRATION_DIR/UPLOAD_INSTRUCTIONS.txt" << 'EOF'
HOSTGATOR UPLOAD INSTRUCTIONS
=============================

1. UPLOAD FILES TO HOSTGATOR:
   - Upload ALL files in this directory to your HostGator public_html folder
   - You can use File Manager in cPanel or FTP client like FileZilla

2. SETUP DATABASE:
   - Create MySQL database in HostGator cPanel
   - Import submarine_faqs_backup.sql through phpMyAdmin
   - Note your database name, username, and password

3. UPDATE DATABASE CONFIG:
   - Rename database-hostgator-template.php to database.php
   - Edit database.php with your HostGator database details
   - Replace YOUR_HOSTGATOR_USERNAME with your actual username

4. TEST YOUR SITE:
   - Visit your domain to test the homepage
   - Check category pages, search, and feedback functionality
   - Verify admin access works

5. ENABLE CONSTRUCTION MODE (OPTIONAL):
   - If you want to show "under construction" while testing:
   - Edit .htaccess and uncomment the construction redirect lines
   - Or upload under-construction.html as your main index.html

HOSTGATOR SUPPORT:
- 24/7 phone and chat support available
- Free migration service available on most plans
- Contact them if you need help with any step

DATABASE INFO YOU'LL NEED:
- Database Name: (will be prefixed with your username)
- Database User: (will be prefixed with your username)  
- Database Password: (you'll set this in cPanel)
- Database Host: localhost (standard for HostGator)

YOUR SITE FEATURES:
- 183 FAQs organized in 6 categories
- Advanced search functionality
- Community feedback system
- Admin dashboard for managing content
- Mobile-responsive design
- Under construction mode available

Good luck with your migration! 🚀
EOF

# Create zip file for easy upload
echo "📦 Creating zip file for upload..."
zip -r "submarine_faqs_hostgator.zip" "$MIGRATION_DIR/"

# Summary
echo ""
echo "✅ Migration package ready!"
echo "📁 Files copied to: $MIGRATION_DIR/"
echo "📦 Zip file created: submarine_faqs_hostgator.zip"
echo "🗄️ Database backup: submarine_faqs_backup.sql (336KB)"
echo ""
echo "NEXT STEPS:"
echo "1. Upload submarine_faqs_hostgator.zip to HostGator"
echo "2. Extract in public_html folder"  
echo "3. Create MySQL database and import submarine_faqs_backup.sql"
echo "4. Update config/database.php with HostGator details"
echo "5. Test your site!"
echo ""
echo "📖 Read HOSTGATOR-MIGRATION-GUIDE.md for detailed instructions"
echo "📋 Check $MIGRATION_DIR/UPLOAD_INSTRUCTIONS.txt for quick setup"