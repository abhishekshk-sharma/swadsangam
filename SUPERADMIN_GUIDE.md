# Super Admin Panel - Complete Guide

## 🎯 Overview

The Super Admin Panel is the **platform-level control center** for managing the entire multi-tenant restaurant management system. Super admins have complete control over all tenants (restaurants), users, and global settings.

---

## 🔐 Access & Authentication

### Login URL
```
http://localhost:8000/superadmin/login
```

### Default Credentials
You need to create a super admin user first. Use Laravel Tinker:

```bash
php artisan tinker
```

Then run:
```php
\App\Models\User::create([
    'name' => 'Super Admin',
    'email' => 'superadmin@swadsangam.com',
    'password' => bcrypt('password123'),
    'role' => 'super_admin',
    'is_active' => true,
    'tenant_id' => null
]);
```

---

## 📊 Dashboard Features

### Statistics Overview
The dashboard displays key metrics:
- **Total Tenants** - Number of restaurants on the platform
- **Active Tenants** - Currently active restaurants
- **Total Users** - All users across all tenants
- **Super Admins** - Number of platform administrators

### Recent Activity
- View recently created tenants
- Quick access to tenant management

---

## 🏢 Tenant Management

### What is a Tenant?
A tenant represents a **restaurant** on the platform. Each tenant has:
- Isolated data (tables, menu, orders, staff)
- Unique slug for identification
- Optional custom domain
- Active/Suspended status

### Creating a Tenant

1. Navigate to **Tenants** → **Add Tenant**
2. Fill in the form:
   - **Restaurant Name**: Display name (e.g., "Swad Sangam Downtown")
   - **Slug**: URL identifier (e.g., "swadsangam-downtown")
     - Only letters, numbers, dashes, underscores
     - Must be unique
     - Used in URLs: `/tenant/{slug}`
   - **Domain** (Optional): Custom domain (e.g., "downtown.swadsangam.com")
3. Click **Create Tenant**

### Editing a Tenant

1. Go to **Tenants** list
2. Click **Edit** on any tenant
3. Update:
   - Name
   - Slug
   - Domain
   - **Status**: Active or Suspended
4. Click **Update Tenant**

### Tenant Status
- **Active**: Restaurant can operate normally
- **Suspended**: Restaurant access is blocked (useful for non-payment, violations, etc.)

### Deleting a Tenant
⚠️ **Warning**: This will delete ALL data for that restaurant:
- All tables and QR codes
- All menu items
- All orders and order history
- All staff members (except super admins)

---

## 👥 Super Admin User Management

### Creating Super Admins

1. Navigate to **Super Admins** → **Add Super Admin**
2. Fill in:
   - **Name**: Full name
   - **Email**: Unique email address
   - **Password**: Minimum 6 characters
3. Click **Create Super Admin**

### Editing Super Admins

1. Go to **Super Admins** list
2. Click **Edit** on any user
3. Update:
   - Name
   - Email
   - Password (leave blank to keep current)
   - **Active Status**: Enable/disable account
4. Click **Update Super Admin**

