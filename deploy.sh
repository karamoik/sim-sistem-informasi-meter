#!/bin/bash

# ============================================
# SIM Sistem Informasi Meter - Deploy Script
# Laravel + Vue SPA
# ============================================
# Usage: chmod +x deploy.sh && ./deploy.sh
# Run from project root directory on server
# ============================================

set -e

echo "========================================="
echo "  SIM Sistem Informasi Meter Deployment Script"
echo "========================================="

# --- Configuration ---
APP_DIR=$(pwd)
PHP_VERSION="8.3"
NODE_VERSION="18"
ENV_FILE=".env"
BACKUP_DIR="storage/backups"
DB_NAME=$(grep DB_DATABASE .env 2>/dev/null | cut -d '=' -f2)
DB_USER=$(grep DB_USERNAME .env 2>/dev/null | cut -d '=' -f2)
DB_PASS=$(grep DB_PASSWORD .env 2>/dev/null | cut -d '=' -f2)

# --- Helper Functions ---
success() { echo -e "\033[0;32m[SUCCESS]\033[0m $1"; }
error()   { echo -e "\033[0;31m[ERROR]\033[0m $1"; exit 1; }
info()    { echo -e "\033[0;36m[INFO]\033[0m $1"; }

# --- Pre-check ---
[ -f "artisan" ] || error "Not a Laravel project. Run from project root."
[ -f "$ENV_FILE" ] || error ".env file not found. Copy .env.example to .env first."

echo ""
info "Starting deployment in: $APP_DIR"
echo ""

# --- Step 1: Git Pull ---
if [ -d ".git" ]; then
    info "Pulling latest changes..."
    git pull origin main || git pull origin master
    success "Code updated"
else
    info "No git repo found, skipping pull"
fi

# --- Step 2: Install PHP Dependencies ---
info "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
success "Composer done"

# --- Step 3: Install & Build Frontend ---
if command -v node &> /dev/null; then
    info "Installing npm dependencies..."
    npm ci --prefer-offline
    info "Building frontend..."
    npm run build
    success "Frontend built"
else
    info "Node.js not found, skipping frontend build (pre-built assets assumed)"
fi

# --- Step 4: Environment Setup ---
if ! grep -q "APP_KEY=" "$ENV_FILE" || [ -z "$(grep APP_KEY= $ENV_FILE | cut -d '=' -f2)" ]; then
    info "Generating APP_KEY..."
    php artisan key:generate --force
    success "APP_KEY generated"
else
    info "APP_KEY already set"
fi

if ! grep -q "JWT_SECRET=" "$ENV_FILE" || [ -z "$(grep JWT_SECRET= $ENV_FILE | cut -d '=' -f2)" ]; then
    info "Generating JWT_SECRET..."
    php artisan jwt:secret --force
    success "JWT_SECRET generated"
else
    info "JWT_SECRET already set"
fi

# --- Step 5: Database Migration ---
info "Running migrations..."
php artisan migrate --force
success "Database migrated"

# --- Step 6: Storage Link ---
if [ ! -L "public/storage" ]; then
    info "Creating storage symlink..."
    php artisan storage:link
    success "Storage linked"
else
    info "Storage link exists"
fi

# --- Step 7: Cache Optimization ---
info "Caching config, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
success "Cache optimized"

# --- Step 8: Permissions ---
info "Setting permissions..."
CURRENT_USER=$(whoami)
if [ "$CURRENT_USER" = "root" ]; then
    chown -R www-data:www-data "$APP_DIR"
    chmod -R 755 "$APP_DIR"
    chmod -R 775 "$APP_DIR/storage"
    chmod -R 775 "$APP_DIR/bootstrap/cache"
    chmod -R 775 "$APP_DIR/public/build" 2>/dev/null || true
    success "Permissions set (www-data)"
else
    chmod -R 755 "$APP_DIR"
    chmod -R 775 "$APP_DIR/storage"
    chmod -R 775 "$APP_DIR/bootstrap/cache"
    success "Permissions set (current user)"
fi

# --- Step 9: Restart Queue Worker (if using Laravel Queue) ---
if [ -f "/etc/supervisor/conf.d/laravel-worker.conf" ]; then
    info "Restarting queue worker..."
    sudo supervisorctl restart laravel-worker:*
    success "Queue worker restarted"
fi

# --- Step 10: Restart PHP-FPM ---
if command -v systemctl &> /dev/null; then
    info "Restarting PHP-FPM..."
    sudo systemctl restart php${PHP_VERSION}-fpm 2>/dev/null || \
    sudo systemctl restart php-fpm 2>/dev/null || \
    sudo systemctl restart php8.2-fpm 2>/dev/null || \
    info "Could not restart PHP-FPM (may need manual restart)"
    success "PHP-FPM restarted"
fi

echo ""
echo "========================================="
echo "  Deployment Complete!"
echo "========================================="
echo ""
info "App URL: $(grep APP_URL .env | cut -d '=' -f2)"
info "Next steps:"
echo "  1. Make sure Nginx/Apache is configured"
echo "  2. Make sure SSL certificate is active"
echo "  3. Make sure MinIO bucket exists"
echo "  4. Make sure database exists and credentials match .env"
echo ""
