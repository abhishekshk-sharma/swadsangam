# HTTP 500 Error - Troubleshooting Guide

## 🔴 IMMEDIATE FIXES (Try these first)

### Method 1: Via SSH (Recommended)

```bash
# 1. Navigate to your Laravel directory
cd /home/username/payserver  # Adjust path as needed

# 2. Check if .env file exists
ls -la .env

# 3. If .env doesn't exist, create it
cp .env.example .env

# 4. Generate APP_KEY
php artisan key:generate --force

# 5. Fix permissions
chmod -R 755 .
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# 6. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 7. Try accessing site now
```

### Method 2: Via cPanel File Manager

1. **Check .env file exists**
   - Navigate to `/home/username/payserver/`
   - Look for `.env` file (enable "Show Hidden Files")
   - If missing, copy `.env.example` and rename to `.env`

2. **Edit .env file**
   ```env
   APP_NAME="Swad Sangam"
   APP_ENV=production
   APP_KEY=base64:GENERATE_THIS_KEY
   APP_DEBUG=false
   APP_URL=https://swadsangam.store
   
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_user
   DB_PASSWORD=your_database_password
   ```

3. **Generate APP_KEY**
   - Go to cPanel → Terminal or use SSH
   - Run: `php artisan key:generate`
   - Or manually generate at: https://generate-random.org/laravel-key-generator
   - Paste in .env as: `APP_KEY=base64:YOUR_GENERATED_KEY`

4. **Fix Permissions**
   - Right-click on `storage` folder → Change Permissions → 775
   - Right-click on `bootstrap/cache` → Change Permissions → 775

### Method 3: Upload debug.php

1. Upload `debug.php` to your `public_html` folder
2. Visit: `https://swadsangam.store/debug.php`
3. This will show you exactly what's wrong
4. **DELETE debug.php after checking!**

---

## 🔍 COMMON CAUSES & SOLUTIONS

### Cause 1: Missing or Empty APP_KEY (80% of cases)

**Symptoms:** 500 error, blank page

**Fix:**
```bash
php artisan key:generate --force
```

Or manually in .env:
```env
APP_KEY=base64:abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGH=
```

### Cause 2: Wrong File Permissions (15% of cases)

**Symptoms:** 500 error, "Permission denied" in logs

**Fix:**
```bash
cd /home/username/payserver
chmod -R 755 .
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R username:username .  # Replace 'username' with your cPanel username
```

### Cause 3: Missing .env File (3% of cases)

**Symptoms:** 500 error immediately

**Fix:**
```bash
cp .env.example .env
php artisan key:generate
```

### Cause 4: Wrong index.php Paths (2% of cases)

**Check your public_html/index.php:**
```php
// Should point to your Laravel installation
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// OR if Laravel is in a different location:
require __DIR__.'/../../payserver/vendor/autoload.php';
$app = require_once __DIR__.'/../../payserver/bootstrap/app.php';
```

### Cause 5: Cached Config with Wrong Values

**Fix:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

---

## 📋 STEP-BY-STEP DIAGNOSTIC

### Step 1: Check Error Logs

**Via cPanel:**
1. Go to cPanel → Errors
2. Look for recent errors
3. Note the error message

**Via SSH:**
```bash
# Laravel log
tail -50 /home/username/payserver/storage/logs/laravel.log

# Server error log
tail -50 /home/username/logs/error_log
```

### Step 2: Enable Debug Mode (Temporarily)

Edit `.env`:
```env
APP_DEBUG=true
APP_ENV=local
```

Visit your site - you'll see the actual error message.

**IMPORTANT:** Set back to production after fixing:
```env
APP_DEBUG=false
APP_ENV=production
```

### Step 3: Check Database Connection

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

If error, check:
- Database name is correct
- Database user has permissions
- Password is correct
- Host is `localhost` (not 127.0.0.1)

### Step 4: Verify File Structure

Your structure should be:
```
/home/username/
├── public_html/              ← Web root
│   ├── index.php            ← Modified to point to Laravel
│   ├── .htaccess
│   ├── css/
│   ├── js/
│   └── uploads/
│
└── payserver/               ← Laravel installation
    ├── app/
    ├── bootstrap/
    │   └── cache/           ← Must be writable (775)
    ├── config/
    ├── database/
    ├── public/              ← Original public folder (not used)
    ├── resources/
    ├── routes/
    ├── storage/             ← Must be writable (775)
    │   └── logs/
    ├── vendor/
    ├── .env                 ← Must exist and have APP_KEY
    ├── artisan
    └── composer.json
```

---

## 🚨 EMERGENCY FIX (If nothing else works)

1. **Backup everything first!**

2. **Fresh .env file:**
```bash
cd /home/username/payserver
rm .env
cp .env.example .env
nano .env  # Edit with your settings
```

3. **Regenerate everything:**
```bash
php artisan key:generate --force
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
php artisan config:cache
php artisan route:cache
```

4. **Nuclear option - Clear all caches:**
```bash
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*
chmod -R 775 storage bootstrap/cache
```

---

## 📞 WHAT TO CHECK NEXT

If still getting 500 error, provide these details:

1. **Error from debug.php** (upload and run it)
2. **Last 20 lines of Laravel log:**
   ```bash
   tail -20 storage/logs/laravel.log
   ```
3. **Server error log:**
   ```bash
   tail -20 ~/logs/error_log
   ```
4. **PHP version:**
   ```bash
   php -v
   ```
5. **File permissions:**
   ```bash
   ls -la storage/
   ls -la bootstrap/cache/
   ```

---

## ✅ VERIFICATION CHECKLIST

After fixing, verify:
- [ ] .env file exists and has APP_KEY
- [ ] storage/ is writable (775)
- [ ] bootstrap/cache/ is writable (775)
- [ ] Database credentials are correct
- [ ] APP_URL matches your domain
- [ ] APP_DEBUG=false in production
- [ ] Caches are cleared
- [ ] Site loads without error

---

## 🎯 MOST LIKELY FIX FOR YOUR CASE

Based on fresh deployment, run these commands:

```bash
cd /home/username/payserver
php artisan key:generate --force
chmod -R 775 storage bootstrap/cache
php artisan config:clear
php artisan cache:clear
```

Then visit your site. This fixes 95% of 500 errors on fresh deployments.
