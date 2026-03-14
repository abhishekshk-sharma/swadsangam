# Authentication System Testing Guide

## Issue Fixed
**Error**: "Attempt to read property 'name' on null" in employee panel layouts

**Root Cause**: Layouts were using `auth()->user()` which returns null because employees authenticate through `employee` guard, not the default `web` guard.

**Solution**: Updated all layouts and controllers to use `current_user()` and `current_user_id()` helper functions that check all guards.

## Files Updated

### Layouts Fixed
1. **resources/views/layouts/cashier.blade.php** - Line 18
2. **resources/views/layouts/waiter.blade.php** - Line 18
3. **resources/views/layouts/cook.blade.php** - Line 18
4. **resources/views/layouts/admin.blade.php** - Lines 408, 421-423

### Controllers Fixed
1. **app/Http/Controllers/Waiter/DashboardController.php** - Line 37
2. **app/Http/Controllers/Waiter/OrderController.php** - Line 50

## Testing Checklist

### 1. Employee Login Tests

#### Waiter Login
- [ ] Navigate to `/login`
- [ ] Enter waiter credentials (email/password)
- [ ] Should redirect to `/waiter/dashboard`
- [ ] Top bar should display waiter's name
- [ ] No "null" or error messages

#### Chef Login
- [ ] Navigate to `/login`
- [ ] Enter chef credentials (email/password)
- [ ] Should redirect to `/cook/dashboard`
- [ ] Top bar should display chef's name with "- Chef"
- [ ] No "null" or error messages

#### Cashier Login
- [ ] Navigate to `/login`
- [ ] Enter cashier credentials (email/password)
- [ ] Should redirect to `/cashier/dashboard`
- [ ] Top bar should display cashier's name with "- Cashier"
- [ ] No "null" or error messages

### 2. Admin Login Tests

#### Admin Login
- [ ] Navigate to `/login`
- [ ] Enter admin credentials (email/password)
- [ ] Should redirect to `/admin/dashboard`
- [ ] Top bar should display admin's name and email
- [ ] Sidebar should show role (Admin Panel)
- [ ] User avatar should show first letter of name

### 3. Functionality Tests

#### Waiter Panel
- [ ] Dashboard loads without errors
- [ ] Can view orders list
- [ ] Can create new order
- [ ] Orders show correct waiter name
- [ ] Profile link works

#### Cook Panel
- [ ] Dashboard loads without errors
- [ ] Can view pending orders
- [ ] Can view processing orders
- [ ] Can view completed orders
- [ ] Can update order status
- [ ] Profile link works

#### Cashier Panel
- [ ] Dashboard loads without errors
- [ ] Can view payments list
- [ ] Can process payments
- [ ] Can view payment history
- [ ] Profile link works

#### Admin Panel
- [ ] Dashboard loads without errors
- [ ] Can manage employees
- [ ] Can manage tables
- [ ] Can manage menu items
- [ ] Can view reports
- [ ] Profile link works

### 4. Logout Tests
- [ ] Waiter logout redirects to `/login`
- [ ] Chef logout redirects to `/login`
- [ ] Cashier logout redirects to `/login`
- [ ] Admin logout redirects to `/login`
- [ ] After logout, cannot access protected routes

### 5. Authorization Tests
- [ ] Waiter cannot access `/cook/dashboard`
- [ ] Chef cannot access `/waiter/dashboard`
- [ ] Cashier cannot access `/admin/dashboard`
- [ ] Unauthorized access shows 403 error

## Before Testing

### 1. Run Composer Autoload
```bash
composer dump-autoload
```

### 2. Clear All Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 3. Verify Database
Ensure you have test users in the `employees` table:
```sql
-- Check employees
SELECT id, name, email, role, is_active FROM employees;

-- Check admins
SELECT id, name, email, is_active FROM admins;
```

## Creating Test Users

If you need to create test users for testing:

```sql
-- Create test waiter
INSERT INTO employees (tenant_id, name, email, password, role, is_active, created_at, updated_at)
VALUES (1, 'Test Waiter', 'waiter@test.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANh98WwnZpu', 'waiter', 1, NOW(), NOW());
-- Password: 'password'

-- Create test chef
INSERT INTO employees (tenant_id, name, email, password, role, is_active, created_at, updated_at)
VALUES (1, 'Test Chef', 'chef@test.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANh98WwnZpu', 'chef', 1, NOW(), NOW());
-- Password: 'password'

-- Create test cashier
INSERT INTO employees (tenant_id, name, email, password, role, is_active, created_at, updated_at)
VALUES (1, 'Test Cashier', 'cashier@test.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANh98WwnZpu', 'cashier', 1, NOW(), NOW());
-- Password: 'password'

-- Create test admin
INSERT INTO admins (tenant_id, name, email, password, is_active, created_at, updated_at)
VALUES (1, 'Test Admin', 'admin@test.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANh98WwnZpu', 1, NOW(), NOW());
-- Password: 'password'
```

## Common Issues & Solutions

### Issue: "current_user() function not found"
**Solution**: Run `composer dump-autoload`

### Issue: Still getting "null" errors
**Solution**: 
1. Clear all caches
2. Check if user is in correct table (employees vs admins)
3. Verify tenant_id matches session tenant_id

### Issue: Login redirects to wrong dashboard
**Solution**: Check user's role in database matches expected role

### Issue: 403 Unauthorized error
**Solution**: 
1. Verify user has correct role
2. Check middleware in routes/web.php
3. Ensure user is_active = 1

## Success Criteria

All tests pass when:
- ✅ No "null" property errors
- ✅ User names display correctly in all layouts
- ✅ Login redirects to correct dashboard
- ✅ All CRUD operations work
- ✅ Logout works properly
- ✅ Authorization prevents unauthorized access

## Next Steps After Testing

1. Migrate production data from `users` table to new structure
2. Update any remaining views that use `auth()->user()`
3. Test with real production scenarios
4. Monitor error logs for any auth-related issues
