#!/bin/bash

# Lentloads Marketplace - Cloudways Deployment Script
# Run this script after uploading files to Cloudways via SSH or SFTP

set -e

echo "=========================================="
echo "  Lentloads Marketplace Deployment"
echo "=========================================="

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}Creating .env from .env.production...${NC}"
    cp .env.production .env
    echo -e "${RED}IMPORTANT: Edit .env file with your Cloudways database credentials!${NC}"
    echo "Run: nano .env"
    exit 1
fi

echo -e "${GREEN}[1/8] Installing Composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader

echo -e "${GREEN}[2/8] Generating application key...${NC}"
php artisan key:generate --force

echo -e "${GREEN}[3/8] Running database migrations...${NC}"
php artisan migrate --force

echo -e "${GREEN}[4/8] Seeding database...${NC}"
php artisan db:seed --force

echo -e "${GREEN}[5/8] Creating storage link...${NC}"
php artisan storage:link

echo -e "${GREEN}[6/8] Caching configuration...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo -e "${GREEN}[7/8] Setting permissions...${NC}"
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo -e "${GREEN}[8/8] Building frontend assets...${NC}"
npm install
npm run build

echo ""
echo -e "${GREEN}=========================================="
echo "  Deployment Complete!"
echo "==========================================${NC}"
echo ""
echo "Next steps:"
echo "1. Update .env with your domain and credentials"
echo "2. Set document root to 'public' folder in Cloudways"
echo "3. Enable SSL in Cloudways dashboard"
echo "4. Test your application at https://your-domain.com"
echo ""
