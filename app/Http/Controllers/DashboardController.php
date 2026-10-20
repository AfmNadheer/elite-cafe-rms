<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Home page after login. Content depends on the user's role (FR-37).
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'tables' => RestaurantTable::count(),
            'occupied_tables' => RestaurantTable::where('status', 'occupied')->count(),
            'today_sales' => (float) Bill::whereDate('created_at', today())->sum('total'),
            'today_bills' => Bill::whereDate('created_at', today())->count(),
            'today_orders' => Order::whereDate('created_at', today())->where('status', '!=', 'cancelled')->count(),
            'open_orders' => Order::where('status', 'open')->count(),
            'low_stock' => Ingredient::whereColumn('quantity', '<=', 'reorder_level')->count(),
            'today_reservations' => Reservation::whereDate('reserved_at', today())->where('status', 'booked')->count(),
        ];

        return view('dashboard', [
            'user' => $request->user(),
            'stats' => $stats,
            'chart' => $request->user()->hasRole('admin') ? $this->lastSevenDays() : null,
        ]);
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
