# Theme Update System - Complete Setup Guide

## Overview
The Impulse Academy Theme now includes an automatic update detection system that checks GitHub for new releases and notifies WordPress users, exactly like premium themes do.

## Features
✓ Automatic update detection from GitHub (checks every 12 hours)
✓ Admin dashboard notifications when updates are available
✓ One-click updates from WordPress Appearance → Themes
✓ Manual update check button in admin panel
✓ Optional GitHub token for higher API rate limits
✓ Works on unlimited sites

## Prerequisites
1. **GitHub Repository**: Your theme must be hosted on GitHub
2. **GitHub Releases**: You must publish releases with version tags
3. **WordPress 6.0+**: This feature requires WordPress 6.0 or later
4. **PHP 8.0+**: Compatible with PHP 8.0 and above

## Step 1: Set Up GitHub Releases (One-Time Setup)

### Option A: Create a New Release via GitHub Web UI
1. Go to your GitHub repository: `https://github.com/impulsecomputeracademy/impulse-academy-clone`
2. Click **"Releases"** on the right sidebar
3. Click **"Create a new release"**
4. Fill in the form:
   - **Tag version**: `v1.0.1` (must start with `v`)
   - **Release title**: `Version 1.0.1 - Bug Fixes`
   - **Description**: Add changelog/update notes
5. Click **"Publish release"**

### Option B: Create Release via Git Command Line
```bash
# Add tag and push to GitHub
git tag v1.0.1
git push origin v1.0.1
git push --tags
```

Then create a release on GitHub UI, or use GitHub CLI:
```bash
gh release create v1.0.1 --generate-notes
```

## Step 2: Update Version in WordPress Theme

Edit your theme's `style.css` to match the GitHub release version:

```css
/*
Theme Name: Impulse Academy Site Clone
Version: 1.0.1
...
*/
```

**Important**: The version in `style.css` MUST match! Otherwise updates won't be detected.

## Step 3: Configure Update Settings in WordPress Admin

1. Log in to WordPress admin as administrator
2. Go to **Appearance** (on the left menu) → **LMS Settings** → **Theme Updates**
3. You'll see:
   - Current theme version
   - Manual "Check Now" button
   - GitHub token field (optional)

## Step 4: Optional - Add GitHub Personal Access Token

For higher API rate limits (useful if checking updates frequently):

1. Go to GitHub: https://github.com/settings/tokens/new
2. Click "Generate new token"
3. Give it a name: "Impulse Academy Theme Updater"
4. Select scopes: Only `public_repo` is needed
5. Click "Generate token"
6. Copy the token
7. In WordPress admin, paste it in **Theme Updates** → **GitHub Personal Access Token**
8. Click **Save Changes**

## How It Works for End Users

### For Site Administrators:
1. **Automatic Detection**: Every 12 hours, WordPress checks GitHub for new releases
2. **Admin Notification**: When an update is available, a blue notification appears at the top of the admin dashboard
3. **Manual Check**: Can click "Check Now" in **Appearance → Theme Updates**
4. **Update Installation**: Go to **Appearance → Themes**, find the theme, and click "Update"

### Update Notification Example:
```
ℹ️ Impulse Academy Theme Update Available
A new version (1.0.1) of the Impulse Academy theme is available.
[View Update] [Dismiss]
```

## File Structure

```
impulse-academy-clone/
├── lms/
│   └── includes/
│       ├── class-ica-theme-updater.php          ← Main update checker
│       └── class-ica-theme-update-manager.php   ← Admin UI
└── bootstrap.php                                 ← Loads the updater
```

## API Rate Limits

### Without GitHub Token:
- 60 requests per hour per IP address

### With GitHub Personal Access Token:
- 5,000 requests per hour per user
- Recommended for production

## Troubleshooting

### Updates Not Showing Up?
1. Check that version in `style.css` is lower than GitHub release version
2. Click "Check Now" to manually trigger update check
3. Check WordPress debug log (`wp-content/debug.log`) for errors
4. Ensure GitHub repository is **public** (private repos need authentication)

