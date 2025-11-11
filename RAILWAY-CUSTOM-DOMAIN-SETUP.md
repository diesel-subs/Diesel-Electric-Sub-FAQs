# Railway Custom Domain Setup Guide

# Setting up dieselsubs.com with your submarine FAQ project

## Prerequisites

✅ Railway account created
✅ GitHub repository uploaded (diesel-subs/Diesel-Electric-Sub-FAQs)
✅ Domain dieselsubs.com registered and accessible

## Step 1: Deploy Project to Railway

1. **Login to Railway Dashboard:**
   - Go to <https://railway.app>
   - Click "Login" and sign in with GitHub

2. **Create New Project:**
   - Click "New Project"
   - Select "Deploy from GitHub repo"
   - Choose `diesel-subs/Diesel-Electric-Sub-FAQs`
   - Railway will automatically start building your PHP project

3. **Add MySQL Database:**
   - In your project dashboard, click "Add Service"
   - Select "MySQL"
   - Railway automatically connects it and sets environment variables

## Step 2: Import Your Database

1. **Get Database Connection Details:**
   - Click on your MySQL service
   - Go to "Connect" tab
   - Note the connection details (host, port, user, password, database)

2. **Import Your Data:**

   ```bash
   # Option A: Use Railway CLI (recommended)
   railway login
   railway link [your-project-id]
   railway run mysql -h $MYSQLHOST -P $MYSQLPORT -u $MYSQLUSER -p$MYSQLPASSWORD $MYSQLDATABASE < submarine_faqs_backup.sql

   # Option B: Use local MySQL client
   mysql -h [railway-host] -P [railway-port] -u [railway-user] -p[railway-password] [railway-database] < submarine_faqs_backup.sql
   ```

3. **Switch to Railway Database Config:**

   ```bash
   # Update your repository to use Railway config
   git add .
   git commit -m "Switch to Railway database configuration"
   git push origin main
   # Railway auto-deploys!
   ```

## Step 3: Set Up Custom Domain (dieselsubs.com)

### In Railway Dashboard

1. **Access Domain Settings:**
   - Go to your project dashboard
   - Click on your web service (the one running your PHP app)
   - Go to "Settings" tab
   - Scroll down to "Domains" section

2. **Add Custom Domain:**
   - Click "Custom Domain"
   - Enter: `dieselsubs.com`
   - Click "Add Domain"

3. **Get DNS Records:**
   Railway will provide you with DNS records to configure. You'll typically see:
   - **CNAME Record:** `dieselsubs.com` → `[your-project].railway.app`
   - Or **A Records:** IP addresses to point to

### In Your Domain Registrar (GoDaddy, Namecheap, etc.)

4. **Configure DNS Records:**

   **For Root Domain (dieselsubs.com):**

   ```
   Type: A
   Name: @
   Value: [Railway's IP address]
   TTL: 300 (or default)
   ```

   **For WWW Subdomain:**

   ```
   Type: CNAME
   Name: www
   Value: [your-project].railway.app
   TTL: 300 (or default)
   ```

   **Alternative (if CNAME supported for root):**

   ```
   Type: CNAME
   Name: @
   Value: [your-project].railway.app
   ```

## Step 4: SSL Certificate Setup

Railway automatically provides SSL certificates for custom domains:

1. **Automatic SSL:**
   - Railway uses Let's Encrypt
   - SSL certificate is automatically provisioned
   - Usually takes 5-15 minutes after DNS propagation

2. **Verify SSL:**
   - Check that `https://dieselsubs.com` works
   - Railway automatically redirects HTTP to HTTPS

## Step 5: Configure Redirects (Optional)

To ensure <www.dieselsubs.com> redirects to dieselsubs.com (or vice versa):

1. **Add Both Domains in Railway:**
   - Add `dieselsubs.com`
   - Add `www.dieselsubs.com`

2. **Set Primary Domain:**
   - Railway will let you choose which is primary
   - Other will automatically redirect

## Step 6: Update Your Application (If Needed)

If your app has hardcoded URLs, update them:

```php
// In config/database.php or wherever needed
define('SITE_URL', 'https://dieselsubs.com');
define('SITE_NAME', 'Diesel-Electric Submarine FAQs');
```

## Step 7: Test Everything

1. **Domain Access:**
   - `https://dieselsubs.com` - Should load your site
   - `https://www.dieselsubs.com` - Should redirect or load
   - `http://dieselsubs.com` - Should redirect to HTTPS

2. **Functionality Tests:**
   - Homepage loads
   - Categories work
   - Search functionality
   - Individual FAQ pages
   - Feedback system
   - Admin dashboard

## Common Issues & Solutions

**Issue 1: DNS Not Propagating**

- Wait 24-48 hours for full propagation
- Use `nslookup dieselsubs.com` to check DNS
- Clear browser cache

**Issue 2: SSL Certificate Pending**

- Wait 15-30 minutes after DNS propagation
- Ensure DNS records are correct
- Contact Railway support if it takes longer

**Issue 3: 404 Errors on Custom Domain**

- Verify the domain is pointing to the correct Railway service
- Check that your web service is running
- Ensure your PHP app is properly deployed

**Issue 4: Database Connection Issues**

- Verify your app is using the Railway database config
- Check environment variables in Railway dashboard
- Ensure database import was successful

## DNS Propagation Check

Use these tools to verify DNS propagation:

- <https://dnschecker.org>
- <https://www.whatsmydns.net>
- Command line: `nslookup dieselsubs.com`

## Railway CLI Commands (Optional)

Install Railway CLI for easier management:

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login and link project
railway login
railway link

# Check logs
railway logs

# Run database commands
railway run mysql --help
```

## Final Checklist

□ Project deployed to Railway
□ MySQL database added and imported
□ Custom domain added in Railway dashboard
□ DNS records configured at domain registrar
□ SSL certificate provisioned (automatic)
□ Site accessible at <https://dieselsubs.com>
□ All functionality tested and working
□ Redirects configured (www → non-www or vice versa)

## Cost Estimate

- **Railway Hosting:** ~$5/month
- **Domain:** Already owned
- **SSL Certificate:** Free (included with Railway)
- **Database:** Included in hosting cost
- **Total:** ~$5/month

Your submarine FAQ site will be live at dieselsubs.com! 🚂⚓
