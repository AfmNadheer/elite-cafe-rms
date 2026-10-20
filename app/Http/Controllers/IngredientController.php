<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Ingredients and stock levels (FR-29).
 */
class IngredientController extends Controller
{
    public const UNITS = ['kg' => 'Kilogram (kg)', 'g' => 'Gram (g)', 'l' => 'Litre (l)', 'ml' => 'Millilitre (ml)', 'pcs' => 'Pieces (pcs)'];

    public function index(Request $request): View
    {
        $ingredients = Ingredient::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->search.'%'))
            ->when($request->boolean('low'), fn ($q) => $q->whereColumn('quantity', '<=', 'reorder_level'))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('ingredients.index', [
            'ingredients' => $ingredients,
            'lowCount' => Ingredient::whereColumn('quantity', '<=', 'reorder_level')->count(),
        ]);
    }

    public function create(): View
    {
        return view('ingredients.form', ['ingredient' => new Ingredient(['unit' => 'kg']), 'units' => self::UNITS]);
    }

    public function store(Request $request, StockService $stock): RedirectResponse
    {
        $data = $request->validate($this->rules() + [
            'quantity' => ['required', 'numeric', 'min:0', 'max:999999'],
        ]);

        $ingredient = Ingredient::create($data);
        $stock->checkLevel($ingredient);

        return redirect()->route('ingredients.index')->with('success', 'Ingredient added.');
    }

    public function edit(Ingredient $ingredient): View
    {
        return view('ingredients.form', ['ingredient' => $ingredient, 'units' => self::UNITS]);
    }

    /** Quantity is not edited here: use a purchase or a stock adjustment, so every change is recorded. */
    public function update(Request $request, Ingredient $ingredient, StockService $stock): RedirectResponse
    {
        $ingredient->update($request->validate($this->rules($ingredient)));
        $stock->checkLevel($ingredient);

        return redirect()->route('ingredients.index')->with('success', 'Ingredient updated.');
    }

    public function destroy(Ingredient $ingredient): RedirectResponse
    {
        if ($ingredient->recipeItems()->exists() || $ingredient->purchaseItems()->exists()) {
            return back()->with('error', 'This ingredient is used in recipes or purchases and cannot be deleted.');
        }

        $ingredient->delete();

        return back()->with('success', 'Ingredient deleted.');
    }

    private function rules(?Ingredient $ingredient = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('ingredients', 'name')->ignore($ingredient?->id)],
            'unit' => ['required', Rule::in(array_keys(self::UNITS))],
            'reorder_level' => ['required', 'numeric', 'min:0', 'max:999999'],
        ];
    }
}
