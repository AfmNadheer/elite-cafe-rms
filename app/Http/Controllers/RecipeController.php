<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Recipes: which ingredients each menu item uses (FR-30).
 */
class RecipeController extends Controller
{
    public function index(): View
    {
        $categories = Category::with(['menuItems' => fn ($q) => $q->withCount('ingredients')->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->filter(fn ($c) => $c->menuItems->isNotEmpty());

        return view('recipes.index', ['categories' => $categories]);
    }

    public function edit(MenuItem $menuItem): View
    {
        return view('recipes.edit', [
            'menuItem' => $menuItem->load('ingredients'),
            'ingredients' => Ingredient::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['nullable', 'array'],
            'items.*.ingredient_id' => ['required', 'distinct', 'exists:ingredients,id'],
            'items.*.quantity_required' => ['required', 'numeric', 'gt:0', 'max:9999'],
        ], [
            'items.*.ingredient_id.distinct' => 'The same ingredient is listed twice.',
            'items.*.ingredient_id.required' => 'Select an ingredient in every row.',
            'items.*.quantity_required.gt' => 'Quantity must be more than 0.',
        ]);

        // Replace the whole recipe with the rows on the form
        $menuItem->ingredients()->sync(
            collect($data['items'] ?? [])->mapWithKeys(fn ($row) => [
                $row['ingredient_id'] => ['quantity_required' => $row['quantity_required']],
            ])->all()
        );

        return redirect()->route('recipes.index')->with('success', 'Recipe for '.$menuItem->name.' saved.');
    }
}
