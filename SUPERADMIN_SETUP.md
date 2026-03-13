# Super Admin Quick Setup

## 🚀 Create Your First Super Admin

### Method 1: Using Seeder (Recommended)

Run this command:
```bash
php artisan db:seed --class=SuperAdminSeeder
```

**Default Credentials:**
- Email: `superadmin@swadsangam.com`
- Password: `SuperAdmin@123`

⚠️ **Important:** Change the password immediately after first login!

---

### Method 2: Using Tinker

```bash
php artisan tinker
```

Then paste this code:
```php
\App\Models\User::create([
    'name' => 'Super Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('your-password-here'),
    'role' => 'super_admin',
    'is_active' => true,
    'tenant_id' => null
]);
```

Press Enter and exit with `exit`

---

### Method 3: Direct Database Insert

```sql
INSERT INTO users (name, email, password, role, is_active, tenant_id, created_at, updated_at)
VALUES (
    'Super Admin',
    'admin@example.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: 'password'
    'super_admin',
    1,
    NULL,
    NOW(),
    NOW()
);
```

---

## 🔐 Login

1. Visit: `http://localhost:8000/superadmin/login`
2. Enter your credentials
3. Click "Login as Super Admin"

---

## 📋 First Steps After Login

1. **Change Your Password**
   - Go to Profile (if available)
   - Or use Tinker to update password

2. **Create Global Categories**
   - Table Categories: VIP, Regular, Outdoor
   - Menu Categories: Starters, Main Course, Desserts, Beverages

3. **Create Your First Tenant**
   - Name: Your restaurant name
   - Slug: URL-friendly identifier
   - Status: Active

4. **Create Additional Super Admins** (Optional)
   - Add team members who need platform access

---

## 🎯 What Can Super Admins Do?

✅ Manage all restaurants (tenants)  
✅ Create/edit/delete tenants  
✅ Manage super admin users  
✅ Create global table categories  
✅ Create global menu categories  
✅ View platform-wide statistics  
✅ Suspend/activate tenants  

❌ Cannot access tenant-specific operations  
❌ Cannot place orders or manage individual restaurant operations  

---

## 🔄 Difference: Super Admin vs Tenant Admin

| Feature | Super Admin | Tenant Admin |
|---------|-------------|--------------|
| Manage Multiple Restaurants | ✅ Yes | ❌ No (only their own) |
| Create Tenants | ✅ Yes | ❌ No |
| Platform Settings | ✅ Yes | ❌ No |
| Manage Restaurant Tables | ❌ No | ✅ Yes |
| Manage Menu Items | ❌ No | ✅ Yes |
| View Orders | ❌ No | ✅ Yes |
| Manage Staff | ❌ No | ✅ Yes |

---

## 🛠️ Troubleshooting

### Can't Login?

**Check if user exists:**
```bash
php artisan tinker
```
```php
\App\Models\User::where('role', 'super_admin')->get();
```

**Reset password:**
```php
$user = \App\Models\User::where('email', 'your-email@example.com')->first();
$user->password = bcrypt('new-password');
$user->save();
```

### Getting 404 Error?

Make sure routes are cached:
```bash
php artisan route:clear
php artisan route:cache
```

### Getting 500 Error?

Check logs:
```bash
tail -f storage/logs/laravel.log
```

Clear all caches:
```bash
php artisan optimize:clear
```

---

## 📚 Full Documentation

For complete documentation, see:
- **SUPERADMIN_GUIDE.md** - Complete super admin guide
- **PROJECT_CHRONOLOGY.md** - System architecture
- **SYSTEM_COMPLETE.md** - All features

---

## 🔐 Security Tips

1. **Use Strong Passwords**
   - Minimum 12 characters
   - Mix of letters, numbers, symbols

2. **Limit Super Admin Accounts**
   - Only create what you need
   - Review accounts regularly

3. **Enable 2FA** (when available)
   - Add extra security layer

4. **Regular Audits**
   - Review tenant activity
   - Check for suspicious behavior

5. **Backup Regularly**
   - Database backups
   - Code backups

---

## ✅ Quick Checklist

- [ ] Super admin account created
- [ ] Successfully logged in
- [ ] Password changed from default
- [ ] Global categories created
- [ ] First tenant created
- [ ] Tested tenant access
- [ ] Documentation reviewed

---

**Need Help?** Check `SUPERADMIN_GUIDE.md` for detailed instructions!
