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
ssh -i ~/.ssh/cloudways_rsa -o StrictHostKeyChecking=no "lentlo@12@139.59.24.36"
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
ssh -i ~/.ssh/cloudways_rsa -o StrictHostKeyChecking=no "lentlo@12@139.59.24.36" "cd /home/master/applications/bpadwztsjg/public_html && git pull origin main"
```

4. **Clear caches (if needed):**
```bash
ssh -i ~/.ssh/cloudways_rsa -o StrictHostKeyChecking=no "lentlo@12@139.59.24.36" "cd /home/master/applications/bpadwztsjg/public_html && php artisan optimize:clear"
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
- `app/Http/Controllers/Api/ListingController.php` - Accept slug or ID
- `bootstrap/app.php` - Laravel 10 compatibility
- `public/index.php` - Laravel 10 compatibility
- Created missing models: Banner, City, State, Country, PushSubscription

### Configuration
- `.gitignore` - Allow public/build folder
- `public/deploy.php` - Webhook for auto-deploy (currently broken, use SSH)
- `public/opcache-clear.php` - Clear OPcache utility

---

## Common Issues & Solutions

### 1. 500 Server Error after deployment
```bash
# Clear all caches
ssh -i ~/.ssh/cloudways_rsa -o StrictHostKeyChecking=no "lentlo@12@139.59.24.36" "cd /home/master/applications/bpadwztsjg/public_html && rm -rf bootstrap/cache/*.php && php artisan optimize:clear"

# Clear OPcache
curl -s 'https://phplaravel-1016958-6108537.cloudwaysapps.com/opcache-clear.php'
```

### 2. APP_KEY errors
DO NOT run `php artisan config:cache`. Just delete the cached config:
```bash
ssh -i ~/.ssh/cloudways_rsa -o StrictHostKeyChecking=no "lentlo@12@139.59.24.36" "cd /home/master/applications/bpadwztsjg/public_html && rm -f bootstrap/cache/config.php"
```

### 3. Git pull conflicts
```bash
ssh -i ~/.ssh/cloudways_rsa -o StrictHostKeyChecking=no "lentlo@12@139.59.24.36" "cd /home/master/applications/bpadwztsjg/public_html && git checkout . && git pull origin main"
```

### 4. Design not updating
- Run `npm run build` locally
- Commit and push the `public/build` folder
- Hard refresh browser: `Cmd + Shift + R`

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

## Pending/Future Tasks

- [ ] Fix auto-deploy webhook (public/deploy.php)
- [ ] Add more test listings with real images
- [ ] Implement user authentication UI improvements
- [ ] Add image upload functionality testing
- [ ] Mobile responsive testing
- [ ] PWA testing on mobile devices

---

## Session Notes

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

1. **Always test the site** after deployment before telling user it's ready
2. **Never run** `php artisan config:cache` - it breaks the site
3. **Always run** `npm run build` before committing frontend changes
4. **Use SSH key** at `~/.ssh/cloudways_rsa` for server access
5. **User has no coding knowledge** - provide complete solutions
6. **Commit build files** - `public/build` is tracked in git

---

*Last Updated: January 1, 2026*
