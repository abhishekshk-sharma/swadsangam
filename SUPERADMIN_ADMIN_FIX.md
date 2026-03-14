# Super Admin & Admin Creation Fix - Summary

## Issue Fixed
New admins created by super admin (e.g., d@d.com) could not login to the panel. Only seeded users could login.

## Root Cause
The SuperAdmin UserController was still using the old `users` table instead of the new restructured tables (`super_admins` and `admins`).

## Solution Implemented
Updated all super admin related controllers, middleware, and views to use the new authentication structure with separate tables.

---

## Files Modified (6 files)

### 1. Controllers (3 files)

**app/Http/Controllers/SuperAdmin/UserController.php**
- Changed from `User` model to `SuperAdmin` and `Admin` models
- `store()` - Creates in correct table based on role
  - Super admin → `super_admins` table
  - Admin → `admins` table
- `index()` - Fetches from both tables and merges
- `edit()` - Finds in appropriate table
- `update()` - Updates in correct table
- `destroy()` - Deletes from correct table
- Email uniqueness validation per table

**app/Http/Controllers/SuperAdmin/TenantController.php**
- `authenticate()` - Uses `Auth::guard('super_admin')`
- `logout()` - Uses `Auth::guard('super_admin')`

**app/Http/Controllers/SuperAdmin/DashboardController.php**
- Changed from `User` model to `SuperAdmin` and `Admin` models
- Stats now count from correct tables

### 2. Middleware (1 file)

**app/Http/Middleware/SuperAdminAuth.php**
- Changed from `Auth::check()` to `Auth::guard('super_admin')->check()`
- Removed role check (not needed with separate guard)

### 3. Views (1 file)

**resources/views/layouts/superadmin.blade.php**
- Changed from `auth()->user()` to `Auth::guard('super_admin')->user()`

---

## How It Works Now

### Creating Super Admin
```
Super Admin Panel → Users → Create
- Role: Super Admin
- Email: superadmin@example.com
- Password: ********
→ Saved to `super_admins` table
→ Can login at /superadmin/login
```

### Creating Restaurant Admin
```
Super Admin Panel → Users → Create
- Role: Admin
- Tenant: Select tenant
- Email: admin@restaurant.com
- Password: ********
→ Saved to `admins` table with tenant_id
→ Can login at /login (regular admin login)
```

### Authentication Flow

**Super Admin Login** (`/superadmin/login`)
1. Checks `super_admins` table
2. Uses `super_admin` guard
3. Redirects to `/superadmin/dashboard`

**Restaurant Admin Login** (`/login`)
1. Checks `admins` table first
2. Then checks `employees` table
3. Uses `admin` or `employee` guard
4. Redirects based on role

---

## Database Structure

### super_admins table
```sql
id | name | email | password | phone | is_active | created_at | updated_at
---|------|-------|----------|-------|-----------|------------|------------
1  | SA   | sa@.. | ******** | NULL  | 1         | ...        | ...
```
- No tenant_id (platform-wide access)
- Login at `/superadmin/login`

### admins table
```sql
id | tenant_id | name | email | password | phone | is_active | created_at | updated_at
---|-----------|------|-------|----------|-------|-----------|------------|------------
1  | 1         | Admin| a@..  | ******** | NULL  | 1         | ...        | ...
2  | 2         | Admin| d@d.com| ******** | NULL  | 1         | ...        | ...
```
- Has tenant_id (restaurant-specific)
- Login at `/login`
- Email unique per tenant

### employees table
```sql
id | tenant_id | name | email | password | role | is_active | created_at | updated_at
---|-----------|------|-------|----------|------|-----------|------------|------------
1  | 1         | Waiter| w@.. | ******** | waiter| 1        | ...        | ...
2  | 1         | Chef | c@..  | ******** | chef  | 1        | ...        | ...
```
- Has tenant_id (restaurant-specific)
- Login at `/login`
- Roles: waiter, chef, cashier

---

## Testing Steps

