<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Menu categories (FR-06).
 */
class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('menuItems')->orderBy('name')->get();

        return view('categories.index', ['categories' => $categories]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:categories,name'],
        ]);

        Category::create($data);

        return back()->with('success', 'Category added.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('categories', 'name')->ignore($category->id)],
        ]);

        $category->update($data);

        return back()->with('success', 'Category renamed.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        // Do not delete a category that still has menu items
        if ($category->menuItems()->exists()) {
            return back()->with('error', 'This category still has menu items. Move or delete them first.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }
}
