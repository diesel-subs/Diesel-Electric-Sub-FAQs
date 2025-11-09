#!/bin/bash
# GitHub Repository Replacement Script
# This safely replaces your existing GitHub repository with the submarine FAQ project

echo "🔄 GitHub Repository Replacement"
echo "================================"

# Get the GitHub repository URL
echo "📝 What's your GitHub repository URL?"
echo "Examples:"
echo "  - https://github.com/username/repository-name"
echo "  - git@github.com:username/repository-name.git"
echo ""
read -p "Enter your GitHub repository URL: " REPO_URL

# Validate URL format
if [[ ! $REPO_URL =~ github\.com ]]; then
    echo "❌ Invalid GitHub URL. Please try again."
    exit 1
fi

echo ""
echo "⚠️  WARNING: This will COMPLETELY REPLACE your existing repository contents!"
echo "📁 Repository: $REPO_URL"
echo ""
read -p "Are you sure you want to continue? (yes/no): " CONFIRM

if [[ $CONFIRM != "yes" ]]; then
    echo "❌ Operation cancelled."
    exit 0
fi

echo ""
echo "🚀 Starting repository replacement..."

# Step 1: Check if remote already exists
echo "📡 Checking existing remotes..."
if git remote get-url origin 2>/dev/null; then
    echo "📡 Removing existing origin..."
    git remote remove origin
fi

# Step 2: Add your repository as origin
echo "🔗 Adding your GitHub repository..."
git remote add origin "$REPO_URL"

# Step 3: Prepare all files for commit
echo "📦 Preparing project files..."
git add .

# Step 4: Create initial commit
echo "💾 Creating commit..."
git commit -m "Replace repository with Submarine FAQ project

- Complete LAMP stack submarine FAQ website
- 183+ FAQs organized in 6 categories  
- Advanced search and filtering system
- Community feedback system with admin dashboard
- Mobile-responsive Bootstrap design
- Under construction mode for maintenance
- Railway deployment ready
- Database export included (submarine_faqs_backup.sql)

Features:
✅ Category-based FAQ organization
✅ Individual FAQ pages with related content
✅ Ajax-powered search functionality  
✅ Visitor feedback collection system
✅ Admin management dashboard
✅ Quick feedback widgets (thumbs up/down)
✅ Exit-intent feedback prompts
✅ Community dashboard with analytics
✅ Under construction pages (HTML & PHP)
✅ HostGator migration package included
✅ Railway deployment configuration
✅ Git-based deployment workflow

Tech Stack: PHP 8.4, MySQL 9.5, Bootstrap 5, JavaScript"

# Step 5: Force push to replace repository contents
echo "🚀 Replacing repository contents..."
echo "⚠️  This will permanently delete existing content..."

# Use force push with lease for safety
git push --force-with-lease origin main 2>/dev/null || \
git push --force-with-lease origin master 2>/dev/null || \
git push --set-upstream origin main --force-with-lease

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Repository successfully replaced!"
    echo "🎉 Your submarine FAQ project is now live on GitHub!"
    echo ""
    echo "📁 Repository URL: $REPO_URL"
    echo "🌐 You can now deploy to Railway, Vercel, or other platforms"
    echo ""
    echo "🚀 Next steps:"
    echo "1. Visit your GitHub repository to verify the upload"
    echo "2. Deploy to Railway: https://railway.app"
    echo "3. Or deploy to Vercel: https://vercel.com"
    echo ""
else
    echo ""
    echo "❌ Push failed. This might be due to:"
    echo "1. Repository doesn't exist or you don't have access"
    echo "2. Branch protection rules"
    echo "3. Authentication issues"
    echo ""
    echo "💡 Manual steps:"
    echo "1. Go to your GitHub repository settings"
    echo "2. Temporarily disable branch protection (if any)"
    echo "3. Run: git push origin main --force"
    echo ""
fi