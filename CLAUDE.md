# Lentloads Marketplace - Claude Code Documentation

> **IMPORTANT**: This file contains all project information, credentials, and instructions for Claude Code to automatically manage this project without asking the user for any information.

---

## Quick Start for Claude

When the user opens a new terminal session, Claude should:
1. Read this file first
2. Automatically connect to the server if deployment is needed
3. Remember all project context without asking questions

---

## Project Overview

| Field | Value |
|-------|-------|
| **Project Name** | Lentloads Marketplace |
| **Type** | OLX-like classifieds/marketplace platform |
| **Tech Stack** | Laravel 10 + Vue.js 3 + Tailwind CSS + Vite + PWA |
| **Local Path** | `/Users/abbas/Desktop/Lentloads` |
| **Live URL** | https://phplaravel-1016958-6108537.cloudwaysapps.com |
| **Owner** | Abbas (no coding knowledge - needs full assistance) |

---

## Server Credentials (Cloudways)

### SSH Access
```
Host: 139.59.24.36
Username: lentlo@12
Password: 789-=uioP
SSH Key: ~/.ssh/cloudways_rsa
```

### SSH Command (use this)
```bash
ssh -i ~/.ssh/cloudways_rsa -o StrictHostKeyChecking=no -l 'lentlo@12' 139.59.24.36
```

### Application Path on Server
```
/home/master/applications/bpadwztsjg/public_html
```

---

## Git Repository

| Field | Value |
|-------|-------|
| **Repository** | github.com:Lentlo/lentloads.git |
| **Branch** | main |
| **Remote** | origin |

---

## Deployment Process

### Automatic Deployment Steps (Claude should run these)

1. **Build locally:**
```bash
cd /Users/abbas/Desktop/Lentloads
npm run build
```

2. **Commit and push:**
```bash
git add .
git commit -m "Your commit message"
git push origin main
```

3. **Deploy to server:**
```bash
ssh -i ~/.ssh/cloudways_rsa -o StrictHostKeyChecking=no -l 'lentlo@12' 139.59.24.36 "cd /home/master/applications/bpadwztsjg/public_html && git checkout . && git pull origin main"
```

4. **Clear caches (if needed):**
```bash
ssh -i ~/.ssh/cloudways_rsa -o StrictHostKeyChecking=no -l 'lentlo@12' 139.59.24.36 "cd /home/master/applications/bpadwztsjg/public_html && php artisan optimize:clear"
```

5. **Clear OPcache (if PHP errors persist):**
```bash
curl -s 'https://phplaravel-1016958-6108537.cloudwaysapps.com/opcache-clear.php'
```

### IMPORTANT: Do NOT run `php artisan config:cache`
The config:cache command causes APP_KEY issues on this server. Leave config uncached.

---

## Project Structure

```
/Users/abbas/Desktop/Lentloads/
├── app/
│   ├── Http/Controllers/Api/    # API Controllers
│   └── Models/                  # Eloquent Models
├── resources/
│   └── js/
│       ├── components/          # Vue Components
│       │   ├── common/          # ListingCard.vue, etc.
│       │   └── layout/          # AppHeader.vue, AppFooter.vue
│       ├── views/               # Page Components (Home.vue, etc.)
│       ├── stores/              # Pinia Stores
│       └── assets/css/app.css   # Custom CSS
├── public/
│   └── build/                   # Compiled assets (committed to git)
├── tailwind.config.js           # Tailwind configuration
└── vite.config.js               # Vite configuration
```

---

## Current Design Style

