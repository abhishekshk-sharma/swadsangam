<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\TableCategoryController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MenuCategoryController;
use App\Http\Controllers\Admin\CookController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ManagerController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\SuperAdmin\TenantController;
use App\Http\Controllers\Waiter\DashboardController as WaiterDashboardController;
use App\Http\Controllers\Waiter\OrderController as WaiterOrderController;
use App\Http\Controllers\Cook\DashboardController as CookDashboardController;
use App\Http\Controllers\Cook\OrderController as CookOrderController;
use App\Http\Controllers\Cashier\DashboardController as CashierDashboardController;
use App\Http\Controllers\Cashier\PaymentController as CashierPaymentController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\Admin\TelegramIntegrationController;
use App\Http\Controllers\Api\OrderUpdatesController;
use App\Http\Controllers\Admin\CashHandoverController as AdminCashHandoverController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\MenuOcrController;
use App\Http\Controllers\Cashier\CashHandoverController as CashierCashHandoverController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\StaffController as ManagerStaffController;
use App\Http\Controllers\Manager\MenuController as ManagerMenuController;
use App\Http\Controllers\Manager\TableController as ManagerTableController;
use App\Http\Controllers\Manager\CookController as ManagerCookController;
use App\Http\Controllers\Manager\ReportController as ManagerReportController;
use App\Http\Controllers\Manager\CashHandoverController as ManagerCashHandoverController;
use App\Http\Controllers\Manager\TableCategoryController as ManagerTableCategoryController;
use App\Http\Controllers\Manager\MenuCategoryController as ManagerMenuCategoryController;
use App\Http\Controllers\Manager\MenuOcrController as ManagerMenuOcrController;

Route::get('/', function () {
    try {
        return view('welcome');
    } catch (\Exception $e) {
        return response('Error: ' . $e->getMessage(), 500);
    }
});

Route::get('/test', function () {
    if (!app()->environment('local')) abort(404);
    return 'Laravel is working!';
});

Route::get('/test-upload', function () {
    if (!app()->environment('local')) abort(404);
    return view('test-upload');
});

Route::post('/test-upload-direct', function (Illuminate\Http\Request $request) {
    if (!app()->environment('local')) abort(404);
    $debug = [];
    $debug['has_file'] = $request->hasFile('image') ? 'YES' : 'NO';
    $debug['all_files'] = $request->allFiles();
    $debug['all_input'] = $request->all();
    $debug['content_type'] = $request->header('Content-Type');
    
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->move(public_path('uploads/menu'), $imageName);
        
        $item = App\Models\MenuItem::find(1);
        $item->image = 'uploads/menu/' . $imageName;
        $item->save();
        
        return 'SUCCESS! Image uploaded: ' . $imageName;
    }
    return response()->json(['error' => 'No file uploaded', 'debug' => $debug]);
});

Route::match(['get', 'post'], '/debug/upload', function() {
    if (!app()->environment('local')) abort(404);
    return app(App\Http\Controllers\DebugController::class)->testUpload(request());
});

// Telegram Webhook (no middleware)
Route::post('/telegram/webhook', [TelegramController::class, 'webhook']);
Route::get('/telegram/test', function() {
    return 'Telegram webhook endpoint is working!';
});

// API for real-time updates
Route::get('/api/order-updates', [OrderUpdatesController::class, 'getUpdates'])->middleware('multi.auth');
Route::get('/api/waiter/order-updates', [App\Http\Controllers\Api\WaiterNotificationController::class, 'getUpdates'])->middleware('multi.auth');
Route::get('/api/chef/order-updates', [App\Http\Controllers\Api\ChefNotificationController::class, 'getUpdates'])->middleware('multi.auth');

// Profile Routes (for all authenticated users)
Route::middleware('multi.auth')->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});

