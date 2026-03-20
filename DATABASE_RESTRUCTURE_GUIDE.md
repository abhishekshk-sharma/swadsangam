# Database Restructuring Guide

## Overview
This guide explains the database restructuring to separate user roles into different tables.

## New Structure

### 1. **super_admins** Table
- Stores super admin users (platform administrators)
- No tenant relationship (global access)
- Columns:
  - id
  - name
  - email (unique)
  - password
  - phone (nullable)
  - is_active (boolean, default: true)
  - remember_token
  - timestamps

### 2. **admins** Table
- Stores restaurant admin users
- Has tenant relationship (one admin per restaurant)
- Columns:
  - id
  - tenant_id (foreign key to tenants)
  - name
  - email (unique per tenant)
  - password
  - phone (nullable)
  - is_active (boolean, default: true)
  - remember_token
  - timestamps
  - Unique constraint: (tenant_id, email)

### 3. **employees** Table (Restructured)
- Stores staff members (waiter, chef, cashier)
- Has tenant relationship
- Columns:
  - id
  - tenant_id (foreign key to tenants)
  - name (renamed from fullname)
  - email (unique per tenant)
  - phone (nullable)
  - password
  - role (enum: 'waiter', 'chef', 'cashier')
  - telegram_chat_id (nullable)
  - telegram_username (nullable)
  - is_active (boolean, default: true)
  - remember_token
  - timestamps
  - Unique constraint: (tenant_id, email)

### Removed from employees:
- emp_id
- username
- department
- position
- status (replaced with is_active)

## Models Created

### SuperAdmin Model
- Location: `app/Models/SuperAdmin.php`
- Extends: `Illuminate\Foundation\Auth\User`
- Methods:
  - isSuperAdmin() → true
  - isAdmin() → true

### Admin Model
- Location: `app/Models/Admin.php`
- Extends: `Illuminate\Foundation\Auth\User`
- Relationships:
  - belongsTo(Tenant)
- Methods:
  - isSuperAdmin() → false
  - isAdmin() → true

### Employee Model
- Location: `app/Models/Employee.php`
- Extends: `Illuminate\Foundation\Auth\User`
- Relationships:
  - belongsTo(Tenant)
- Methods:
  - isSuperAdmin() → false
  - isAdmin() → false
  - isWaiter() → checks if role is 'waiter'
  - isChef() → checks if role is 'chef'
  - isCashier() → checks if role is 'cashier'

## Migration Files

1. **2026_03_13_231352_create_super_admins_table.php**
   - Creates super_admins table

2. **2026_03_13_231358_create_admins_table.php**
   - Creates admins table with tenant relationship

3. **2026_03_13_231405_restructure_employees_table.php**
   - Restructures employees table
   - Removes old columns
   - Adds new columns
   - Renames fullname to name

## Migration Steps

### IMPORTANT: Backup First!
```bash
# Backup your database
mysqldump -u username -p database_name > backup_before_restructure.sql
```

### Step 1: Run Migrations
```bash
php artisan migrate
```

### Step 2: Data Migration (Manual)
You'll need to manually migrate existing data from the `users` table to the new tables:

```sql
-- Migrate super admins
INSERT INTO super_admins (name, email, password, phone, is_active, created_at, updated_at)
SELECT name, email, password, phone, is_active, created_at, updated_at
FROM users
WHERE role = 'super_admin';

-- Migrate admins
INSERT INTO admins (tenant_id, name, email, password, phone, is_active, created_at, updated_at)
SELECT tenant_id, name, email, password, phone, is_active, created_at, updated_at
FROM users
WHERE role IN ('admin', 'manager');

-- Migrate employees (waiter, chef, cashier)
INSERT INTO employees (tenant_id, name, email, password, phone, role, telegram_chat_id, telegram_username, is_active, created_at, updated_at)
SELECT tenant_id, name, email, password, phone, role, telegram_chat_id, telegram_username, is_active, created_at, updated_at
FROM users
WHERE role IN ('waiter', 'chef', 'cashier');
```

### Step 3: Update Authentication Configuration
You'll need to update `config/auth.php` to support multiple guards for different user types.

### Step 4: Update Controllers
All authentication controllers need to be updated to use the appropriate model (SuperAdmin, Admin, or Employee).

## Next Steps Required

After running migrations, you'll need to update:

1. **Authentication System**
   - Update login logic to check all three tables
   - Update guards in config/auth.php
   - Update middleware to work with new models

2. **Controllers**
   - Update all controllers that reference User model
   - Update employee management controllers
   - Update authentication controllers

3. **Views**
   - Update any views that display user information
   - Update employee management views

4. **Relationships**
   - Update Order model if it references users
   - Update any other models with user relationships

## Rollback Plan

If something goes wrong:
```bash
# Restore from backup
mysql -u username -p database_name < backup_before_restructure.sql

# Or rollback migrations
php artisan migrate:rollback --step=3
```

## Testing Checklist

After migration:
- [ ] Super admin can login
- [ ] Admin can login
- [ ] Employees (waiter, chef, cashier) can login
- [ ] Tenant isolation works correctly
- [ ] All existing orders are still accessible
- [ ] Employee management works
- [ ] Telegram integration still works for employees

## Notes

- The old `users` table is NOT dropped automatically for safety
- You can drop it manually after confirming everything works
- Email uniqueness is now per tenant for admins and employees
- Super admins have global unique emails
- All three models extend Laravel's Authenticatable class
