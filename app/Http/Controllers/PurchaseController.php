<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Purchases from suppliers. Saving a purchase increases stock (FR-35).
 */
class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $purchases = Purchase::with('supplier')->withCount('items')
            ->when($request->filled('supplier'), fn ($q) => $q->where('supplier_id', $request->supplier))
            ->when($request->filled('month'), fn ($q) => $q->where('purchase_date', 'like', $request->month.'%'))
            ->orderByDesc('purchase_date')->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('purchases.index', [
            'purchases' => $purchases,
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $suppliers = Supplier::orderBy('name')->get();
        $ingredients = Ingredient::orderBy('name')->get();

        if ($suppliers->isEmpty() || $ingredients->isEmpty()) {
            return redirect()->route('purchases.index')->with('error', 'Add at least one supplier and one ingredient first.');
        }

        return view('purchases.create', ['suppliers' => $suppliers, 'ingredients' => $ingredients]);
    }

    public function store(Request $request, StockService $stock): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date', 'before_or_equal:today'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'distinct', 'exists:ingredients,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:999999'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0', 'max:9999999'],
        ], [
            'items.*.ingredient_id.distinct' => 'The same ingredient is listed twice.',
            'items.*.ingredient_id.required' => 'Select an ingredient in every row.',
            'items.*.quantity.gt' => 'Quantity must be more than 0.',
        ]);

        $purchase = DB::transaction(function () use ($data, $stock) {
            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'total' => 0,
            ]);

            $total = 0;
            foreach ($data['items'] as $line) {
                $purchase->items()->create($line);
                $total += $line['quantity'] * $line['unit_cost'];

                // Stock goes up automatically
                $stock->change(Ingredient::findOrFail($line['ingredient_id']), (float) $line['quantity']);
            }

            $purchase->update(['total' => round($total, 2)]);

            return $purchase;
        });

        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase saved and stock updated.');
    }

    public function show(Purchase $purchase): View
    {
        return view('purchases.show', ['purchase' => $purchase->load(['supplier', 'items.ingredient'])]);
    }
}
