<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\StockAdjustment;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Wastage and stock adjustments with a reason (FR-33).
 */
class StockAdjustmentController extends Controller
{
    /** Reasons that remove stock and reasons that add stock. */
    public const REASONS = [
        'Wastage' => -1,
        'Expired' => -1,
        'Damaged' => -1,
        'Count correction (less)' => -1,
        'Count correction (more)' => 1,
    ];

    public function index(Request $request): View
    {
        $adjustments = StockAdjustment::with('ingredient')
            ->when($request->filled('ingredient'), fn ($q) => $q->where('ingredient_id', $request->ingredient))
            ->latest('adjusted_at')
            ->paginate(25)
            ->withQueryString();

        return view('stock-adjustments.index', [
            'adjustments' => $adjustments,
            'ingredients' => Ingredient::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Ingredient $ingredient): View
    {
        return view('stock-adjustments.create', ['ingredient' => $ingredient, 'reasons' => self::REASONS]);
    }

    public function store(Request $request, Ingredient $ingredient, StockService $stock): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', Rule::in(array_keys(self::REASONS))],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:999999'],
            'note' => ['nullable', 'string', 'max:60'],
        ]);

        $change = self::REASONS[$data['reason']] * (float) $data['quantity'];

        if ($ingredient->quantity + $change < 0) {
            return back()->withInput()->withErrors([
                'quantity' => 'You cannot remove more than the current stock ('.(float) $ingredient->quantity.' '.$ingredient->unit.').',
            ]);
        }

        DB::transaction(function () use ($ingredient, $data, $change, $stock) {
            StockAdjustment::create([
                'ingredient_id' => $ingredient->id,
                'quantity' => $change,
                'reason' => trim($data['reason'].($data['note'] ? ' – '.$data['note'] : '')),
                'adjusted_at' => now(),
            ]);
            $stock->change($ingredient, $change);
        });

        return redirect()->route('ingredients.index')->with('success', 'Stock of '.$ingredient->name.' updated.');
    }
}
