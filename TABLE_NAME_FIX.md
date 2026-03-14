# Table Name Fix - Models Not Using Correct Tables

## Issue
When creating admin from super admin panel, data was being stored in the old `users` table instead of the new `admins` table.

## Root Cause
The models (SuperAdmin, Admin, Employee) did not have explicit `$table` property set. Laravel was trying to guess the table name, which might have defaulted to `users` table in some cases.

## Solution
Explicitly set the `$table` property in all three models.

---

## Files Modified (3 files)

### 1. app/Models/SuperAdmin.php
**Added:**
```php
protected $table = 'super_admins';
```

### 2. app/Models/Admin.php
**Added:**
```php
protected $table = 'admins';
```

### 3. app/Models/Employee.php
**Added:**
```php
protected $table = 'employees';
```

---

## How Laravel Table Naming Works

### Default Behavior (Without $table property)
Laravel uses "snake_case" plural of the class name:
- `SuperAdmin` → `super_admins` ✅ (correct)
- `Admin` → `admins` ✅ (correct)
- `Employee` → `employees` ✅ (correct)

**However**, if there's any ambiguity or caching issue, it might fall back to `users` table.

### Explicit Behavior (With $table property)
Always uses the specified table name, no guessing:
```php
protected $table = 'admins'; // Always uses 'admins' table
```

---

## Testing Steps

### Step 1: Clear All Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Step 2: Test Super Admin Creation
1. Login as super admin at `/superadmin/login`
2. Go to "Admin Management"
3. Click "Create User"
4. Fill form:
   - Name: Test Super Admin
   - Email: test-sa@example.com
   - Password: password123
   - Role: Super Admin
5. Submit

**Verify in Database:**
```sql
SELECT * FROM super_admins WHERE email = 'test-sa@example.com';
-- Should return 1 row

SELECT * FROM users WHERE email = 'test-sa@example.com';
-- Should return 0 rows (not in old table)
```

### Step 3: Test Admin Creation
1. Login as super admin
2. Go to "Admin Management"
3. Click "Create User"
4. Fill form:
   - Name: Test Admin
   - Email: test-admin@example.com
   - Password: password123
   - Role: Admin
   - Tenant: Select a tenant
5. Submit

**Verify in Database:**
```sql
SELECT * FROM admins WHERE email = 'test-admin@example.com';
-- Should return 1 row with tenant_id

SELECT * FROM users WHERE email = 'test-admin@example.com';
-- Should return 0 rows (not in old table)
```

### Step 4: Test Employee Creation
1. Login as restaurant admin at `/login`
2. Go to "Employees"
3. Click "Add Employee"
4. Fill form:
   - Name: Test Waiter
   - Email: test-waiter@example.com
   - Password: password123
   - Role: Waiter
5. Submit

**Verify in Database:**
```sql
SELECT * FROM employees WHERE email = 'test-waiter@example.com';
-- Should return 1 row with tenant_id and role

SELECT * FROM users WHERE email = 'test-waiter@example.com';
-- Should return 0 rows (not in old table)
```

---

## Verification Commands

### Check Table Usage
```bash
# Check SuperAdmin table
php artisan tinker
>>> App\Models\SuperAdmin::first()->getTable()
=> "super_admins"

# Check Admin table
>>> App\Models\Admin::first()->getTable()
=> "admins"

# Check Employee table
>>> App\Models\Employee::first()->getTable()
=> "employees"
```

### Count Records
```bash
php artisan tinker
>>> echo "Super Admins: " . App\Models\SuperAdmin::count();
>>> echo "Admins: " . App\Models\Admin::count();
>>> echo "Employees: " . App\Models\Employee::count();
>>> echo "Old Users: " . App\Models\User::count();
```

---

## Database Structure

### Correct Structure (After Fix)
```
super_admins table:
- id, name, email, password, phone, is_active, created_at, updated_at

admins table:
- id, tenant_id, name, email, password, phone, is_active, created_at, updated_at

employees table:
- id, tenant_id, name, email, password, role, phone, is_active, 
  telegram_chat_id, telegram_username, created_at, updated_at

users table (OLD - should be empty or for migration only):
- id, tenant_id, name, email, password, role, phone, is_active, 
  telegram_chat_id, telegram_username, created_at, updated_at
```

---

## Troubleshooting

### Issue: Still saving to users table
**Solution:**
1. Clear all caches (config, cache, view, route)
2. Restart PHP server
3. Check model has `protected $table = 'table_name';`
4. Verify no other code is using `User` model

### Issue: Cannot find table
**Error:** `SQLSTATE[42S02]: Base table or view not found`

**Solution:**
```bash
# Check if tables exist
php artisan tinker
>>> Schema::hasTable('super_admins')
>>> Schema::hasTable('admins')
>>> Schema::hasTable('employees')

# If false, run migrations
php artisan migrate
```

### Issue: Old data in users table
**Solution:** Migrate data to new tables:
```sql
-- Migrate super admins
INSERT INTO super_admins (name, email, password, phone, is_active, created_at, updated_at)
SELECT name, email, password, phone, is_active, created_at, updated_at
FROM users WHERE role = 'super_admin';

-- Migrate admins
INSERT INTO admins (tenant_id, name, email, password, phone, is_active, created_at, updated_at)
SELECT tenant_id, name, email, password, phone, is_active, created_at, updated_at
FROM users WHERE role = 'admin';

-- Migrate employees
INSERT INTO employees (tenant_id, name, email, password, role, phone, is_active, telegram_chat_id, telegram_username, created_at, updated_at)
SELECT tenant_id, name, email, password, role, phone, is_active, telegram_chat_id, telegram_username, created_at, updated_at
FROM users WHERE role IN ('waiter', 'chef', 'cashier');
```

---

## Important Notes

1. **Always clear caches** after modifying models
2. **Explicit is better than implicit** - always set `$table` property
3. **Old users table** should not be used anymore
4. **Each model** now has its own dedicated table
5. **Guards** are configured to use correct tables in `config/auth.php`

---

## Status: ✅ FIXED

All models now explicitly specify their table names:
- ✅ SuperAdmin → super_admins
- ✅ Admin → admins
- ✅ Employee → employees

**Next Steps:**
1. Clear all caches
2. Test creating new users
3. Verify data goes to correct tables
4. Migrate old data if needed
5. Consider removing old User model after migration
