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

## Mobile-First Design Patterns (CRITICAL REFERENCE)

### Bottom Sheet Modal Pattern
Used for: ReportModal, Search Filters, Offer Modal, Save Search Modal

```css
.modal-container {
  position: fixed;
  inset: 0;
  z-index: 50;
  display: flex;
  align-items: flex-end;  /* Mobile: slides up from bottom */
  justify-content: center;
}

@media (min-width: 640px) {
  .modal-container { align-items: center; }  /* Desktop: centered */
}

.modal-sheet {
  position: relative;
  background: white;
  width: 100%;
  max-width: 28rem;
  border-radius: 1rem 1rem 0 0;  /* Rounded top only on mobile */
  display: flex;
  flex-direction: column;
  max-height: 80vh;
  max-height: 80dvh;  /* dvh = dynamic viewport height (handles iOS keyboard) */
}

.modal-header { flex-shrink: 0; }  /* Never shrink */
.modal-content { flex: 1; overflow-y: auto; min-height: 0; }  /* Scrollable */
.modal-actions {
  flex-shrink: 0;
  padding-bottom: max(1rem, env(safe-area-inset-bottom, 1rem));  /* Safe area for iOS home bar */
}
```

### Full-Screen Chat Layout (Conversation.vue)
```css
.conversation-container {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  display: flex;
  flex-direction: column;
}

.conversation-header {
  position: sticky;
  top: 0;
  flex-shrink: 0;
  padding-top: env(safe-area-inset-top, 0);  /* iOS notch */
}

.messages-area {
  flex: 1;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.conversation-input {
  position: sticky;
  bottom: 0;
  flex-shrink: 0;
  padding-bottom: max(12px, env(safe-area-inset-bottom, 12px));  /* iOS home bar */
}

/* Use dvh for proper mobile viewport */
@supports (height: 100dvh) { .conversation-container { height: 100dvh; } }
```

### Fixed Bottom Action Bar Pattern (CreateListing, etc.)
```css
.bottom-actions {
  position: fixed;
  bottom: 0; left: 0; right: 0;
  background: white;
  border-top: 1px solid #e5e7eb;
  padding: 12px 0;
  padding-bottom: max(12px, env(safe-area-inset-bottom, 12px));
  z-index: 40;
}
```
**IMPORTANT:** Content area needs `pb-24` to prevent overlap with fixed bottom bar.

### MobileNav Visibility (App.vue)
```javascript
const showMobileNav = computed(() =>
  !isAdminRoute.value &&
  isMobile.value &&
  !isConversationPage.value &&
  !isCreateListingPage.value  // Hide nav on these pages
)
```
When mobile nav is visible, main content needs `pb-20`:
```vue
<main class="flex-1" :class="showMobileNav ? 'pb-20' : ''">
```

### Safe Area CSS Reference
```css
/* iOS status bar (notch) */
padding-top: env(safe-area-inset-top, 0);

/* iOS home indicator bar */
padding-bottom: max(16px, env(safe-area-inset-bottom, 16px));

/* Dynamic viewport height (handles iOS keyboard) */
max-height: 80dvh;  /* With fallback: */
max-height: 80vh;
max-height: 80dvh;
```

---

## Session Notes

### Session: January 2, 2026 (Mobile UX Restructure - MAJOR)

**Issues Reported:**
1. Chat message input not sticky at bottom (goes below sometimes)
2. Search filters not being honored + button hidden in filter popup
3. Report modal submit button impossible to click
4. CreateListing continue button has abnormal space below

**Complete Restructures Done:**

**1. Conversation.vue - Full Rewrite**
- Changed from flexbox hacks to proper fixed container with CSS layout
- Uses `position: fixed; inset: 0;` as container
- Header uses `position: sticky; top: 0;`
- Input uses `position: sticky; bottom: 0;`
- Messages area scrolls between with `flex: 1; overflow-y: auto;`
- Uses `100dvh` for dynamic viewport height (handles iOS keyboard)
- Added resize event listener to scroll to bottom on keyboard open

