# Lentlo Ads - Project Notes for Claude

## Project Info
- **Domain**: lentloads.com (Production: phplaravel-1016958-6108537.cloudwaysapps.com)
- **App Name**: Lentlo Ads (OLX-like classifieds marketplace)
- **Region**: India

## Tech Stack
- **Backend**: Laravel 10 (PHP 8.x)
- **Frontend**: Vue.js 3 with Vite
- **State Management**: Pinia
- **Styling**: Tailwind CSS
- **Icons**: Heroicons (@heroicons/vue)
- **Mobile Apps**: Capacitor v8 (Android/iOS)
- **Maps**: Leaflet

## API Base Path
All API routes are prefixed with `/v1/`

---

## Recent Changes (Changelog)

### 2026-01-10 - Header & Navigation Overhaul (v1.7)
- **Fixed Android WebView header padding**: Buttons had extra vertical padding in app vs mobile web
  - Added explicit heights with `min-height`/`max-height`
  - Used `!important` declarations to override WebView defaults
  - Added `-webkit-appearance: none` for consistent rendering
  - Row 1 fixed at 36px, Row 2 fixed at 40px
- **Created All Ads page** (`AllAds.vue`):
  - Shows all main categories with colored icons
  - Lists all listings with "Load More" pagination
  - Sort options: Newest, Price Low-High, Price High-Low
- **Restructured Mobile Navigation**:
  - Removed: Chat button
  - Added: All Ads button (with Squares2X2Icon)
  - Moved: My Ads to where Chat was
  - Order: All Ads → Search → Post Ad → My Ads → Account/Login
- **CRITICAL FIX**: Added AllAds route to `router/mobile.js` (was causing 404 in app)

### 2026-01-09 - Android App Fixes (v1.2)
- **Fixed extra space on top**: Removed `env(safe-area-inset-top)` from mobile header
- **Fixed scroll collapse**: Improved scroll detection for WebView compatibility
- **Fixed splash screen**: Updated styles.xml, configured launchAutoHide: false
- **StatusBar configuration**: Runtime StatusBar style and background color

