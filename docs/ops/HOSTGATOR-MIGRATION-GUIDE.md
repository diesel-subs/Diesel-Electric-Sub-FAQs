# HostGator Migration Guide

# Complete step-by-step instructions for moving your Submarine FAQ site

## 🚀 HOSTGATOR MIGRATION CHECKLIST

### STEP 1: HOSTGATOR ACCOUNT SETUP

□ Purchase HostGator hosting plan (recommended: Business plan for MySQL support)
□ Set up your domain name (or subdomain for testing)
□ Access cPanel from HostGator dashboard
□ Note down your hosting credentials (FTP, cPanel login)

### STEP 2: DATABASE SETUP ON HOSTGATOR

**2.1 Create Database:**

- Login to cPanel
- Go to "MySQL Databases"
- Create new database: `submarine_faqs` (or similar)
- Create database user with full privileges
- Note: HostGator often prefixes with your username (e.g., `yourusername_submarine_faqs`)

**2.2 Import Database:**

- Go to "phpMyAdmin" in cPanel
- Select your new database
- Click "Import" tab
- Upload: `submarine_faqs_backup.sql` (336KB file already created)
- Click "Go" to import

### STEP 3: UPDATE CONFIGURATION FILES

**3.1 Edit database.php for HostGator:**

```php
// HostGator Database configuration
define('DB_HOST', 'localhost');  // Usually localhost on HostGator
define('DB_USERNAME', 'yourusername_dbuser');  // Your HostGator DB user
define('DB_PASSWORD', 'your_new_password');     // Your HostGator DB password  
define('DB_NAME', 'yourusername_submarine_faqs'); // Your HostGator DB name
define('DB_CHARSET', 'utf8mb4');

// Site configuration  
define('SITE_NAME', 'Diesel-Electric Submarine FAQs');
```

**3.2 Update .htaccess (if needed):**

- Most HostGator shared hosting works with your current .htaccess
- May need to adjust file paths for some configurations

### STEP 4: FILE UPLOAD TO HOSTGATOR

**4.1 Upload Methods (choose one):**

**Option A: File Manager (cPanel)**

- Login to cPanel
- Open "File Manager"
- Navigate to `public_html` folder
- Upload zip file of your project
- Extract files in public_html

**Option B: FTP Upload**

- Use FileZilla or similar FTP client
- Connect with HostGator FTP credentials
- Upload all files to `/public_html/` directory

**Option C: HostGator Site Migration Tool**

- HostGator offers free site migration service
- Contact support to migrate your site

### STEP 5: FILES TO UPLOAD

**Essential Files:**
□ index.php (homepage)
□ category.php (category pages)
□ faq.php (individual FAQ pages)  
□ search.php (search functionality)
□ feedback.php (feedback form)
□ feedback-dashboard.php (community dashboard)
□ about.php (about page)
□ .htaccess (URL rewriting rules)

**Directories to Upload:**
□ /config/ (database configuration)
□ /includes/ (header, footer, widgets)
□ /admin/ (admin dashboard)
□ /api/ (search, feedback APIs)
□ /assets/ (CSS, JS, images if any)

**Optional Files:**
□ under-construction.html/php (for maintenance)
□ construction-mode.sh (maintenance script)
□ pma.php (phpMyAdmin shortcut - may not work on shared hosting)

### STEP 6: TESTING AND VERIFICATION

**6.1 Basic Functionality Tests:**
□ Homepage loads correctly
□ Category pages display FAQs
□ Search functionality works
□ Individual FAQ pages open
□ Database connection successful

**6.2 Advanced Features Tests:**
□ Feedback submission works
□ Admin dashboard accessible
□ Feedback widgets function
□ Email notifications (if configured)

**6.3 Database Verification:**

- Check FAQ count: Should show 183 FAQs
- Verify categories are populated
- Test feedback system
- Confirm all tables imported correctly

### STEP 7: DNS AND DOMAIN SETUP

**7.1 Domain Configuration:**
□ Point domain to HostGator nameservers (if using external domain)
□ Set up SSL certificate (usually free with HostGator)
□ Configure any subdomains needed

**7.2 Email Setup (if needed):**
□ Create email accounts for feedback notifications
□ Update any email settings in your PHP code

### STEP 8: SECURITY AND OPTIMIZATION

**8.1 Security:**
□ Change all default passwords
□ Update admin login credentials
□ Review file permissions (usually 644 for files, 755 for directories)
□ Remove any development/testing files

**8.2 Performance:**
□ Enable caching if available
□ Optimize images if any
□ Test page load speeds
□ Configure error reporting for production

### STEP 9: LAUNCH PREPARATION

**9.1 Content Review:**
□ Remove "localhost" references
□ Update any hardcoded URLs
□ Test all internal links
□ Verify contact information

**9.2 Monitoring Setup:**
□ Set up Google Analytics (if desired)
□ Configure error logging
□ Test backup procedures
□ Document admin procedures

## 🔧 COMMON HOSTGATOR ISSUES & SOLUTIONS

**Issue 1: Database Connection Errors**

- Solution: Double-check DB credentials, ensure user has all privileges
- HostGator often uses prefixed database names

**Issue 2: .htaccess Not Working**  

- Solution: Contact HostGator support to enable mod_rewrite
- Some shared plans have limitations

**Issue 3: File Permission Errors**

- Solution: Set proper permissions via File Manager or FTP
- Files: 644, Directories: 755, Executables: 755

**Issue 4: PHP Version**

- Solution: Set PHP version to 8.0+ in cPanel
- Your site requires modern PHP features

**Issue 5: Email Sending Issues**

- Solution: Use HostGator SMTP settings for mail() functions
- Consider external services like SendGrid for reliability

## 📞 HOSTGATOR SUPPORT

- **Phone Support:** Available 24/7
- **Live Chat:** Through cPanel or HostGator website  
- **Migration Service:** Free with most plans
- **Knowledge Base:** Extensive documentation available

## 🎯 POST-MIGRATION CHECKLIST

□ All pages load without errors
□ Database queries execute properly  
□ Search functionality works
□ Feedback system operational
□ SSL certificate active
□ Backups configured
□ Performance optimized
□ Analytics tracking (if used)
□ Email notifications working
□ Admin access confirmed

## 📋 BACKUP STRATEGY

**Regular Backups:**

- HostGator provides automatic backups
- Download manual backups monthly
- Keep local copies of database exports
- Document any customizations made

## 🚀 GOING LIVE

When ready to launch:

1. Test everything thoroughly
2. Remove under-construction mode
3. Update DNS if needed
4. Announce launch
5. Monitor for issues
6. Celebrate! 🎉

---

**Estimated Migration Time:** 2-4 hours
**Difficulty Level:** Intermediate
**Required Skills:** Basic cPanel navigation, FTP usage
**HostGator Support:** Available if needed
