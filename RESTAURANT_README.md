# Restaurant Management System

A Laravel-based restaurant management system with QR code table ordering, real-time order tracking, and cook panel.

## Features

- **QR Code Table Management**: Generate unique QR codes for each table
- **Customer Ordering**: Scan QR code → Browse menu → Place orders
- **Cook Panel**: Real-time order display with preparation time management
- **Live Updates**: Real-time countdown timers for order preparation
- **Order Tracking**: Track order status from pending to ready

## Installation

### 1. Install Dependencies
```bash
composer install
```

### 2. Configure Environment
Copy `.env.example` to `.env` and configure:
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Setup
Configure your database in `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restaurant_db
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations:
```bash
php artisan migrate
```

### 4. Seed Sample Data
```bash
php artisan db:seed
```
This creates 10 tables and sample menu items.

### 5. Storage Link
```bash
php artisan storage:link
```

### 6. Configure Broadcasting (Optional for Real-time)
For real-time features, configure Pusher in `.env`:
```
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster
```

Or use Laravel Reverb (Laravel's WebSocket server):
```bash
php artisan reverb:install
```

### 7. Start Server
```bash
php artisan serve
```

## Usage

### Admin Panel

#### Manage Tables
- **View Tables**: `/admin/tables`
- **Create Table**: `/admin/tables/create`
- **View QR Code**: Click "View QR" on any table
- **Print QR codes** and place them on physical tables

#### Manage Menu
- **View Menu**: `/admin/menu`
- **Add Item**: `/admin/menu/create`
- **Edit Item**: Click "Edit" on any menu item
- **Toggle Availability**: Edit item and uncheck "Available"

#### Cook Panel
- **Access**: `/admin/cook`
- **View Orders**: See all pending and preparing orders in real-time
- **Set Prep Time**: Enter minutes and click "Start"
- **Mark Ready**: Click "Mark Ready" when order is complete
- **Auto-refresh**: New orders appear automatically

### Customer Flow

1. **Scan QR Code**: Customer scans table QR code
2. **View Menu**: Redirected to `/table/{qrCode}` with menu
3. **Select Items**: Use +/- buttons to add items
4. **Place Order**: Click "Place Order" button
5. **Track Order**: See order status and countdown timer
6. **Multiple Orders**: Can place multiple orders from same table

### Order Status Flow

1. **Pending**: Order placed, waiting for cook
2. **Preparing**: Cook set preparation time, countdown started
3. **Ready**: Order completed and ready for pickup

## Project Structure

```
app/
├── Events/
│   ├── NewOrder.php          # Broadcast new orders to cook panel
│   └── OrderUpdated.php      # Broadcast order updates to customers
├── Http/Controllers/
│   ├── Admin/
│   │   ├── TableController.php    # Table & QR management
│   │   ├── MenuController.php     # Menu CRUD
│   │   └── CookController.php     # Cook panel & order updates
│   └── Customer/
│       └── OrderController.php    # Customer ordering
└── Models/
    ├── RestaurantTable.php
    ├── MenuItem.php
    ├── Order.php
    └── OrderItem.php

database/
├── migrations/
│   ├── *_create_restaurant_tables.php
│   ├── *_create_menu_items.php
│   ├── *_create_orders.php
│   └── *_create_order_items.php
└── seeders/
    └── DatabaseSeeder.php

resources/views/
├── layouts/
│   └── app.blade.php
├── admin/
│   ├── tables/
│   ├── menu/
│   └── cook/
└── customer/
    └── menu.blade.php
```

## Database Schema

### restaurant_tables
- id, table_number, qr_code, is_occupied, timestamps

### menu_items
- id, name, description, price, image, category, is_available, timestamps

### orders
- id, table_id, status, preparation_time, ready_at, total_amount, timestamps

### order_items
- id, order_id, menu_item_id, quantity, price, timestamps

## API Endpoints

### Admin Routes
- `GET /admin/tables` - List tables
- `POST /admin/tables` - Create table
- `GET /admin/tables/{id}` - View QR code
- `GET /admin/menu` - List menu items
- `POST /admin/menu` - Create menu item
- `GET /admin/cook` - Cook panel
- `POST /admin/cook/{id}/time` - Set preparation time
- `POST /admin/cook/{id}/ready` - Mark order ready

### Customer Routes
- `GET /table/{qrCode}` - View menu & place orders
- `POST /table/{qrCode}/order` - Submit order
- `GET /order/{orderId}/status` - Get order status

## Real-time Features

The system uses Laravel Broadcasting for real-time updates:

1. **New Orders**: Broadcast to cook panel when customer places order
2. **Order Updates**: Broadcast to customer when cook updates status
3. **Countdown Timers**: JavaScript updates every second

## Customization

### Add Menu Categories
Edit menu items and use any category name. Categories are automatically grouped.

### Modify Order Statuses
Edit `orders` migration to add more statuses:
```php
$table->enum('status', ['pending', 'preparing', 'ready', 'completed', 'cancelled']);
```

### Change QR Code Size
In `TableController@show`:
```php
QrCode::size(500)->generate(url('/table/' . $table->qr_code));
```

## Troubleshooting

### QR Codes Not Showing
Install GD or Imagick PHP extension:
```bash
# For GD
sudo apt-get install php-gd
```

### Real-time Not Working
1. Check Pusher credentials in `.env`
2. Verify `BROADCAST_DRIVER=pusher`
3. Clear config cache: `php artisan config:clear`

### Images Not Uploading
1. Check storage permissions: `chmod -R 775 storage`
2. Create storage link: `php artisan storage:link`

## Production Deployment

1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false`
3. Run `php artisan config:cache`
4. Run `php artisan route:cache`
5. Run `php artisan view:cache`
6. Configure queue worker for broadcasting
7. Use supervisor to keep queue worker running

## License

This project is built on Laravel framework (MIT License).
