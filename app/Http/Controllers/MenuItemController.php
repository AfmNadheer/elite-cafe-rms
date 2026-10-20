<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Menu items with price, photo and availability (FR-07, FR-08).
 */
class MenuItemController extends Controller
{
    public function index(Request $request): View
    {
        $items = MenuItem::with('category')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->search.'%'))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->category))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('menu-items.index', [
            'items' => $items,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('menu-items.form', [
            'item' => new MenuItem(['is_available' => true]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['is_available'] = $request->boolean('is_available');

        if ($request->hasFile('photo')) {
            // Saved in storage/app/public/menu, shown through public/storage
            $data['photo'] = $request->file('photo')->store('menu', 'public');
        }

        MenuItem::create($data);

        return redirect()->route('menu-items.index')->with('success', 'Menu item added.');
    }

    public function edit(MenuItem $menuItem): View
    {
        return view('menu-items.form', [
            'item' => $menuItem,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['is_available'] = $request->boolean('is_available');

        if ($request->hasFile('photo')) {
            if ($menuItem->photo) {
                Storage::disk('public')->delete($menuItem->photo);   // remove the old photo
            }
            $data['photo'] = $request->file('photo')->store('menu', 'public');
        }

        $menuItem->update($data);

        return redirect()->route('menu-items.index')->with('success', 'Menu item updated.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        // Items already sold must stay for old bills and reports
        if (OrderItem::where('menu_item_id', $menuItem->id)->exists()) {
            return back()->with('error', 'This item appears in past orders. Mark it as unavailable instead of deleting it.');
        }

        if ($menuItem->photo) {
            Storage::disk('public')->delete($menuItem->photo);
        }
        $menuItem->delete();

        return back()->with('success', 'Menu item deleted.');
    }

    /** Quick on/off switch from the list (FR-08 manual override). */
    public function toggleAvailability(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update(['is_available' => ! $menuItem->is_available]);

        return back()->with('success', $menuItem->name.' is now '.($menuItem->is_available ? 'available' : 'unavailable').'.');
    }

    private function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'description' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],   // max 2 MB
        ];
    }
}
