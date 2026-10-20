<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MenuItemController;
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
