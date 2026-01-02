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

## Production Readiness Audit (2026-01-02)

### Audit Summary
- **Total Issues Found:** 34 (2 Critical, 8 High, 12 Medium, 12 Low)
- **Status:** Requires fixes before production deployment

---

## CRITICAL ISSUES (Must Fix Before Production)

### 1. Sensitive Information Exposed in API Responses
**File:** `app/Http/Controllers/Api/ListingController.php` (line 152)
**Issue:** Phone numbers exposed in public listing API:
```php
'user:id,name,phone,avatar,city,rating,total_reviews,created_at,is_verified_seller',
```
**Fix:** Remove `phone` from public API responses. Only show to authenticated users who initiate contact.

### 2. Rate Limiting Too Weak on Auth Endpoints
**File:** `routes/api.php` (lines 35-45)
**Issues:**
- `check-phone` endpoint: 10 req/min (should be 3-5)
- Login/Register: 5 req/min but no account enumeration protection
- No CAPTCHA after failed attempts

**Fix:** Implement stricter throttling, add exponential backoff, implement CAPTCHA.

---

## HIGH SEVERITY ISSUES

### 3. N+1 Query Problems
**Files:**
- `app/Http/Controllers/Api/AuthController.php` (lines 116-127)
- `app/Models/Conversation.php` (lines 80-85) - `unread_count` accessor

**Issue:** The `getUnreadCountAttribute()` executes a query every time accessed in a loop.
```php
public function getUnreadCountAttribute(): int
{
    return $this->messages()
        ->where('is_read', false)
        ->where('sender_id', '!=', auth()->id())
        ->count();  // Query in loop!
}
```
**Fix:** Use `withCount()` or cache unread count in database column.

### 4. Missing Input Validation on Location Parameters
**File:** `app/Http/Controllers/Api/ListingController.php` (lines 94-107)
**Issue:** Latitude/longitude not bounds-checked before Haversine calculation.
**Fix:** Add validation: `latitude|numeric|between:-90,90`, `longitude|numeric|between:-180,180`

### 5. Email Verification Vulnerability
**File:** `app/Http/Controllers/Api/AuthController.php` (lines 243-256)
**Issue:** Verification endpoint accepts any user ID without rate limiting per email.
**Fix:** Add rate limiting, use signed URLs, log all verification attempts.

### 6. Missing Authorization on Admin Actions
**File:** `app/Http/Controllers/Api/Admin/AdminReportController.php` (lines 95-121)
**Issue:** `takeAction()` can suspend users without checking if target is admin/super-admin.
**Fix:** Add policy checks, prevent suspension of admins, add audit logging.

### 7. Soft-Delete Not Checked in Relationships
**File:** `app/Models/Conversation.php` (lines 88-95)
**Issue:** `forUser()` scope allows viewing conversations of soft-deleted users.
**Fix:** Add `->whereHas('buyer', fn($q) => $q->withoutTrashed())`.

### 8. Missing HTTPS Enforcement
**Issue:** No middleware forcing HTTPS, no HSTS headers configured.
**Fix:** Add HTTPS middleware for production, configure HSTS, ensure `APP_URL=https://...`.

### 9. Weak Password Reset Tokens
**File:** `app/Http/Controllers/Api/AuthController.php` (lines 218-241)
**Issue:** No tracking of used tokens, no logging of reset requests.
**Fix:** Track used tokens, log all password resets with IP/timestamp, notify user.

### 10. Undefined Array Key in ReviewController (FIXED)
**File:** `app/Http/Controllers/Api/ReviewController.php` (line 55)
**Issue:** `$validated['listing_id']` accessed without null check.
**Status:** ✅ Fixed on 2026-01-02 - Added `?? null` operator.

---

## MEDIUM SEVERITY ISSUES

### 11. Complex Raw SQL in Search
**File:** `app/Http/Controllers/Api/ListingController.php` (lines 44-51)
**Issue:** SOUNDEX queries with complex substring operations could impact performance.
**Fix:** Consider full-text search, limit search term variations to 10.

### 12. File Upload Security
**File:** `app/Http/Controllers/Api/AuthController.php` (lines 152-181)
**Issues:**
- Only MIME validation (can be spoofed)
- No image dimension validation
- No virus scanning
**Fix:** Validate actual image content, add dimension limits, implement ClamAV.

