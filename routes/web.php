<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DayEndController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PayHereController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicMenuController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomBookingController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\ShopRoomController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockAlertController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebsiteSettingsController;
use Illuminate\Support\Facades\Route;

// Public website (home page with menu, rooms, about and contact)
Route::get('/', [SiteController::class, 'home'])->name('home');
Route::post('/contact', [SiteController::class, 'contact'])->middleware('throttle:5,1')->name('contact.send');

// Digital menu for customers (QR code), no login needed (FR-41)
Route::get('/menu', [PublicMenuController::class, 'index'])->name('public.menu');

// PayHere sends the payment result here, server to server (no login, no CSRF token)
Route::post('/payhere/notify', [PayHereController::class, 'notify'])->name('payhere.notify');

// ---------- Online ordering for delivery (customers) ----------
Route::prefix('shop')->name('shop.')->group(function () {
    // Menu and cart: open to everyone
    Route::get('/', [ShopController::class, 'index'])->name('index');
    Route::get('/cart', [ShopController::class, 'cart'])->name('cart');
    Route::post('/cart', [ShopController::class, 'addToCart'])->name('cart.add');
    Route::patch('/cart', [ShopController::class, 'updateCart'])->name('cart.update');
    Route::delete('/cart/{menuItem}', [ShopController::class, 'removeFromCart'])->name('cart.remove');
    Route::get('/rooms', [ShopRoomController::class, 'index'])->name('rooms.index');

    // Customer register / login
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [CustomerAuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');
        Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [CustomerAuthController::class, 'register'])->middleware('throttle:10,1')->name('register.submit');
    });

    // Logged-in customers
    Route::middleware('auth:customer')->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
        Route::get('/checkout', [ShopController::class, 'checkout'])->name('checkout');
        Route::post('/checkout', [ShopController::class, 'placeOrder'])->name('checkout.place');
        Route::get('/orders', [ShopController::class, 'orders'])->name('orders');
        Route::get('/orders/{delivery}', [ShopController::class, 'showOrder'])->name('orders.show');
        Route::get('/orders/{delivery}/status', [ShopController::class, 'orderStatus'])->name('orders.status');
        Route::get('/pay/{onlinePayment}', [ShopController::class, 'pay'])->name('pay');
        Route::get('/pay/{onlinePayment}/return', [ShopController::class, 'payReturn'])->name('pay.return');
        Route::get('/pay/{onlinePayment}/cancel', [ShopController::class, 'payCancel'])->name('pay.cancel');
        Route::get('/pay/{onlinePayment}/status', [ShopController::class, 'payStatus'])->name('pay.status');
        Route::post('/pay/{onlinePayment}/confirm-sandbox', [ShopController::class, 'confirmSandbox'])->name('pay.confirm-sandbox');

        // Online room booking
        Route::get('/rooms/book', [ShopRoomController::class, 'book'])->name('rooms.book');
        Route::post('/rooms/book', [ShopRoomController::class, 'store'])->name('rooms.store');
        Route::get('/my-bookings', [ShopRoomController::class, 'myBookings'])->name('rooms.mine');
        Route::get('/my-bookings/{roomBooking}', [ShopRoomController::class, 'show'])->name('rooms.show');
    });
});