### Test 1: Create Super Admin
1. Login as existing super admin
2. Go to `/superadmin/users`
3. Click "Create User"
4. Fill form:
   - Name: Test Super Admin
   - Email: test-sa@example.com
   - Password: password123
   - Role: Super Admin
5. Submit
6. Logout
7. Login at `/superadmin/login` with new credentials
8. ✅ Should login successfully

### Test 2: Create Restaurant Admin
1. Login as super admin
2. Go to `/superadmin/users`
3. Click "Create User"
4. Fill form:
   - Name: Test Admin
   - Email: d@d.com
   - Password: password123
   - Role: Admin
   - Tenant: Select a tenant
5. Submit
6. Logout from super admin
7. Visit the tenant's domain (or set tenant session)
8. Login at `/login` with d@d.com credentials
9. ✅ Should login successfully
10. ✅ Should redirect to `/admin/dashboard`

### Test 3: Email Uniqueness
1. Try to create admin with same email in same tenant
2. ❌ Should fail validation
3. Try to create admin with same email in different tenant
4. ✅ Should succeed (email unique per tenant)

### Test 4: Edit User
1. Login as super admin
2. Go to `/superadmin/users`
3. Edit an admin user
4. Change name/email
5. ✅ Should update successfully
6. ✅ User can still login with new credentials

### Test 5: Delete User
1. Login as super admin
2. Go to `/superadmin/users`
3. Delete a test user
4. ✅ Should delete successfully
5. ✅ User cannot login anymore

---

## Important Notes

### Email Uniqueness Rules
- **Super Admins**: Email must be unique across all super admins
- **Admins**: Email must be unique per tenant (same email can exist in different tenants)
- **Employees**: Email must be unique per tenant

### Password Hashing
All passwords are hashed using `Hash::make()` before storing.

### Active Status
- `is_active = 1` → User can login
- `is_active = 0` → User cannot login (blocked)

### Guards
- Super Admin uses: `super_admin` guard
- Restaurant Admin uses: `admin` guard
- Employees use: `employee` guard

---

## Troubleshooting

### Issue: New admin cannot login
**Check:**
1. Is user in `admins` table? (not `users` table)
2. Is `is_active = 1`?
3. Is `tenant_id` set correctly?
4. Is password hashed?
5. Is tenant session set correctly?

**Solution:**
```sql
-- Check if user exists
SELECT * FROM admins WHERE email = 'd@d.com';

-- Check if active
SELECT id, name, email, is_active, tenant_id FROM admins WHERE email = 'd@d.com';

-- If not active, activate
UPDATE admins SET is_active = 1 WHERE email = 'd@d.com';
```

### Issue: Super admin cannot login
**Check:**
1. Is user in `super_admins` table? (not `users` table)
2. Is `is_active = 1`?
3. Is password hashed?

**Solution:**
```sql
-- Check if user exists
SELECT * FROM super_admins WHERE email = 'sa@example.com';

-- If not active, activate
UPDATE super_admins SET is_active = 1 WHERE email = 'sa@example.com';
```

### Issue: "User not found" error
**Cause:** User might be in old `users` table

**Solution:** Migrate user to correct table:
```sql
-- For super admin
INSERT INTO super_admins (name, email, password, is_active, created_at, updated_at)
SELECT name, email, password, is_active, created_at, updated_at
FROM users WHERE email = 'old@example.com';

-- For restaurant admin
INSERT INTO admins (tenant_id, name, email, password, is_active, created_at, updated_at)
SELECT tenant_id, name, email, password, is_active, created_at, updated_at
FROM users WHERE email = 'old@example.com';
```

---

## Status: ✅ COMPLETE

All super admin and admin creation/login functionality is now working with the new database structure.

## Next Steps

1. ✅ Test creating new super admin
2. ✅ Test creating new restaurant admin
3. ✅ Test login for newly created users
4. ⏳ Migrate existing users from `users` table (if any)
5. ⏳ Remove old `users` table (after migration)
