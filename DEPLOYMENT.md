# Laravel Deployment Guide

## The Storage Link Problem

The `public/storage` symlink doesn't transfer through git because:
- Git doesn't properly track symbolic links across systems
- The link is now in `.gitignore` to prevent conflicts

## Solutions

### Option 1: Automatic (Recommended)
The `composer.json` has been updated to automatically run `php artisan storage:link` after every `composer install` or `composer update`.

**On your server, after git pull, just run:**
```bash
composer install --no-dev --optimize-autoloader
```

This will automatically recreate the storage link.

### Option 2: Deployment Script
Use the provided `deploy.sh` script on your server:

```bash
# Make it executable (first time only)
chmod +x deploy.sh

# Run deployment
./deploy.sh
```

### Option 3: Manual
After every git pull on the server, run:
```bash
php artisan storage:link
```

## Complete Deployment Steps

### First Time Setup (on server)
```bash
# Clone repository
git clone <your-repo-url> /path/to/project
cd /path/to/project

# Copy environment file
cp .env.example .env

# Edit .env with production settings
nano .env

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Build assets
npm run build

# Create storage link (done automatically by composer)
php artisan storage:link

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Regular Updates (after git push)
```bash
# On server
cd /path/to/project

# Pull latest changes
git pull origin master

# Install/update dependencies (this will auto-run storage:link)
composer install --no-dev --optimize-autoloader

# Update npm packages if needed
npm install

# Build assets
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 755 storage bootstrap/cache
```

## Alternative: Automated Deployment with GitHub Actions

Create `.github/workflows/deploy.yml` to automatically deploy when you push:

```yaml
name: Deploy to Production

on:
  push:
    branches: [ master ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Deploy to server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.SERVER_HOST }}
          username: ${{ secrets.SERVER_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /path/to/project
            git pull origin master
            composer install --no-dev --optimize-autoloader
            npm install
            npm run build
            php artisan migrate --force
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
```

## Troubleshooting

### Storage link not working
```bash
# Remove existing link
rm public/storage

# Recreate it
php artisan storage:link
```

### Permission errors
```bash
# Fix permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Images not showing
Check:
1. Storage link exists: `ls -la public/storage`
2. Files exist: `ls -la storage/app/public`
3. Web server user has read permissions
4. `.env` has correct `APP_URL` and `FILESYSTEM_DISK=public`
