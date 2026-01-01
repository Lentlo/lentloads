# Lentloads Marketplace - Cloudways Deployment Guide

This guide will walk you through deploying Lentloads Marketplace on Cloudways.

## Prerequisites

- Cloudways account (https://www.cloudways.com)
- Domain name (optional, can use Cloudways subdomain initially)
- SFTP client (FileZilla, Cyberduck) or Git

---

## Step 1: Create a Cloudways Server

1. Log in to Cloudways Dashboard
2. Click **"Launch"** button
3. Select your preferences:
   - **Application**: PHP (Custom App)
   - **Server Size**: 1GB RAM minimum (2GB recommended)
   - **Cloud Provider**: DigitalOcean, Vultr, or AWS
   - **Location**: Choose closest to your target audience (e.g., Bangalore for India)
4. Click **"Launch Now"** and wait 5-10 minutes

---

## Step 2: Configure the Server

### Enable Required Services

1. Go to **Server** → **Settings & Packages**
2. Under **Packages**, ensure:
   - PHP 8.1 or higher
   - MySQL 8.0
3. Under **Advanced**, enable:
   - **Supervisor** (for queue workers)

### Get Server Credentials

1. Go to **Server** → **Master Credentials**
2. Note down:
   - SSH/SFTP Username
   - SSH/SFTP Password
   - Public IP

---

## Step 3: Configure the Application

1. Go to **Applications** → Your App
2. Note down from **Access Details**:
   - Application URL
   - MySQL Database Name
   - MySQL Username
   - MySQL Password

### Set Document Root

1. Go to **Application Settings**
2. Change **WEBROOT** from `public_html` to `public_html/public`
3. Save changes

---

## Step 4: Upload Files

### Option A: Using SFTP (Recommended for beginners)

1. Download and install FileZilla
2. Connect using:
   - Host: Your server's Public IP
   - Username: SSH Username
   - Password: SSH Password
   - Port: 22
3. Navigate to `applications/your-app/public_html/`
4. Upload ALL files from the Lentloads folder
5. Wait for upload to complete (may take 10-15 minutes)

### Option B: Using Git (Recommended for updates)

1. SSH into your server:
   ```bash
   ssh username@your-server-ip
   ```

2. Navigate to app folder:
   ```bash
   cd applications/your-app/public_html
   ```

3. Initialize and pull from your Git repository:
   ```bash
   git init
   git remote add origin https://github.com/your-username/lentloads.git
   git pull origin main
   ```

---

## Step 5: Configure Environment

1. SSH into your server or use Cloudways SSH terminal
2. Navigate to your app:
   ```bash
   cd applications/your-app/public_html
   ```

3. Create .env file:
   ```bash
   cp .env.production .env
   nano .env
   ```

4. Update these values with your Cloudways credentials:
   ```
   APP_URL=https://your-domain.com

   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_username
   DB_PASSWORD=your_database_password

   MAIL_HOST=smtp.mailgun.org
   MAIL_USERNAME=your_mail_username
   MAIL_PASSWORD=your_mail_password
   MAIL_FROM_ADDRESS=noreply@your-domain.com
   ```

5. Save and exit (Ctrl+X, Y, Enter)

---

## Step 6: Run Deployment

1. Make the deploy script executable:
   ```bash
   chmod +x deploy.sh
   ```

2. Run the deployment:
   ```bash
   ./deploy.sh
   ```

   Or run commands manually:
   ```bash
   # Install PHP dependencies
   composer install --no-dev --optimize-autoloader

   # Generate app key
   php artisan key:generate

   # Run migrations
   php artisan migrate --force

   # Seed database
   php artisan db:seed --force

   # Create storage link
   php artisan storage:link

   # Cache config
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache

   # Build frontend
   npm install
   npm run build

   # Set permissions
   chmod -R 775 storage bootstrap/cache
   ```

---

## Step 7: Set Up SSL (HTTPS)

1. Go to **Applications** → Your App → **SSL Certificate**
2. Choose **Let's Encrypt**
3. Enter your domain name
4. Click **Install Certificate**
5. Enable **Force HTTPS Redirect**

---

## Step 8: Configure Domain (Optional)

### If using custom domain:

1. Go to **Applications** → **Domain Management**
2. Add your domain name
3. Update your domain's DNS:
   - Add an **A Record** pointing to your server's IP
   - Example: `@ → 123.456.789.10`
   - Example: `www → 123.456.789.10`
4. Wait for DNS propagation (up to 24 hours)

---

## Step 9: Set Up Queue Worker (For Background Jobs)

1. SSH into your server
2. Create supervisor config:
   ```bash
   sudo nano /etc/supervisor/conf.d/lentloads-worker.conf
   ```

3. Add this configuration:
   ```ini
   [program:lentloads-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /home/master/applications/YOUR_APP/public_html/artisan queue:work --sleep=3 --tries=3
   autostart=true
   autorestart=true
   stopasgroup=true
   killasgroup=true
   user=master
   numprocs=1
   redirect_stderr=true
   stdout_logfile=/home/master/applications/YOUR_APP/public_html/storage/logs/worker.log
   ```

4. Start the worker:
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start lentloads-worker:*
   ```

---

## Step 10: Create Admin User

1. SSH into your server
2. Run:
   ```bash
   cd applications/your-app/public_html
   php artisan tinker
   ```

3. Create admin user:
   ```php
   $user = new App\Models\User();
   $user->name = 'Admin';
   $user->email = 'admin@your-domain.com';
   $user->password = bcrypt('your-secure-password');
   $user->role = 'admin';
   $user->email_verified_at = now();
   $user->save();
   exit
   ```

---

## Verification Checklist

After deployment, verify these work:

- [ ] Homepage loads at your domain
- [ ] Can register a new user
- [ ] Can log in
- [ ] Can create a listing
- [ ] Images upload successfully
- [ ] Admin panel accessible at /admin
- [ ] SSL certificate working (https://)

---

## Troubleshooting

### 500 Internal Server Error
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check permissions
chmod -R 775 storage bootstrap/cache
```

### Images Not Loading
```bash
# Recreate storage link
php artisan storage:link

# Check public/storage folder exists
ls -la public/
```

### Database Connection Error
```bash
# Test database connection
php artisan migrate:status

# Clear config cache
php artisan config:clear
```

### Page Not Found (404)
- Ensure document root is set to `public_html/public`
- Clear route cache: `php artisan route:clear`

---

## Updating the Application

To deploy updates:

```bash
# If using Git
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Run new migrations
php artisan migrate --force

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Support

For issues with:
- **Cloudways**: Contact Cloudways support via dashboard
- **Application**: Check Laravel logs in `storage/logs/`

---

**Your Lentloads Marketplace is now live!** 🎉
