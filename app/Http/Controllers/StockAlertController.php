<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\StockAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Low-stock alerts (FR-32).
 */
class StockAlertController extends Controller
{
    public function index(): View
    {
        return view('stock-alerts.index', [
            'alerts' => StockAlert::with('ingredient')->where('is_resolved', false)->latest()->get(),
            'lowItems' => Ingredient::whereColumn('quantity', '<=', 'reorder_level')->orderBy('name')->get(),
        ]);
    }

    /** Hide an alert after the storekeeper has seen it (it comes back if stock drops again). */
    public function resolve(StockAlert $stockAlert): RedirectResponse
    {
        $stockAlert->update(['is_resolved' => true]);

        return back()->with('success', 'Alert closed.');
    }
}
