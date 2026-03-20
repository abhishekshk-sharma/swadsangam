# Laravel Deployment Checklist for swadsangam.store

## 🔴 CRITICAL ISSUES TO CHECK

### 1. **File Structure on Server**
Your Laravel project should be structured like this on the server:
```
/home/username/
├── public_html/           (This is your document root - should contain ONLY public folder contents)
│   ├── index.php
│   ├── .htaccess
│   ├── css/
│   ├── js/
│   └── uploads/
├── payserver/             (Laravel app - OUTSIDE public_html for security)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   └── artisan
```

### 2. **Root .htaccess File** (Place in public_html root)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine on

    # Redirect www to non-www and enforce HTTPS
    RewriteCond %{HTTP_HOST} ^www\. [NC,OR]
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://swadsangam.store%{REQUEST_URI} [L,R=301,NE]

    # Handle Laravel routing
    RewriteCond %{REQUEST_URI} !^public
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

# Disable Directory listing
Options -Indexes

# Block files which need to be hidden
<Files ~ "\.(env|json|config.js|md|gitignore|gitattributes|lock|example)$">
    Order allow,deny
    Deny from all
</Files>
```

### 3. **Public Folder .htaccess** (Place in public_html/public/)
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 4. **Environment Configuration (.env)**
Update your `.env` file on the server:

```env
APP_NAME="Swad Sangam"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://swadsangam.store

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_DOMAIN=.swadsangam.store

CACHE_STORE=database
QUEUE_CONNECTION=database

FILESYSTEM_DISK=public
```

### 5. **Update index.php** (in public_html/public/index.php)
Change the paths to point to your Laravel installation:

```php
<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Adjust these paths based on your server structure
require __DIR__.'/../../payserver/vendor/autoload.php';

$app = require_once __DIR__.'/../../payserver/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
```

### 6. **Commands to Run on Server (via SSH)**

```bash
# Navigate to your Laravel directory
cd /home/username/payserver

# Install/Update dependencies
    composer install --optimize-autoloader --no-dev

# Generate application key (if not set)
php artisan key:generate

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Set proper permissions
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 7. **File Permissions**
```bash
# Laravel directories
chmod -R 755 /home/username/payserver
chmod -R 775 /home/username/payserver/storage
chmod -R 775 /home/username/payserver/bootstrap/cache

# Public directory
chmod -R 755 /home/username/public_html

# Uploads directory
chmod -R 775 /home/username/public_html/uploads
```

### 8. **Database Configuration**
- Create database on cPanel/Hosting panel
- Import your database dump
- Update `.env` with correct credentials
- Run migrations: `php artisan migrate --force`

### 9. **Storage Link**
```bash
# Create symbolic link from public/storage to storage/app/public
php artisan storage:link
```

### 10. **Common Issues & Solutions**

#### Issue: 500 Internal Server Error
**Solutions:**
- Check `.env` file exists and has correct values
- Run `php artisan key:generate`
- Check file permissions (755 for directories, 644 for files)
- Check error logs: `/home/username/logs/` or `storage/logs/laravel.log`
- Set `APP_DEBUG=true` temporarily to see error details

#### Issue: 404 Not Found on all routes
**Solutions:**
- Check `.htaccess` files are uploaded
- Verify `mod_rewrite` is enabled on server
- Check document root points to correct directory
- Verify index.php paths are correct

#### Issue: CSS/JS not loading
**Solutions:**
- Check `APP_URL` in `.env` matches your domain
- Run `php artisan storage:link`
- Check file permissions on public directory
- Clear browser cache

#### Issue: Database connection error
**Solutions:**
- Verify database credentials in `.env`
- Check database exists on server
- Ensure database user has proper permissions
- Try `DB_HOST=localhost` instead of `127.0.0.1`

#### Issue: Session/Cache errors
**Solutions:**
- Run `php artisan cache:clear`
- Run `php artisan config:clear`
- Check storage directory permissions
- Ensure `sessions` table exists in database

### 11. **Security Checklist**
- [ ] `APP_DEBUG=false` in production
- [ ] `APP_ENV=production`
- [ ] Strong `APP_KEY` generated
- [ ] `.env` file is NOT in public directory
- [ ] `storage/` and `bootstrap/cache/` have write permissions
- [ ] Database credentials are secure
- [ ] HTTPS is enforced
- [ ] Directory listing is disabled

### 12. **Testing After Deployment**
1. Visit `https://swadsangam.store` - Should show welcome page
2. Visit `https://swadsangam.store/login` - Should show login page
3. Try logging in with test credentials
4. Check if images/uploads are loading
5. Test all major features (orders, menu, tables, etc.)
6. Check browser console for JavaScript errors
7. Check network tab for failed requests

### 13. **Debug Mode (Temporary)**
If site is not working, temporarily enable debug:
```env
APP_DEBUG=true
APP_ENV=local
```
Visit the site to see detailed error messages, then set back to:
```env
APP_DEBUG=false
APP_ENV=production
```

### 14. **Check Server Requirements**
- PHP >= 8.1
- MySQL >= 5.7
- Required PHP Extensions:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML

### 15. **Contact Hosting Support If:**
- mod_rewrite is not enabled
- PHP version is too old
- Required PHP extensions are missing
- File permission issues persist
- .htaccess files are not working

---

## 📝 Quick Deployment Steps

1. Upload Laravel files to `/home/username/payserver/`
2. Move `public` folder contents to `/home/username/public_html/`
3. Update `index.php` paths in public_html
4. Upload both `.htaccess` files (root and public)
5. Create and configure `.env` file
6. Run: `composer install --no-dev`
7. Run: `php artisan key:generate`
8. Run: `php artisan migrate --force`
9. Run: `php artisan storage:link`
10. Set permissions: `chmod -R 775 storage bootstrap/cache`
11. Clear caches: `php artisan config:cache`
12. Test the site!

---

## 🆘 Still Not Working?

Check these files for errors:
- `storage/logs/laravel.log`
- Server error logs (usually in `/home/username/logs/`)
- Browser console (F12)
- Network tab in browser dev tools

Enable debug mode temporarily and share the exact error message.
