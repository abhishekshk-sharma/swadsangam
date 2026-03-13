# PayServer - Restaurant Management System
## Complete Project Chronology & Architecture

---

## 📋 PROJECT OVERVIEW

**Project Name:** PayServer (Restaurant Management System)  
**Framework:** Laravel 12.x  
**PHP Version:** 8.2+  
**Database:** MySQL  
**Primary Purpose:** Multi-tenant restaurant management with QR-based ordering, real-time kitchen operations, and role-based access control

---

## 🏗️ SYSTEM ARCHITECTURE

### Multi-Tenant Architecture
The system is built with a **multi-tenant architecture** allowing multiple restaurants to operate independently on the same platform:

- **Super Admin Level:** Manages all tenants (restaurants)
- **Tenant Level:** Each restaurant operates independently with its own:
  - Tables and QR codes
  - Menu items and categories
  - Staff members (admin, manager, waiter, chef, cashier)
  - Orders and customers
  - Reports and analytics

### Core Components

1. **Tenant Management System**
   - Isolated data per restaurant
   - Subdomain/domain routing capability
   - Centralized super admin control

2. **User Role System**
   - `super_admin` - Platform administrator
   - `admin` - Restaurant owner/administrator
   - `manager` - Restaurant manager
   - `waiter` - Service staff
   - `chef` - Kitchen staff
   - `cashier` - Payment processing staff

3. **QR Code Ordering System**
   - Unique QR codes per table
   - Customer self-ordering interface
   - Real-time order tracking

4. **Kitchen Management**
   - Cook panel with real-time updates
   - Order preparation tracking
   - Countdown timers

5. **Payment Processing**
   - Cashier dashboard
   - Payment status tracking
   - Transaction history

---

## 📅 DEVELOPMENT CHRONOLOGY

### Phase 1: Foundation (Initial Setup)
**Date Range:** Early development  
**Key Migrations:**
- `0001_01_01_000001_create_cache_table.php` - Caching infrastructure
- `2026_03_09_141708_employees.php` - Initial employee system (later evolved to users)
- `2026_03_09_143810_create_restaurant_tables.php` - Table management
- `2026_03_09_143811_create_menu_items.php` - Menu system
- `2026_03_09_143812_create_orders.php` - Order management
- `2026_03_09_143815_create_order_items.php` - Order line items

**Features Implemented:**
- Basic Laravel setup
- Database structure for restaurant operations
- Employee management (precursor to user system)
- Table and menu CRUD operations
- Order placement system

### Phase 2: Session & Capacity Management
**Date Range:** Mid-development  
**Key Migrations:**
- `2026_03_10_102129_create_sessions_table.php` - Session management
- `2026_03_10_102526_add_capacity_to_restaurant_tables.php` - Table capacity tracking

**Features Implemented:**
- Database-driven sessions for better scalability
- Table capacity management for seating optimization

### Phase 3: Categorization System
**Date Range:** Mid-development  
**Key Migrations:**
- `2026_03_10_173658_create_table_categories_table.php` - Table categorization
- `2026_03_10_173704_add_category_to_restaurant_tables.php` - Link tables to categories
- `2026_03_10_174429_create_menu_categories_table.php` - Menu categorization
- `2026_03_10_174435_add_menu_category_to_menu_items.php` - Link menu items to categories

**Features Implemented:**
- Table categories (VIP, Regular, Outdoor, etc.)
- Menu categories (Appetizer, Main Course, Dessert, Beverage)
- Better organization and filtering

### Phase 4: Multi-Tenant Architecture
**Date Range:** Major architectural shift  
**Key Migrations:**
- `2026_03_10_175826_create_tenants_table.php` - Tenant system
- `2026_03_10_175831_add_tenant_id_to_all_tables.php` - Multi-tenancy implementation
- `2026_03_10_181019_create_users_table.php` - Modern user system with roles

