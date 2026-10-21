<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\ContactMessage;
use App\Models\Delivery;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Purchase;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\RoomPayment;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Home page after login: live numbers, alerts and quick actions for the user's role (FR-37).
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('dashboard', [
            'user' => $user,
            'kpis' => $this->kpis($user),
            'alerts' => $this->alerts($user),
            'chart' => $user->hasRole('admin') ? $this->lastSevenDays() : null,
            'recentBills' => $user->hasRole('admin', 'cashier')
                ? Bill::with('order.table')->latest()->take(6)->get() : collect(),
        ]);
    }

    /** Tiles of numbers, chosen by role. Each: label, value, sub-line, icon, link, progress (0–100 or null). */
    private function kpis($user): array
    {
        $k = [];
        $tables = RestaurantTable::count();

        if ($user->hasRole('admin', 'cashier')) {
            $sales = (float) Bill::whereDate('created_at', today())->sum('total');
            $roomIncome = (float) RoomPayment::whereDate('paid_at', today())->sum('amount');
            $rooms = Room::count();
            $inHouse = RoomBooking::where('status', 'checked_in')->count();

            $k[] = ['Restaurant sales today', 'Rs. '.number_format($sales), Bill::whereDate('created_at', today())->count().' bills', 'bi-cash-stack', 'bills.index', null];
            $k[] = ['Room income today', 'Rs. '.number_format($roomIncome), 'payments received', 'bi-building', 'room-bookings.index', null];
            $k[] = ['Rooms occupied', $inHouse.' / '.$rooms, 'guests in house', 'bi-door-closed', 'room-bookings.board', $rooms ? round($inHouse / $rooms * 100) : 0];
        }

        if ($user->hasRole('admin', 'cashier', 'waiter')) {
            $occupied = RestaurantTable::where('status', 'occupied')->count();
            $k[] = ['Tables occupied', $occupied.' / '.$tables, Order::where('status', 'open')->count().' open orders', 'bi-grid-3x3-gap', 'tables.map', $tables ? round($occupied / $tables * 100) : 0];
        }

        if ($user->hasRole('waiter')) {
            $k[] = ['Ready to serve', OrderItem::where('status', 'ready')->whereHas('order', fn ($q) => $q->where('status', 'open')->where('order_type', '!=', 'delivery'))->count(), 'items waiting at the pass', 'bi-bell', 'orders.index', null];
            $k[] = ['Reservations today', Reservation::whereDate('reserved_at', today())->where('status', 'booked')->count(), 'still to arrive', 'bi-calendar-check', 'reservations.index', null];
        }

        if ($user->hasRole('kitchen', 'admin')) {
            $open = fn ($q) => $q->where('status', 'open');
            $waiting = OrderItem::where('status', 'pending')->whereHas('order', $open)->count();
            $cooking = OrderItem::where('status', 'preparing')->whereHas('order', $open)->count();
            $late = OrderItem::whereIn('status', ['pending', 'preparing'])->whereHas('order', $open)
                ->where('created_at', '<=', now()->subMinutes(KitchenController::DELAY_MINUTES))->count();
            if ($user->hasRole('kitchen')) {
                $k[] = ['Waiting to start', $waiting, 'items', 'bi-hourglass-split', 'kitchen.index', null];
                $k[] = ['Cooking now', $cooking, 'items', 'bi-fire', 'kitchen.index', null];
                $k[] = ['Late (over '.KitchenController::DELAY_MINUTES.' min)', $late, 'items', 'bi-alarm', 'kitchen.index', null];
                $k[] = ['Done today', OrderItem::whereIn('status', ['ready', 'served'])->whereDate('updated_at', today())->count(), 'items ready or served', 'bi-check2-circle', 'kitchen.index', null];
            } else {
                $k[] = ['Kitchen queue', $waiting + $cooking, $late ? $late.' late' : 'none late', 'bi-fire', 'kitchen.index', null];
            }
        }

        if ($user->hasRole('storekeeper')) {
            $k[] = ['Low stock', Ingredient::whereColumn('quantity', '<=', 'reorder_level')->count(), 'ingredients to reorder', 'bi-exclamation-triangle', 'stock-alerts.index', null];
            $k[] = ['Out of stock', Ingredient::where('quantity', '<=', 0)->count(), 'dishes may be hidden', 'bi-x-octagon', 'ingredients.index', null];
            $k[] = ['Purchases this month', 'Rs. '.number_format((float) Purchase::whereYear('purchase_date', now()->year)->whereMonth('purchase_date', now()->month)->sum('total')), Purchase::whereMonth('purchase_date', now()->month)->whereYear('purchase_date', now()->year)->count().' purchases', 'bi-cart-check', 'purchases.index', null];
            $k[] = ['Ingredients', Ingredient::count(), 'items tracked', 'bi-box-seam', 'ingredients.index', null];
        }

        return $k;
    }

    /** Things that need someone now. Each: text, icon, link. */
    private function alerts($user): array
    {
        $a = [];
        $add = function (bool $show, string $text, string $icon, string $route) use (&$a) {
            if ($show) {
                $a[] = [$text, $icon, $route];
            }
        };

        if ($user->hasRole('admin', 'cashier')) {
            $d = Delivery::where('status', 'placed')->count();
            $add($d > 0, $d.' online '.str('order')->plural($d).' to dispatch', 'bi-bicycle', 'deliveries.index');
            $r = RoomBooking::where('status', 'booked')->whereDate('check_in', today())->count();
            $add($r > 0, $r.' room '.str('guest')->plural($r).' arriving today', 'bi-box-arrow-in-right', 'room-bookings.board');
            $o = RoomBooking::where('status', 'checked_in')->whereDate('check_out', '<=', today())->count();
            $add($o > 0, $o.' room check-'.str('out')->plural($o).' due', 'bi-box-arrow-right', 'room-bookings.board');
        }
        if ($user->hasRole('admin', 'storekeeper')) {
            $l = Ingredient::whereColumn('quantity', '<=', 'reorder_level')->count();
            $add($l > 0, $l.' '.str('ingredient')->plural($l).' low on stock', 'bi-exclamation-triangle', 'stock-alerts.index');
        }
        if ($user->hasRole('admin')) {
            $m = ContactMessage::where('is_read', false)->count();
            $add($m > 0, $m.' new website '.str('message')->plural($m), 'bi-envelope', 'messages.index');
        }
        if ($user->hasRole('waiter', 'admin')) {
            $ready = OrderItem::where('status', 'ready')->whereHas('order', fn ($q) => $q->where('status', 'open')->where('order_type', '!=', 'delivery'))->count();
            $add($ready > 0, $ready.' '.str('item')->plural($ready).' ready to serve', 'bi-bell', 'orders.index');
        }

        return $a;
    }

    /** Sales for the last 7 days, including days with no sales. */
    private function lastSevenDays(): array
    {
        $start = today()->subDays(6);
        $totals = Bill::where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, SUM(total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $values = [];
        for ($d = $start->copy(); $d->lte(today()); $d->addDay()) {
            $labels[] = $d->format('D d');
            $values[] = (float) ($totals[$d->toDateString()] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
