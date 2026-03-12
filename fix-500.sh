#!/bin/bash
# Quick Fix Script for HTTP 500 Error
# Run this via SSH: bash fix-500.sh

echo "==================================="
echo "Laravel 500 Error Quick Fix Script"
echo "==================================="
echo ""

# Get the Laravel root directory (adjust if needed)
LARAVEL_DIR="/home/username/payserver"

echo "1. Checking if Laravel directory exists..."
if [ -d "$LARAVEL_DIR" ]; then
    echo "✓ Found: $LARAVEL_DIR"
else
    echo "✗ Laravel directory not found!"
    echo "Please edit this script and set the correct LARAVEL_DIR path"
    exit 1
fi

cd "$LARAVEL_DIR"

echo ""
echo "2. Checking .env file..."
if [ -f ".env" ]; then
    echo "✓ .env file exists"
else
    echo "✗ .env file missing!"
    if [ -f ".env.example" ]; then
        echo "Creating .env from .env.example..."
        cp .env.example .env
        echo "✓ .env file created"
    else
        echo "✗ .env.example also missing!"
    fi
fi

echo ""
echo "3. Generating APP_KEY..."
php artisan key:generate --force
echo "✓ APP_KEY generated"

echo ""
echo "4. Setting correct permissions..."
chmod -R 755 "$LARAVEL_DIR"
chmod -R 775 "$LARAVEL_DIR/storage"
chmod -R 775 "$LARAVEL_DIR/bootstrap/cache"
echo "✓ Permissions set"

echo ""
echo "5. Clearing all caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "✓ Caches cleared"

echo ""
echo "6. Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✓ Optimized"

echo ""
echo "7. Creating storage link..."
php artisan storage:link
echo "✓ Storage linked"

echo ""
echo "==================================="
echo "Fix script completed!"
echo "==================================="
echo ""
echo "Now try accessing your site again."
echo "If still not working, check:"
echo "  - storage/logs/laravel.log"
echo "  - Server error logs"
echo "  - Run debug.php in browser"
