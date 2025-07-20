#!/bin/bash

# Server Deployment Script for Talabna Laravel Application
# Run this script on your server after uploading the files

echo "=== Talabna Server Deployment Script ==="
echo "Starting deployment process..."

# 1. Navigate to project directory (adjust path as needed)
cd /home/u693675641/public_html/talabna || cd /home/u693675641/domains/talbna.cloud/public_html || cd /var/www/html

echo "Current directory: $(pwd)"

# 2. Set proper file permissions
echo "Setting file permissions..."
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod -R 755 public/
chmod -R 644 .env

# 3. Install/Update Composer dependencies
echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

# 4. Clear all caches
echo "Clearing application caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 5. Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# 6. Run seeders
echo "Running database seeders..."
php artisan db:seed --class=RolesAndPermissionsSeeder --force

# 7. Add all permissions to admin user
echo "Adding permissions to admin user..."
php add_admin_permissions.php

# 8. Optimize the application
echo "Optimizing application..."
php artisan optimize
php artisan config:cache
php artisan route:cache

# 9. Set proper ownership (if needed)
echo "Setting file ownership..."
chown -R u693675641:u693675641 . || chown -R www-data:www-data .

# 10. Create storage links
echo "Creating storage links..."
php artisan storage:link

# 11. Check application status
echo "Checking application status..."
php artisan about

echo "=== Deployment Completed Successfully ==="
echo "Please check the application at your domain."
echo "Admin credentials:"
echo "Email: kol.eljra7.90@gmail.com"
echo "Password: nedal135" 