### 2026-01-09 - Mobile Header Redesign (v1.1)
- **Separate mobile/desktop headers**: Different layouts for mobile (< 768px) and desktop
- **Mobile header features**:
  - Gradient purple background (#6366f1 to #8b5cf6)
  - Two-row layout: Top bar (logo + actions) + Search bar
  - Safe area padding for notched devices

### 2026-01-03 - Capacitor Mobile App (v1.0)
- Initial Capacitor setup for Android
- Hash-based routing for native app
- API base URL detection for native vs web

---

## ⚠️ CRITICAL: Dual Router Architecture

### THE MOST IMPORTANT THING TO KNOW

This project has **TWO SEPARATE ROUTER FILES**:

| Router File | Used By | History Mode | Entry Point |
|-------------|---------|--------------|-------------|
| `router/index.js` | Web app, Browser | `createWebHistory()` or `createWebHashHistory()` | `main.js` |
| `router/mobile.js` | Capacitor Android/iOS | `createWebHashHistory()` | `main-capacitor.js` |

### Why This Matters

**When adding a new route, you MUST add it to BOTH routers!**

If you only add to `router/index.js`:
- ✅ Works on web (browser)
- ❌ **404 error** in Android/iOS app

**Real Example (2026-01-10):**
The All Ads page showed 404 in the app because the route was only added to `router/index.js`, not `router/mobile.js`.

### How to Add a New Route

1. **Add to `router/index.js`** (for web):
```javascript
const AllAds = () => import('@/views/AllAds.vue')

const routes = [
  // ... existing routes
  {
    path: '/all-ads',
    name: 'all-ads',
    component: AllAds,
    meta: { title: 'All Ads' }
  },
]
```

2. **Add to `router/mobile.js`** (for app):
```javascript
const AllAds = () => import('@/views/AllAds.vue')

const routes = [
  // ... existing routes
  {
    path: '/all-ads',
    name: 'all-ads',
    component: AllAds,
    meta: { title: 'All Ads' }
  },
]
```

### Entry Points

| File | Purpose | Router Import |
|------|---------|---------------|
| `main.js` | Web app entry | `import router from './router'` |
| `main-capacitor.js` | Capacitor app entry | `import router from './router/mobile'` |

### Build Configs

| Config File | Purpose | Entry Point |
|-------------|---------|-------------|
| `vite.config.js` | Web build | `main.js` |
| `vite.config.capacitor.js` | Capacitor build | `main-capacitor.js` |

---

## Key Files Reference

### Routing & Entry Points
- `resources/js/main.js` - Web app entry point
- `resources/js/main-capacitor.js` - Capacitor app entry point
- `resources/js/router/index.js` - Web router (history or hash mode)
- `resources/js/router/mobile.js` - **MOBILE ROUTER** (hash mode only)

### Layout Components
- `resources/js/components/layout/AppHeader.vue` - Header with location picker & search
- `resources/js/components/layout/MobileNav.vue` - Bottom mobile navigation
- `resources/js/components/layout/AppFooter.vue` - Footer

### Views
- `resources/js/views/Home.vue` - Homepage with hero, categories, featured listings
- `resources/js/views/AllAds.vue` - All categories + all listings with pagination
- `resources/js/views/Search.vue` - Search page with filters
- `resources/js/views/Categories.vue` - Categories list
- `resources/js/views/CategoryListings.vue` - Listings in a category
- `resources/js/views/ListingDetail.vue` - Single listing detail

### Stores
- `resources/js/stores/app.js` - App state (categories, location)
- `resources/js/stores/auth.js` - Authentication state
- `resources/js/stores/listings.js` - Listings state

### Services
- `resources/js/services/api.js` - Axios instance with interceptors

### Build Scripts
- `scripts/build-capacitor.js` - Builds Capacitor assets
- `vite.config.js` - Web Vite config
- `vite.config.capacitor.js` - Capacitor Vite config

---

## APK Versioning

### Current Version
- **Version Name:** 1.7
- **Version Code:** 8
- **APK Filename:** `LentloAds-v1.7-header-fix.apk`

### Version History

| Version | Code | Date | Changes |
|---------|------|------|---------|
| 1.7 | 8 | 2026-01-10 | Header padding fix, All Ads page, navigation restructure |
| 1.2 | 3 | 2026-01-09 | Fixed header space, splash screen, scroll collapse |
| 1.1 | 2 | 2026-01-09 | Mobile header redesign |
| 1.0 | 1 | 2026-01-03 | Initial Capacitor build |

### How to Update Version
1. Edit `android/app/build.gradle`:
   ```gradle
   versionCode 8  // Increment this
   versionName "1.7"  // Update this
   ```
2. Build: `npm run mobile:build`
3. Build APK: `JAVA_HOME="/Applications/Android Studio.app/Contents/jbr/Contents/Home" ./android/gradlew -p ./android assembleDebug`
4. Copy: `cp android/app/build/outputs/apk/debug/app-debug.apk ~/Desktop/LentloAds-v1.7.apk`

---

## Build Commands

### Web Build
```bash
npm run build          # Build for production web
npm run dev            # Development server
```

### Capacitor/Mobile Build
```bash
npm run mobile:build   # Build Capacitor assets + sync
npm run mobile:apk     # Build assets + create APK

# Manual APK build (if mobile:apk fails)
npm run mobile:build
JAVA_HOME="/Applications/Android Studio.app/Contents/jbr/Contents/Home" ./android/gradlew -p ./android assembleDebug
```

### APK Location
```
android/app/build/outputs/apk/debug/app-debug.apk
```

---

## Mobile Navigation Structure

### Current Navigation (Bottom Bar)
```
┌─────────┬─────────┬─────────┬─────────┬─────────┐
│ All Ads │ Search  │ Post Ad │ My Ads  │ Account │
│   ⊞⊞    │   🔍    │   ➕    │   📋    │   👤    │
└─────────┴─────────┴─────────┴─────────┴─────────┘
```

### Icons Used
- All Ads: `Squares2X2Icon` (4 squares)
- Search: `MagnifyingGlassIcon`
- Post Ad: `PlusIcon` (elevated button with gradient)
- My Ads: `ClipboardDocumentListIcon`
- Account: `UserCircleIcon` (logged out) / Avatar (logged in)

### File: `MobileNav.vue`
```javascript
// Navigation items
<router-link to="/all-ads">All Ads</router-link>
<router-link to="/search">Search</router-link>
<router-link to="/sell">Post Ad</router-link>  // Center elevated
<router-link to="/my-listings">My Ads</router-link>  // requiresAuth
<router-link to="/dashboard">Account</router-link>  // or /login
```

---

## Android WebView CSS Compatibility

### The Problem
Android WebView (used by Capacitor) renders elements differently than mobile Safari/Chrome. Buttons and inputs can have extra padding/height.

### The Solution: Aggressive CSS Resets

**Always use these for buttons in mobile header:**
```css
.mobile-button {
  height: 24px !important;
  min-height: 24px !important;
  max-height: 24px !important;
  padding: 0 10px !important;
  margin: 0 !important;
  border: none !important;
  line-height: 1 !important;
  box-sizing: border-box !important;
  -webkit-appearance: none !important;
  appearance: none !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
}
```

### Key Properties for WebView
- `!important` - Override WebView defaults
- `-webkit-appearance: none` - Disable native styling
- `min-height` + `max-height` - Prevent expansion
- `box-sizing: border-box` - Include padding in height
- `line-height: 1` - Prevent text from expanding height

### Current Header Heights (AppHeader.vue)
```css
.mobile-row-1 { height: 36px; min-height: 36px; max-height: 36px; }
.mobile-row-2 { height: 40px; min-height: 40px; max-height: 40px; }
.mobile-login-btn { height: 24px; }
.mobile-location-btn { height: 28px; }
.mobile-search-box { height: 28px; }
```

---

## Mobile Header Design

### Architecture
```vue
<!-- Mobile header (< 768px) -->
<div class="mobile-header">
  <div class="mobile-safe-area"></div>  <!-- Status bar space -->
  <div class="mobile-row-1">Logo + Actions (36px)</div>
  <div class="mobile-row-2">Location + Search (40px)</div>
</div>

<!-- Desktop header (>= 768px) -->
<div class="desktop-header">
  Logo + Location + Search + Actions + Post Ad
</div>
```

### Mobile Header Features
- **Gradient background**: `linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)`
- **Row 1 (36px)**: Logo (Lentlo Ads) + Notifications + Messages + Login/Avatar
- **Row 2 (40px)**: Location button + Search input
- **Safe area**: `env(safe-area-inset-top)` for notched phones

### Key CSS Classes
- `.mobile-header` - Visible < 768px
- `.desktop-header` - Visible >= 768px
- `.mobile-login-btn` - White button with purple text
- `.mobile-location-btn` - Semi-transparent white button

---

## Category Icons Mapping

### Used in Home.vue and AllAds.vue
```javascript
import {
  DevicePhoneMobileIcon,  // mobiles
  TruckIcon,              // vehicles, bikes
  HomeModernIcon,         // property, furniture
  BriefcaseIcon,          // jobs
  ComputerDesktopIcon,    // electronics
  ShoppingBagIcon,        // fashion, books
  WrenchScrewdriverIcon,  // services
  MusicalNoteIcon,        // music
  HeartIcon,              // sports
  Squares2X2Icon,         // default/fallback
} from '@heroicons/vue/24/outline'

const getCategoryIcon = (slug) => {
  const icons = {
    'mobiles': DevicePhoneMobileIcon,
    'vehicles': TruckIcon,
    'property': HomeModernIcon,
    'jobs': BriefcaseIcon,
    'electronics': ComputerDesktopIcon,
    'fashion': ShoppingBagIcon,
    'furniture': HomeModernIcon,
    'services': WrenchScrewdriverIcon,
    'bikes': TruckIcon,
    'books': ShoppingBagIcon,
    'sports': HeartIcon,
    'music': MusicalNoteIcon,
  }
  return icons[slug] || Squares2X2Icon
}

const getCategoryColor = (slug) => {
  const colors = {
    'mobiles': 'icon-purple',
    'vehicles': 'icon-blue',
    'property': 'icon-green',
    'jobs': 'icon-orange',
    'electronics': 'icon-indigo',
    'fashion': 'icon-pink',
    'furniture': 'icon-teal',
    'services': 'icon-amber',
  }
  return colors[slug] || 'icon-gray'
}
```

### Icon Color CSS Classes
```css
.icon-purple { background: #ede9fe; color: #7c3aed; }
.icon-blue { background: #dbeafe; color: #2563eb; }
.icon-green { background: #d1fae5; color: #059669; }
.icon-orange { background: #ffedd5; color: #ea580c; }
.icon-indigo { background: #e0e7ff; color: #4f46e5; }
.icon-pink { background: #fce7f3; color: #db2777; }
.icon-teal { background: #ccfbf1; color: #0d9488; }
.icon-amber { background: #fef3c7; color: #d97706; }
.icon-gray { background: #f1f5f9; color: #64748b; }
```

---

## Important UI Considerations

### Mobile Bottom Navigation
- **Height**: `calc(60px + env(safe-area-inset-bottom, 8px))`
- **Z-Index**: 9999 (below modals)

### Z-Index Hierarchy
| Element | Z-Index |
|---------|---------|
| Mobile Nav | 9999 |
| Chat Modal | 10000 |
| Location Picker Modal | 10001 |
| App Header | 100 |

### CSS Gotchas
- **NEVER use `overflow-x: hidden` on html/body** - breaks `position: sticky`
- Use `overflow-x: clip` instead
- iOS zooms on inputs with `font-size < 16px` - always use 16px for mobile inputs

---

## Deployment

### ⚠️ CRITICAL WARNING - READ BEFORE DEPLOYING ⚠️

**NEVER use these commands on production:**
- `git clean -fd` - DELETES ALL USER UPLOADS (images, files)
- `git reset --hard` - Can lose uncommitted changes
- `rm -rf storage/` - Deletes all uploads and cache

**User uploads are stored in `storage/app/public/` which is NOT tracked by git.**

### Production Server
- **URL**: https://phplaravel-1016958-6108537.cloudwaysapps.com
- **Server IP**: 139.59.24.36
- **SSH User**: lentlo@12
- **SSH Password**: 789-=uioP
- **App Path**: /home/master/applications/bpadwztsjg/public_html

### Safe Deploy Commands

**✅ SAFE - Standard deployment:**
```bash
/usr/bin/expect <<'EOF'
set timeout 180
spawn ssh -o StrictHostKeyChecking=no -o ConnectTimeout=30 "lentlo@12@139.59.24.36"
expect {
    "password:" {
        send "789-=uioP\r"
        expect {
            "$ " {
                send "cd /home/master/applications/bpadwztsjg/public_html && git pull origin main\r"
                expect -re {Already up|Updating|Fast-forward|files changed|$ }
                send "php artisan config:clear && php artisan cache:clear && php artisan view:clear\r"
                expect "$ "
                send "exit\r"
            }
        }
    }
    timeout {
        send_user "\nConnection timed out\n"
    }
}
expect eof
EOF
```

### After Code Changes
1. Run `npm run build` locally
2. Commit and push to GitHub
3. Run the SAFE deployment command above
4. Test the live site immediately

### Health Check Page
**URL:** https://phplaravel-1016958-6108537.cloudwaysapps.com/health-check

---

## Capacitor Configuration

### capacitor.config.json
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
    },
    "StatusBar": {
      "style": "LIGHT",
      "backgroundColor": "#6366f1"
    }
  }
}
```

### Installed Plugins
```
@capacitor/app           - App lifecycle events
@capacitor/camera        - Camera access
@capacitor/geolocation   - Location services
@capacitor/haptics       - Vibration feedback
@capacitor/keyboard      - Keyboard handling
@capacitor/push-notifications - Push notifications
@capacitor/share         - Native share
@capacitor/splash-screen - Splash screen
@capacitor/status-bar    - Status bar customization
```

### API Base URL (api.js)
```javascript
const getBaseURL = () => {
  if (Capacitor.isNativePlatform()) {
    return 'https://phplaravel-1016958-6108537.cloudwaysapps.com/api/v1'
  }
  return '/api/v1'
}
```

---

## ⚠️ INCIDENT LOG - Learn From Past Mistakes

### 2025-01-02: Image Data Loss
**What happened:** All user-uploaded images were permanently deleted
**Cause:** Used `git clean -fd` during deployment
**Lesson:** NEVER use `git clean` on production
**Prevention:** Safe deploy commands documented above

### 2026-01-10: All Ads 404 in App
**What happened:** All Ads page returned 404 in Android app but worked on web
**Cause:** Route only added to `router/index.js`, not `router/mobile.js`
**Lesson:** Always add routes to BOTH router files
**Prevention:** Documented dual router architecture above

---

## Complete Frontend File List

### Views (resources/js/views/)
- `Home.vue` - Homepage with hero, categories, listings
- `AllAds.vue` - All categories + all listings
- `Search.vue` - Search with filters
- `Categories.vue` - All categories list
- `CategoryListings.vue` - Listings in a category
- `ListingDetail.vue` - Single listing detail
- `UserProfile.vue` - Public user profile
- `StaticPage.vue` - CMS static pages
- `NotFound.vue` - 404 page

### Auth Views (resources/js/views/auth/)
- `Login.vue`, `Register.vue`, `ForgotPassword.vue`, `ResetPassword.vue`

### Dashboard Views (resources/js/views/dashboard/)
- `Dashboard.vue` - User dashboard
- `MyListings.vue` - User's listings
- `CreateListing.vue` - New listing form
- `EditListing.vue` - Edit listing
- `Favorites.vue` - Saved listings
- `Messages.vue` - Conversations list
- `Conversation.vue` - Chat thread
- `Settings.vue` - Account settings
- `MyReviews.vue` - Reviews received
- `SavedSearches.vue` - Saved search alerts
- `Notifications.vue` - Notifications

### Admin Views (resources/js/views/admin/)
- `Dashboard.vue`, `Users.vue`, `Listings.vue`, `Categories.vue`
- `Reports.vue`, `Settings.vue`, `Conversations.vue`, `ContactViews.vue`

### Components (resources/js/components/)
- **Layout:** `AppHeader.vue`, `AppFooter.vue`, `MobileNav.vue`, `AdminHeader.vue`
- **Common:** `ListingCard.vue`, `LoadingSpinner.vue`, `LocationPicker.vue`
- **Modals:** `ChatModal.vue`, `ConfirmModal.vue`, `ReportModal.vue`

---

## Testing Checklist

### After Adding New Routes
```
□ Route added to router/index.js
□ Route added to router/mobile.js
□ npm run build works
□ npm run mobile:build works
□ Test on web browser
□ Test on Android app (APK)
```

### After Deployment
```
□ Homepage loads
□ Listings display with images
□ Search works
□ Login/logout works
□ Mobile navigation works
□ All Ads page works
```

### After APK Build
```
□ App opens without blank screen
□ Navigation works
□ API calls succeed
□ All routes accessible (no 404)
□ Header renders correctly
□ Bottom nav works
```
