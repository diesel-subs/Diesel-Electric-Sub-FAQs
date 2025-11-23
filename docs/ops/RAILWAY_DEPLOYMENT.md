# Railway Deployment Guide for Submarine FAQs

## Prerequisites

- GitHub repository: `diesel-subs/Diesel-Electric-Sub-FAQs` ✅
- Code committed and pushed ✅
- Database export ready ✅

## Step 1: Create Railway Project

1. Go to [Railway.app](https://railway.app)
2. Sign in with your GitHub account
3. Click "New Project"
4. Select "Deploy from GitHub repo"
5. Choose your repository: `diesel-subs/Diesel-Electric-Sub-FAQs`

## Step 2: Add MySQL Database

1. In your Railway project dashboard
2. Click "New Service"
3. Select "Database" → "MySQL"
4. Wait for the database to provision

## Step 3: Configure Environment Variables

The app will automatically use Railway's MySQL environment variables:

- `MYSQLHOST`
- `MYSQLPORT`
- `MYSQLDATABASE`
- `MYSQLUSER`
- `MYSQLPASSWORD`

## Step 4: Import Database

1. In Railway, go to your MySQL service
2. Click "Connect" to get connection details
3. Use a MySQL client (like MySQL Workbench) to connect
4. Import the `submarine_faqs_export.sql` file

**OR**

Upload and run the `railway-db-import.php` script via your Railway app URL to automatically set up the database structure.

## Step 5: Deploy

1. Railway will automatically deploy when you push to GitHub
2. Your app will be available at: `https://your-app-name.up.railway.app`

## Step 6: Access Admin

1. Go to: `https://your-app-name.up.railway.app/admin/login.php`
2. Login with:
   - Username: `admin`
   - Password: `submarine2024!`

## Step 7: Import FAQ Data

Use the admin dashboard to import your FAQ data, or run the import scripts.

## Custom Domain (Optional)

1. In Railway project settings
2. Go to "Domains"
3. Add your custom domain
4. Update DNS records as instructed

## Monthly Cost

- Basic usage: ~$5/month
- Includes web app hosting and MySQL database
- No traffic limits on the Hobby plan

## Support Files Included

- `nixpacks.toml` - Railway build configuration
- `railway-db-import.php` - Database setup script
- `submarine_faqs_export.sql` - Complete database backup
- All admin tools and FAQ management system

Your submarine FAQ site will be live and fully functional on Railway!
