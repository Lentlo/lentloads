# Lentlo Ads - Project Notes for Claude

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

### Production Server
- **URL**: https://phplaravel-1016958-6108537.cloudwaysapps.com
- **Server IP**: 139.59.24.36
- **SSH User**: lentlo@12
- **SSH Password**: 789-=uioP
- **App Path**: /home/master/applications/bpadwztsjg/public_html

### Deploy Commands

**Automated deployment (use this):**
```bash
expect -c '
set timeout 120
spawn ssh -o StrictHostKeyChecking=no "lentlo@12@139.59.24.36" "cd /home/master/applications/bpadwztsjg/public_html && git clean -fd && git checkout . && git pull origin main && php artisan config:clear && php artisan cache:clear"
expect "password:"
send "789-=uioP\r"
expect eof
'
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
3. Run the automated deployment command above
4. Clear OPcache

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
