# Lentlo Ads - Project Notes for Claude

## Project Info
- **Domain**: lentloads.com
- **App Name**: Lentlo Ads (OLX-like classifieds marketplace)
- **Region**: India

## ⚠️ INCIDENT LOG - Learn From Past Mistakes

### 2025-01-02: Image Data Loss
**What happened:** All user-uploaded images were permanently deleted
**Cause:** Used `git clean -fd` during deployment, which deleted untracked `storage/app/public/`
**Lesson:** NEVER use `git clean` on production - user uploads are not tracked by git
**Prevention:** Safe deploy commands documented below, backups recommended

## Important UI Considerations

### Mobile Bottom Navigation
This app has a bottom mobile navigation bar that is always visible on mobile devices. When creating modals, popups, or any fixed/absolute positioned elements, **always account for the mobile nav height**.

**Mobile nav height:** `calc(60px + env(safe-area-inset-bottom, 8px))`

Example CSS for modals that should appear above the mobile nav:
```css
.modal-sheet {
  /* Position above mobile nav on mobile */
  margin-bottom: calc(60px + env(safe-area-inset-bottom, 8px));
}

/* Desktop - no mobile nav */
@media (min-width: 768px) {
  .modal-sheet {
    margin-bottom: 0;
  }
}
```

### Z-Index Values
- Mobile nav: z-index 40
- Modals/Popups: Use z-index 9999 to ensure they appear above everything

### CSS Gotchas
- **NEVER use `overflow-x: hidden` on html/body** - it breaks `position: sticky`
- Use `overflow-x: clip` instead - provides same clipping but preserves sticky positioning

### Filter Tags Display
- Filter tags should wrap to new lines (use `flex-wrap`) instead of horizontal scroll
- Keep filter tags visible in viewport at all times
- Example: `class="flex flex-wrap gap-2"` instead of `overflow-x-auto`

### Search Page Layout
- Mobile: Controls (Filters, Near Me, Sort) in one row, active filter tags wrap below
- Desktop: Sidebar filters on left, results on right

## Tech Stack
- Backend: Laravel 10 (PHP)
- Frontend: Vue.js 3 with Vite
- State Management: Pinia
- Styling: Tailwind CSS
- Icons: Heroicons

## API Base Path
All API routes are prefixed with `/v1/`

## Key Files
- `resources/js/views/Search.vue` - Search page with filters
- `resources/js/components/layout/AppHeader.vue` - Header with location picker
- `resources/js/components/layout/MobileNav.vue` - Bottom mobile navigation
- `resources/js/stores/app.js` - App store with location state

## Deployment

### ⚠️ CRITICAL WARNING - READ BEFORE DEPLOYING ⚠️

**NEVER use these commands on production:**
- `git clean -fd` - DELETES ALL USER UPLOADS (images, files, etc.)
- `git reset --hard` - Can lose uncommitted changes
- `rm -rf storage/` - Deletes all uploads and cache

**User uploads are stored in `storage/app/public/` which is NOT tracked by git.**
Running `git clean` will permanently delete all user-uploaded images!

### Production Server
- **URL**: https://phplaravel-1016958-6108537.cloudwaysapps.com
- **Server IP**: 139.59.24.36
- **SSH User**: lentlo@12
- **SSH Password**: 789-=uioP
- **App Path**: /home/master/applications/bpadwztsjg/public_html

### Safe Deploy Commands

**✅ SAFE - Standard deployment (use this):**
```bash
expect -c '
set timeout 120
spawn ssh -o StrictHostKeyChecking=no "lentlo@12@139.59.24.36" "cd /home/master/applications/bpadwztsjg/public_html && git pull origin main && php artisan config:clear && php artisan cache:clear"
expect "password:"
send "789-=uioP\r"
expect eof
'
```

**✅ SAFE - If you have local file conflicts (preserves storage/):**
```bash
expect -c '
set timeout 120
spawn ssh -o StrictHostKeyChecking=no "lentlo@12@139.59.24.36" "cd /home/master/applications/bpadwztsjg/public_html && git stash && git pull origin main && php artisan config:clear && php artisan cache:clear"
expect "password:"
send "789-=uioP\r"
expect eof
'
```

**❌ DANGEROUS - Never use (deletes uploads):**
```bash
# git clean -fd  # NEVER USE - deletes storage/app/public/
# git reset --hard  # Avoid unless necessary
```

**Clear OPcache:**
```bash
curl -s 'https://phplaravel-1016958-6108537.cloudwaysapps.com/opcache-clear.php'
```

**Run migrations/seeders (if needed):**
```bash
expect -c '
set timeout 120
spawn ssh -o StrictHostKeyChecking=no "lentlo@12@139.59.24.36" "cd /home/master/applications/bpadwztsjg/public_html && php artisan migrate --force"
expect "password:"
send "789-=uioP\r"
expect eof
'
```