**2. Search.vue - Filter Modal Rewrite**
- Created new CSS classes: `.filter-modal`, `.filter-sheet`, `.filter-header`, `.filter-content`, `.filter-actions`
- Uses `max-height: 80dvh` for proper mobile height
- Actions always visible at bottom with safe-area-inset
- Changed from calling store to calling API directly (fixed filter merging issue)
- Fixed price range comparison using `String()` for proper comparison

**3. ReportModal.vue - Complete Rewrite**
- New CSS classes: `.report-modal`, `.report-sheet`, `.report-header`, `.report-content`, `.report-actions`
- Responsive: bottom sheet on mobile, centered modal on desktop
- Uses `max-height: 80dvh` with flex layout
- Actions fixed at bottom with safe-area-inset support
- Scrollable content area with `min-height: 0` for proper flex behavior

**4. CreateListing.vue - Spacing Fix**
- Removed 80px mobile padding (was assuming mobile nav exists, but it doesn't on this page)
- Uses `.bottom-actions` CSS class with proper fixed positioning
- Only uses safe-area-inset-bottom, no extra padding
- Content has `pb-24` to prevent overlap with fixed bottom bar

**Key CSS Patterns Established:**
- Always use `flex-shrink: 0` on header/footer elements
- Always use `flex: 1; min-height: 0; overflow-y: auto;` on scrollable content
- Always use `max-height: 80dvh` (with vh fallback) for modals
- Always use `padding-bottom: max(Xpx, env(safe-area-inset-bottom, Xpx))` for bottom elements
- Use `position: fixed; inset: 0;` for full-screen layouts

**Deploy:** Webhook at `https://phplaravel-1016958-6108537.cloudwaysapps.com/deploy.php?token=07d5b8cee9e561ce7305fa2a489d0aaa55b77734`

---

### Session: January 2, 2026 (Guest Listing Flow & Auth Modal)

**New Feature: Guest Listing Flow (No Login Required Upfront)**

Users can now fill the entire listing form without logging in first. Authentication only happens at submit time.

**The Flow:**
1. User clicks "SELL" → Goes directly to listing form (no login wall)
2. Fills all details, uploads photos, sets location
3. Clicks "Post Ad" → Auth modal appears
4. Step 1: Enter phone number
5. Step 2a: If phone exists → "Welcome back!" + password field + forgot password link
6. Step 2b: If phone is new → Name + Email + Password fields
7. After auth → Listing automatically submitted

**Files Created:**
- `resources/js/components/common/AuthPromptModal.vue` - Phone-first authentication modal

**Files Modified:**
- `app/Http/Controllers/Api/AuthController.php` - Added `checkPhone()` and `quickRegister()` methods
- `routes/api.php` - Added `/auth/check-phone` and `/auth/quick-register` routes
- `resources/js/views/dashboard/CreateListing.vue` - Shows auth modal for guests on submit
- `resources/js/router/index.js` - Removed `requiresAuth` from `/sell` route

**API Endpoints Added:**
```php
POST /api/v1/auth/check-phone   // Check if phone is registered, returns {exists: bool, name: string}
POST /api/v1/auth/quick-register // Register new user with phone, email, password
```

**Why This Is Better:**
- Users invest time filling the form first (less likely to abandon)
- Phone-first auth (Indians prefer phone over email)
- No SMS costs (password-based, not OTP)
- Existing users can login with forgot password option
- Higher conversion rate for listings

---

### Session: January 2, 2026 (Storage & IP Geolocation Fixes)

**Bug Fix - Storage Permissions:**
- **Issue:** "Unable to create directory" error when posting listing
- **Cause:** Storage symlink was pointing to wrong path (`/home/1016958.cloudwaysapps.com/...` instead of `/home/master/applications/...`)
- **Fix:** Recreated symlink with correct path, set 777 permissions on storage directories

**Bug Fix - Server Error on Listing Creation:**
- **Issue:** IP geolocation was using `http://ip-api.com` which fails on HTTPS sites (mixed content)
- **Fix:** Switched to `https://ipapi.co/json/` which provides free HTTPS access (1000 requests/day)
- **Also:** Added locality column to main migration for fresh installs, made add_locality migration idempotent

---

### Session: January 2, 2026 (Location Pin & Map Feature)

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

### Session: January 2, 2026 (UI Polish & Card Redesign)

**Issues Fixed:**

1. **Extra Gap After Keyboard Closes (Search Page)**
   - **Issue:** Blue gap appearing after mobile keyboard closes
   - **Fix:** Added `min-height: 100dvh` to search page container using new `.search-page` class
   - **File:** `resources/js/views/Search.vue`

2. **Chat Send Button Misaligned**
   - **Issue:** Send button appeared slightly below the text input field
   - **Fix:** Changed `items-end` to `items-center` in form flex container
   - **Fix:** Made buttons fixed 44x44px (w-11 h-11) for consistent alignment
   - **Fix:** Reduced textarea padding for visual alignment
   - **File:** `resources/js/views/dashboard/Conversation.vue`

3. **Removed Count from Filter Button**
   - **Issue:** "Show 15 Results" was cluttered
   - **Fix:** Changed to just "Show Results" (no dynamic count)
   - **File:** `resources/js/views/Search.vue` line 279

4. **Listing Cards Redesign (Major)**
   - **Issues:**
     - "(Negotiable)" text cluttered price display
     - "Like_new" showed with underscore instead of "Like New"
     - Location text overlapping, truncation issues
     - Cards looked messy and unattractive

   - **Fixes Applied:**
     - Created new scoped CSS (no Tailwind utility classes spam)
     - Square 1:1 aspect ratio images
     - Clean price display without "(Negotiable)"
     - Properly formatted condition badge: "Like New", "Good", etc.
     - Shortened location (city only, max 15 chars)
     - Better typography hierarchy
     - Subtle hover effects with elevation change
     - Featured/Urgent badges with gradient backgrounds
     - Favorite button appears on hover

   - **File:** `resources/js/components/common/ListingCard.vue` (complete rewrite)

**ListingCard.vue Key Changes:**
```javascript
// Clean price (removes Negotiable text)
const displayPrice = computed(() => {
  const formatted = props.listing.formatted_price
  return formatted.replace(/\s*\(Negotiable\)/gi, '').trim()
})

// Format condition properly
const formattedCondition = computed(() => {
  const conditionMap = {
    'new': 'New',
    'like_new': 'Like New',
    'good': 'Good',
    'fair': 'Fair'
  }
  return conditionMap[condition.toLowerCase()] || condition.replace(/_/g, ' ')
})

// Shorter location for cards
const shortLocation = computed(() => {
  const city = loc.split(',')[0].trim()
  return city.length > 15 ? city.substring(0, 15) + '...' : city
})
```

---

### Session: January 2, 2026 (Major Page Redesigns)

**Pages Redesigned:**

1. **ListingDetail.vue - Complete Overhaul**
   - OLX-style full-width image gallery
   - Lightbox with pinch-to-zoom on mobile (touch gestures)
   - Zoom controls (+/- buttons)
   - Pan/drag when zoomed in
   - Image counter badge
   - Thumbnail strip navigation
   - Card-based content layout
   - Better seller card with placeholder avatar
   - Removed "(Negotiable)" from prices

   **Key Features:**
   ```javascript
   // Lightbox zoom state
   const zoomLevel = ref(1)
   const panX = ref(0)
   const panY = ref(0)

   // Pinch-to-zoom handling
   const handleTouchMove = (e) => {
     if (e.touches.length === 2) {
       const distance = Math.hypot(...)
       zoomLevel.value = Math.max(1, Math.min(3, zoomLevel.value * scale))
     }
   }

   // Placeholder avatar
   const sellerAvatar = computed(() => {
     if (avatar && !avatar.includes('default')) return avatar
     return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(name) + '&background=7c3aed&color=fff'
   })
   ```

2. **UserProfile.vue - Complete Overhaul**
   - Purple gradient header
   - Placeholder avatar using ui-avatars.com API
   - Star ratings display
   - Stats bar (Listings, Sold, Response Rate)
   - Modern tab design for Listings/Reviews
   - Verified badge with green checkmark
   - Mobile-first responsive design

   **Placeholder Avatar:**
   ```javascript
   const userAvatar = computed(() => {
     const avatar = user.value?.avatar_url
     if (avatar && !avatar.includes('default') && !avatar.includes('null')) return avatar
     return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.value?.name || 'U') + '&background=7c3aed&color=fff&size=200&bold=true'
   })
   ```

3. **MyListings.vue - Complete Overhaul**
   - Stats overview cards at top (Total, Active, Pending, Sold)
   - Pill-style filter buttons
   - Modern listing cards with status badges
   - Clean action buttons (Edit, Mark Sold, Renew, Delete)
   - Better responsive layout
   - Empty state with icon
   - Removed "(Negotiable)" from prices

**Files Changed:**
- `resources/js/views/ListingDetail.vue` - Complete rewrite (~970 lines)
- `resources/js/views/UserProfile.vue` - Complete rewrite (~600 lines)
- `resources/js/views/dashboard/MyListings.vue` - Complete rewrite (~690 lines)

**Design System Updates:**
- Purple primary color: `#7c3aed`
- Card border-radius: `12px` / `16px`
- Consistent shadow: `0 1px 3px rgba(0, 0, 0, 0.05)`
- Status badge colors:
  - Active: Green (`#dcfce7`, `#16a34a`)
  - Pending: Yellow (`#fef3c7`, `#d97706`)
  - Sold: Blue (`#dbeafe`, `#2563eb`)
  - Expired: Gray (`#f1f5f9`, `#64748b`)

---

### Session: January 2, 2026 (Swipe Images, SavedSearches & Chat Fix)

**1. ListingDetail.vue - Swipable Images**
- Added touch swipe gestures for mobile image navigation
- Swipe left/right to change images
- Added swipe dots indicator (shows current position)
- Navigation arrows hidden on mobile, visible only on desktop
- Smooth transitions with 50px threshold for swipe detection

**Key Implementation:**
```javascript
// Swipe state
const swipeOffset = ref(0)
let swipeStartX = 0
let isSwiping = false

const handleSwipeStart = (e) => {
  if (e.touches.length === 1) {
    swipeStartX = e.touches[0].clientX
    isSwiping = true
  }
}

const handleSwipeEnd = () => {
  if (!isSwiping) return
  const threshold = 50
  if (swipeOffset.value > threshold) prevImage()
  else if (swipeOffset.value < -threshold) nextImage()
  swipeOffset.value = 0
  isSwiping = false
}
```

**2. SavedSearches.vue - Complete Redesign**
- Modern card-based UI with clean spacing
- Toggle buttons for Push/Email notifications (styled as buttons, not checkboxes)
- Both notifications enabled by default via null coalescing operator
- Tags displaying search criteria: query, category, city, price range
- Frequency dropdown (Instant/Daily/Weekly)
- Action buttons: Run search, Edit, Delete
- Edit modal for changing search name
- Empty state with icon and CTA

**Key Changes:**
```javascript
// Both notifications enabled by default
searches.value = response.data.data.map(search => ({
  ...search,
  notify_push: search.notify_push ?? true,
  notify_email: search.notify_email ?? true,
}))
```

**3. Conversation.vue - Chat Hang Fix (Optimistic Updates)**
- **Issue:** Chat UI would freeze for seconds after sending message
- **Cause:** Textarea was disabled (`:disabled="sending"`) during API call
- **Fix:** Implemented optimistic updates:
  1. Removed `:disabled` from textarea
  2. Show message immediately with temp ID
  3. Display "Sending..." status for pending messages
  4. Add subtle opacity (70%) for messages being sent
  5. Replace temp message with real one after API success
  6. Re-enable sending immediately after adding temp message

**Key Changes:**
```javascript
// Optimistic update - show message immediately
const tempId = 'temp-' + Date.now()
const optimisticMessage = {
  id: tempId,
  body: messageText,
  sender_id: currentUserId.value,
  created_at: new Date().toISOString(),
  is_read: false,
  type: 'text',
}
messages.value.push(optimisticMessage)
scrollToBottom()
sending.value = false  // Re-enable immediately
```

**Files Modified:**
- `resources/js/views/ListingDetail.vue` - Added swipe gestures, dots indicator
- `resources/js/views/dashboard/SavedSearches.vue` - Complete rewrite
- `resources/js/views/dashboard/Conversation.vue` - Optimistic message updates

**Note:** SSH to server was timing out during this session. Code pushed to GitHub - deploy manually when server is accessible:
```bash
ssh lentlo@139.59.24.36 "cd /home/master/applications/bpadwztsjg/public_html && git checkout . && git pull origin main"
```

---

### Session: January 2, 2026 (Chat Sending Overlay + SavedSearch Defaults)

**1. SavedSearch Model - Default Notifications**
- Added `$attributes` array to set defaults when creating new saved search:
  - `notify_push` = true
  - `notify_email` = true
  - `notify_frequency` = 'instant'
- File: `app/Models/SavedSearch.php`

**2. Conversation.vue - Complete Rewrite with Sending Overlay**
- **Problem:** Chat was hanging after sending, couldn't scroll, input wouldn't work
- **Solution:** Added blocking "Sending..." overlay popup
  - Full-screen overlay with spinner while message sends
  - User waits for message to complete, then can send next
  - Clear visual feedback prevents confusion
  - Input and buttons disabled during send
  - Refocuses input after send completes

**Key Features:**
```vue
<!-- Sending Overlay -->
<div v-if="sending" class="sending-overlay">
  <div class="sending-popup">
    <div class="sending-spinner"></div>
    <p>Sending...</p>
  </div>
</div>
```

```css
.sending-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}
```

**Why This Approach:**
- Optimistic updates caused race conditions and glitches
- Blocking overlay ensures one message at a time
- Clear feedback that something is happening
- After overlay closes, user can immediately type next message

---

### Session: January 2, 2026 (Chat Header Redesign + Mobile Nav Fix)

**Issues Fixed:**

1. **Chat Header Getting Hidden After Sending Message**
   - **Issue:** Top header would disappear after sending a message and not come back
   - **Issue:** Safari had problems but Chrome worked fine
   - **Fix:** Complete rewrite of Conversation.vue with proper flexbox layout

2. **Mobile Navigation Tap Targets Too Small**
   - **Issue:** Bottom nav buttons (My Ads, Search, Sell, etc.) sometimes not working
   - **Cause:** Touch targets were smaller than recommended 44-48px
   - **Fix:** Updated MobileNav.vue with 48x48px minimum tap targets

**Files Modified:**

1. **`resources/js/views/dashboard/Conversation.vue`** - Complete Rewrite with Stunning Header
   - Purple to pink gradient background: `linear-gradient(135deg, #7c3aed 0%, #a855f7 50%, #ec4899 100%)`
   - SVG pattern overlay for texture
   - Glassmorphism buttons with backdrop blur
   - Avatar with white ring and pulsing online indicator
   - Product card embedded in gradient header
   - Icons in dropdown menu (View Listing, View Profile, Block User, Report User)
   - Proper safe area handling for iOS notch
   - Fixed flexbox layout to prevent header hiding

   **Key CSS:**
   ```css
   .chat-header {
     position: relative;
     flex-shrink: 0;
     background: linear-gradient(135deg, #7c3aed 0%, #a855f7 50%, #ec4899 100%);
     padding-top: env(safe-area-inset-top, 0);
     box-shadow: 0 4px 20px rgba(124, 58, 237, 0.3);
   }

   .back-btn {
     width: 40px;
     height: 40px;
     background: rgba(255, 255, 255, 0.2);
     backdrop-filter: blur(10px);
     border-radius: 12px;
     -webkit-tap-highlight-color: transparent;
   }
   ```

2. **`resources/js/components/layout/MobileNav.vue`** - Mobile Tap Target Fix
   - All nav items now minimum 48x48px (Apple HIG = 44pt, Material = 48dp)
   - Added `-webkit-tap-highlight-color: transparent` for iOS
   - Added `touch-action: manipulation` to prevent delays
   - Simplified component with scoped CSS

   **Key CSS:**
   ```css
   .nav-item {
     display: flex;
     flex-direction: column;
     align-items: center;
     justify-content: center;
     flex: 1;
     min-width: 48px;
     min-height: 48px;
     padding: 6px 0;
     -webkit-tap-highlight-color: transparent;
     touch-action: manipulation;
     cursor: pointer;
     user-select: none;
   }
   ```

3. **`app/Models/SavedSearch.php`** - Default Notification Settings
   - Push and email notifications now auto-enabled when saving a search
   ```php
   protected $attributes = [
       'notify_push' => true,
       'notify_email' => true,
       'notify_frequency' => 'instant',
   ];
   ```

**Business/Branding Notes (User Questions):**

1. **Name: "Lentloads" vs "Lentlo Marketplace"**
   - "Lentloads" is catchier, shorter, more memorable
   - "Lentlo Marketplace" is more descriptive but longer
   - Recommendation: Keep "Lentloads" as brand, use tagline for clarity

2. **Platform Positioning:**
   - User wants universal ad posting platform (like OLX but broader)
   - Categories to consider:
     - Buy/Sell (traditional classifieds)
     - Jobs (posting and seeking)
     - Services (plumbers, electricians, drivers)
     - Travel (carpooling, co-passengers, fuel sharing)
     - Rentals (houses, cars, equipment)
     - Wholesale (B2B bulk deals)
   - Tagline idea: "Post anything. Find everything."

3. **Logo Ideas:**
   - Letter "L" with loading/upload arrow incorporated
   - Gradient purple to pink (matches app theme)
   - Simple, modern, app-icon friendly

---

### Session: January 2, 2026 (Fuzzy Search + Main Header Redesign with Logo)

**Features Implemented:**

1. **Fuzzy/Typo-Tolerant Search (Google-like)**
   - Multiple search strategies for better results
   - File: `app/Http/Controllers/Api/ListingController.php`

   **Search Strategies:**
   - **Strategy 1: Exact Match** - Standard LIKE query
   - **Strategy 2: FULLTEXT Search** - MySQL natural language mode
   - **Strategy 3: Word-by-Word** - Matches individual words
   - **Strategy 4: SOUNDEX** - Phonetic matching for similar sounding words
   - **Strategy 5: Typo Variations** - Keyboard adjacency and common typos

   **Typo Handling:**
   ```php
   // Keyboard adjacency substitutions
   $substitutions = [
       'a' => ['s', 'q', 'z'],
       'e' => ['w', 'r', '3'],
       'i' => ['u', 'o', '8', '9'],
       // ... more
   ];

   // Indian English variations
   $indianVariations = [
       'mobile' => ['phone', 'cell', 'handset'],
       'flat' => ['apartment', 'house'],
       'bike' => ['motorcycle', 'two wheeler', 'scooter'],
       'ac' => ['air conditioner', 'air conditioning'],
       // ... more
   ];
   ```

   **Database Migration Added:**
   - `2024_01_01_000019_add_fulltext_search_to_listings.php`
   - Adds FULLTEXT index on `title` and `description` columns
   - Adds `search_terms` column for future enhancements

2. **Main Header Redesign (AppHeader.vue) - Complete Rewrite**
   - Purple gradient background matching app theme
   - New SVG logo with "L" letter and upload arrow
   - Tagline: "Post anything. Find everything."
   - Integrated search bar with suggestions dropdown
   - Glassmorphism action buttons
   - Animated notification badges
   - User menu with gradient header
   - Mobile-responsive with separate mobile search bar

   **Logo Design (SVG):**
   - Circular gradient background (#7c3aed → #a855f7 → #ec4899)
   - White "L" letter
   - Upload arrow symbol (represents posting/selling)
   - Hover animation with slight rotation

   **Key CSS:**
   ```css
   .app-header {
     background: linear-gradient(135deg, #7c3aed 0%, #9333ea 50%, #a855f7 100%);
     box-shadow: 0 4px 20px rgba(124, 58, 237, 0.25);
   }

   .search-box {
     background: rgba(255, 255, 255, 0.95);
     border-radius: 12px;
     box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
   }

   .action-btn {
     background: rgba(255, 255, 255, 0.15);
     backdrop-filter: blur(10px);
     border-radius: 12px;
   }
   ```

   **Search Suggestions:**
   - Debounced API calls (300ms delay)
   - Shows matching listing titles
   - Click to select and search
   - Auto-hide on blur

3. **Chat Send Button Spinner**
   - Already implemented in Conversation.vue
   - Shows rotating spinner while message is sending
   - Button disabled during send
   - Uses CSS animation for smooth rotation

**Files Modified:**
- `app/Http/Controllers/Api/ListingController.php` - Added fuzzy search methods
- `resources/js/components/layout/AppHeader.vue` - Complete redesign
- `database/migrations/2024_01_01_000019_add_fulltext_search_to_listings.php` - New

**How Fuzzy Search Works:**
1. User types "mobil" (typo for "mobile")
2. System generates variations: "mobile", "nobil", "mqbil", etc.
3. Also checks SOUNDEX: words that sound like "mobil"
4. Also checks Indian variations: "mobile" → "phone", "cell", "handset"
5. Returns all matching results

**Testing Fuzzy Search:**
- Search "mobil" → finds "mobile"
- Search "phne" → finds "phone"
- Search "apartmnt" → finds "apartment"
- Search "bicyle" → finds "bicycle"

---

### Session: January 2, 2026 (iOS Input Zoom Fix)

**Issue Reported:**
- Whole site zooms out when search field is clicked on iOS
- User has to pinch to zoom back in to normal view

**Root Cause:**
- iOS Safari auto-zooms when an input has `font-size` smaller than 16px
- This is a "feature" to help users see what they're typing, but causes layout issues

**Fix Applied:**

1. **Global CSS Rule** (`resources/js/assets/css/app.css`)
   - Added minimum 16px font-size for all inputs/textareas/selects on mobile
   - On desktop (768px+), reverts to inherit
   ```css
   @layer base {
     input, textarea, select {
       font-size: 16px;
     }
     @media (min-width: 768px) {
       input, textarea, select {
         font-size: inherit;
       }
     }
   }
   ```

2. **Chat Textarea** (`resources/js/views/dashboard/Conversation.vue`)
   - Changed from `font-size: 15px` to `font-size: 16px`

3. **Search Inputs** (`resources/js/components/layout/AppHeader.vue`)
   - Already had 16px - no change needed

**Technical Notes:**
- iOS Safari requires minimum 16px to prevent auto-zoom on focus
- The global rule ensures all form inputs get this fix automatically
- Desktop users unaffected (inherit allows smaller sizes)

**Files Modified:**
- `resources/js/assets/css/app.css` - Global iOS zoom prevention
- `resources/js/views/dashboard/Conversation.vue` - Chat textarea 16px

**Deploy Status:**
- Code pushed to GitHub
- SSH to server timed out - needs manual deployment:
  ```bash
  ssh master@139.59.24.36 "cd /home/master/applications/bpadwztsjg/public_html && git checkout . && git pull origin main"
  ```

---

*Last Updated: January 2, 2026 (iOS Input Zoom Fix)*