### "No releases found" Error?
1. Go to your GitHub repo → **Releases**
2. Verify you have created at least one release
3. Release tag must start with 'v' (e.g., `v1.0.0`, not `1.0.0`)

### Rate Limit Issues?
1. Add a GitHub Personal Access Token
2. Follow Step 4 above to generate and add token

### Manual Debug Check
Add this to your theme's `functions.php` temporarily:
```php
// Clear update cache and check manually
delete_transient('ica_theme_update_check');
$updater = ICA_Theme_Updater::get_instance();
$update_info = $updater->get_github_release(); // Manually trigger check
error_log('Update Info: ' . json_encode($update_info));
```

## Release Version Format

### Correct ✓
- `v1.0.0`
- `v1.0.1`
- `v1.1.0-beta`
- `v2.0.0-rc1`

### Incorrect ✗
- `1.0.0` (missing 'v')
- `release-1.0.0` (wrong format)
- `version1.0.0` (wrong format)

## Workflow Example

### Day 1: Development
```bash
# Make changes to theme
git commit -am "Fix login redirect bug"
git push origin main
```

### Day 2: Release
```bash
# Update version in style.css
# Version: 1.2.3

# Create Git tag and push
git tag v1.2.3
git push origin v1.2.3
```

### WordPress automatically detects within 12 hours:
1. Admin gets notification: "Update available: v1.2.3"
2. They can click to update
3. WordPress downloads from GitHub
4. Theme updates automatically
5. Site runs latest version

## Manual Update Trigger (PHP)

If you need to trigger an update check programmatically:

```php
// Force immediate update check
delete_transient('ica_theme_update_check');
do_action('set_site_transient_update_themes', null);

// Or via AJAX call
wp_remote_post(admin_url('admin-ajax.php'), array(
    'blocking' => false,
    'sslverify' => apply_filters('https_local_ssl_verify', false),
    'body' => array(
        'action' => 'ica_check_theme_updates',
        'nonce' => wp_create_nonce('ica_nonce'),
    ),
));
```

## Security Considerations

1. **Public Repository**: Anyone can see your theme code. This is recommended.
2. **GitHub Token**: Should have minimal permissions (`public_repo` only)
3. **SSL/TLS**: All GitHub API requests use HTTPS
4. **Nonce Verification**: Admin actions are nonce-protected

## Frequently Asked Questions

### Q: Can users update automatically without clicking anything?
**A:** Not yet, but "auto-update" feature is coming in future versions. For now, admins must click "Update".

### Q: Does this work with private GitHub repositories?
**A:** Yes, if you provide a GitHub Personal Access Token with appropriate permissions.

### Q: How often are updates checked?
**A:** Automatically every 12 hours. Admins can click "Check Now" for immediate manual check.

### Q: Can I change the check interval?
**A:** Yes, edit `class-ica-theme-updater.php` line with `$this->check_interval = 12 * HOUR_IN_SECONDS;`

### Q: What happens if GitHub is down?
**A:** The system will silently fail and try again in 12 hours. No errors shown to users.

### Q: Do I need a GitHub token?
**A:** No, it's optional. You only need it for higher rate limits or private repos.

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review WordPress debug log: `wp-content/debug.log`
3. Verify GitHub repository settings
4. Check GitHub API status: https://www.githubstatus.com/

## Related Files

- Update Checker: [`lms/includes/class-ica-theme-updater.php`](../includes/class-ica-theme-updater.php)
- Admin UI: [`lms/includes/class-ica-theme-update-manager.php`](../includes/class-ica-theme-update-manager.php)
- Bootstrap: [`bootstrap.php`](../bootstrap.php)

## Version History

- **v1.0** (Apr 2026): Initial release with basic update checking
- Future: Auto-update, rollback, staged rollout features

---

**Last Updated**: April 17, 2026
