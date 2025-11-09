#!/bin/bash
# Construction Mode Toggle Script
# Usage: ./construction-mode.sh [enable|disable|status]

HTACCESS_FILE=".htaccess"
BACKUP_FILE=".htaccess.backup"

case "$1" in
    "enable")
        echo "🚧 Enabling Under Construction Mode..."
        
        # Create backup of current .htaccess
        if [ -f "$HTACCESS_FILE" ]; then
            cp "$HTACCESS_FILE" "$BACKUP_FILE"
            echo "✓ Backed up current .htaccess"
        fi
        
        # Enable construction mode by uncommenting redirect rules
        sed -i.tmp 's/# RewriteCond %{REQUEST_URI} !^\/under-construction/RewriteCond %{REQUEST_URI} !^\/under-construction/g' "$HTACCESS_FILE"
        sed -i.tmp 's/# RewriteRule \^\(\.\*\)\$ \/under-construction/RewriteRule ^\(.*\)$ \/under-construction/g' "$HTACCESS_FILE"
        rm "${HTACCESS_FILE}.tmp" 2>/dev/null
        
        echo "✅ Construction mode ENABLED"
        echo "   Visitors will now see the under-construction page"
        echo "   Admin access: Add ?admin=1 to any URL for admin access"
        ;;
        
    "disable")
        echo "🚀 Disabling Under Construction Mode (Going Live)..."
        
        # Disable construction mode by commenting redirect rules
        sed -i.tmp 's/^RewriteCond %{REQUEST_URI} !^\/under-construction/# RewriteCond %{REQUEST_URI} !^\/under-construction/g' "$HTACCESS_FILE"
        sed -i.tmp 's/^RewriteRule \^\(\.\*\)\$ \/under-construction/# RewriteRule ^\(.*\)$ \/under-construction/g' "$HTACCESS_FILE"
        rm "${HTACCESS_FILE}.tmp" 2>/dev/null
        
        echo "✅ Construction mode DISABLED"
        echo "   Site is now LIVE and accessible to all visitors"
        ;;
        
    "status")
        echo "📊 Construction Mode Status Check..."
        
        if grep -q "^RewriteCond.*under-construction" "$HTACCESS_FILE" 2>/dev/null; then
            echo "🚧 Status: UNDER CONSTRUCTION"
            echo "   Visitors see the construction page"
        else
            echo "🚀 Status: LIVE SITE"
            echo "   Full site is accessible to visitors"
        fi
        ;;
        
    *)
        echo "🚧 Construction Mode Toggle"
        echo ""
        echo "Usage: $0 [enable|disable|status]"
        echo ""
        echo "Commands:"
        echo "  enable   - Redirect all visitors to under-construction page"
        echo "  disable  - Make the full site live and accessible"
        echo "  status   - Check current construction mode status"
        echo ""
        echo "Files:"
        echo "  under-construction.html - Static HTML version"
        echo "  under-construction.php  - PHP version with server features"
        echo ""
        ;;
esac