### After Code Changes
1. Run `npm run build` locally
2. Commit and push to GitHub
3. Run the **SAFE** deployment command above
4. Clear OPcache if needed

### Backup Recommendations

**Critical directories that need backup:**
- `storage/app/public/` - User uploaded images
- `.env` - Environment configuration
- Database - MySQL/PostgreSQL dumps

**Recommended backup strategy:**
1. Set up daily automated backups of `storage/app/public/`
2. Set up daily database dumps
3. Store backups on external storage (S3, Cloudways backup, etc.)
4. Test restore process periodically

## Production Safeguards Checklist

### Before ANY Deployment
- [ ] Code changes tested locally
- [ ] `npm run build` completed successfully
- [ ] No sensitive data in commits (passwords, keys, etc.)
- [ ] Using SAFE deploy command (no `git clean`)

### Critical Data Protection
| Data | Location | Backed Up? | Recovery Plan |
|------|----------|------------|---------------|
| User uploads | `storage/app/public/` | ⚠️ SETUP NEEDED | Need Cloudways backup |
| Database | MySQL | ⚠️ SETUP NEEDED | Need daily dumps |
| Environment | `.env` | ⚠️ SETUP NEEDED | Copy to secure location |

### Recommended Production Setup

**1. Cloud Storage for Uploads (Best Practice)**
Instead of local storage, configure Laravel to use S3/DigitalOcean Spaces:
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=ap-south-1
AWS_BUCKET=lentlo-uploads
```

**2. Automated Backups via Cloudways**
- Enable Cloudways automated backups
- Set backup frequency to daily
- Keep at least 7 days of backups

**3. Database Backup Cron**
```bash
# Add to server crontab
0 2 * * * mysqldump -u user -p'pass' database > /backups/db_$(date +\%Y\%m\%d).sql
```

**4. Error Monitoring**
Consider adding:
- Sentry for error tracking
- Laravel Telescope for debugging
- Server monitoring (Cloudways provides this)

### Emergency Recovery
If uploads are deleted:
1. Check Cloudways backups immediately
2. Restore from most recent backup
3. If no backup, data is lost permanently

## Staging Environment (Optional)

### When You Need Staging
- Breaking changes or risky migrations
- Team of multiple developers
- High traffic, can't afford downtime
- Testing payment integrations

### When You DON'T Need Staging (Current State)
- Small app, few users
- Solo developer
- Safe deploy commands in place
- Backups enabled

### How Staging Works (If You Set It Up Later)
```
Production (lentloads.com)      → Real users, real database
Staging (staging.lentloads.com) → Test data, separate database
```

**Key points:**
- Staging has SEPARATE database (not production data)
- Deploying code does NOT affect production database
- User data is safe - only CODE is deployed
- Periodic DB clones can sync structure (anonymize sensitive data)

### Current Recommendation
1. ✅ Use safe deploy commands (done)
2. ✅ Enable Cloudways backups (priority)
3. ⏸️ Add staging later when app grows

## Mobile Header Design

### Features
- **Gradient background**: Purple gradient (`#667eea` to `#764ba2`) on mobile for visual appeal
- **Compact design**: Reduced height on mobile to save space
- **Collapsible search**: Search bar shows on scroll up, hides on scroll down
- **White text/icons**: Mobile header uses white text to contrast with gradient

### Scroll Behavior
- Search bar visible by default at top of page
- Scrolling **down**: Search bar collapses (hidden)
- Scrolling **up**: Search bar expands (visible)
- Smooth animation transitions

### Desktop vs Mobile
- Mobile: Gradient background, compact layout, collapsible search
- Desktop (768px+): White background, full layout

## LocationPicker Component

### Props
The LocationPicker supports both v-model bindings and legacy initial* props:

**v-model props** (preferred for forms):
- `v-model:latitude`, `v-model:longitude`
- `v-model:city`, `v-model:state`, `v-model:locality`, `v-model:postalCode`

**Legacy props** (backward compatibility):
- `initialLatitude`, `initialLongitude`
- `initialCity`, `initialState`, `initialLocality`, `initialPostalCode`

### Usage in Admin/Edit Forms
```vue
<LocationPicker
  :key="editingItem?.id"
  v-model:latitude="form.latitude"
  v-model:longitude="form.longitude"
  v-model:city="form.city"
  v-model:state="form.state"
  v-model:locality="form.locality"
  v-model:postalCode="form.postal_code"
  :initialLatitude="form.latitude"
  :initialLongitude="form.longitude"
  :initialCity="form.city"
  :initialState="form.state"
  :initialLocality="form.locality"
  :initialPostalCode="form.postal_code"
/>
```

**Important**: Use `:key` to force component recreation when editing different items.
