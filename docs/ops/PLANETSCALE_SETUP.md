# PlanetScale Setup Guide for Submarine FAQ Database

## Step 1: Create PlanetScale Database

1. **Sign up at [PlanetScale](https://planetscale.com/)**
   - Use GitHub login for easy integration

2. **Create new database**
   - Database name: `submarine-faqs`  
   - Region: Choose closest to your users (US East for most users)

3. **Create development branch**
   - PlanetScale will create a `main` branch automatically
   - You can work directly with `main` for this setup

## Step 2: Set Up Database Schema

1. **Connect to your database**
   - Go to your database dashboard
   - Click "Connect" → "Connect with MySQL CLI" 
   - Copy the connection command

2. **Run the schema setup**
   ```bash
   # Connect to your PlanetScale database
   mysql -h <host> -u <username> -p<password> --ssl-mode=REQUIRED <database>
   
   # Copy and paste the contents of database/schema.sql
   ```

3. **Alternative: Use PlanetScale Console**
   - Go to "Console" tab in your PlanetScale dashboard
   - Paste and execute the schema commands directly

## Step 3: Configure Vercel Environment Variables

1. **Get PlanetScale connection string**
   - In PlanetScale dashboard: Connect → "Connect with Prisma"
   - Copy the DATABASE_URL (it will look like this):
   ```
   mysql://username:password@aws.connect.psdb.cloud/submarine-faqs?ssl={"rejectUnauthorized":true}
   ```

2. **Set Vercel Environment Variables**
   ```bash
   # In Vercel dashboard → Settings → Environment Variables
   DATABASE_URL=mysql://username:password@aws.connect.psdb.cloud/submarine-faqs?ssl={"rejectUnauthorized":true}
   ADMIN_PASSWORD=submarine123
   NODE_ENV=production
   ```

## Step 4: Deploy and Migrate

1. **Push changes to GitHub**
   ```bash
   git push origin main
   ```

2. **Deploy to Vercel** (will happen automatically)

3. **Run migration** once deployed:
   ```bash
   # Get your admin token
   ADMIN_TOKEN=$(echo -n 'admin:$(date +%s)' | base64)
   
   # Run migration
   curl -X POST https://your-app.vercel.app/api/migrate-faqs \
     -H "Authorization: Bearer $ADMIN_TOKEN" \
     -H "Content-Type: application/json"
   ```

## Step 5: Test the Database

1. **Verify FAQ loading** - Visit your site and check that FAQs load
2. **Test admin panel** - Try adding/editing FAQs
3. **Test feedback** - Submit feedback through the site

## PlanetScale-Specific Benefits

- **Serverless scaling** - Handles traffic spikes automatically  
- **Branch-based workflow** - Make schema changes safely
- **Built-in backups** - Automatic daily backups
- **Connection pooling** - Optimized for serverless functions
- **Zero-downtime migrations** - Schema changes without downtime

## Troubleshooting

### Connection Issues
- Verify the DATABASE_URL includes the SSL parameter
- Check that the database name matches exactly
- Ensure environment variables are set in Vercel

### Migration Issues  
- Check that the admin token is properly formatted
- Verify the migration endpoint is deployed
- Check Vercel function logs for error details

## Next Steps

Once PlanetScale is set up, you'll have:
- ✅ Scalable database for unlimited FAQs
- ✅ Fast search across all content  
- ✅ Visitor feedback system
- ✅ Admin dashboard for content management
- ✅ Analytics on FAQ popularity
- ✅ Professional backup and recovery