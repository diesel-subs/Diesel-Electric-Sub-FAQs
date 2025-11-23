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
