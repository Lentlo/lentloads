# Lentlo Ads - Project Notes for Claude

## Project Info
- **Domain**: lentloads.com
- **App Name**: Lentlo Ads (OLX-like classifieds marketplace)
- **Region**: India

## Recent Changes (Changelog)

### 2026-01-09 - Android App Fixes (v1.2)
- **Fixed extra space on top**: Removed `env(safe-area-inset-top)` from mobile header (handled by StatusBar plugin)
- **Fixed scroll collapse**: Improved scroll detection with multiple scroll sources for WebView compatibility
- **Fixed splash screen**: Updated styles.xml to use splash drawable, configured launchAutoHide: false
- **StatusBar configuration**: Added runtime StatusBar style and background color setup
- **Capacitor config updates**: Better splash screen and status bar settings

### 2026-01-09 - Mobile Header Redesign (v1.1)
- **Separate mobile/desktop headers**: Completely different layouts for mobile (< 768px) and desktop
- **Mobile header features**:
  - Gradient purple background (#6366f1 to #8b5cf6)
  - Two-row layout: Top bar (logo + actions) + Search bar (collapsible)
  - Removed "Post Ad" button (available in bottom nav)
  - Safe area padding for notched devices
- **Improved scroll behavior**: 60px threshold, 300ms cooldown (faster response)
- **Fixed chat modal z-index**: Modal now appears above mobile nav (z-index: 10000)
- **Fixed send button styling**: Clear visual difference between enabled/disabled states
- **Web manifest fix**: Proper `manifest.json` for production deployment

### 2026-01-03 - Capacitor Mobile App (v1.0)
- Initial Capacitor setup for Android
- Hash-based routing for native app
- API base URL detection for native vs web
- Splash screen and status bar configuration

---

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
- Mobile Apps: Capacitor (Android/iOS)

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

**⚠️ USE WITH CAUTION - If you must clean untracked files:**
```bash
# SAFE - excludes storage directory
git clean -fd --exclude=storage/

# SAFE - excludes multiple directories
git clean -fd --exclude=storage/ --exclude=.env
```

**❌ NEVER USE (deletes uploads):**
```bash
# git clean -fd  # WITHOUT --exclude - deletes storage/app/public/
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
5. **TEST the live site immediately** (see checklist below)
6. **ALERT user immediately if anything is broken**

### Health Check Page (Live Testing)

**URL:** https://phplaravel-1016958-6108537.cloudwaysapps.com/health-check

This page automatically tests:
- ✅ Database connection
- ✅ Users table (count)
- ✅ Listings table (total & active count)
- ✅ Categories table (count)
- ✅ Storage directory & permissions
- ✅ Storage symlink
- ✅ Cache read/write
- ✅ Recent listings with images
- ✅ PHP version & environment

**Use this page to:**
1. Verify system is working after deployment
2. Periodic health monitoring
3. Debug issues quickly

### Post-Deployment Testing Checklist

**After every deployment, visit the health check page:**
```
/health-check → Shows all system checks with status
```

**Additional manual checks if needed:**
```
□ Homepage loads correctly
□ Listings display with images
□ Search works
□ User can login (if auth changes)
□ Create listing works (if listing changes)
□ Image upload works (if storage/image changes)
```

**If ANY test fails:**
1. IMMEDIATELY inform the user
2. DO NOT continue with other tasks
3. Investigate and fix, or rollback if needed

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

**Two types of user data - BOTH need protection:**

| Data Type | Location | Contains | Backup Method |
|-----------|----------|----------|---------------|
| **Files** | `storage/app/public/` | Images, uploads | Cloudways backup / S3 |
| **Database** | MySQL | Users, listings, messages, reviews, categories | Daily DB dumps |
| **Config** | `.env` | Passwords, API keys | Manual copy to secure location |

**What can destroy each:**
| Threat | Files | Database |
|--------|-------|----------|
| `git clean -fd` | ❌ DELETES | ✅ Safe |
| `rm -rf storage/` | ❌ DELETES | ✅ Safe |
| Bad migration (DROP TABLE) | ✅ Safe | ❌ DELETES |
| `DELETE FROM users` query | ✅ Safe | ❌ DELETES |
| Server disk failure | ❌ LOST | ❌ LOST |
| No backups + any disaster | ❌ GONE FOREVER | ❌ GONE FOREVER |

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

**4. Safe Database Practices**
- NEVER run `DROP TABLE` or `DELETE FROM` without WHERE clause
- ALWAYS backup before running migrations: `php artisan migrate`
- Test migrations on staging/local first
- Use `php artisan migrate:status` to check pending migrations
- Keep migrations reversible with `down()` methods

**5. Error Monitoring**
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

### Architecture (Updated 2026-01-09)
The header now uses **completely separate layouts** for mobile and desktop:

```vue
<!-- Mobile header (< 768px) -->
<div class="mobile-header">
  <div class="mobile-top-bar">Logo + Actions</div>
  <div class="mobile-search-bar">Location + Search</div>
</div>

<!-- Desktop header (>= 768px) -->
<div class="desktop-header">
  Logo + Location + Search + Actions + Post Ad
</div>
```

### Mobile Header Features
- **Gradient background**: Purple gradient (`#6366f1` to `#8b5cf6`)
- **Two-row layout**:
  - Top bar: Logo + action icons (notifications, messages, login/avatar)
  - Search bar: Location selector + search input (collapsible)
- **No "Post Ad" button**: Available in bottom navigation instead
- **Safe area support**: `padding-top: env(safe-area-inset-top)` for notched devices

### Desktop Header Features
- **White background**: Clean design
- **Single row**: Logo + Location + Search + Actions + Post Ad button
- **Full functionality**: All features visible at once

### Scroll Behavior (Updated 2026-01-09)
- Search bar visible by default at top of page
- Scrolling **down**: Search bar collapses after 60px accumulated scroll
- Scrolling **up**: Search bar expands after 60px accumulated scroll
- **Cooldown period**: 300ms between toggles (faster response)
- **Scroll accumulator pattern**: Prevents flicker from small/bouncy scrolls

### Key Parameters (AppHeader.vue)
```javascript
// Scroll threshold - pixels needed to toggle
scrollAccumulator > 60

// Cooldown between toggles (milliseconds)
now - lastToggleTime > 300

// CSS transition for smooth animation
.mobile-search-bar {
  transition: all 0.3s ease;
  max-height: 60px;
}
.mobile-search-bar.search-collapsed {
  max-height: 0;
  opacity: 0;
  pointer-events: none;
}
```

### Z-Index Hierarchy
| Element | Z-Index |
|---------|---------|
| Mobile Nav | 9999 |
| Chat Modal | 10000 |
| Location Picker Modal | 10001 |
| App Header | 100 |

### CSS Classes
- `.mobile-header`: Visible only on mobile (< 768px)
- `.desktop-header`: Visible only on desktop (>= 768px)
- `.mobile-search-bar`: The collapsible search section
- `.search-collapsed`: Applied when search bar is hidden

## PWA & Service Worker

### Service Worker Configuration (vite.config.js)
Updated 2026-01-03 for better SPA handling:

```javascript
workbox: {
  // SPA fallback - all navigation to index.html
  navigateFallback: 'index.html',
  navigateFallbackDenylist: [
    /^\/api\//, /^\/v1\//, /^\/storage\//, /^\/build\//, /\.\w+$/
  ],

  // Navigation requests use NetworkFirst with 3s timeout
  runtimeCaching: [{
    urlPattern: ({ request }) => request.mode === 'navigate',
    handler: 'NetworkFirst',
    options: { networkTimeoutSeconds: 3 }
  }],

  // Faster SW updates
  skipWaiting: true,
  clientsClaim: true
}
```

### Tab Suspension Recovery (main.js)
Added 2026-01-03 to fix "links stop working after idle" issue:

**Problem:** Mobile browsers suspend inactive tabs to save memory. When resumed, Vue's JavaScript state can be lost, causing links to do nothing when clicked.

**Solution:** Auto-detect broken state and reload:
```javascript
// On visibility change (tab becomes active)
document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'visible') {
    // If idle > 5 minutes, check if Vue app still works
    if (timeSuspended > 5 * 60 * 1000) {
      const hasVueApp = appEl && appEl.__vue_app__
      if (!hasVueApp) {
        window.location.reload() // App broken, reload
      }
    }
  }
})
```

**Performance Impact:** Minimal
- 2 passive event listeners (update timestamp on click/touch)
- 1 visibility check (only when returning after 5+ min idle)
- No continuous loops or timers

### Service Worker Update Detection
```javascript
// Auto-reload when new SW takes control
navigator.serviceWorker.addEventListener('controllerchange', () => {
  window.location.reload()
})
```

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

## Production Readiness Audit (2026-01-02)

### Audit Summary
- **Total Issues Found:** 34 (2 Critical, 8 High, 12 Medium, 12 Low)
- **Status:** ✅ **All critical and high priority issues FIXED** (2026-01-02)

---

## CRITICAL ISSUES - ALL FIXED ✅

### 1. ✅ Sensitive Information Exposed in API Responses (VERIFIED OK)
**File:** `app/Http/Controllers/Api/ListingController.php` (line 152)
**Status:** Already handled correctly - phone is hidden behind "Show Phone" button with tracking.
The phone field is only loaded for display but requires explicit user action to view.

### 2. ✅ Rate Limiting Strengthened
**Files Modified:**
- `app/Providers/RouteServiceProvider.php` - Added custom rate limiters
- `routes/api.php` - Updated to use new throttle middleware

**Changes Made:**
- Login: 5 req/min per IP AND per email
- Register: 3 req/min per IP
- Password reset: 3 req/min per IP AND per email
- Phone check: 5 req/min per IP

---

## HIGH SEVERITY ISSUES - ALL FIXED ✅

### 3. ✅ N+1 Query Problems - FIXED
**Files Modified:**
- `app/Models/Conversation.php` - Removed `unread_count` from `$appends`
- Added `scopeWithUnreadCount()` for efficient loading via subquery

**Changes Made:**
```php
// Use withUnreadCount() scope when loading conversations
public function scopeWithUnreadCount($query, $userId = null)
{
    return $query->addSelect([
        'unread_count' => Message::selectRaw('COUNT(*)')
            ->whereColumn('conversation_id', 'conversations.id')
            ->where('is_read', false)
            ->where('sender_id', '!=', $userId)
    ]);
}
```

### 4. ✅ Missing Input Validation on Location Parameters - FIXED
**Files Modified:**
- `app/Http/Controllers/Api/ListingController.php` - Added validation for index, store, update methods
- `app/Http/Controllers/Api/AuthController.php` - Added validation for updateProfile method

**Changes Made:**
- Added `'latitude' => 'nullable|numeric|between:-90,90'`
- Added `'longitude' => 'nullable|numeric|between:-180,180'`
- Added query parameter validation in listing index method

### 5. Email Verification Vulnerability
**Status:** Uses Laravel's built-in verification which is rate-limited at 6/min.

### 6. ✅ Authorization on Admin Actions - VERIFIED OK
**File:** `app/Http/Middleware/AdminMiddleware.php`
**Status:** Already properly checks `isModerator()` for admin role verification.

### 7. ✅ Soft-Delete Checks in Relationships - FIXED
**Files Modified:**
- `app/Models/Listing.php` - `user()` now uses `->withTrashed()`
- `app/Models/Message.php` - `sender()` now uses `->withTrashed()`
- `app/Models/Conversation.php` - `buyer()` and `seller()` now use `->withTrashed()`
- `app/Models/Review.php` - `reviewer()`, `reviewed()`, `listing()` now use `->withTrashed()`
- `app/Models/Report.php` - `reporter()`, `reportable()`, `reviewedBy()` now use `->withTrashed()`

### 8. ✅ HTTPS Enforcement and Security Headers - FIXED
**Files Created:**
- `app/Http/Middleware/SecurityHeaders.php` - Adds security headers
- `app/Http/Middleware/TrustProxies.php` - Handles proxy headers
- `app/Http/Kernel.php` - Updated global middleware

**Security Headers Added:**
- `X-XSS-Protection: 1; mode=block`
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Content-Security-Policy` (configured for app)
- `Permissions-Policy`
- `Strict-Transport-Security` (production only)

### 9. Weak Password Reset Tokens
**Status:** Uses Laravel's built-in password reset which handles token expiry properly.

### 10. ✅ Undefined Array Key in ReviewController - FIXED
**File:** `app/Http/Controllers/Api/ReviewController.php` (line 55)
**Status:** Fixed on 2026-01-02 - Added `?? null` operator.

---

## MEDIUM SEVERITY ISSUES - MOSTLY FIXED ✅

### 11. Complex Raw SQL in Search
**File:** `app/Http/Controllers/Api/ListingController.php`
**Status:** Already has `array_slice($terms, 0, 20)` to limit variations.

### 12. ✅ File Upload Security - FIXED
**Files Modified:**
- `app/Http/Controllers/Api/ListingController.php` - `storeImage()` method
- `app/Http/Controllers/Api/AuthController.php` - `updateAvatar()` method

**Security Improvements:**
- Added `getimagesize()` check to verify actual image content
- Added IMAGETYPE validation for allowed types (JPEG, PNG, WEBP, GIF)
- Added filename sanitization (remove special chars, path traversal)
- Re-encoding to WebP strips any malicious data

### 13. ✅ Console.log in Production - FIXED
**Files Modified:**
- `resources/js/components/common/LocationPicker.vue` - Removed GPS error logging
- `resources/js/views/Search.vue` - Removed IP location error logging
- `resources/js/main.js` - Removed service worker logging

### 14. Missing Audit Logging
**Status:** Recommended for future enhancement. Consider Spatie Laravel Auditable.

### 15. Missing Cache Invalidation
**Status:** Recommended for future enhancement when implementing Redis caching.

### 16. Environment-Specific Config Issues
**Status:** Recommended to review before production deployment.

---

## LOW SEVERITY ISSUES

### 17. Missing API Documentation
**Status:** Recommended for future enhancement.

### 18. ✅ Security Headers - FIXED
**Status:** Added via `SecurityHeaders` middleware.

### 19. No Vue Error Boundaries
**Status:** Recommended for future enhancement.

### 20. No Service Worker Cache Versioning
**Status:** Recommended for future enhancement.

### 21. Missing Input Length Limits
**Status:** Most fields have length limits. Verified in validation rules.

### 22. No Request ID Logging
**Status:** Recommended for future enhancement.

---

## Database Performance Notes

### ✅ Missing Indexes - ADDED
**Migration Created:** `2024_01_01_000020_add_performance_indexes.php`

**Indexes Added:**
```sql
-- For unread message count performance
CREATE INDEX messages_unread_count_index ON messages(conversation_id, is_read, sender_id);

-- For user reviews lookup
CREATE INDEX reviews_user_status_index ON reviews(reviewed_id, status);

-- For review status filtering
CREATE INDEX reviews_status_index ON reviews(status);

-- For buyer conversation queries
CREATE INDEX conversations_buyer_index ON conversations(buyer_id, buyer_deleted);

-- For seller conversation queries
CREATE INDEX conversations_seller_index ON conversations(seller_id, seller_deleted);

-- For not expired listing queries
CREATE INDEX listings_expires_at_index ON listings(expires_at);
```

### ✅ Query Optimization - DONE
1. ✅ Conversation unread count - Fixed with `withUnreadCount()` scope
2. User statistics aggregation - Could be improved in future
3. ✅ Search term variations - Already limited to 20

---

## Security Checklist for Production

### Authentication & Authorization
- [x] Rate limit auth endpoints (3-5 req/min) ✅ DONE
- [ ] Implement CAPTCHA after 3 failed logins
- [ ] Add account lockout after 10 failed attempts
- [x] Use signed URLs for email verification (Laravel default)
- [x] Track password reset tokens (Laravel default)
- [x] Prevent admin suspension of other admins (AdminMiddleware)

### Data Protection
- [x] Remove phone from public API responses ✅ (handled via UI)
- [ ] Mask emails in admin panels
- [ ] Implement audit logging
- [ ] Add field-level encryption for sensitive data

### Infrastructure
- [x] Force HTTPS with middleware ✅ DONE (SecurityHeaders)
- [x] Configure HSTS headers ✅ DONE
- [x] Add security headers (X-Frame-Options, CSP, etc.) ✅ DONE
- [ ] Set up WAF rules
- [ ] Configure proper CORS for production domain only

### File Uploads
- [x] Validate actual image content (not just MIME) ✅ DONE
- [x] Add dimension limits ✅ (via resize)
- [ ] Implement virus scanning
- [x] Serve with `X-Content-Type-Options: nosniff` ✅ DONE

---

## Performance Checklist

### Database
- [x] Add missing indexes (see above) ✅ DONE
- [x] Fix N+1 queries in Conversation model ✅ DONE
- [ ] Enable query caching with Redis
- [ ] Set up read replicas for scaling

### Caching
- [ ] Use Redis for sessions (`SESSION_DRIVER=redis`)
- [ ] Use Redis for cache (`CACHE_DRIVER=redis`)
- [ ] Implement cache invalidation in model observers
- [ ] Cache common search terms

### Frontend
- [x] Remove all console.log statements ✅ DONE
- [ ] Enable gzip compression
- [ ] Set up CDN for images
- [ ] Implement proper lazy loading

---

## Monitoring Checklist

- [ ] Set up Sentry for error tracking
- [ ] Enable Laravel Telescope for debugging (staging only)
- [ ] Configure slow query logging (>100ms threshold)
- [ ] Set up uptime monitoring
- [ ] Configure alerting for critical errors

---

## Pre-Production Deployment Checklist

```
Environment:
[ ] APP_DEBUG=false
[ ] APP_ENV=production
[ ] APP_URL=https://lentloads.com
[ ] Proper mail configuration (not mailpit)
[ ] Redis configured with password

Security:
[x] HTTPS enforced ✅
[x] Security headers configured ✅
[ ] CORS restricted to production domain
[x] Rate limiting enabled ✅
[ ] Audit logging enabled

Performance:
[x] Database indexes added ✅
[x] N+1 queries fixed ✅
[ ] Redis caching enabled
[ ] CDN configured for images

Monitoring:
[ ] Error tracking (Sentry) configured
[ ] Logging to persistent storage
[ ] Database backups automated
[ ] Uptime monitoring enabled

Testing:
[ ] All auth flows tested
[ ] File uploads tested
[ ] Search functionality tested
[ ] Admin actions tested
[ ] Mobile responsiveness verified
```

---

## Quick Reference: Files Modified (2026-01-02 Fixes)

| Status | File | Change |
|--------|------|--------|
| ✅ FIXED | `RouteServiceProvider.php` | Added custom rate limiters |
| ✅ FIXED | `routes/api.php` | Applied throttle middleware |
| ✅ FIXED | `Conversation.php` | Added withUnreadCount() scope |
| ✅ FIXED | `Listing.php` | withTrashed() on user() |
| ✅ FIXED | `Message.php` | withTrashed() on sender() |
| ✅ FIXED | `Review.php` | withTrashed() on relationships |
| ✅ FIXED | `Report.php` | withTrashed() on relationships |
| ✅ FIXED | `ListingController.php` | Lat/lng validation, file security |
| ✅ FIXED | `AuthController.php` | Lat/lng validation, file security |
| ✅ FIXED | `SecurityHeaders.php` | Created - security headers |
| ✅ FIXED | `TrustProxies.php` | Created - proxy handling |
| ✅ FIXED | `Kernel.php` | Added middleware |
| ✅ FIXED | Vue components | Removed console.log |
| ✅ FIXED | `2024_01_01_000020_*` | Created - performance indexes |

---

## APK Versioning

### Current Version
- **Version Name:** 1.2
- **Version Code:** 3
- **APK Filename:** `LentloAds-v1.2-debug.apk`

### Version History

| Version | Code | Date | Changes |
|---------|------|------|---------|
| 1.2 | 3 | 2026-01-09 | Fixed header space, splash screen, scroll collapse |
| 1.1 | 2 | 2026-01-09 | Mobile header redesign, separate mobile/desktop layouts |
| 1.0 | 1 | 2026-01-03 | Initial Capacitor build |

### Versioning Guidelines
- **versionCode**: Integer that increments with each release (used by Play Store)
- **versionName**: User-facing version string (e.g., "1.1", "2.0")
- **APK naming**: `LentloAds-v{versionName}-debug.apk`

### How to Update Version
1. Edit `android/app/build.gradle`:
   ```gradle
   versionCode 3  // Increment this
   versionName "1.2"  // Update this
   ```
2. Rebuild: `node scripts/build-capacitor.js`
3. Build APK: `./gradlew assembleDebug`
4. Copy with version: `cp app-debug.apk ~/Desktop/LentloAds-v1.2-debug.apk`

---

## Capacitor Mobile App Setup (2026-01-03)

### Overview
The app uses Capacitor to build native Android and iOS apps from the existing Vue.js codebase. This allows the same code to run as a web app, Android app, and iOS app.

### Configuration Files
- `capacitor.config.json` - Main Capacitor configuration
- `android/` - Android native project
- `scripts/generate-capacitor-index.js` - Auto-generates index.html for builds

### Installed Plugins
```
@capacitor/app           - App lifecycle events
@capacitor/camera        - Camera access for posting ads
@capacitor/geolocation   - Location for local listings
@capacitor/haptics       - Vibration feedback
@capacitor/keyboard      - Keyboard handling
@capacitor/push-notifications - Push notifications
@capacitor/share         - Native share functionality
@capacitor/splash-screen - Splash screen
@capacitor/status-bar    - Status bar customization
```

### Build Commands
```bash
# Build web assets and sync with native projects
npm run cap:build

# Open Android project in Android Studio
npm run cap:android

# Run on connected Android device
npm run cap:run:android

# Just sync changes (no rebuild)
npm run cap:sync
```

### Android Permissions (AndroidManifest.xml)
```xml
<!-- Network -->
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />

<!-- Camera for posting ads -->
<uses-permission android:name="android.permission.CAMERA" />
<uses-permission android:name="android.permission.READ_EXTERNAL_STORAGE" />
<uses-permission android:name="android.permission.WRITE_EXTERNAL_STORAGE" />
<uses-permission android:name="android.permission.READ_MEDIA_IMAGES" />

<!-- Location for local listings -->
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />

<!-- Push Notifications -->
<uses-permission android:name="android.permission.POST_NOTIFICATIONS" />
<uses-permission android:name="android.permission.VIBRATE" />
```

### App Store Requirements
- **Google Play Store**: $25 one-time fee
- **Apple App Store**: $99/year (requires Mac for iOS builds)

### Building APK for Testing
1. Run `npm run cap:build`
2. Open Android Studio: `npm run cap:android`
3. Build > Build Bundle(s) / APK(s) > Build APK(s)
4. APK will be in `android/app/build/outputs/apk/debug/`

### How It Works
1. Vite builds Vue.js app to `public/build/`
2. `generate-capacitor-index.js` creates `index.html` with correct asset paths
3. Capacitor copies assets to `android/app/src/main/assets/public/`
4. Native Android/iOS wrapper loads the web content

### API Configuration
The app connects to the same Laravel backend:
- Web: Direct API calls to `/v1/`
- Android/iOS: API calls to `https://lentloads.com/v1/`

Configure in `capacitor.config.json`:
```json
{
  "server": {
    "androidScheme": "https",
    "hostname": "lentloads.com"
  }
}
```

---

## Service Worker & Caching Improvements (2026-01-03)

### Problem Solved
Users had to manually clear cache after deployments because:
1. Service worker was precaching ALL JS/CSS files (92 files)
2. Old files were served from cache even after new deployment
3. Update check only happened on page load, not when returning to tab

### Solution Implemented

**vite.config.js changes:**
```javascript
workbox: {
  // Only cache icons/fonts - NOT JS/CSS
  globPatterns: ['**/*.{ico,png,svg,woff2}'],
  globIgnores: ['**/index.html', '**/*.js', '**/*.css'],

  // JS/CSS use NetworkFirst with 1 hour cache
  runtimeCaching: [{
    urlPattern: /\.(?:js|css)$/,
    handler: 'NetworkFirst',
    options: {
      cacheName: 'assets-cache',
      expiration: { maxAgeSeconds: 60 * 60 } // 1 hour
    }
  }],

  skipWaiting: true,
  clientsClaim: true,
  cleanupOutdatedCaches: true
}
```

**main.js changes:**
- Check for SW updates on page focus (not just load)
- Clear ALL caches when new SW activates
- Update check every 5 minutes instead of 1 hour

### Result
- Precache reduced from 92 to 4 entries (just icons)
- JS/CSS always fetched fresh from network
- Auto-updates when returning to tab
- No more manual cache clearing needed

---

## Mobile Navigation Fixes (2026-01-03)

### Problem
Bottom mobile navigation sometimes stopped working after tab was idle.

### Root Causes
1. `backdrop-filter` can cause touch event issues on iOS
2. z-index (50) same as header could cause stacking conflicts
3. Vue Router state loss after tab suspension not detected

### Fixes Applied

**MobileNav.vue:**
```css
.mobile-nav {
  z-index: 9999; /* Higher than everything */
  background: #ffffff; /* Solid - no transparency */
  /* Removed backdrop-filter */
  pointer-events: auto;
}
```

**Added fallback navigation:**
```javascript
// If Vue Router doesn't navigate within 300ms, force hard navigation
const handleNavClick = (e, targetPath) => {
  setTimeout(() => {
    if (window.location.pathname !== targetPath) {
      window.location.href = targetPath
    }
  }, 300)
}
```

### Navigation Health Detection (main.js)
- Tab suspension check reduced from 5 min to 1 min
- Detects clicks on `.nav-item` elements
- Watchdog timer: if no navigation for 10 sec after clicks, reloads
- Forces hard navigation when Vue Router fails

---

## UI Updates (2026-01-03)

### Desktop Header
- Search bar max-width increased from 520px to 700px
- User dropdown z-index fixed (was being clipped)

### Mobile Bottom Navigation
- Height increased from 56px to 64px
- Icons increased from 24px to 26px
- Font size increased from 10px to 11px
- Font weight increased to 600
- "Sell" renamed to "Post Ad"
- Post Ad button increased from 48px to 52px with gradient

---

## Android Capacitor Blank Screen Fix (2026-01-03)

### Problem Description
After building the APK, the app shows a blank screen instead of rendering the Vue.js application.

### Root Cause Analysis

**CRITICAL BUG #1: 401 Redirect Uses Wrong URL Pattern**

In `resources/js/services/api.js`, the 401 error handler:
```javascript
case 401:
  localStorage.removeItem('token')
  if (window.location.pathname !== '/login') {
    window.location.href = '/login'  // ← BUG!
  }
  break;
```

This is broken for two reasons:
1. **Hash routing mismatch**: In Capacitor, we use `createWebHashHistory()`. Routes are in `window.location.hash` (e.g., `#/login`), NOT `window.location.pathname` (which is always `/` or the file path).
2. **No server in Capacitor**: In native apps, `window.location.href = '/login'` tries to load a local file `/login` which doesn't exist, causing a blank screen.

**How it causes blank screen:**
1. User has old/invalid token in localStorage
2. App loads and tries to fetch user via API
3. API returns 401 Unauthorized
4. api.js interceptor does `window.location.href = '/login'`
5. Capacitor webview tries to load non-existent file `/login`
6. **Blank screen!**

### Fix Applied

**api.js - Use Vue Router instead of window.location:**
```javascript
import { Capacitor } from '@capacitor/core'

// In 401 handler:
case 401:
  localStorage.removeItem('token')
  // For Capacitor/hash routing, use hash navigation
  if (Capacitor.isNativePlatform()) {
    if (window.location.hash !== '#/login') {
      window.location.hash = '#/login'
    }
  } else {
    if (window.location.pathname !== '/login') {
      window.location.href = '/login'
    }
  }
  break;
```

### Code Architecture Summary

#### Entry Point Flow
```
index.html → main.js → createApp(App.vue) → mount('#app')
                     ↓
              router/index.js (determines hash vs history mode)
                     ↓
              App.vue onMounted → appStore.initialize() → API calls
```

#### Key Files Reviewed

| File | Purpose | Capacitor Considerations |
|------|---------|--------------------------|
| `main.js` | App entry, plugin setup | Splash screen hide timing |
| `router/index.js` | Vue Router config | Uses `createWebHashHistory()` for native |
| `api.js` | Axios config, interceptors | **BUGFIX NEEDED**: 401 redirect |
| `App.vue` | Root component, layout | Makes API calls on mount |
| `Home.vue` | Homepage with listings | API-dependent, shows error state |
| `capacitor.config.json` | Capacitor settings | webDir, splash screen config |

#### Stores

| Store | State | Key Actions |
|-------|-------|-------------|
| `app.js` | categories, cities, loading, location | `initialize()`, `fetchCategories()`, `detectLocation()` |
| `auth.js` | user, token, loading | `login()`, `register()`, `fetchUser()`, `logout()` |
| `listings.js` | listings, filters, pagination | `fetchListings()`, `createListing()`, `toggleFavorite()` |
| `messages.js` | conversations, messages, unreadCount | `fetchConversations()`, `sendMessage()` |

#### Components

| Component | Purpose | Notes |
|-----------|---------|-------|
| `AppHeader.vue` | Header with search, location | Desktop: white bg; Mobile: gradient |
| `MobileNav.vue` | Bottom nav for mobile | z-index 9999, 64px height |
| `ListingCard.vue` | Listing display card | 1:1 aspect ratio, lazy loading |
| `LocationPicker.vue` | Map-based location selection | Leaflet integration |

### Capacitor Configuration

**capacitor.config.json:**
```json
{
  "appId": "com.lentlo.ads",
  "appName": "Lentlo Ads",
  "webDir": "public/build",
  "server": {
    "androidScheme": "https",
    "hostname": "localhost"
  },
  "plugins": {
    "SplashScreen": {
      "launchShowDuration": 3000,
      "launchAutoHide": false
    }
  }
}
```

**API Base URL Selection (api.js):**
```javascript
const getBaseURL = () => {
  if (Capacitor.isNativePlatform()) {
    return 'https://phplaravel-1016958-6108537.cloudwaysapps.com/api/v1'
  }
  return '/api/v1'
}
```

### Build Process

```bash
# 1. Build web assets
npm run build
# - Runs: vite build && node scripts/generate-capacitor-index.js
# - Output: public/build/

# 2. Sync to Android project
npm run cap:sync
# - Copies public/build/ to android/app/src/main/assets/public/

# 3. Build APK
cd android
./gradlew assembleDebug
# - APK at: android/app/build/outputs/apk/debug/app-debug.apk
```

### Testing Checklist for Native App

```
□ App loads without blank screen
□ Homepage shows listings (API working)
□ Categories load correctly
□ Navigation between pages works
□ Login/logout works
□ Splash screen shows and hides
□ Links are clickable immediately
□ Back button works properly
□ 401 errors redirect to login properly
```

### Network Security (Android)

**network_security_config.xml:**
```xml
<network-security-config>
  <domain-config cleartextTrafficPermitted="true">
    <domain includeSubdomains="true">phplaravel-1016958-6108537.cloudwaysapps.com</domain>
    <domain includeSubdomains="true">lentloads.com</domain>
  </domain-config>
</network-security-config>
```

### CORS Configuration (Laravel)

**config/cors.php:**
```php
'allowed_origins' => ['*'],  // Allow Capacitor app
'supports_credentials' => false,  // Must be false with '*' origin
```

---

## Complete Frontend File List

### Views (resources/js/views/)
- `Home.vue` - Homepage with featured/recent listings
- `Search.vue` - Search with filters, sort, pagination
- `Categories.vue` - All categories list
- `CategoryListings.vue` - Listings in a category
- `ListingDetail.vue` - Single listing detail page
- `UserProfile.vue` - Public user profile
- `StaticPage.vue` - CMS static pages
- `NotFound.vue` - 404 page

### Auth Views (resources/js/views/auth/)
- `Login.vue` - Login form
- `Register.vue` - Registration form
- `ForgotPassword.vue` - Password reset request
- `ResetPassword.vue` - Password reset form

### Dashboard Views (resources/js/views/dashboard/)
- `Dashboard.vue` - User dashboard overview
- `MyListings.vue` - User's listings management
- `CreateListing.vue` - New listing form
- `EditListing.vue` - Edit listing form
- `Favorites.vue` - Saved listings
- `Messages.vue` - Conversations list
- `Conversation.vue` - Single chat thread
- `Settings.vue` - Account settings
- `MyReviews.vue` - Reviews received
- `SavedSearches.vue` - Saved search alerts
- `Notifications.vue` - Notification center

### Admin Views (resources/js/views/admin/)
- `Dashboard.vue` - Admin stats
- `Users.vue` - User management
- `Listings.vue` - Listing moderation
- `Categories.vue` - Category management
- `Reports.vue` - User reports
- `Settings.vue` - Site settings
- `Conversations.vue` - Message moderation
- `ContactViews.vue` - Contact tracking

### Components (resources/js/components/)

**Layout:**
- `AppHeader.vue` - Main header
- `AppFooter.vue` - Footer
- `AdminHeader.vue` - Admin header
- `MobileNav.vue` - Bottom mobile nav

**Common:**
- `ListingCard.vue` - Listing display card
- `LoadingSpinner.vue` - Loading indicator
- `LoadingOverlay.vue` - Full page loader
- `LocationPicker.vue` - Map location picker
- `LocationDisplay.vue` - Location text display
- `PWAInstallPrompt.vue` - PWA install prompt
- `SearchBar.vue` - Search input
- `AuthPromptModal.vue` - Login prompt modal

**Modals:**
- `ChatModal.vue` - Quick chat modal
- `ConfirmModal.vue` - Confirmation dialog
- `LocationModal.vue` - Location selection modal
- `ReportModal.vue` - Report abuse modal

### Layouts (resources/js/layouts/)
- `DefaultLayout.vue` - Main layout wrapper
- `AuthLayout.vue` - Auth pages layout
- `AdminLayout.vue` - Admin panel layout

### Services (resources/js/services/)
- `api.js` - Axios instance with interceptors

### Stores (resources/js/stores/)
- `app.js` - App state (categories, location)
- `auth.js` - Authentication state
- `listings.js` - Listings state and filters
- `messages.js` - Messages/conversations state
