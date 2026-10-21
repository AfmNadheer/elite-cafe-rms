<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Bill;
use App\Models\Expense;
use App\Models\MenuItem;
use App\Models\OrderItem;
use App\Models\Purchase;
use App\Models\SiteSetting;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Page numbers (1, 2, 3 ...) styled with Bootstrap 5
        Paginator::useBootstrapFive();

        $this->registerAuditLog();

        // Public website pages get the website settings as $site
        View::composer(['layouts.site', 'site.*', 'shop.*', 'public.*'], function ($view) {
            $view->with('site', SiteSetting::values());
        });
    }

    /**
     * Audit log (NFR-06): important changes are recorded automatically,
     * wherever in the system they happen.
     */
    private function registerAuditLog(): void
    {
        Event::listen(Login::class, fn (Login $e) => AuditLog::record('login', $e->user->name.' logged in', null, $e->user->getAuthIdentifier()));
        Event::listen(Logout::class, function (Logout $e) {
            if ($e->user) {
                AuditLog::record('logout', $e->user->name.' logged out', null, $e->user->getAuthIdentifier());
            }
        });

        MenuItem::updated(function (MenuItem $item) {
            if ($item->wasChanged('price')) {
                AuditLog::record('price_changed', sprintf('%s: Rs. %s → Rs. %s',
                    $item->name, number_format((float) $item->getOriginal('price'), 2), number_format((float) $item->price, 2)), $item);
            }
        });
        MenuItem::deleted(fn (MenuItem $item) => AuditLog::record('menu_item_deleted', 'Deleted '.$item->name, $item));

        OrderItem::updated(function (OrderItem $line) {
            if ($line->wasChanged('status') && $line->status === 'cancelled') {
                AuditLog::record('item_cancelled', sprintf('Order #%d: %d × %s cancelled (was %s)',
                    $line->order_id, $line->quantity, $line->menuItem?->name ?? 'item', $line->getOriginal('status')), $line);
            }
        });

        Bill::created(function (Bill $bill) {
            if ($bill->discount > 0) {
                AuditLog::record('discount', sprintf('Bill %s: discount Rs. %s approved by %s',
                    $bill->bill_no, number_format((float) $bill->discount, 2), $bill->approver?->name ?? 'unknown'), $bill);
            }
        });

        User::created(fn (User $user) => AuditLog::record('user_created', "Created user {$user->username} ({$user->role})", $user));
        User::updated(function (User $user) {
            $changes = [];
            if ($user->wasChanged('role')) {
                $changes[] = 'role '.$user->getOriginal('role').' → '.$user->role;
            }
            if ($user->wasChanged('is_active')) {
                $changes[] = $user->is_active ? 'activated' : 'deactivated';
            }
            if ($user->wasChanged('password') && auth()->id() !== $user->id) {
                $changes[] = 'password reset';
            }
            if ($changes) {
                AuditLog::record('user_changed', $user->username.': '.implode(', ', $changes), $user);
            }
        });

        StockAdjustment::created(fn (StockAdjustment $adj) => AuditLog::record('stock_adjusted', sprintf('%s: %s%s %s (%s)',
            $adj->ingredient?->name, $adj->quantity > 0 ? '+' : '', (float) $adj->quantity, $adj->ingredient?->unit, $adj->reason), $adj));

        Purchase::created(fn (Purchase $p) => AuditLog::record('purchase', 'Purchase #'.$p->id.' from '.$p->supplier?->name, $p));

        Expense::deleted(fn (Expense $e) => AuditLog::record('expense_deleted', sprintf('Deleted %s expense Rs. %s (%s)',
            $e->category, number_format((float) $e->amount, 2), $e->expense_date?->format('d M Y')), $e));
    }
}
