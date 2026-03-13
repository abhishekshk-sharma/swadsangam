# Super Admin - Create Restaurant Admins Feature

## Overview
Super Admin can now create both Super Admins and Restaurant Admins from a unified interface.

## Changes Made

### 1. Controller Updates
**File**: `app/Http/Controllers/SuperAdmin/UserController.php`

**Changes**:
- Added support for creating both `super_admin` and `admin` roles
- Added tenant selection for restaurant admins
- Updated validation to require tenant_id when role is 'admin'
- Modified index to show both super admins and restaurant admins
- Updated success messages based on role type

**Key Logic**:
```php
// Tenant is required only for restaurant admins
'tenant_id' => 'required_if:role,admin|exists:tenants,id',

// Set tenant_id only for admins
'tenant_id' => $request->role === 'admin' ? $request->tenant_id : null,
```

### 2. View Updates

#### Index Page (`resources/views/superadmin/users/index.blade.php`)
**Changes**:
- Updated title from "Super Admins" to "Users"
- Added role badge (purple for super admin, blue for restaurant admin)
- Display tenant name for restaurant admins
- Different avatar colors based on role

**Visual Indicators**:
- Purple badge: Super Admin
- Blue badge: Restaurant Admin
- Green/Red badge: Active/Inactive status
- Restaurant icon with tenant name for admins

#### Create Page (`resources/views/superadmin/users/create.blade.php`)
**Changes**:
- Added role selection dropdown (Super Admin / Restaurant Admin)
- Added dynamic tenant selection field
- JavaScript to show/hide tenant field based on role
- Tenant field is required only when role is "Restaurant Admin"

**Dynamic Behavior**:
```javascript
function toggleTenantField() {
    if (role === 'admin') {
        // Show tenant field and make it required
    } else {
        // Hide tenant field and remove requirement
    }
}
```

#### Edit Page (`resources/views/superadmin/users/edit.blade.php`)
**Changes**:
- Added role selection dropdown
- Added tenant selection field
- JavaScript to handle role changes
- Pre-selects current role and tenant

## User Flow

### Creating a Super Admin
1. Navigate to `/superadmin/users`
2. Click "Add User"
3. Select "Super Admin" from role dropdown
4. Enter name, email, password
5. Click "Create User"
6. Tenant field is hidden and not required

### Creating a Restaurant Admin
1. Navigate to `/superadmin/users`
2. Click "Add User"
3. Select "Restaurant Admin" from role dropdown
4. Tenant field appears automatically
5. Select restaurant from dropdown
6. Enter name, email, password
7. Click "Create User"
8. Admin is created and linked to selected restaurant

### Editing Users
1. Navigate to `/superadmin/users`
2. Click "Edit" on any user card
3. Can change role between Super Admin and Restaurant Admin
4. Tenant field shows/hides based on role selection
5. Can change tenant assignment for restaurant admins
6. Password field is optional (leave blank to keep current)

## Database Structure

### Users Table
```
- id
- tenant_id (nullable, only for restaurant admins)
- name
- email
- password
- role (super_admin, admin, manager, waiter, chef, cashier)
- is_active
- telegram_chat_id
- telegram_username
- timestamps
```

### Relationships
- User `belongsTo` Tenant
- Tenant `hasMany` Users

## Validation Rules

### Create User
```php
'name' => 'required',
'email' => 'required|email|unique:users',
'password' => 'required|min:6',
'role' => 'required|in:super_admin,admin',
'tenant_id' => 'required_if:role,admin|exists:tenants,id',
```

### Update User
```php
'name' => 'required',
'email' => 'required|email|unique:users,email,{id}',
'role' => 'required|in:super_admin,admin',
'tenant_id' => 'required_if:role,admin|exists:tenants,id',
'password' => 'nullable|min:6', // Optional on update
```

## Access Control

### Super Admin Can:
- Create other super admins
- Create restaurant admins for any tenant
- Edit any user (super admin or restaurant admin)
- Delete any user
- Change user roles
- Assign/reassign tenants to restaurant admins
- Activate/deactivate users

### Restaurant Admin Cannot:
- Access super admin panel
- Create super admins
- See other restaurants' data
- Change their own tenant assignment

## UI Features

### User Cards Display
- Avatar with first letter of name
- Color-coded by role (purple/blue)
- Name and email
- Role badge
- Active/Inactive status badge
- Restaurant name (for admins)
- Edit and Delete buttons

### Form Features
- Dynamic field visibility
- Client-side validation
- Server-side validation
- Error messages
- Old input preservation
- Required field indicators

## Security Considerations

1. **Tenant Isolation**: Restaurant admins can only access their assigned tenant's data
2. **Role Validation**: Only super_admin and admin roles allowed
3. **Email Uniqueness**: Prevents duplicate accounts
4. **Password Hashing**: All passwords are bcrypt hashed
5. **CSRF Protection**: All forms include CSRF tokens
6. **Authorization**: SuperAdminAuth middleware protects all routes

## Testing Checklist

- [x] Create super admin
- [x] Create restaurant admin with tenant
- [x] Edit super admin
- [x] Edit restaurant admin
- [x] Change role from super admin to restaurant admin
- [x] Change role from restaurant admin to super admin
- [x] Tenant field shows/hides correctly
- [x] Validation works for all fields
- [x] Cannot create admin without tenant
- [x] Can create super admin without tenant
- [x] User list shows both types correctly
- [x] Tenant name displays for restaurant admins
- [x] Delete functionality works

## Routes

```php
GET    /superadmin/users           - List all users
GET    /superadmin/users/create    - Show create form
POST   /superadmin/users           - Store new user
GET    /superadmin/users/{id}/edit - Show edit form
PUT    /superadmin/users/{id}      - Update user
DELETE /superadmin/users/{id}      - Delete user
```

## Example Usage

### Create Restaurant Admin via Form
```
Role: Restaurant Admin
Restaurant: Swad Sangam
Name: John Doe
Email: john@restaurant.com
Password: password123
```

### Result
```php
User {
    id: 5,
    tenant_id: 1,
    name: "John Doe",
    email: "john@restaurant.com",
    role: "admin",
    is_active: true
}
```

## Benefits

1. **Centralized Management**: Super admin manages all users from one place
2. **Flexible Role Assignment**: Easy to create different types of users
3. **Clear Visual Distinction**: Color-coded badges and avatars
4. **Tenant Association**: Restaurant admins are clearly linked to their restaurants
5. **Easy Editing**: Can change roles and tenant assignments
6. **Validation**: Prevents invalid configurations

## Future Enhancements

- [ ] Bulk user creation
- [ ] User import from CSV
- [ ] Email verification for new users
- [ ] Password reset functionality
- [ ] User activity logs
- [ ] Role permissions customization
- [ ] Multi-tenant assignment for managers

---

**Status**: ✅ IMPLEMENTED AND READY
**Version**: 1.2.0
**Date**: March 13, 2026
