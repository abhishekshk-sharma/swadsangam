# Quick Start Guide - Restaurant Management System

## 🚀 Get Started in 5 Minutes

### Step 1: Run Migrations
```bash
php artisan migrate
```

### Step 2: Seed Sample Data
```bash
php artisan db:seed
```
This creates:
- 10 tables (T1 to T10) with QR codes
- 9 menu items across categories

### Step 3: Create Storage Link
```bash
php artisan storage:link
```

### Step 4: Start Server
```bash
php artisan serve
```

## 📱 Test the System

### 1. View Tables & QR Codes
Visit: `http://localhost:8000/admin/tables`
- Click "View QR" on any table
- You'll see the QR code to print

### 2. Test Customer Ordering
Visit: `http://localhost:8000/table/{qr_code}`
- Replace `{qr_code}` with actual code from table (e.g., `table_65d3f4a2b1c8e`)
- Or scan the QR code with your phone
- Add items using +/- buttons
- Click "Place Order"

### 3. Cook Panel
Visit: `http://localhost:8000/admin/cook`
- See the order appear in real-time
- Enter preparation time (e.g., 15 minutes)
- Click "Start" to begin countdown
- Customer will see countdown timer
- Click "Mark Ready" when done

### 4. Manage Menu
Visit: `http://localhost:8000/admin/menu`
- Add new items
- Edit prices
- Toggle availability
- Upload images

## 🔧 Optional: Enable Real-time Updates

### Option 1: Use Pusher (Easiest)
1. Sign up at https://pusher.com (free tier available)
2. Create a new app
3. Add to `.env`:
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1
```

### Option 2: Use Laravel Reverb (Self-hosted)
```bash
composer require laravel/reverb
php artisan reverb:install
php artisan reverb:start
```

Then update `.env`:
```env
BROADCAST_DRIVER=reverb
```

## 📋 Default URLs

| Panel | URL |
|-------|-----|
| Tables Management | http://localhost:8000/admin/tables |
| Menu Management | http://localhost:8000/admin/menu |
| Cook Panel | http://localhost:8000/admin/cook |
| Customer Menu | http://localhost:8000/table/{qr_code} |

## 🎯 Workflow

```
1. Admin creates tables → QR codes generated
2. Print QR codes → Place on physical tables
3. Customer scans QR → Opens menu
4. Customer orders → Order sent to cook panel
5. Cook sees order → Sets prep time
6. Customer sees countdown → Waits
7. Cook marks ready → Customer notified
```

## 💡 Tips

- **Test without QR scanner**: Just copy the URL from "View QR" page
- **Multiple orders**: Same table can place multiple orders
- **Real-time works locally**: Even without Pusher, page refreshes work
- **Mobile friendly**: Customer menu is responsive

## 🐛 Common Issues

**QR Code not showing?**
```bash
composer require simplesoftwareio/simple-qrcode
```

**Images not uploading?**
```bash
php artisan storage:link
```

**Database error?**
Check `.env` database credentials and run:
```bash
php artisan migrate:fresh --seed
```

## 📞 Need Help?

Check `RESTAURANT_README.md` for detailed documentation.