### Super Admin Privileges
Super admins can:
- ✅ View and manage all tenants
- ✅ Create/edit/delete tenants
- ✅ Manage other super admins
- ✅ Access global categories
- ✅ View platform-wide statistics
- ❌ Cannot access tenant-specific operations (that's for tenant admins)

---

## 📋 Table Categories (Global Templates)

### Purpose
Create **reusable table category templates** that tenants can use:
- VIP Section
- Regular Seating
- Outdoor Dining
- Private Rooms
- Bar Area
- Terrace

### Managing Table Categories

**Add Category:**
1. Go to **Table Categories**
2. Enter category name in the form
3. Click **Add Category**

**View Usage:**
- See how many tables across all tenants use each category

**Delete Category:**
- Click **Delete** next to any category
- ⚠️ This will unlink tables using this category (they won't be deleted)

---

## 🍽️ Menu Categories (Global Templates)

### Purpose
Create **reusable menu category templates** that tenants can use:
- Starters / Appetizers
- Main Course
- Desserts
- Beverages
- Specials
- Kids Menu
- Vegan Options

### Managing Menu Categories

**Add Category:**
1. Go to **Menu Categories**
2. Enter category name in the form
3. Click **Add Category**

**View Usage:**
- See how many menu items across all tenants use each category

**Delete Category:**
- Click **Delete** next to any category
- ⚠️ This will unlink menu items using this category (they won't be deleted)

---

## 🔄 Workflow: Setting Up a New Restaurant

### Step-by-Step Process

1. **Create Tenant**
   ```
   Name: "Swad Sangam Downtown"
   Slug: "swadsangam-downtown"
   Domain: (optional)
   Status: Active
   ```

2. **Create Tenant Admin**
   - This is done by the tenant admin themselves
   - They register at: `/login` (tenant login)
   - Or you can create them via database/tinker

3. **Tenant Admin Sets Up Restaurant**
   - Creates staff accounts (waiters, chefs, cashiers)
   - Creates tables and generates QR codes
   - Adds menu items
   - Configures categories

4. **Restaurant Goes Live**
   - Print QR codes and place on tables
   - Staff can log in to their respective panels
   - Customers can scan QR codes and order

---

## 🎨 User Interface

### Color Scheme
- **Primary**: Purple gradient (platform branding)
- **Accent**: Pink (highlights)
- **Success**: Green (active status)
- **Danger**: Red (suspended, delete actions)

### Navigation
- **Sidebar**: Main navigation menu
- **Header**: Current page title and logout
- **Content Area**: Main workspace

### Responsive Design
- Desktop-optimized (admin panels are typically used on computers)
- Mobile-friendly for quick checks

---

## 🔒 Security Features

### Authentication
- Session-based authentication
- Password hashing (bcrypt)
- CSRF protection on all forms
- Remember me functionality

### Authorization
- Role-based access control
- Super admin middleware
- Tenant isolation (super admins bypass this)

### Data Protection
- Tenant data isolation
- Secure password storage
- SQL injection prevention (Eloquent ORM)
- XSS protection (Blade templating)

---

## 📊 Monitoring & Analytics

### Current Metrics Available
- Total tenants count
- Active vs suspended tenants
- Total users across platform
- Super admin count
- Per-tenant statistics (tables, menu items, orders)

### Future Analytics (Planned)
- Revenue per tenant
- Order volume trends
- Popular menu items across platform
- Peak hours analysis
- Tenant growth rate

---

## 🛠️ Troubleshooting

### Common Issues

**1. Can't Login**
- Verify email and password
- Check if account is active
- Ensure role is 'super_admin'
- Check database: `SELECT * FROM users WHERE role = 'super_admin'`

**2. Tenant Count Shows 0**
- No tenants created yet
- Create your first tenant from the Tenants page

**3. Categories Not Showing**
- Categories are optional templates
- Tenants can create their own categories
- Global categories are just suggestions

**4. Can't Delete Tenant**
- Check for foreign key constraints
- Ensure no active sessions for that tenant
- Check error logs: `storage/logs/laravel.log`

**5. Forgot Super Admin Password**
Reset via Tinker:
```bash
php artisan tinker
```
```php
$user = \App\Models\User::where('email', 'superadmin@example.com')->first();
$user->password = bcrypt('newpassword');
$user->save();
```

---

## 🚀 Best Practices

### Tenant Management
1. **Use Descriptive Slugs**: Make them memorable and meaningful
2. **Regular Audits**: Review tenant activity periodically
3. **Suspend, Don't Delete**: Suspend inactive tenants instead of deleting
4. **Backup Before Delete**: Always backup data before deleting tenants

### User Management
1. **Limit Super Admins**: Only create necessary super admin accounts
2. **Strong Passwords**: Enforce strong password policy
3. **Regular Reviews**: Audit super admin accounts quarterly
4. **Deactivate, Don't Delete**: Deactivate unused accounts

### Category Management
1. **Keep It Simple**: Don't create too many categories
2. **Standardize Names**: Use consistent naming conventions
3. **Monitor Usage**: Remove unused categories
4. **Get Feedback**: Ask tenants what categories they need

---

## 📝 Quick Reference

### URLs
```
Dashboard:          /superadmin/dashboard
Tenants:           /superadmin/tenants
Super Admins:      /superadmin/users
Table Categories:  /superadmin/table-categories
Menu Categories:   /superadmin/menu-categories
Login:             /superadmin/login
Logout:            POST /superadmin/logout
```

### Keyboard Shortcuts
- `Ctrl + Click` on links: Open in new tab
- `Esc`: Close modals (if implemented)

### Status Indicators
- 🟢 **Green Badge**: Active/Available
- 🔴 **Red Badge**: Suspended/Inactive
- 🔵 **Blue Badge**: Pending/Processing

---

## 🔮 Future Enhancements

### Planned Features
- [ ] Tenant analytics dashboard
- [ ] Bulk tenant operations
- [ ] Email notifications to tenants
- [ ] Tenant subscription management
- [ ] Payment gateway integration
- [ ] Tenant usage limits (tables, menu items, orders)
- [ ] API access for tenants
- [ ] White-label options
- [ ] Multi-language support
- [ ] Tenant onboarding wizard

### Technical Improvements
- [ ] Real-time notifications
- [ ] Advanced search and filters
- [ ] Export reports (PDF, Excel)
- [ ] Audit logs
- [ ] Two-factor authentication
- [ ] IP whitelisting
- [ ] Rate limiting
- [ ] Automated backups

---

## 📞 Support

### Getting Help
1. Check this documentation first
2. Review error logs: `storage/logs/laravel.log`
3. Check Laravel documentation: https://laravel.com/docs
4. Review project chronology: `PROJECT_CHRONOLOGY.md`

### Reporting Issues
When reporting issues, include:
- What you were trying to do
- What happened instead
- Error messages (if any)
- Steps to reproduce
- Browser and version (if UI issue)

---

## ✅ Checklist: First Time Setup

- [ ] Create first super admin account (via Tinker)
- [ ] Login to super admin panel
- [ ] Create global table categories
- [ ] Create global menu categories
- [ ] Create first tenant (test restaurant)
- [ ] Verify tenant can be accessed
- [ ] Create additional super admin (optional)
- [ ] Review security settings
- [ ] Set up backup system
- [ ] Document custom configurations

---

## 📚 Related Documentation

- **PROJECT_CHRONOLOGY.md** - Complete project history and architecture
- **SYSTEM_COMPLETE.md** - Full system features and capabilities
- **RESTAURANT_README.md** - Restaurant management features
- **DEPLOYMENT_CHECKLIST.md** - Production deployment guide
- **QUICK_START.md** - 5-minute setup guide

---

**Last Updated:** March 2026  
**Version:** 1.0.0  
**Status:** ✅ Fully Operational

---

*This guide covers all aspects of the Super Admin Panel. For tenant-specific operations, refer to the Restaurant Admin documentation.*