// Super Admin Routes (no tenant middleware)
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('login', [TenantController::class, 'login'])->name('login');
    Route::post('login', [TenantController::class, 'authenticate']);
    Route::post('logout', [TenantController::class, 'logout'])->name('logout');
    
    Route::middleware(\App\Http\Middleware\SuperAdminAuth::class)->group(function () {
        Route::get('dashboard', [App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
        
        Route::resource('tenants', TenantController::class)->except(['show']);
        
        Route::resource('users', App\Http\Controllers\SuperAdmin\UserController::class)->except(['show']);
        
        Route::get('table-categories', [App\Http\Controllers\SuperAdmin\TableCategoryController::class, 'index'])->name('table-categories.index');
        Route::post('table-categories', [App\Http\Controllers\SuperAdmin\TableCategoryController::class, 'store'])->name('table-categories.store');
        Route::delete('table-categories/{id}', [App\Http\Controllers\SuperAdmin\TableCategoryController::class, 'destroy'])->name('table-categories.destroy');
        
        Route::get('menu-categories', [App\Http\Controllers\SuperAdmin\MenuCategoryController::class, 'index'])->name('menu-categories.index');
        Route::post('menu-categories', [App\Http\Controllers\SuperAdmin\MenuCategoryController::class, 'store'])->name('menu-categories.store');
        Route::delete('menu-categories/{id}', [App\Http\Controllers\SuperAdmin\MenuCategoryController::class, 'destroy'])->name('menu-categories.destroy');
    });
});

// Login Routes
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware(['multi.auth', 'role:admin,super_admin'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('employees', EmployeeController::class);
        Route::post('employees/{manager}/assign', [EmployeeController::class, 'assignEmployee'])->name('employees.assign');
        Route::post('employees/{employee}/unassign', [EmployeeController::class, 'unassignEmployee'])->name('employees.unassign');
        Route::get('managers/ajax', [ManagerController::class, 'ajaxIndex'])->name('managers.ajax');
        Route::resource('managers', ManagerController::class);
        Route::get('staff/ajax', [StaffController::class, 'ajaxIndex'])->name('staff.ajax');
        Route::resource('staff', StaffController::class);
        Route::resource('branches', \App\Http\Controllers\Admin\BranchController::class);
        Route::resource('tables', TableController::class);
        Route::get('categories', [TableCategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [TableCategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{id}', [TableCategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{id}', [TableCategoryController::class, 'destroy'])->name('categories.destroy');
        Route::post('categories/quick-create', [TableCategoryController::class, 'quickCreate'])->name('categories.quickCreate');
        Route::get('menu-categories', [MenuCategoryController::class, 'index'])->name('menu-categories.index');
        Route::post('menu-categories', [MenuCategoryController::class, 'store'])->name('menu-categories.store');
        Route::put('menu-categories/{id}', [MenuCategoryController::class, 'update'])->name('menu-categories.update');
        Route::delete('menu-categories/{id}', [MenuCategoryController::class, 'destroy'])->name('menu-categories.destroy');
        Route::post('menu-categories/quick-create', [MenuCategoryController::class, 'quickCreate'])->name('menu-categories.quickCreate');
        Route::post('menu/{id}/update', [MenuController::class, 'update'])->name('menu.update.post');
        Route::resource('menu', MenuController::class);
        Route::get('cook', [CookController::class, 'index'])->name('cook.index');
        Route::post('cook/{id}/start', [CookController::class, 'startPreparing'])->name('cook.start');
        Route::post('cook/{id}/ready', [CookController::class, 'markReady'])->name('cook.ready');
        Route::post('cook/{id}/served', [CookController::class, 'markServed'])->name('cook.served');
        Route::patch('cook/{id}/payment', [CookController::class, 'processPayment'])->name('cook.payment');
        Route::patch('cook/orders/{id}/cancel', [CookController::class, 'cancelOrder'])->name('cook.orders.cancel');
        Route::patch('cook/order-items/{id}/cancel', [CookController::class, 'cancelItem'])->name('cook.orderItems.cancel');
        Route::patch('cook/order-items/{id}/update', [CookController::class, 'updateItem'])->name('cook.orderItems.update');
        Route::get('telegram', [TelegramIntegrationController::class, 'index'])->name('telegram.index');
        Route::post('telegram/link/{id}', [TelegramIntegrationController::class, 'linkUser'])->name('telegram.link');
        Route::delete('telegram/reject/{id}', [TelegramIntegrationController::class, 'reject'])->name('telegram.reject');
        Route::delete('telegram/unlink/{id}', [TelegramIntegrationController::class, 'unlink'])->name('telegram.unlink');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
        // Menu OCR
        Route::get('menu-ocr', [MenuOcrController::class, 'index'])->name('menu-ocr.index');
        Route::post('menu-ocr', [MenuOcrController::class, 'process'])->name('menu-ocr.process');
        Route::post('menu-ocr/import', [MenuOcrController::class, 'import'])->name('menu-ocr.import');
        Route::post('menu-ocr/excel', [MenuOcrController::class, 'importExcel'])->name('menu-ocr.excel');
        // Cash Handover
        Route::get('handover', [AdminCashHandoverController::class, 'index'])->name('handover.index');
        Route::get('handover/export', [AdminCashHandoverController::class, 'export'])->name('handover.export');
        Route::get('handover/{handover}/edit', [AdminCashHandoverController::class, 'edit'])->name('handover.edit');
        Route::patch('handover/{handover}', [AdminCashHandoverController::class, 'update'])->name('handover.update');
        Route::post('handover/{handover}/approve', [AdminCashHandoverController::class, 'approve'])->name('handover.approve');
    });
});

