# Talabna Server Setup Guide

## 🚀 Complete Server Deployment Instructions

### Step 1: Upload Files to Server

1. **Upload all project files** to your server directory:
   ```
   /home/u693675641/public_html/talabna/
   ```
   or
   ```
   /home/u693675641/domains/talbna.cloud/public_html/
   ```

2. **Ensure these files are uploaded:**
   - `add_admin_permissions.php` (permission assignment script)
   - `server_deployment.sh` (deployment script)
   - All Laravel project files
   - Updated `config/adminlte.php` (with BUSINESS section permissions)

### Step 2: Server Environment Setup

1. **SSH into your server:**
   ```bash
   ssh -p 65002 u693675641@45.84.207.200
   ```

2. **Navigate to project directory:**
   ```bash
   cd /home/u693675641/public_html/talabna
   # or
   cd /home/u693675641/domains/talbna.cloud/public_html
   ```

3. **Set proper file permissions:**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 bootstrap/cache/
   chmod -R 755 public/
   chmod +x server_deployment.sh
   chmod +x add_admin_permissions.php
   ```

### Step 3: Environment Configuration

1. **Create/Update `.env` file:**
   ```bash
   cp .env.example .env
   ```

2. **Edit `.env` file with your server settings:**
   ```env
   APP_NAME=Talabna
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://talbna.cloud

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_username
   DB_PASSWORD=your_database_password

   CACHE_DRIVER=file
   SESSION_DRIVER=file
   QUEUE_CONNECTION=sync
   ```

### Step 4: Run Deployment Script

1. **Execute the deployment script:**
   ```bash
   ./server_deployment.sh
   ```

2. **Or run commands manually:**
   ```bash
   # Install dependencies
   composer install --no-dev --optimize-autoloader

   # Clear caches
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear

   # Run migrations
   php artisan migrate --force

   # Run seeders
   php artisan db:seed --class=RolesAndPermissionsSeeder --force

   # Add permissions to admin
   php add_admin_permissions.php

   # Optimize application
   php artisan optimize
   php artisan config:cache
   php artisan route:cache

   # Create storage link
   php artisan storage:link
   ```

### Step 5: Verify Installation

1. **Check application status:**
   ```bash
   php artisan about
   ```

2. **Test the application:**
   - Visit: `https://talbna.cloud`
   - Login with admin credentials:
     - Email: `kol.eljra7.90@gmail.com`
     - Password: `nedal135`

### Step 6: Troubleshooting

#### If you encounter permission issues:
```bash
chown -R u693675641:u693675641 .
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

#### If you encounter database issues:
```bash
php artisan migrate:fresh --seed
php add_admin_permissions.php
```

#### If you encounter cache issues:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Step 7: Verify BUSINESS Section

1. **Login to admin panel**
2. **Check sidebar menu** - you should now see:
   - MAIN NAVIGATION
   - SHORTCUTS
   - **BUSINESS** (with all subcategories visible)
     - Investor Dashboard
     - Investor Relations
     - Investment Tracking
     - Strategic Planning
     - Budget Planning
     - Expense Approvals
     - Budget Controls

### Step 8: Final Verification

1. **Test all menu items** in the BUSINESS section
2. **Verify permissions** are working correctly
3. **Check that no JavaScript errors** appear in browser console
4. **Test all CRUD operations** for different modules

## 🔧 Additional Server Commands

### Check PHP Version:
```bash
php -v
```

### Check Composer:
```bash
composer --version
```

### Check Laravel:
```bash
php artisan --version
```

### Check Database Connection:
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

### Monitor Logs:
```bash
tail -f storage/logs/laravel.log
```

## 📋 Post-Deployment Checklist

- [ ] All files uploaded to server
- [ ] Environment file configured correctly
- [ ] Database migrations completed
- [ ] Permissions assigned to admin user
- [ ] Caches cleared and optimized
- [ ] Storage links created
- [ ] BUSINESS section visible in sidebar
- [ ] All menu items accessible
- [ ] No JavaScript errors in console
- [ ] All CRUD operations working

## 🆘 Support

If you encounter any issues:

1. **Check Laravel logs:** `tail -f storage/logs/laravel.log`
2. **Check server error logs:** `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
3. **Verify file permissions:** `ls -la storage/`
4. **Test database connection:** `php artisan tinker`

## 🎯 Expected Results

After successful deployment:
- ✅ BUSINESS section visible in sidebar
- ✅ All subcategories under BUSINESS accessible
- ✅ Admin user has all necessary permissions
- ✅ No JavaScript errors
- ✅ All CRUD operations functional
- ✅ System Health page working with full width
- ✅ Service Posts management working properly 