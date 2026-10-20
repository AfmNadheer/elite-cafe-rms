<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Suppliers (FR-34).
 */
class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $suppliers = Supplier::withCount('purchases')
            ->with('purchases.items.ingredient:id,name')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->search.'%'))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('suppliers.index', ['suppliers' => $suppliers]);
    }

    public function create(): View
    {
        return view('suppliers.form', ['supplier' => new Supplier()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Supplier::create($request->validate($this->rules()));

        return redirect()->route('suppliers.index')->with('success', 'Supplier added.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.form', ['supplier' => $supplier]);
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validate($this->rules()));

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->purchases()->exists()) {
            return back()->with('error', 'This supplier has purchases and cannot be deleted.');
        }

        $supplier->delete();

        return back()->with('success', 'Supplier deleted.');
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9 +\-]{9,20}$/'],
        ];
    }
}
