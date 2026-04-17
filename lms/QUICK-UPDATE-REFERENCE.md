# Theme Update System - Quick Reference Card

## 🚀 Quick Start (5 Minutes)

### 1. Create GitHub Release
```bash
# Step 1: Update version in style.css
Version: 1.0.1

# Step 2: Create tag and push
git tag v1.0.1
git push origin v1.0.1

# Or use GitHub Web UI:
# Releases → Create a new release → tag: v1.0.1
```

### 2. WordPress Auto-Detection
- Checks every 12 hours automatically
- Admin gets notification: "Update Available"
- Users can update from Appearance → Themes

### 3. Verify Installation
- Go to WordPress Admin
- Navigate to **Appearance → Theme Updates**
- See current version
- Click "Check Now" to manually trigger

## 📋 Checklist

- [ ] GitHub repository created: `github.com/impulsecomputeracademy/impulse-academy-clone`
- [ ] First release published with tag (e.g., `v1.0.0`)
- [ ] `style.css` Version matches release tag (without 'v')
- [ ] WordPress admin can see "Theme Updates" menu
- [ ] Click "Check Now" shows update detection works
- [ ] GitHub token added (optional, for rate limits)

## 🔗 Admin Panel Locations

| Feature | Location |
|---------|----------|
| **Check Updates** | Admin Menu → Appearance → Theme Updates |
| **Update Theme** | Admin Menu → Appearance → Themes → (find theme) → Update |
| **View Version** | Admin Menu → Appearance → Theme Updates |
| **Add GitHub Token** | Admin Menu → Appearance → Theme Updates → Settings |

## 🏷️ GitHub Release Format

### Tag Names (Correct)
```
v1.0.0
v1.0.1
v1.1.0
v2.0.0-beta
v1.5.0-rc1
```

### style.css Version (Must Match)
```css
Version: 1.0.0
Version: 1.0.1
Version: 1.1.0
Version: 2.0.0-beta
Version: 1.5.0-rc1
```

## ⚙️ GitHub API Rate Limits

| Option | Limit | Recommended |
|--------|-------|-------------|
| **No Token** | 60/hour | Development only |
| **With Token** | 5,000/hour | Production recommended |

### Get GitHub Token
1. https://github.com/settings/tokens/new
2. Name: "Impulse Academy Updater"
3. Scope: `public_repo` only
4. Admin Panel → Theme Updates → Paste token

## 🐛 Troubleshooting

| Problem | Solution |
|---------|----------|
| No update shown | Version in `style.css` must be LOWER than GitHub tag |
| "No releases found" | Create release on GitHub with tag starting with 'v' |
| Rate limit exceeded | Add GitHub Personal Access Token |
| Manual check fails | Check internet, verify GitHub repo is public |

## 📝 Release Workflow Example

```bash
# 1. Make changes
git add .
git commit -m "Fix bug XXX"

# 2. Update version
# Edit: style.css → Version: 1.2.3

# 3. Commit version bump
git add style.css
git commit -m "Bump version to 1.2.3"

# 4. Create release tag
git tag v1.2.3
git push origin main
git push origin v1.2.3

# WordPress detects within 12 hours!
# Or: Admin → Theme Updates → Check Now
```

## 📚 Documentation Files

- **Full Guide**: [`lms/THEME-UPDATES-GUIDE.md`](../THEME-UPDATES-GUIDE.md)
- **Updater Code**: [`lms/includes/class-ica-theme-updater.php`](../includes/class-ica-theme-updater.php)
- **Admin UI Code**: [`lms/includes/class-ica-theme-update-manager.php`](../includes/class-ica-theme-update-manager.php)

## 💡 Pro Tips

1. **Automatic Checks**: System checks GitHub every 12 hours automatically
2. **Manual Trigger**: Admin can click "Check Now" in Theme Updates menu
3. **Multi-Site**: Works on unlimited WordPress installations
4. **Rollback Ready**: Always backup before updating
5. **Changelog**: Add release notes in GitHub release description

## 🔐 Security

✓ All GitHub API calls use HTTPS
✓ Nonce verification on admin actions
✓ GitHub token not exposed in code
✓ Public repository only (recommended)

## 🆘 Need Help?

1. Check debug log: `wp-content/debug.log`
2. Review Full Guide: `lms/THEME-UPDATES-GUIDE.md`
3. Verify GitHub repo: `github.com/impulsecomputeracademy/impulse-academy-clone`
4. Verify releases exist: `github.com/impulsecomputeracademy/impulse-academy-clone/releases`

---
**System**: Theme Update Checker v1.0
**Last Updated**: April 17, 2026
