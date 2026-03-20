# Quick Reference - New Authentication System

## Helper Functions (Use These!)

```php
// Get current authenticated user (from any guard)
$user = current_user();

// Get current user ID
$userId = current_user_id();

// In Blade templates
{{ current_user()->name }}
{{ current_user()->email }}
{{ current_user()->role }}
```

## ❌ Don't Use (Old Way)
```php
auth()->user()      // Only checks web guard
auth()->id()        // Only checks web guard
Auth::user()        // Only checks web guard
```

## ✅ Use Instead (New Way)
```php
current_user()      // Checks all guards
current_user_id()   // Checks all guards
```

## User Tables Structure

| Table | Users | Guard | Tenant |
|-------|-------|-------|--------|
| super_admins | Platform admins | super_admin | No |
| admins | Restaurant admins | admin | Yes |
| employees | Waiter/Chef/Cashier | employee | Yes |

## Employee Roles (employees table)
- `waiter` - Takes orders
- `chef` - Prepares food
- `cashier` - Processes payments

## Login Redirects

| User Type | Redirect To |
|-----------|-------------|
| Admin | /admin/dashboard |
| Waiter | /waiter/dashboard |
| Chef | /cook/dashboard |
| Cashier | /cashier/dashboard |

## Middleware Usage

```php
// Old way
Route::middleware('auth')->group(function () {
    // routes
});

// New way
Route::middleware('multi.auth')->group(function () {
    // routes
});
```

## Common Patterns

### In Controllers
```php
// Get current user
$user = current_user();

// Get user ID for queries
$orders = Order::where('user_id', current_user_id())->get();

// Check if user exists
if (current_user()) {
    // User is authenticated
}
```

### In Blade Views
```blade
@if(current_user())
    <p>Welcome, {{ current_user()->name }}!</p>
@endif

<!-- With null safety -->
<p>{{ current_user()->name ?? 'Guest' }}</p>
```

### In Middleware
```php
// CheckRole middleware automatically uses multi-guard
Route::middleware(['multi.auth', 'role:waiter'])->group(function () {
    // Only waiters can access
});
```

## Quick Commands

```bash
# After any auth changes
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Check current user in tinker
php artisan tinker
>>> current_user()
>>> current_user_id()
```

## Troubleshooting

| Issue | Solution |
|-------|----------|
| "current_user() not found" | Run `composer dump-autoload` |
| Still getting null errors | Clear all caches |
| Wrong dashboard redirect | Check user role in database |
| 403 Unauthorized | Check user is_active = 1 |

## File Locations

```
app/
├── Helpers/
│   └── AuthHelper.php          # Main auth helper class
├── helpers.php                  # Global helper functions
└── Http/
    └── Middleware/
        ├── MultiGuardAuth.php   # Multi-guard middleware
        └── CheckRole.php        # Role checking middleware

config/
└── auth.php                     # Guard configuration

routes/
└── web.php                      # All routes use multi.auth
```

## Testing Checklist

- [ ] Run `composer dump-autoload`
- [ ] Clear all caches
- [ ] Test waiter login
- [ ] Test chef login
- [ ] Test cashier login
- [ ] Test admin login
- [ ] Verify no "null" errors
- [ ] Check all dashboards load
- [ ] Test logout functionality

## Need Help?

- See `AUTHENTICATION_TESTING_GUIDE.md` for detailed testing
- See `COMPLETE_UPDATE_SUMMARY.md` for full change list
- See `DATABASE_RESTRUCTURE_GUIDE.md` for data migration