**Theme: OLX India Style**
- Primary Color: Dark Teal (#002f34) - `primary-950`
- Accent Color: Cyan (#23e5db)
- Background: Light gray (#f3f4f6)
- Cards: White with subtle border, square images
- Header: Dark teal with white text
- Clean, minimal, professional look

---

## Database Info

- **Type**: MySQL
- **Connection**: Configured in `.env` on server
- **Test Data**: 16 listings, 8 categories, 1 user
- **Seeder**: `database/seeders/ListingSeeder.php`

---

## Key Files Modified (History)

### Design Updates (OLX Style)
- `resources/js/components/common/ListingCard.vue` - Square cards, minimal design
- `resources/js/components/layout/AppHeader.vue` - Dark teal header, SELL button
- `resources/js/components/layout/AppFooter.vue` - Dark teal footer
- `resources/js/views/Home.vue` - OLX-style search bar, horizontal categories
- `tailwind.config.js` - Teal color palette
- `resources/js/assets/css/app.css` - Custom styles

### Bug Fixes
- `app/Models/Listing.php` - Removed Scout, fixed placeholder images
- `app/Http/Controllers/Api/ListingController.php` - Accept slug or ID, fixed slug/ID matching bug
- `bootstrap/app.php` - Laravel 10 compatibility
- `public/index.php` - Laravel 10 compatibility
- Created missing models: Banner, City, State, Country, PushSubscription
- `app/Models/Category.php` - Removed `getTotalActiveListingsCountAttribute` accessor (caused issues)
- `app/Http/Controllers/Api/PageController.php` - Calculate category listing counts including children
- `app/Http/Controllers/Api/CategoryController.php` - Calculate category listing counts including children
- `resources/js/views/ListingDetail.vue` - Added route watcher for similar listings navigation

### Configuration
- `.gitignore` - Allow public/build folder
- `public/deploy.php` - Webhook for auto-deploy (currently broken, use SSH)
- `public/opcache-clear.php` - Clear OPcache utility

---

## Common Issues & Solutions

### 1. 500 Server Error after deployment
```bash
# Clear all caches
ssh -i ~/.ssh/cloudways_rsa -o StrictHostKeyChecking=no -l 'lentlo@12' 139.59.24.36 "cd /home/master/applications/bpadwztsjg/public_html && rm -rf bootstrap/cache/*.php && php artisan optimize:clear"

# Clear OPcache
curl -s 'https://phplaravel-1016958-6108537.cloudwaysapps.com/opcache-clear.php'
```

### 2. APP_KEY errors
DO NOT run `php artisan config:cache`. Just delete the cached config:
```bash
ssh -i ~/.ssh/cloudways_rsa -o StrictHostKeyChecking=no -l 'lentlo@12' 139.59.24.36 "cd /home/master/applications/bpadwztsjg/public_html && rm -f bootstrap/cache/config.php"
```

### 3. Git pull conflicts
```bash
ssh -i ~/.ssh/cloudways_rsa -o StrictHostKeyChecking=no -l 'lentlo@12' 139.59.24.36 "cd /home/master/applications/bpadwztsjg/public_html && git checkout . && git pull origin main"
```

### 4. Design not updating
- Run `npm run build` locally
- Commit and push the `public/build` folder
- Hard refresh browser: `Cmd + Shift + R`

### 5. Category counts showing 0 ads
- Listings are assigned to subcategories, not parent categories
- Parent category counts must include all children's listings
- Calculate in controller AFTER `load()` call (not before, or values get overwritten)
- DO NOT use model accessors for this - they cause N+1 queries and override set values
```php
// Correct pattern in controller:
$categories->load(['children' => fn($q) => $q->active()->limit(5)]);
foreach ($categories as $category) {
    $allChildIds = Category::where('parent_id', $category->id)->pluck('id')->toArray();
    $allIds = array_merge([$category->id], $allChildIds);
    $category->total_active_listings_count = Listing::whereIn('category_id', $allIds)
        ->where('status', 'active')->count();
}
```

### 6. Listing shows wrong content (slug starting with number)
- Slugs like "3-bhk-apartment..." were matching listing ID 3
- MySQL casts "3-bhk..." to integer 3 when comparing to ID column
- Fix: Only check ID if value is purely numeric (`is_numeric($slugOrId)`)

### 7. Vue component not updating on route param change
- Vue Router doesn't re-mount components when only params change
- Add a watcher for route params to trigger re-fetch:
```javascript
watch(() => route.params.slug, (newSlug, oldSlug) => {
  if (newSlug !== oldSlug) {
    loading.value = true
    fetchData()
  }
})
```

---

## API Endpoints

| Endpoint | Description |
|----------|-------------|
| `GET /api/v1/home` | Homepage data (categories, featured, recent listings) |
| `GET /api/v1/listings` | All listings with filters |
| `GET /api/v1/listings/{slug}` | Single listing detail |
| `GET /api/v1/categories` | All categories |
| `POST /api/v1/auth/login` | User login |
| `POST /api/v1/auth/register` | User registration |

---

## User Instructions

### When reopening Claude Code terminal:

1. **macOS**: Open Terminal, navigate to project:
   ```bash
   cd ~/Desktop/Lentloads
   ```

2. **Start Claude Code**:
   ```bash
   claude
   ```

3. **Claude will automatically**:
   - Read this CLAUDE.md file
   - Remember all project context
   - Connect to server when needed
   - Deploy changes without asking for credentials

---

## Admin Panel Features (Added January 2026)

### Admin Access
- **URL**: `/admin` (accessible via "Admin Panel" link in user menu)
- **Required Role**: `admin` or `moderator`
- **Routes**: Protected by `auth:sanctum` and `admin` middleware

### Admin Dashboard (`/admin`)
- **Stats Cards**: Total users, listings, pending approvals, open reports
- **Charts**: New listings and users (bar charts for 7/14/30/90 days)
- **Category Distribution**: Visual breakdown of listings per category
- **Top Cities**: Most active cities with listing counts
- **Recent Activity**: Latest listings, users, and pending reports
- **Quick Actions**: Direct links to common admin tasks

### Admin Listings (`/admin/listings`)
- **Bulk Actions**: Select multiple listings for bulk approve/delete
- **Inline Status Change**: Dropdown to change listing status
- **Badge Toggles**: One-click toggle for Featured/Urgent/Highlighted badges
- **Edit Modal**: Full listing editor with all fields
- **Filters**: By status, category, date range, search
- **Sortable Columns**: By date, title, price, views, status

### Admin Users (`/admin/users`)
- **Inline Role Change**: Dropdown for user/moderator/admin roles
- **Inline Status Change**: Dropdown for active/suspended/banned
- **Verified Seller Toggle**: One-click badge toggle
- **View Details Modal**: Shows user stats, recent listings, ratings
- **Filters**: By role, status, search

### Admin Settings (`/admin/settings`)
- **General Tab**: Site name, tagline, contact info, currency, timezone, maintenance mode
- **Listings Tab**: Approval settings, image limits, expiry, watermarks
- **Email Tab**: SMTP configuration, notification preferences
- **SEO Tab**: Meta tags, OG image, Google Analytics, Facebook Pixel
- **Social Tab**: Facebook, Instagram, Twitter, YouTube, LinkedIn, WhatsApp links
- **Pages Tab**: Create/edit static pages with HTML support
- **Banners Tab**: Manage banners by position (home, sidebar, listing page)

### Admin Conversations (`/admin/conversations`)
- **View All Chats**: See all conversations between buyers and sellers
- **Search**: By user name, email, or listing title
- **Filter**: By blocked status, date range
- **Read Messages**: Click to view full conversation history
- **Message Details**: Sender name, timestamp, message content

### Admin Contact Views (`/admin/contact-views`)
- **Track Phone Reveals**: See who clicked "Show Phone Number"
- **Stats Dashboard**: Total views, today, this week, this month
- **Top Sellers**: Most viewed contact info
- **Top Viewers**: Most active contact info viewers
- **Search & Filter**: By name, email, date range

### API Routes Added
```php
// Admin Listings
Route::post('/bulk-approve', [AdminListingController::class, 'bulkApprove']);
Route::post('/bulk-delete', [AdminListingController::class, 'bulkDelete']);
Route::put('/{id}', [AdminListingController::class, 'update']);
Route::post('/{id}/toggle-feature', [AdminListingController::class, 'toggleFeature']);

// Admin Conversations & Contact Views
Route::get('/conversations', [AdminConversationController::class, 'index']);
Route::get('/conversations/{id}', [AdminConversationController::class, 'show']);
Route::get('/conversations/{id}/messages', [AdminConversationController::class, 'messages']);
Route::get('/contact-views', [AdminConversationController::class, 'contactViews']);
Route::get('/contact-views/stats', [AdminConversationController::class, 'contactViewStats']);

// User Contact Tracking
Route::post('/listings/{id}/track-contact', [ListingController::class, 'trackContactView']);
```

---

## Pending/Future Tasks

- [ ] Fix auto-deploy webhook (public/deploy.php)
- [x] Add real images to listings (32 Unsplash images seeded)
- [ ] Implement payment gateway (currently simulated)
- [ ] Mobile responsive testing
- [ ] PWA testing on mobile devices
- [x] **Location & Map Feature** - Implemented with Leaflet + OpenStreetMap (January 2, 2026)

---

## Plan: Location Pin & Locality Selection Feature

### Current State Analysis
| Field | In Database | Used in Form | Notes |
|-------|-------------|--------------|-------|
| city | ✅ | ✅ (text input) | Required |
| state | ✅ | ✅ (text input) | Optional |
| address | ✅ | ❌ | Not used |
| postal_code | ✅ | ❌ | Not used |
| latitude | ✅ | ❌ | Not used |
| longitude | ✅ | ❌ | Not used |
| country | ✅ | ❌ | Defaults to 'IN' |
| **locality** | ❌ | ❌ | **MISSING - Need to add** |

### Proposed Solution

#### Option A: Google Maps Integration (Recommended)
**Pros:** Most accurate, auto-complete, reverse geocoding
**Cons:** Requires API key, costs money after free tier

#### Option B: Leaflet + OpenStreetMap (Free Alternative)
**Pros:** Completely free, no API limits
**Cons:** Slightly less accurate, no Places autocomplete

#### Option C: Simple Dropdown for Localities (Simplest)
**Pros:** No external dependencies, fast
**Cons:** Need to maintain locality list, less flexible

### Recommended Implementation: Option B (Leaflet + OpenStreetMap)

#### Phase 1: Database Changes
```php
// New migration: add_locality_to_listings_table.php
Schema::table('listings', function (Blueprint $table) {
    $table->string('locality')->nullable()->after('address');
    $table->index('locality');
});
```

#### Phase 2: Vue Components to Create

**1. LocationPicker.vue** - Reusable map component
```
Features:
- Interactive Leaflet map
- Click to place marker
- Drag marker to adjust
- "Use My Location" button (GPS)
- Shows selected coordinates
- Reverse geocode to get address details
```

**2. LocationDisplay.vue** - For listing detail page
```
Features:
- Static map showing listing location
- Approximate area (not exact address for privacy)
- "Get Directions" link
```

#### Phase 3: CreateListing.vue Changes

**New Location Section:**
```
┌─────────────────────────────────────────────┐
│  📍 Your Location                           │
├─────────────────────────────────────────────┤
│  [Map - Click to pin location]              │
│  ┌─────────────────────────────────────┐   │
│  │                                     │   │
│  │        [Interactive Map]            │   │
│  │            📍                       │   │
│  │                                     │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  [📍 Use My Current Location]               │
│                                             │
│  ─────────────────────────────────────────  │
│  Locality/Area: [Auto-filled________]       │
│  City:          [Auto-filled________]       │
│  State:         [Auto-filled________]       │
│  PIN Code:      [Auto-filled________]       │
│                                             │
│  (Can edit manually if needed)              │
└─────────────────────────────────────────────┘
```

#### Phase 4: Backend Changes

**ListingController.php:**
- Accept locality, latitude, longitude, postal_code
- Validate coordinates are within India (optional)

**ListingResource.php:**
- Include location data in API response
- Optionally hide exact coordinates (show approximate area only)

#### Phase 5: ListingDetail.vue Changes
- Show mini-map with approximate location
- Display: Locality, City, State
- "Get Directions" button (opens Google Maps)

### Files to Create/Modify

| File | Action | Description |
|------|--------|-------------|
| `database/migrations/xxx_add_locality.php` | Create | Add locality field |
| `resources/js/components/common/LocationPicker.vue` | Create | Map picker component |
| `resources/js/components/common/LocationDisplay.vue` | Create | Static map display |
| `resources/js/views/dashboard/CreateListing.vue` | Modify | Add map picker |
| `resources/js/views/dashboard/EditListing.vue` | Modify | Add map picker |
| `resources/js/views/ListingDetail.vue` | Modify | Show location map |
| `app/Http/Controllers/Api/ListingController.php` | Modify | Accept new fields |
| `package.json` | Modify | Add Leaflet dependency |

### NPM Packages Needed
```bash
npm install leaflet vue-leaflet @vue-leaflet/vue-leaflet
```

### Reverse Geocoding (Free)
Use Nominatim (OpenStreetMap's free geocoding service):
```
https://nominatim.openstreetmap.org/reverse?lat={lat}&lon={lon}&format=json
```
Response includes: road, suburb (locality), city, state, postcode

### Privacy Considerations
- Show approximate location on listing (not exact)
- Option: "Hide exact location" toggle
- Only show city + locality to buyers, not exact address

### Estimated Components

1. **LocationPicker.vue** (~200 lines)
   - Leaflet map initialization
   - Click handler for placing marker
   - GPS location button
   - Nominatim reverse geocoding
   - Emit location data to parent

2. **LocationDisplay.vue** (~100 lines)
   - Static map with marker
   - Get directions link

### Implementation Order - COMPLETED (January 2, 2026)
1. ✅ Create migration for locality field - `2024_01_01_000018_add_locality_to_listings_table.php`
2. ✅ Install Leaflet packages - `npm install leaflet @vue-leaflet/vue-leaflet`
3. ✅ Create LocationPicker.vue component - `resources/js/components/common/LocationPicker.vue`
4. ✅ Integrate into CreateListing.vue - Map picker with GPS and reverse geocoding
5. ✅ Integrate into EditListing.vue - Pre-fills existing coordinates
6. ✅ Update ListingController to save new fields - Added `locality` validation
7. ✅ Create LocationDisplay.vue - `resources/js/components/common/LocationDisplay.vue`
8. ✅ Add map to ListingDetail.vue - Shows location with privacy circle
9. ✅ Test and deploy - LIVE

### Alternative: Keep it Simple (Dropdowns Only)
If map is too complex, simpler option:
- State dropdown (29 Indian states)
- City dropdown (filtered by state)
- Locality text input with suggestions

---

---

## Security Audit Findings (January 1, 2026)

### Fixed Issues:
1. **CORS Configuration** - Restricted allowed origins (was `*` with credentials)
2. **Rate Limiting** - Added to auth routes (5 attempts/min, 3 for password reset)
3. **XSS Prevention** - Added HTML sanitization in StaticPage.vue
4. **Open Redirect** - Validated redirect URLs in Login.vue
5. **SQL Injection Prevention** - Validated sort columns in admin controllers
6. **Pagination DoS** - Limited max per_page to 50-100
7. **Database Indexes** - Created migration for 6 missing indexes
8. **Accessibility** - Added aria-labels to icon buttons

### Remaining Issues (Lower Priority):
- Payment gateway is simulated (PackageController.php:53) - needs real implementation
- localStorage token storage - consider httpOnly cookies for production
- Some npm dependencies are outdated (run `npm outdated` to check)

### N+1 Query Fixes (January 1, 2026):
Optimized PageController and CategoryController to use batch queries instead of per-category queries:
```php
// Old: N+1 queries (one per category)
foreach ($categories as $category) {
    $category->total_active_listings_count = Listing::whereIn('category_id', $allIds)->count();
}

// New: 2 batch queries total
$childrenByParent = Category::whereIn('parent_id', $parentIds)->get(['id', 'parent_id'])->groupBy('parent_id');
$listingCounts = Listing::whereIn('category_id', $allCategoryIds)->selectRaw('category_id, COUNT(*) as count')->groupBy('category_id')->pluck('count', 'category_id');
// Then calculate in PHP without database queries
```

### Security Best Practices Applied:
- Sanitized HTML content before v-html rendering
- Whitelisted sort columns for orderBy queries
- Limited pagination to prevent memory exhaustion
- Added database indexes for common queries
- Restricted CORS to specific domains

---

## Session Notes

### Session: January 2, 2026 (Location Pin & Map Feature)

**Bug Fix - Server Error on Listing Creation:**
- **Issue:** IP geolocation was using `http://ip-api.com` which fails on HTTPS sites (mixed content)
- **Fix:** Switched to `https://ipapi.co/json/` which provides free HTTPS access (1000 requests/day)
- **Also:** Added locality column to main migration for fresh installs, made add_locality migration idempotent

**Implemented:** Leaflet + OpenStreetMap location picker for listings

**New Files Created:**
- `database/migrations/2024_01_01_000018_add_locality_to_listings_table.php`
- `resources/js/components/common/LocationPicker.vue` (~200 lines)
- `resources/js/components/common/LocationDisplay.vue` (~150 lines)

**Files Modified:**
- `resources/js/views/dashboard/CreateListing.vue` - Added LocationPicker
- `resources/js/views/dashboard/EditListing.vue` - Added LocationPicker
- `resources/js/views/ListingDetail.vue` - Added LocationDisplay map
- `app/Http/Controllers/Api/ListingController.php` - Added locality validation
- `app/Models/Listing.php` - Added locality to fillable

**Features:**
1. **Create/Edit Listing:**
   - Interactive map with click-to-pin
   - "Use My Current Location" GPS button
   - Auto-fills: Locality, City, State, PIN Code
   - Uses Nominatim reverse geocoding (free)

2. **Listing Detail Page:**
   - Static map showing approximate location
   - 500m privacy radius circle
   - "Get Directions" link to Google Maps
   - Shows: Locality, City, State, PIN Code

**NPM Packages Added:**
- `leaflet` - Map rendering
- `@vue-leaflet/vue-leaflet` - Vue integration

---

### Session: January 1, 2026 (Phone Login & Auth Enhancements - Full Details)

#### Phone + Password Login Support
**Files Modified:**
- `app/Http/Controllers/Api/AuthController.php`
- `resources/js/views/auth/Login.vue`
- `resources/js/views/auth/Register.vue`
- `resources/js/router/index.js`
- `resources/js/layouts/AdminLayout.vue`
- `database/migrations/2024_01_01_000017_make_phone_unique_on_users_table.php`

**Implementation Details:**

1. **Login Method Toggle (Login.vue)**
   - Added toggle between Phone/Email on login page
   - Phone is the **primary/default** login method
   - Toggle buttons: Phone | Email (Phone selected by default)
   ```javascript
   const loginMethod = ref('phone') // Default is phone
   ```

2. **Backend Phone Matching (AuthController.php)**
   - Added `normalizePhone()` - strips all non-digit chars except leading +
   - Added `findUserByPhone()` - flexible matching for various formats
   - Supported phone formats:
     - `8122116594` (10 digits)
     - `+918122116594` (with country code)
     - `918122116594` (country code without +)
     - `08122116594` (with leading 0)
   - Works for Indian (+91) and international numbers
   ```php
   private function normalizePhone(string $phone): string
   private function findUserByPhone(string $phone): ?User
   ```

3. **Phone Registration (Register.vue)**
   - Phone number is now **required** field
   - Added unique constraint on phone column
   - Helper text: "With or without country code (e.g., 8122116594 or +91 8122116594)"

4. **Contact Support Options**
   - Added "Need Help?" section to Login.vue and ForgotPassword.vue
   - Call Us button: +91 81221 16594
   - WhatsApp button with pre-filled message
   ```html
   <a href="tel:+918122116594">Call Us</a>
   <a href="https://wa.me/918122116594?text=Hi...">WhatsApp</a>
   ```

5. **Admin Layout Fix**
   - Fixed admin sidebar not showing
   - Changed from flat routes to nested children under AdminLayout
   - Changed `<slot />` to `<router-view />` in AdminLayout.vue
   ```javascript
   // router/index.js - Nested admin routes
   {
     path: '/admin',
     component: AdminLayout,
     children: [
       { path: '', name: 'admin-dashboard', component: AdminDashboard },
       { path: 'users', name: 'admin-users', component: AdminUsers },
       // ... all admin routes as children
     ]
   }
   ```

6. **Database Migration**
   - Created migration: `2024_01_01_000017_make_phone_unique_on_users_table.php`
   - Added unique constraint on `phone` column

### Session: January 1, 2026 (Conversation Monitoring & Contact Tracking)
- Added Admin Conversations page to monitor all user chats
- Added Admin Contact Views page to track who viewed whose phone
- Added ContactView model and migration for tracking
- Updated listing detail page with "Show Phone Number" button that tracks views
- Added navigation links to admin sidebar (Conversations, Contact Views)
- Fixed auth persistence on page refresh (router now waits for user fetch)

### Session: January 1, 2026 (Admin Panel Enhancement)
- Added real images to 12 listings using Unsplash URLs (ListingImageSeeder)
- Added "Admin Panel" link to header for admin users
- Fixed admin listings page stuck loading (missing fetchCategories method)
- Fixed 39 duplicate categories (deleted IDs 34-72, updated 12 listings)
- **Enhanced Admin Listings**:
  - Bulk select with approve/delete all selected
  - Inline status dropdown for each listing
  - Featured/Urgent/Highlighted badge toggle buttons
  - Full edit modal with all listing fields
- **Enhanced Admin Users**:
  - Inline role dropdown (user/moderator/admin)
  - Inline status dropdown (active/suspended/banned)
  - Verified seller badge toggle
  - View details modal with user's listings
- **Enhanced Admin Dashboard**:
  - Added period selector (7/14/30/90 days)
  - Bar charts for new listings and users over time
  - Category distribution progress bars
  - Top cities ranking
  - Recent users column
  - Refresh button
- **Enhanced Admin Settings**:
  - Added Email tab (SMTP, notifications)
  - Added SEO tab (meta tags, analytics)
  - Added Social tab (all social links)
  - Enhanced Pages with meta fields
  - Enhanced Banners with position filter

### Session: January 1, 2026 (Continued)
- Fixed category counts showing "0 ads" - now includes child category listings
- Removed problematic `getTotalActiveListingsCountAttribute` accessor from Category model
- Fixed listing detail page not updating when clicking similar listings (added route watcher)
- Fixed slug/ID matching bug - slugs starting with numbers (e.g., "3-bhk...") were matching wrong listing
- Updated CLAUDE.md with all fixes and common issues

### Session: January 1, 2026
- Fixed 500 errors (Laravel 10 compatibility)
- Created missing models
- Added test listings (16 total)
- Fixed edit functionality (slug vs ID issue)
- Set up SSH key authentication for deployment
- Redesigned to OLX India style
- Fixed APP_KEY caching issues

---

## Important Reminders for Claude

1. **ALWAYS update CLAUDE.md** - Document every change, every fix, every plan in this file. Update immediately after:
   - Making any code changes
   - Fixing bugs
   - Adding new features
   - Planning implementations
   - Discovering issues
   This is CRITICAL - the user relies on this file to understand what was done.

2. **Always test the site** after deployment before telling user it's ready
3. **Never run** `php artisan config:cache` - it breaks the site
4. **Always run** `npm run build` before committing frontend changes
5. **Use SSH key** at `~/.ssh/cloudways_rsa` for server access
6. **User has no coding knowledge** - provide complete solutions
7. **Commit build files** - `public/build` is tracked in git
8. **Always clear OPcache** after deploying PHP changes: `curl -s 'https://phplaravel-1016958-6108537.cloudwaysapps.com/opcache-clear.php'`

---

*Last Updated: January 1, 2026*
