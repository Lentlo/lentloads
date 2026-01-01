# Lentloads Marketplace - Setup Guide

A complete OLX-like marketplace built with Laravel + Vue.js PWA.

## Requirements

- PHP 8.1+
- Composer
- Node.js 18+
- MySQL 8.0+ or PostgreSQL
- Redis (optional, for caching/queues)

## Quick Start (Local Development)

### 1. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 2. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Configure Database

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lentloads_marketplace
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Run Migrations & Seed Data

```bash
# Create database tables
php artisan migrate

# Seed initial data (categories, admin user, etc.)
php artisan db:seed
```

### 5. Create Storage Link

```bash
php artisan storage:link
```

### 6. Start Development Servers

```bash
# Terminal 1: Laravel backend
php artisan serve

# Terminal 2: Vue.js frontend (with hot reload)
npm run dev
```

Visit `http://localhost:8000` to see the app.

**Default Admin Login:**
- Email: `admin@lentloads.com`
- Password: `password`

---

## Cloudways Deployment

### 1. Create Application on Cloudways

1. Log in to Cloudways
2. Create a new PHP application
3. Choose Laravel as the application type
4. Select server size (2GB RAM minimum recommended)

### 2. Connect Git Repository

1. Go to Application > Deployment via Git
2. Connect your GitHub/GitLab repository
3. Set branch to `main` or `master`

### 3. Configure Environment

1. Go to Application > Application Settings
2. Set document root to: `public_html/public`

SSH into your server and configure:

```bash
cd ~/public_html

# Copy and edit environment file
cp .env.example .env
nano .env

# Update these values:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

### 4. Install Dependencies & Build

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
npm install

# Build frontend for production
npm run build

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Seed database
php artisan db:seed --force

# Create storage link
php artisan storage:link

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Set Permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 6. Configure SSL

1. Go to Application > SSL Certificate
2. Enable Let's Encrypt SSL
3. Force HTTPS redirect

---

## Features Included

### User Features
- User registration & authentication
- Profile management with avatar
- Email verification
- Password reset

### Listings
- Create, edit, delete listings
- Multi-image upload with compression
- Category & subcategory organization
- Advanced search with filters
- Location-based search
- Mark as sold/renew listing
- Favorites/watchlist

### Messaging
- Real-time chat between buyers & sellers
- Make offers within chat
- Block users

### Reviews & Ratings
- Rate buyers and sellers
- Respond to reviews

### Admin Panel
- Dashboard with statistics
- Manage users (suspend, verify sellers)
- Approve/reject listings
- Manage categories
- Handle reports
- Site settings

### PWA Features
- Installable on mobile devices
- Offline capability
- Push notifications ready
- App-like experience

---

## API Endpoints Reference

All API endpoints are prefixed with `/api/v1/`

### Authentication
```
POST /auth/register
POST /auth/login
POST /auth/logout
GET  /auth/user
PUT  /auth/profile
POST /auth/avatar
PUT  /auth/password
POST /auth/forgot-password
POST /auth/reset-password
```

### Listings
```
GET    /listings
GET    /listings/{slug}
POST   /listings
PUT    /listings/{id}
DELETE /listings/{id}
POST   /listings/{id}/sold
POST   /listings/{id}/renew
GET    /my-listings
```

### Categories
```
GET /categories
GET /categories/{slug}
GET /categories/featured
GET /categories/tree
```

### Search
```
GET /search
GET /search/suggestions
GET /search/trending
```

### Favorites
```
GET  /favorites
POST /favorites/toggle/{id}
```

### Conversations
```
GET  /conversations
POST /conversations
GET  /conversations/{uuid}
POST /conversations/{uuid}/messages
```

### Reviews
```
GET  /reviews/user/{id}
POST /reviews
POST /reviews/{id}/respond
```

---

## Customization

### Adding New Categories

1. Go to Admin Panel > Categories
2. Click "Add Category"
3. Fill in name, icon, and parent (if subcategory)

Or via seeder:
```php
Category::create([
    'name' => 'New Category',
    'slug' => 'new-category',
    'is_active' => true,
]);
```

### Changing Theme Colors

Edit `tailwind.config.js`:

```js
theme: {
  extend: {
    colors: {
      primary: {
        600: '#your-color',
        // ... other shades
      }
    }
  }
}
```

Then rebuild: `npm run build`

### Adding Payment Gateway

1. Install payment SDK (Razorpay, Stripe, etc.)
2. Add credentials to `.env`
3. Create payment controller in `app/Http/Controllers/Api/PaymentController.php`
4. Add routes in `routes/api.php`

---

## Maintenance Commands

```bash
# Clear all caches
php artisan optimize:clear

# Rebuild caches
php artisan optimize

# Run queue worker
php artisan queue:work

# Run scheduled tasks
php artisan schedule:run
```

---

## Troubleshooting

### Images not loading
```bash
php artisan storage:link
chmod -R 775 storage
```

### 500 Server Error
```bash
php artisan config:clear
php artisan cache:clear
chmod -R 775 storage bootstrap/cache
```

### Database connection failed
- Check `.env` database credentials
- Ensure database exists
- Check MySQL is running

---

## Support

For issues and feature requests, please create an issue in the repository.