**Features Implemented:**
- Complete multi-tenant architecture
- Tenant isolation (each restaurant's data is separate)
- Unified user system replacing employees
- Role-based access control (RBAC)
- Super admin panel for platform management

**Major Changes:**
- Migrated from `employees` table to `users` table
- Added `tenant_id` to all core tables
- Implemented tenant middleware for data isolation

### Phase 5: User Enhancement & Order Tracking
**Date Range:** Late development  
**Key Migrations:**
- `2026_03_10_184432_add_user_id_to_orders_table.php` - Track order creators
- `2026_03_10_194442_add_payment_fields_to_orders_table.php` - Payment tracking
- `2026_03_10_201231_add_phone_to_users_table.php` - User contact info

**Features Implemented:**
- Order attribution to users (waiters/customers)
- Payment status tracking
- Payment method recording
- User phone numbers for communication

### Phase 6: Telegram Integration
**Date Range:** Recent development  
**Key Migrations:**
- `2026_03_10_203623_add_chat_id_to_users_table.php` - Telegram linking
- `2026_03_10_203634_create_telegram_requests_table.php` - Telegram request management

**Features Implemented:**
- Telegram bot integration
- User linking via Telegram
- Real-time notifications via Telegram
- Telegram webhook handling

### Phase 7: Order Status Enhancement
**Date Range:** Latest development  
**Key Migrations:**
- `2026_03_12_000835_update_orders_status_enum.php` - Enhanced order statuses

**Features Implemented:**
- Expanded order status workflow
- Better order lifecycle management
- Status: pending → preparing → ready → served → completed

---

## 🗄️ DATABASE SCHEMA

### Core Tables

#### tenants
```
- id (PK)
- name (Restaurant name)
- slug (URL-friendly identifier)
- domain (Custom domain, optional)
- status (active/inactive)
- timestamps
```

#### users
```
- id (PK)
- tenant_id (FK → tenants)
- name
- email (unique per tenant)
- phone
- password
- role (super_admin, admin, manager, waiter, chef, cashier)
- is_active
- telegram_chat_id
- telegram_username
- remember_token
- timestamps
```

#### restaurant_tables
```
- id (PK)
- tenant_id (FK → tenants)
- table_number
- capacity
- category_id (FK → table_categories)
- qr_code (unique)
- is_occupied
- timestamps
```

#### table_categories
```
- id (PK)
- tenant_id (FK → tenants)
- name
- description
- timestamps
```

#### menu_items
```
- id (PK)
- tenant_id (FK → tenants)
- name
- description
- price
- image
- category_id (FK → menu_categories)
- is_available
- timestamps
```

#### menu_categories
```
- id (PK)
- tenant_id (FK → tenants)
- name
- description
- display_order
- timestamps
```

#### orders
```
- id (PK)
- tenant_id (FK → tenants)
- table_id (FK → restaurant_tables)
- user_id (FK → users, nullable)
- status (pending, preparing, ready, served, completed, cancelled)
- preparation_time (minutes)
- ready_at (timestamp)
- total_amount
- payment_status (pending, paid, cancelled)
- payment_method (cash, card, upi, etc.)
- timestamps
```

#### order_items
```
- id (PK)
- order_id (FK → orders)
- menu_item_id (FK → menu_items)
- quantity
- price (snapshot at order time)
- timestamps
```

#### telegram_requests
```
- id (PK)
- tenant_id (FK → tenants)
- chat_id
- username
- first_name
- last_name
- status (pending, approved, rejected)
- timestamps
```

---

## 🎯 USER WORKFLOWS

### 1. Super Admin Workflow
```
Login → Super Admin Dashboard
├── Manage Tenants (Create/Edit/Delete restaurants)
├── Manage Global Users
├── Manage Table Categories (global templates)
├── Manage Menu Categories (global templates)
└── View Platform Analytics
```

### 2. Restaurant Admin Workflow
```
Login → Admin Dashboard
├── Manage Employees (Create staff accounts)
├── Manage Tables (Create tables, generate QR codes)
├── Manage Menu (Add/edit menu items, upload images)
├── View Cook Panel (Monitor kitchen operations)
├── View Reports (Sales, orders, analytics)
└── Telegram Integration (Link staff to Telegram bot)
```

### 3. Waiter Workflow
```
Login → Waiter Dashboard
├── View Assigned Tables
├── Create Manual Orders (for walk-in customers)
├── View Order Status
├── Mark Orders as Served
└── View Table Availability
```

### 4. Chef/Cook Workflow
```
Login → Cook Dashboard
├── View Pending Orders (real-time)
├── View Order Details (table, items, quantities)
├── Set Preparation Time (starts countdown)
├── Mark Orders as Ready
└── View Order History
```

### 5. Cashier Workflow
```
Login → Cashier Dashboard
├── View Ready Orders (awaiting payment)
├── Process Payments (cash, card, UPI)
├── Mark Orders as Paid
├── View Payment History
└── Generate Payment Reports
```

### 6. Customer Workflow
```
Scan QR Code → Customer Menu Page
├── View Table Number & Capacity
├── Browse Menu by Category
├── Add Items to Cart (+/- buttons)
├── Place Order
├── View Order Status (real-time)
├── See Preparation Countdown
└── Receive Ready Notification
```

---

## 🔐 AUTHENTICATION & AUTHORIZATION

### Authentication System
- Laravel's built-in authentication
- Session-based (database driver)
- Remember me functionality
- Password hashing (bcrypt)

### Authorization Middleware
1. **SuperAdminAuth** - Platform-level access
2. **CheckRole** - Role-based access control
3. **IdentifyTenant** - Tenant isolation
4. **Authenticate** - Basic authentication check

### Role Hierarchy
```
super_admin (Platform level)
    └── admin (Tenant level)
        └── manager (Tenant level)
            ├── waiter (Operational)
            ├── chef (Operational)
            └── cashier (Operational)
```

---

## 🚀 KEY FEATURES

### 1. QR Code System
- **Library:** SimpleSoftwareIO/simple-qrcode
- **Generation:** Automatic on table creation
- **Format:** Unique alphanumeric codes
- **Usage:** Customer scans → Opens menu → Places order

### 2. Real-Time Updates
- **Events:** NewOrder, OrderCreated, OrderStatusUpdated, OrderUpdated
- **Broadcasting:** Pusher/Laravel Reverb support
- **Use Cases:**
  - New orders appear instantly on cook panel
  - Order status updates push to customers
  - Live countdown timers

### 3. Image Management
- **Storage:** public/uploads/menu/
- **Handling:** File upload with validation
- **Display:** Optimized thumbnails in menu
- **Formats:** JPEG, PNG, GIF

### 4. Reporting System
- **Export:** Excel format (Maatwebsite/Excel)
- **Reports:**
  - Daily sales
  - Order history
  - Menu item performance
  - Staff performance
  - Payment summaries

### 5. Telegram Integration
- **Service:** TelegramService
- **Features:**
  - Staff notifications
  - Order alerts
  - Status updates
  - User linking system

---

## 📁 PROJECT STRUCTURE

```
payserver/
├── app/
│   ├── Events/                    # Broadcasting events
│   │   ├── NewOrder.php
│   │   ├── OrderCreated.php
│   │   ├── OrderStatusUpdated.php
│   │   └── OrderUpdated.php
│   ├── Exports/                   # Excel exports
│   │   └── OrdersExport.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/            # Admin panel controllers
│   │   │   ├── Api/              # API endpoints
│   │   │   ├── Cashier/          # Cashier panel
│   │   │   ├── Cook/             # Cook panel
│   │   │   ├── Customer/         # Customer ordering
│   │   │   ├── SuperAdmin/       # Platform admin
│   │   │   ├── Waiter/           # Waiter panel
│   │   │   └── TelegramController.php
│   │   └── Middleware/
│   │       ├── Authenticate.php
│   │       ├── CheckRole.php
│   │       ├── IdentifyTenant.php
│   │       └── SuperAdminAuth.php
│   ├── Models/
│   │   ├── Concerns/
│   │   │   └── BelongsToTenant.php  # Tenant scoping trait
│   │   ├── MenuCategory.php
│   │   ├── MenuItem.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── RestaurantTable.php
│   │   ├── TableCategory.php
│   │   ├── TelegramRequest.php
│   │   ├── Tenant.php
│   │   └── User.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   └── Services/
│       └── TelegramService.php
├── database/
│   ├── migrations/               # 21 migrations (chronological)
│   └── seeders/
│       └── DatabaseSeeder.php   # Sample data
├── public/
│   ├── js/
│   │   └── order-updates.js     # Real-time updates
│   └── uploads/
│       └── menu/                # Menu item images
├── resources/
│   └── views/
│       ├── admin/               # Admin panel views
│       ├── cashier/             # Cashier panel views
│       ├── cook/                # Cook panel views
│       ├── customer/            # Customer menu view
│       ├── layouts/             # Layout templates
│       ├── superadmin/          # Super admin views
│       └── waiter/              # Waiter panel views
├── routes/
│   └── web.php                  # All application routes
└── storage/
    └── logs/
        └── laravel.log          # Application logs
```

---

## 🔧 TECHNOLOGY STACK

### Backend
- **Framework:** Laravel 12.x
- **PHP:** 8.2+
- **Database:** MySQL 5.7+
- **ORM:** Eloquent

### Frontend
- **CSS Framework:** Tailwind CSS (CDN)
- **JavaScript:** Vanilla JS + Alpine.js (for some components)
- **Templating:** Blade

### Key Packages
- **simplesoftwareio/simple-qrcode** - QR code generation
- **maatwebsite/excel** - Excel export functionality
- **laravel/tinker** - REPL for debugging

### Development Tools
- **Composer** - Dependency management
- **Artisan** - CLI tool
- **Pest** - Testing framework
- **Laravel Pint** - Code style fixer

---

## 🌐 ROUTING STRUCTURE

### Public Routes
- `/` - Welcome page
- `/table/{qrCode}` - Customer menu (QR code entry point)
- `/telegram/webhook` - Telegram bot webhook

### Super Admin Routes (Prefix: `/superadmin`)
- `/superadmin/login` - Super admin login
- `/superadmin/dashboard` - Platform dashboard
- `/superadmin/tenants` - Manage restaurants
- `/superadmin/users` - Manage platform users
- `/superadmin/table-categories` - Global table categories
- `/superadmin/menu-categories` - Global menu categories

### Admin Routes (Prefix: `/admin`)
- `/admin/dashboard` - Restaurant dashboard
- `/admin/employees` - Staff management
- `/admin/tables` - Table management
- `/admin/menu` - Menu management
- `/admin/cook` - Cook panel
- `/admin/telegram` - Telegram integration
- `/admin/reports` - Reports & analytics

### Waiter Routes (Prefix: `/waiter`)
- `/waiter/dashboard` - Waiter dashboard
- `/waiter/orders` - Order management
- `/waiter/orders/create` - Manual order creation

### Cook Routes (Prefix: `/cook`)
- `/cook/dashboard` - Cook dashboard
- `/cook/orders/pending` - Pending orders
- `/cook/orders/processing` - In-progress orders
- `/cook/orders/completed` - Completed orders

### Cashier Routes (Prefix: `/cashier`)
- `/cashier/dashboard` - Cashier dashboard
- `/cashier/payments` - Payment processing
- `/cashier/payments/history` - Payment history

---

## 📊 ORDER LIFECYCLE

```
1. PENDING
   ↓ (Customer places order via QR code OR Waiter creates manual order)
   
2. PREPARING
   ↓ (Chef sets preparation time and starts cooking)
   
3. READY
   ↓ (Chef marks order as ready)
   
4. SERVED
   ↓ (Waiter marks order as served to customer)
   
5. COMPLETED
   ↓ (Cashier processes payment)
   
Alternative: CANCELLED (at any stage before SERVED)
```

---

## 🔄 REAL-TIME FEATURES

### Broadcasting Events
1. **NewOrder** - Fired when customer places order
   - Listeners: Cook panel
   - Action: Display new order instantly

2. **OrderStatusUpdated** - Fired when order status changes
   - Listeners: Customer view, Waiter dashboard
   - Action: Update status badge and countdown

3. **OrderUpdated** - General order updates
   - Listeners: All relevant panels
   - Action: Refresh order details

### WebSocket Configuration
- **Driver:** Pusher or Laravel Reverb
- **Channels:** Private channels per tenant
- **Authentication:** Laravel Sanctum/Session

---

## 🎨 UI/UX DESIGN PRINCIPLES

### Customer Interface
- **Clean & Modern:** No emojis, professional design
- **Mobile-First:** Responsive for phone scanning
- **Real-Time:** Live order status updates
- **Intuitive:** Simple +/- buttons for ordering

### Admin Panels
- **Dashboard-Centric:** Quick access to key metrics
- **Role-Specific:** Each role sees relevant information
- **Data Tables:** Sortable, filterable lists
- **Action Buttons:** Clear CTAs for common tasks

### Color Scheme
- **Primary:** Blue tones (trust, professionalism)
- **Success:** Green (completed, ready)
- **Warning:** Yellow (preparing, pending)
- **Danger:** Red (cancelled, errors)

---

## 🔒 SECURITY FEATURES

### Data Protection
- **CSRF Protection:** All forms protected
- **SQL Injection Prevention:** Eloquent ORM
- **XSS Protection:** Blade templating auto-escapes
- **Password Hashing:** Bcrypt algorithm

### Access Control
- **Middleware Stack:** Multiple layers of protection
- **Tenant Isolation:** Strict data separation
- **Role-Based Access:** Granular permissions
- **Session Security:** Secure, httpOnly cookies

### File Upload Security
- **Validation:** File type and size checks
- **Storage:** Outside public directory
- **Naming:** Timestamped, unique names
- **Access Control:** Authenticated access only

---

## 📈 SCALABILITY CONSIDERATIONS

### Database
- **Indexing:** Foreign keys and frequently queried columns
- **Caching:** Database-driven cache for sessions
- **Query Optimization:** Eager loading, select specific columns

### Performance
- **Asset Optimization:** Minified CSS/JS
- **Image Optimization:** Compressed uploads
- **Route Caching:** Production route cache
- **Config Caching:** Production config cache
- **View Caching:** Compiled Blade templates

### Multi-Tenancy
- **Data Isolation:** Tenant ID on all queries
- **Subdomain Routing:** Optional per-tenant domains
- **Resource Limits:** Configurable per tenant

---

## 🧪 TESTING STRATEGY

### Manual Testing Checklist
- ✅ Table creation and QR generation
- ✅ Menu CRUD operations
- ✅ Customer ordering flow
- ✅ Real-time order updates
- ✅ Cook panel functionality
- ✅ Payment processing
- ✅ Multi-tenant isolation
- ✅ Role-based access control

### Automated Testing (Pest)
- Unit tests for models
- Feature tests for controllers
- Integration tests for workflows

---

## 🚀 DEPLOYMENT HISTORY

### Development Environment
- **Server:** XAMPP (Windows)
- **PHP:** 8.4.12
- **MySQL:** Via XAMPP
- **URL:** http://localhost:8000

### Production Deployment
- **Domain:** swadsangam.store
- **Hosting:** Shared hosting (cPanel)
- **Challenges:** 500 errors, file permissions, .htaccess configuration
- **Status:** Documented in DEPLOYMENT_CHECKLIST.md and FIX-500-ERROR.md

---

## 📚 DOCUMENTATION FILES

1. **README.md** - Laravel framework information
2. **RESTAURANT_README.md** - Feature documentation
3. **SYSTEM_COMPLETE.md** - Complete system guide
4. **QUICK_START.md** - 5-minute setup guide
5. **DEPLOYMENT_CHECKLIST.md** - Production deployment guide
6. **FIX-500-ERROR.md** - Troubleshooting guide
7. **PROJECT_CHRONOLOGY.md** - This file

---

## 🔮 FUTURE ENHANCEMENTS

### Planned Features
- [ ] Customer accounts and order history
- [ ] Loyalty program integration
- [ ] Advanced analytics dashboard
- [ ] Mobile app (React Native/Flutter)
- [ ] Multi-language support
- [ ] Online payment gateway integration
- [ ] Inventory management
- [ ] Reservation system
- [ ] Customer feedback system
- [ ] Kitchen display system (KDS)

### Technical Improvements
- [ ] API documentation (Swagger/OpenAPI)
- [ ] Automated testing suite
- [ ] CI/CD pipeline
- [ ] Docker containerization
- [ ] Redis caching
- [ ] Queue workers for background jobs
- [ ] Elasticsearch for advanced search
- [ ] GraphQL API

---

## 🤝 CONTRIBUTION GUIDELINES

### Code Standards
- Follow PSR-12 coding standards
- Use Laravel best practices
- Write descriptive commit messages
- Document complex logic

### Git Workflow
- Feature branches for new features
- Bug fix branches for fixes
- Pull requests for code review
- Semantic versioning

---

## 📞 SUPPORT & MAINTENANCE

### Common Issues
1. **500 Error** → Check .env, permissions, logs
2. **QR Not Showing** → Install GD extension
3. **Images Not Uploading** → Run `php artisan storage:link`
4. **Real-Time Not Working** → Configure Pusher/Reverb

### Log Locations
- **Application:** storage/logs/laravel.log
- **Web Server:** Check hosting panel
- **Database:** MySQL error logs

### Maintenance Commands
```bash
# Clear all caches
php artisan optimize:clear

# Rebuild caches (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Database maintenance
php artisan migrate:status
php artisan db:seed

# Storage link
php artisan storage:link
```

---

## 📊 PROJECT METRICS

### Database
- **Tables:** 12 core tables
- **Migrations:** 21 migration files
- **Relationships:** Fully normalized schema

### Codebase
- **Controllers:** 20+ controllers
- **Models:** 9 Eloquent models
- **Views:** 50+ Blade templates
- **Routes:** 60+ defined routes
- **Middleware:** 4 custom middleware

### Features
- **User Roles:** 6 distinct roles
- **Panels:** 5 role-specific dashboards
- **Order Statuses:** 6 status stages
- **Real-Time Events:** 4 broadcast events

---

## 🎓 LEARNING RESOURCES

### Laravel Documentation
- https://laravel.com/docs/12.x

### Key Concepts Used
- Eloquent ORM and relationships
- Blade templating engine
- Middleware and authentication
- Broadcasting and events
- File storage and uploads
- Database migrations and seeding
- Route model binding
- Form validation

---

## ✅ SYSTEM STATUS

**Current Version:** 1.0.0  
**Status:** ✅ FULLY OPERATIONAL  
**Last Updated:** March 2026  
**Environment:** Development (XAMPP)  
**Production Ready:** Yes (with deployment checklist)

---

## 📝 CHANGELOG

### Version 1.0.0 (Current)
- ✅ Multi-tenant architecture
- ✅ Complete role-based access control
- ✅ QR code ordering system
- ✅ Real-time kitchen operations
- ✅ Payment processing
- ✅ Telegram integration
- ✅ Reporting and analytics
- ✅ Comprehensive documentation

---

**End of Project Chronology**

*This document provides a complete understanding of the PayServer Restaurant Management System, its evolution, architecture, and current capabilities.*
