<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Home page after login. Content depends on the user's role.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'tables' => RestaurantTable::count(),
            'free_tables' => RestaurantTable::where('status', 'free')->count(),
            'menu_items' => MenuItem::count(),
            'categories' => Category::count(),
            'staff' => User::where('is_active', true)->count(),
            'today_sales' => (float) Bill::whereDate('created_at', today())->sum('total'),
            'today_bills' => Bill::whereDate('created_at', today())->count(),
            'open_orders' => \App\Models\Order::where('status', 'open')->count(),
            'low_stock' => Ingredient::whereColumn('quantity', '<=', 'reorder_level')->count(),
        ];

        return view('dashboard', [
            'user' => $request->user(),
            'stats' => $stats,
        ]);
    }
}
