<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\WaiterController;
use App\Http\Controllers\Api\Mobile\ChefController;
use App\Http\Controllers\Api\Mobile\CashierController;

Route::prefix('mobile')->group(function () {

    // ── Public ────────────────────────────────────────────────────────────
    Route::post('login', [AuthController::class, 'login']);

    // ── Authenticated ─────────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('logout',  [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('profile',  [AuthController::class, 'profile']);
        Route::get('orders/poll', [\App\Http\Controllers\Api\Mobile\PollController::class, 'poll']);

        // Waiter
        Route::middleware('api.role:waiter')->prefix('waiter')->group(function () {
            Route::get('orders',                        [WaiterController::class, 'orders']);
            Route::post('orders',                       [WaiterController::class, 'store']);
            Route::post('orders/{id}/add-items',        [WaiterController::class, 'addItems']);
            Route::patch('orders/{id}/serve',           [WaiterController::class, 'markServed']);
            Route::patch('orders/{id}/checkout',        [WaiterController::class, 'checkout']);
            Route::patch('orders/{id}/cancel',          [WaiterController::class, 'cancelOrder']);
            Route::patch('order-items/{id}/cancel',     [WaiterController::class, 'cancelItem']);
            Route::patch('order-items/{id}',            [WaiterController::class, 'updateItem']);
            Route::get('menu',                          [WaiterController::class, 'menu']);
            Route::get('tables',                        [WaiterController::class, 'tables']);
        });

        // Chef
        Route::middleware('api.role:chef')->prefix('chef')->group(function () {
            Route::get('orders/pending',                [ChefController::class, 'pending']);
            Route::get('orders/completed',              [ChefController::class, 'completed']);
            Route::patch('order-items/{id}/status',     [ChefController::class, 'updateItemStatus']);
            Route::patch('orders/{id}/cancel',          [ChefController::class, 'cancelOrder']);
            Route::patch('orders/{id}/ready',            [ChefController::class, 'markOrderReady']);
            Route::patch('order-items/{id}/cancel',     [ChefController::class, 'cancelItem']);
            Route::patch('order-items/{id}',            [ChefController::class, 'updateItem']);
        });

        // Cashier
        Route::middleware('api.role:cashier')->prefix('cashier')->group(function () {
            Route::get('payments',                      [CashierController::class, 'payments']);
            Route::patch('payments/{id}/process',       [CashierController::class, 'processPayment']);
            Route::get('payments/history',              [CashierController::class, 'history']);
            Route::get('parcels',                       [CashierController::class, 'parcels']);
            Route::post('parcels',                      [CashierController::class, 'storeParcel']);
            Route::post('parcels/{id}/add-items',       [CashierController::class, 'addParcelItems']);
            Route::patch('parcels/{id}/cancel',         [CashierController::class, 'cancelParcel']);
            Route::patch('parcel-items/{id}/cancel',    [CashierController::class, 'cancelParcelItem']);
            Route::get('menu',                          [CashierController::class, 'menu']);
        });
    });
});