// Public Bill Route — signed URL prevents order ID enumeration
Route::get('/bill/{orderId}', [App\Http\Controllers\BillController::class, 'show'])->name('bill.show');

// Customer Routes
Route::get('table/{qrCode}', [OrderController::class, 'showMenu'])->name('customer.menu');
Route::post('table/{qrCode}/order', [OrderController::class, 'placeOrder'])->name('customer.order');
Route::get('order/{orderId}/status', [OrderController::class, 'getOrderStatus'])->name('order.status');

// Waiter Routes
Route::prefix('waiter')->name('waiter.')->middleware(['multi.auth', 'role:waiter'])->group(function () {
    Route::get('dashboard', [WaiterDashboardController::class, 'index'])->name('dashboard');
    Route::get('orders', [WaiterOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/create', [WaiterOrderController::class, 'create'])->name('orders.create');
    Route::post('orders', [WaiterOrderController::class, 'store'])->name('orders.store');
    Route::post('orders/{id}/serve', [WaiterOrderController::class, 'markServed'])->name('orders.serve');
    Route::post('orders/{id}/checkout', [WaiterOrderController::class, 'checkoutOrder'])->name('orders.checkout');
    Route::post('orders/{id}/add-items', [WaiterOrderController::class, 'addItems'])->name('orders.addItems');
    Route::patch('orders/{id}/cancel', [WaiterOrderController::class, 'cancelOrder'])->name('orders.cancel');
    Route::patch('order-items/{id}/cancel', [WaiterOrderController::class, 'cancelItem'])->name('orderItems.cancel');
    Route::patch('order-items/{id}/update', [WaiterOrderController::class, 'updateItem'])->name('orderItems.update');
});

// Cook Routes
Route::prefix('cook')->name('cook.')->middleware(['multi.auth', 'role:chef'])->group(function () {
    Route::get('dashboard', [CookDashboardController::class, 'index'])->name('dashboard');
    Route::get('orders/pending', [CookOrderController::class, 'pending'])->name('orders.pending');
    Route::get('orders/completed', [CookOrderController::class, 'completed'])->name('orders.completed');
    Route::patch('order-items/{orderItem}/status', [CookOrderController::class, 'updateItemStatus'])->name('orderItems.updateStatus');
    Route::patch('orders/{id}/cancel', [CookOrderController::class, 'cancelOrder'])->name('orders.cancel');
    Route::patch('order-items/{id}/cancel', [CookOrderController::class, 'cancelItem'])->name('orderItems.cancel');
    Route::patch('order-items/{id}/update', [CookOrderController::class, 'updateItem'])->name('orderItems.update');
});

// Manager Routes (dedicated panel — no admin role mixing)
Route::prefix('manager')->name('manager.')->middleware(['multi.auth', 'role:manager'])->group(function () {
    Route::get('dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
    // Staff
    Route::resource('staff', ManagerStaffController::class);
    // Menu
    Route::resource('menu', ManagerMenuController::class);
    // Tables
    Route::resource('tables', ManagerTableController::class);
    // Cook / Orders
    Route::get('cook', [ManagerCookController::class, 'index'])->name('cook.index');
    Route::post('cook/{id}/start', [ManagerCookController::class, 'startPreparing'])->name('cook.start');
    Route::post('cook/{id}/ready', [ManagerCookController::class, 'markReady'])->name('cook.ready');
    Route::post('cook/{id}/served', [ManagerCookController::class, 'markServed'])->name('cook.served');
    Route::patch('cook/{id}/payment', [ManagerCookController::class, 'processPayment'])->name('cook.payment');
    Route::patch('cook/orders/{id}/cancel', [ManagerCookController::class, 'cancelOrder'])->name('cook.orders.cancel');
    Route::patch('cook/order-items/{id}/cancel', [ManagerCookController::class, 'cancelItem'])->name('cook.orderItems.cancel');
    Route::patch('cook/order-items/{id}/update', [ManagerCookController::class, 'updateItem'])->name('cook.orderItems.update');
    // Reports
    Route::get('reports', [ManagerReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [ManagerReportController::class, 'export'])->name('reports.export');
    // Table Categories
    Route::get('table-categories', [ManagerTableCategoryController::class, 'index'])->name('table-categories.index');
    Route::post('table-categories', [ManagerTableCategoryController::class, 'store'])->name('table-categories.store');
    Route::put('table-categories/{id}', [ManagerTableCategoryController::class, 'update'])->name('table-categories.update');
    Route::delete('table-categories/{id}', [ManagerTableCategoryController::class, 'destroy'])->name('table-categories.destroy');
    Route::post('table-categories/quick-create', [ManagerTableCategoryController::class, 'quickCreate'])->name('table-categories.quickCreate');
    // Menu Categories
    Route::get('menu-categories', [ManagerMenuCategoryController::class, 'index'])->name('menu-categories.index');
    Route::post('menu-categories', [ManagerMenuCategoryController::class, 'store'])->name('menu-categories.store');
    Route::put('menu-categories/{id}', [ManagerMenuCategoryController::class, 'update'])->name('menu-categories.update');
    Route::delete('menu-categories/{id}', [ManagerMenuCategoryController::class, 'destroy'])->name('menu-categories.destroy');
    Route::post('menu-categories/quick-create', [ManagerMenuCategoryController::class, 'quickCreate'])->name('menu-categories.quickCreate');
    // Menu OCR
    Route::get('menu-ocr', [ManagerMenuOcrController::class, 'index'])->name('menu-ocr.index');
    Route::post('menu-ocr', [ManagerMenuOcrController::class, 'process'])->name('menu-ocr.process');
    Route::post('menu-ocr/import', [ManagerMenuOcrController::class, 'import'])->name('menu-ocr.import');
    Route::post('menu-ocr/excel', [ManagerMenuOcrController::class, 'importExcel'])->name('menu-ocr.excel');
    // Cash Handover
    Route::get('handover', [ManagerCashHandoverController::class, 'index'])->name('handover.index');
    Route::get('handover/{handover}/edit', [ManagerCashHandoverController::class, 'edit'])->name('handover.edit');
    Route::patch('handover/{handover}', [ManagerCashHandoverController::class, 'update'])->name('handover.update');
    Route::post('handover/{handover}/approve', [ManagerCashHandoverController::class, 'approve'])->name('handover.approve');
});

// Cashier Routes
Route::prefix('cashier')->name('cashier.')->middleware(['multi.auth', 'role:cashier'])->group(function () {
    Route::get('dashboard', [CashierDashboardController::class, 'index'])->name('dashboard');
    Route::get('payments', [CashierPaymentController::class, 'index'])->name('payments.index');
    Route::patch('payments/{order}/process', [CashierPaymentController::class, 'processPayment'])->name('payments.process');
    Route::get('payments/history', [CashierPaymentController::class, 'history'])->name('payments.history');
    Route::get('parcels', [CashierPaymentController::class, 'parcelsIndex'])->name('parcels.index');
    Route::get('parcels/create', [CashierPaymentController::class, 'createParcel'])->name('parcels.create');
    Route::post('parcels', [CashierPaymentController::class, 'storeParcel'])->name('parcels.store');
    Route::post('parcels/{id}/add-items', [CashierPaymentController::class, 'addParcelItems'])->name('parcels.addItems');
    Route::patch('parcels/{id}/cancel', [CashierPaymentController::class, 'cancelParcelOrder'])->name('parcels.cancel');
    Route::patch('parcel-items/{id}/cancel', [CashierPaymentController::class, 'cancelParcelItem'])->name('parcelItems.cancel');
    Route::patch('parcel-items/{id}/update', [CashierPaymentController::class, 'updateParcelItem'])->name('parcelItems.update');
    // Cash Handover
    Route::get('handover', [CashierCashHandoverController::class, 'create'])->name('handover.create');
    Route::post('handover', [CashierCashHandoverController::class, 'store'])->name('handover.store');
});
