# 🚂 Railway Deployment Guide
Complete guide to hosting your Submarine FAQ site and database on Railway

## Why Railway?
- **All-in-one platform**: Host website + database together
- **Simple pricing**: Pay for what you use, no complex tiers
- **Git integration**: Auto-deploy on git push
- **Built-in database**: MySQL with automatic backups
- **Environment management**: Easy environment variable handling
- **Persistent storage**: Your data is safe and backed up

## Step 1: Set Up Railway Account

1. **Sign up**: Visit [railway.app](https://railway.app)
2. **Connect GitHub**: Link your repository
3. **Verify account**: Complete email verification

## Step 2: Deploy Your Database

### Create MySQL Database Service

1. **New Project**: Click "New Project" in Railway dashboard
2. **Add MySQL**: Click "Add Service" → "Database" → "MySQL"
3. **Wait for deployment**: MySQL will start automatically
4. **Note credentials**: Railway will generate connection details

### Configure Database Access

Your MySQL service will have:
```
Host: containers-us-west-xxx.railway.app
Port: 7432 (example)
Username: root
Password: [auto-generated]
Database: railway
```

Railway automatically creates a `DATABASE_URL` environment variable.

## Step 3: Deploy Your Website

### Connect Repository

1. **Add Service**: In same project, click "Add Service" → "GitHub Repo"
2. **Select repo**: Choose `Diesel-Electric-Sub-FAQs`
3. **Configure**: Railway will detect your Node.js app automatically

### Set Environment Variables

In your Railway project settings, add:

```bash
NODE_ENV=production
ADMIN_PASSWORD=your-secure-admin-password
DATABASE_URL=[automatically set by Railway MySQL service]
```

**Important**: Railway automatically sets `DATABASE_URL` when you have a MySQL service in the same project.

## Step 4: Database Schema Setup

### Method 1: Railway Console (Recommended)

1. **Open database console**: In Railway dashboard, click your MySQL service
2. **Open terminal**: Click "Connect" → "Railway CLI"
3. **Run schema**:
   ```sql
   -- Copy and paste the contents of database/schema.sql
   -- This creates all tables and indexes
   ```

### Method 2: Local MySQL Client

```bash
# Get your Railway database URL from dashboard
mysql -h containers-us-west-xxx.railway.app -P 7432 -u root -p

# Run schema setup
source database/schema.sql;
```

## Step 5: Migration and Testing

### Run FAQ Migration

After deployment, migrate your existing FAQs:

```bash
# Get your Railway app URL (e.g., https://submarine-faqs-production.up.railway.app)
# Create admin token
ADMIN_TOKEN=$(echo -n 'admin:'$(date +%s) | base64)

# Run migration
curl -X POST https://your-app.up.railway.app/api/migrate-faqs \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json"
```

### Test Your Deployment

1. **Visit your site**: Check your Railway app URL
2. **Test FAQs**: Verify all 166 FAQs load correctly
3. **Test search**: Try searching for submarine topics
4. **Test admin**: Visit `/admin` and try adding/editing FAQs
5. **Test feedback**: Submit feedback through the contact form

## Step 6: Custom Domain (Optional)

1. **Add domain**: In Railway project settings → "Domains"
2. **Configure DNS**: Point your domain to Railway
3. **SSL certificate**: Automatically provisioned by Railway

## Railway-Specific Benefits

### 🎯 **Integrated Services**
- Database and web app in one project
- Automatic service linking and environment variables
- Built-in monitoring and logging

### 🔄 **Auto-Deploy**
- Push to GitHub → automatic deployment
- Zero-downtime deployments
- Automatic rollback on failure

### 💾 **Database Management**
- Automatic backups
- Point-in-time recovery
- Monitoring and metrics
- Scaling without downtime

### 💰 **Transparent Pricing**
- Pay per resource usage
- No hidden fees or complex tiers
- Generous free tier for development

## Cost Estimation

**Free Tier** (perfect for testing):
- $5 monthly credit
- Covers small websites + database
- No credit card required

**Production** (estimated monthly):
- Website hosting: $2-5/month
- MySQL database: $3-8/month  
- **Total: ~$5-13/month** for professional hosting

## Migration from Vercel + PlanetScale

If you're currently using Vercel + PlanetScale:

1. **Export data**: Use PlanetScale export tools
2. **Deploy Railway**: Follow this guide  
3. **Import data**: Use MySQL import or migration API
4. **Update DNS**: Point domain to Railway
5. **Deactivate old services**: Cancel Vercel/PlanetScale

## Troubleshooting

### Common Issues

**Database connection failed**
```bash
# Check if services are in same project
railway status

# Verify DATABASE_URL
railway variables
```

**API routes not working**
```bash
# Check build logs
railway logs

# Verify express-server.js is loading API files
```

**Migration failed**
```bash
# Check admin authentication
curl -I https://your-app.up.railway.app/api/migrate-faqs

# Verify database tables exist
railway connect mysql
> SHOW TABLES;
```

### Support Resources
- [Railway Documentation](https://docs.railway.app)
- [Railway Discord](https://discord.gg/railway)
- [Railway Status Page](https://status.railway.app)

---

## 🚀 Next Steps

1. **Create Railway account** and connect your GitHub repository
2. **Add MySQL service** to your Railway project  
3. **Deploy your application** - Railway handles the rest!
4. **Run database schema** setup in Railway console
5. **Migrate your FAQs** using the migration API
6. **Configure custom domain** (optional)

Your submarine FAQ site will be live with professional hosting and database management!