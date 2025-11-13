# Database Setup Guide

This guide explains how to set up a database for the Submarine FAQ application to store FAQs and visitor correspondence.

## Database Options

### Option 1: PlanetScale (Recommended for Vercel)

1. **Sign up at [PlanetScale](https://planetscale.com/)**
2. **Create a new database** called `submarine-faqs`
3. **Get connection string** from the dashboard
4. **Set environment variable** in Vercel:
   ```
   DATABASE_URL=mysql://username:password@aws.connect.psdb.cloud/submarine-faqs?ssl={"rejectUnauthorized":true}
   ```

### Option 2: Railway

1. **Sign up at [Railway](https://railway.app/)**
2. **Create a MySQL database**
3. **Copy connection string** from Railway dashboard
4. **Set environment variable**:
   ```
   DATABASE_URL=mysql://root:password@containers-us-west-1.railway.app:port/railway
   ```

### Option 3: Local MySQL (Development)

1. **Install MySQL locally**
2. **Create database**: `CREATE DATABASE submarine_faqs;`
3. **Set environment variables**:
   ```
   DB_HOST=localhost
   DB_USER=root
   DB_PASSWORD=your-password
   DB_NAME=submarine_faqs
   ```

## Database Schema Setup

1. **Run the schema script** on your database:
   ```sql
   -- Execute the contents of database/schema.sql
   -- This creates tables for categories, FAQs, and feedback
   ```

2. **Insert category data**:
   ```sql
   -- The schema.sql file includes INSERT statements for categories
   ```

## Migration Process

### Step 1: Set Up Database Connection

Add your database connection details to Vercel environment variables:

```bash
# In Vercel dashboard, add these environment variables:
DATABASE_URL=your-database-connection-string
ADMIN_PASSWORD=submarine123
NODE_ENV=production
```

### Step 2: Deploy Updated APIs

The following new API endpoints are now available:

- `/api/database-faqs` - Read FAQs from database (with fallback to file system)
- `/api/database-manage-faqs` - Admin CRUD operations on FAQs
- `/api/correspondence` - Handle visitor feedback and correspondence
- `/api/migrate-faqs` - One-time migration of existing FAQ data

### Step 3: Run Migration

1. **Deploy the application** with the new database APIs
2. **Access the migration endpoint** with admin authentication:
   ```bash
   curl -X POST https://your-app.vercel.app/api/migrate-faqs \
     -H "Authorization: Bearer $(echo -n 'admin:token' | base64)" \
     -H "Content-Type: application/json"
   ```
3. **Verify migration** by checking the admin panel

### Step 4: Update Frontend (Optional)

To fully use the database, you can update the frontend to use the new endpoints:

1. **Change API calls** from `/api/corrected-faqs` to `/api/database-faqs`
2. **Update admin interface** to use `/api/database-manage-faqs`
3. **Add correspondence handling** using `/api/correspondence`

## Database Features

### FAQ Management
- **Full CRUD operations** for FAQs
- **Category organization** with existing categories
- **Full-text search** capabilities
- **View tracking** for popular FAQs
- **Status management** (published, draft, archived)

### Correspondence System
- **Visitor feedback** storage and management
- **FAQ-specific feedback** linking
- **Rating system** for feedback quality
- **Admin response tracking**
- **Status workflow** (pending, approved, rejected, implemented)

### Admin Features
- **Unified dashboard** for FAQs and correspondence
- **Bulk operations** for content management
- **Search and filtering** across all content
- **Analytics** on FAQ views and feedback

## Benefits of Database Storage

1. **Scalability** - Handle thousands of FAQs and feedback entries
2. **Performance** - Fast search and filtering with database indexes  
3. **Data Integrity** - Relational constraints and validation
4. **Backup & Recovery** - Database-level backup capabilities
5. **Analytics** - Track FAQ views, popular content, and user engagement
6. **Multi-user** - Support multiple admin users with proper authentication

## Troubleshooting

### Connection Issues
- Verify environment variables are set correctly
- Check database service status and connectivity
- Ensure SSL settings match your database provider

### Migration Problems
- Check that the fallback file `corrected-faqs-fallback.js` exists
- Verify admin authentication token is valid
- Check server logs for detailed error messages

### Performance Optimization
- Monitor database connection usage
- Consider connection pooling for high traffic
- Use database indexes for frequently queried fields

## Maintenance

### Regular Tasks
1. **Monitor database size** and optimize as needed
2. **Review correspondence** and respond to user feedback
3. **Update FAQ content** based on user suggestions
4. **Backup database** regularly
5. **Monitor API performance** and error rates

### Scaling Considerations
- **Connection limits** - Most serverless databases have connection limits
- **Query optimization** - Use proper indexes and query patterns
- **Caching** - Consider Redis or similar for frequently accessed data
- **CDN** - Use Vercel's edge caching for static content