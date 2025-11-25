#!/bin/bash

# Laravel Deployment Script
echo "Starting deployment..."

# Pull latest changes
git pull origin master

# Install/update composer dependencies
composer install --no-dev --optimize-autoloader

# Install/update npm dependencies
npm install

# Build assets
npm run build

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Recreate storage link
php artisan storage:link

# Set proper permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "Deployment completed!"
