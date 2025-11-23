# Under Construction Mode

This directory contains a complete "Under Construction" system for the Submarine FAQ website.

## 🚧 Files Included

### Main Pages

- `under-construction.html` - Static HTML version with animations and submarine theming
- `under-construction.php` - PHP version with server-side features (newsletter signup, etc.)

### Management

- `construction-mode.sh` - Script to easily enable/disable construction mode
- `.htaccess` - Contains redirect rules (commented by default)

## 🚀 Quick Setup

### Option 1: Manual Setup

1. **Enable Construction Mode:**
   - Edit `.htaccess`
   - Uncomment the construction redirect lines (around line 8-12)
   - Save the file

2. **Disable Construction Mode:**
   - Edit `.htaccess`
   - Comment out the construction redirect lines
   - Save the file

### Option 2: Script Setup (Recommended)

```bash
# Enable construction mode
./construction-mode.sh enable

# Check current status  
./construction-mode.sh status

# Disable construction mode (go live)
./construction-mode.sh disable
```

## ✨ Features

### Under Construction Page Features

- **Responsive Design** - Works on all devices
- **Submarine Theming** - Animated submarine, bubbles, waves
- **Progress Indicator** - Shows 85% completion status
- **Newsletter Signup** - Email collection for launch notification
- **Feature Preview** - Highlights what's coming
- **Social Links** - Placeholder social media integration
- **Contact Information** - Easy way for visitors to reach out
- **Fun Facts** - Rotating submarine facts every 10 seconds
- **Professional Design** - Maintains credibility during development

### Technical Features

- **Smart Redirects** - Preserves admin access during construction mode
- **Local Development** - Excludes localhost from redirects
- **Asset Protection** - Allows CSS/JS/image files to load normally
- **SEO Friendly** - Uses 302 redirects (temporary) instead of 301 (permanent)

## 🔧 Customization

### Update Site Information

Edit the variables in `under-construction.php`:

```php
$site_name = "Your Site Name";
$launch_date = "Month Year"; 
$progress_percentage = 85;
$contact_email = "your@email.com";
```

### Change Progress Percentage

In `under-construction.html`, update the CSS:

```css
.progress-fill {
    width: 85%; /* Change this percentage */
}
```

### Add More Submarine Facts

Edit the `submarine_facts` array in either file to add more rotating facts.

### Modify Colors/Styling

The CSS uses a submarine/ocean theme with:

- Blue gradient background (`#1e3c72` to `#2a5298`)
- Warning/gold accent color (`#ffd700`)
- Success green for progress (`#28a745`)

## 📧 Newsletter Integration

The PHP version includes basic newsletter signup. To integrate with a real service:

1. **Mailchimp Integration:**

```php
// Add Mailchimp API call in under-construction.php
$mailchimp = new MailChimp('your-api-key');
$result = $mailchimp->post("lists/list-id/members", [
    'email_address' => $email,
    'status' => 'subscribed'
]);
```

2. **Simple File Storage:**

```php
// Save to file for manual processing
file_put_contents('subscribers.txt', $email . "\n", FILE_APPEND);
```

## 🛡️ Security Considerations

- **Admin Access**: Add `?admin=1` to any URL during construction mode for admin access
- **Local Development**: Script excludes `127.0.0.1` from redirects
- **Asset Loading**: CSS, JS, and images still load normally
- **Temporary Redirects**: Uses 302 (not 301) to avoid SEO issues

## 🚀 Going Live Checklist

When ready to launch:

1. Run `./construction-mode.sh disable`
2. Test all major site functionality
3. Update any "coming soon" dates in content
4. Remove or archive construction files if desired
5. Update social media links from placeholders to real accounts
6. Set up real newsletter integration if using the PHP version

## 📱 Browser Compatibility

- ✅ Chrome/Edge/Safari (modern versions)
- ✅ Firefox (modern versions)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)
- ✅ Tablets and desktop
- ⚠️ IE11 (basic functionality, no animations)

## 🎨 Design Credits

- Uses Bootstrap 5 for responsive design
- Font Awesome for icons
- Google Fonts (Roboto) for typography
- Custom CSS animations for submarine theme
- Glassmorphism design elements
