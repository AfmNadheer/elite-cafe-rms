<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DayEndController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Open the system -> dashboard (or login if not logged in)
Route::redirect('/', '/dashboard');

// Pages for people who are NOT logged in
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Pages for logged-in staff
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Live table map: admin, waiter, cashier (FR-11)
    Route::get('/table-map', [TableController::class, 'map'])
        ->middleware('role:admin,waiter,cashier')
        ->name('tables.map');

    // Orders: waiter and admin (FR-13 to FR-18)
    Route::middleware('role:admin,waiter')->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/add-items', [OrderController::class, 'addItems'])->name('orders.add-items');
        Route::post('/orders/{order}/items', [OrderController::class, 'storeItems'])->name('orders.items.store');
        Route::patch('/order-items/{orderItem}/cancel', [OrderItemController::class, 'cancel'])->name('order-items.cancel');
        Route::patch('/order-items/{orderItem}/serve', [OrderItemController::class, 'serve'])->name('order-items.serve');
    });

    // Kitchen display: kitchen staff and admin (FR-19 to FR-22)
    Route::middleware('role:admin,kitchen')->group(function () {
        Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
        Route::get('/kitchen/items', [KitchenController::class, 'items'])->name('kitchen.items');
        Route::patch('/kitchen/items/{orderItem}', [KitchenController::class, 'updateStatus'])->name('kitchen.update');
    });

    // Billing, payments and day-end: cashier and admin (FR-23 to FR-28)
    Route::middleware('role:admin,cashier')->group(function () {
        Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
        Route::get('/billing/orders/{order}', [BillingController::class, 'create'])->name('billing.create');
        Route::post('/billing/orders/{order}', [BillingController::class, 'store'])->name('billing.store');
        Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
        Route::get('/bills/{bill}', [BillController::class, 'show'])->name('bills.show');
        Route::get('/day-end', [DayEndController::class, 'index'])->name('day-end.index');
    });

    // Admin-only setup pages
    Route::middleware('role:admin')->group(function () {
        // Users (FR-03): no delete, accounts are deactivated instead
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::patch('/users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');

        // Menu categories (FR-06)
        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

        // Menu items (FR-07, FR-08)
        Route::resource('menu-items', MenuItemController::class)->except(['show']);
        Route::patch('/menu-items/{menu_item}/availability', [MenuItemController::class, 'toggleAvailability'])
            ->name('menu-items.availability');

        // Tables (FR-10)
        Route::resource('tables', TableController::class)->except(['show']);
    });
});
