#!/bin/bash
# Quick fix script for Cloudways 500 error

echo "Fixing Laravel deployment..."

# 1. Create .env if missing
if [ ! -f .env ]; then
    cp .env.production .env
    echo "Created .env file - EDIT IT WITH YOUR DATABASE CREDENTIALS!"
fi

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Generate app key
php artisan key:generate --force

# 4. Fix permissions
chmod -R 775 storage bootstrap/cache
find storage -type d -exec chmod 775 {} \;
find storage -type f -exec chmod 664 {} \;

# 5. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 6. Create storage link
php artisan storage:link

echo ""
echo "Done! Now edit .env with your database credentials:"
echo "nano .env"