// Pages for people who are NOT logged in
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Pages for logged-in staff
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Every user: change own password
    Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('profile.password');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Live table map: admin, waiter, cashier (FR-11)
    Route::get('/table-map', [TableController::class, 'map'])
        ->middleware('role:admin,waiter,cashier')
        ->name('tables.map');

    // Reservations: admin, waiter, cashier (FR-12)
    Route::middleware('role:admin,waiter,cashier')->group(function () {
        Route::resource('reservations', ReservationController::class)->except(['show', 'destroy']);
        Route::patch('/reservations/{reservation}/status', [ReservationController::class, 'status'])->name('reservations.status');
    });

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

        // Rooms: board, bookings, check-in / check-out (front desk)
        Route::get('/room-board', [RoomBookingController::class, 'board'])->name('room-bookings.board');
        Route::get('/room-bookings', [RoomBookingController::class, 'index'])->name('room-bookings.index');
        Route::get('/room-bookings/availability', [RoomBookingController::class, 'availability'])->name('room-bookings.availability');
        Route::get('/room-bookings/create', [RoomBookingController::class, 'create'])->name('room-bookings.create');
        Route::post('/room-bookings', [RoomBookingController::class, 'store'])->name('room-bookings.store');
        Route::get('/room-bookings/{roomBooking}', [RoomBookingController::class, 'show'])->name('room-bookings.show');
        Route::patch('/room-bookings/{roomBooking}/check-in', [RoomBookingController::class, 'checkIn'])->name('room-bookings.check-in');
        Route::post('/room-bookings/{roomBooking}/payments', [RoomBookingController::class, 'addPayment'])->name('room-bookings.payments.store');
        Route::patch('/room-bookings/{roomBooking}/check-out', [RoomBookingController::class, 'checkOut'])->name('room-bookings.check-out');
        Route::patch('/room-bookings/{roomBooking}/cancel', [RoomBookingController::class, 'cancel'])->name('room-bookings.cancel');
        Route::get('/room-bookings/{roomBooking}/invoice', [RoomBookingController::class, 'invoice'])->name('room-bookings.invoice');

        // Online delivery orders (staff side)
        Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
        Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show'])->name('deliveries.show');
        Route::patch('/deliveries/{delivery}/dispatch', [DeliveryController::class, 'dispatch'])->name('deliveries.dispatch');
        Route::patch('/deliveries/{delivery}/deliver', [DeliveryController::class, 'deliver'])->name('deliveries.deliver');
        Route::patch('/deliveries/{delivery}/cancel', [DeliveryController::class, 'cancel'])->name('deliveries.cancel');

        // Online card payment through PayHere
        Route::get('/payhere/{onlinePayment}/checkout', [PayHereController::class, 'checkout'])->name('payhere.checkout');
        Route::get('/payhere/{onlinePayment}/return', [PayHereController::class, 'return'])->name('payhere.return');
        Route::get('/payhere/{onlinePayment}/cancel', [PayHereController::class, 'cancel'])->name('payhere.cancel');
        Route::get('/payhere/{onlinePayment}/status', [PayHereController::class, 'status'])->name('payhere.status');
        Route::post('/payhere/{onlinePayment}/confirm-sandbox', [PayHereController::class, 'confirmSandbox'])
            ->middleware('role:admin')->name('payhere.confirm-sandbox');
    });

    // Inventory, suppliers and purchases: storekeeper and admin (FR-29, FR-32 to FR-35)
    Route::middleware('role:admin,storekeeper')->group(function () {
        Route::resource('ingredients', IngredientController::class)->except(['show']);
        Route::get('/ingredients/{ingredient}/adjust', [StockAdjustmentController::class, 'create'])->name('stock-adjustments.create');
        Route::post('/ingredients/{ingredient}/adjust', [StockAdjustmentController::class, 'store'])->name('stock-adjustments.store');
        Route::get('/stock-adjustments', [StockAdjustmentController::class, 'index'])->name('stock-adjustments.index');
        Route::get('/stock-alerts', [StockAlertController::class, 'index'])->name('stock-alerts.index');
        Route::patch('/stock-alerts/{stockAlert}/resolve', [StockAlertController::class, 'resolve'])->name('stock-alerts.resolve');
        Route::resource('suppliers', SupplierController::class)->except(['show']);
        Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
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

        // Recipes (FR-30)
        Route::get('/recipes', [RecipeController::class, 'index'])->name('recipes.index');
        Route::get('/recipes/{menuItem}', [RecipeController::class, 'edit'])->name('recipes.edit');
        Route::put('/recipes/{menuItem}', [RecipeController::class, 'update'])->name('recipes.update');

        // Rooms and room types (Admin)
        Route::resource('rooms', RoomController::class)->except(['show']);
        Route::resource('room-types', RoomTypeController::class)->only(['index', 'store', 'update', 'destroy']);

        // Other expenses (FR-36)
        Route::resource('expenses', ExpenseController::class)->except(['show']);

        // Reports with PDF export (FR-38 to FR-40)
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/items', [ReportController::class, 'items'])->name('reports.items');
        Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/wastage', [ReportController::class, 'wastage'])->name('reports.wastage');
        Route::get('/reports/expenses', [ReportController::class, 'expenses'])->name('reports.expenses');

        // QR code for the digital menu (FR-41)
        Route::get('/qr-menu', [PublicMenuController::class, 'qr'])->name('qr-menu');

        // Public website settings and contact form messages
        Route::get('/website-settings', [WebsiteSettingsController::class, 'edit'])->name('website-settings.edit');
        Route::put('/website-settings', [WebsiteSettingsController::class, 'update'])->name('website-settings.update');
        Route::get('/messages', [ContactMessageController::class, 'index'])->name('messages.index');
        Route::patch('/messages/{contactMessage}/toggle', [ContactMessageController::class, 'toggle'])->name('messages.toggle');
        Route::delete('/messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('messages.destroy');

        // Audit log (NFR-06) and database backup
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
        Route::get('/backup/download', [BackupController::class, 'download'])->name('backup.download');
    });
});