### 13. Console.log in Production
**Files:** Multiple Vue components (15+ files)
**Issue:** Debug statements logged in production.
**Fix:** Remove all console.log, use Sentry/LogRocket for logging.

### 14. Missing Audit Logging
**Issue:** No logging for admin actions, sensitive user actions, data modifications.
**Fix:** Implement Spatie Laravel Auditable or custom audit logging.

### 15. Missing Cache Invalidation
**Files:** AuthController, ListingController, FavoriteController
**Issue:** Cache not invalidated on updates.
**Fix:** Add cache invalidation in model observers.

### 16. Environment-Specific Config Issues
**File:** `.env.example`
**Issues:**
- CORS hardcoded with localhost
- Redis uses no password
- No separate production template
**Fix:** Create `.env.production.example`, use secrets manager.

---

## LOW SEVERITY ISSUES

### 17. Missing API Documentation
**Fix:** Generate OpenAPI/Swagger documentation.

### 18. Missing Security Headers
**Fix:** Add `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, CSP headers.

### 19. No Vue Error Boundaries
**Fix:** Add ErrorBoundary component for graceful error handling.

### 20. No Service Worker Cache Versioning
**Fix:** Implement cache busting strategy for PWA updates.

### 21. Missing Input Length Limits
**Fix:** Add `max:1000` or `max:5000` to all text fields.

### 22. No Request ID Logging
**Fix:** Add request ID header for debugging distributed issues.

---

## Database Performance Notes

### Missing Indexes (Add These)
```sql
-- For unread message count performance
CREATE INDEX idx_messages_conversation_read ON messages(conversation_id, is_read);

-- For conversation sorting
CREATE INDEX idx_conversations_created ON conversations(created_at);

-- For listing sorting
CREATE INDEX idx_listings_created ON listings(created_at);

-- For user analytics
CREATE INDEX idx_users_created ON users(created_at);
```

### Query Optimization Needed
1. Conversation unread count - cache in column or use withCount()
2. User statistics aggregation - use single query with subqueries
3. Search term variations - limit to prevent query explosion

---

## Security Checklist for Production

### Authentication & Authorization
- [ ] Rate limit auth endpoints (3-5 req/min)
- [ ] Implement CAPTCHA after 3 failed logins
- [ ] Add account lockout after 10 failed attempts
- [ ] Use signed URLs for email verification
- [ ] Track password reset tokens
- [ ] Prevent admin suspension of other admins

### Data Protection
- [ ] Remove phone from public API responses
- [ ] Mask emails in admin panels
- [ ] Implement audit logging
- [ ] Add field-level encryption for sensitive data

### Infrastructure
- [ ] Force HTTPS with middleware
- [ ] Configure HSTS headers
- [ ] Add security headers (X-Frame-Options, CSP, etc.)
- [ ] Set up WAF rules
- [ ] Configure proper CORS for production domain only

### File Uploads
- [ ] Validate actual image content (not just MIME)
- [ ] Add dimension limits
- [ ] Implement virus scanning
- [ ] Serve with `X-Content-Type-Options: nosniff`

---

## Performance Checklist

### Database
- [ ] Add missing indexes (see above)
- [ ] Fix N+1 queries in Conversation model
- [ ] Enable query caching with Redis
- [ ] Set up read replicas for scaling

### Caching
- [ ] Use Redis for sessions (`SESSION_DRIVER=redis`)
- [ ] Use Redis for cache (`CACHE_DRIVER=redis`)
- [ ] Implement cache invalidation in model observers
- [ ] Cache common search terms

### Frontend
- [ ] Remove all console.log statements
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
[ ] HTTPS enforced
[ ] Security headers configured
[ ] CORS restricted to production domain
[ ] Rate limiting enabled
[ ] Audit logging enabled

Performance:
[ ] Database indexes added
[ ] N+1 queries fixed
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

## Quick Reference: Files That Need Attention

| Priority | File | Issue |
|----------|------|-------|
| CRITICAL | `ListingController.php:152` | Remove phone from API |
| CRITICAL | `routes/api.php:35-45` | Strengthen rate limiting |
| HIGH | `Conversation.php:80-85` | Fix N+1 in unread_count |
| HIGH | `AuthController.php:243-256` | Secure email verification |
| HIGH | `AdminReportController.php:95-121` | Add authorization checks |
| MEDIUM | Vue components (15 files) | Remove console.log |
| MEDIUM | `AuthController.php:152-181` | Improve file upload security |
