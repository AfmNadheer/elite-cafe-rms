<?php

namespace App\Http\Controllers;

use App\Models\RestaurantTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Restaurant tables (FR-10) and the live table map (FR-11).
 */
class TableController extends Controller
{
    public const STATUSES = ['free' => 'Free', 'reserved' => 'Reserved', 'occupied' => 'Occupied'];

    /** Live map of all tables, grouped by section. */
    public function map(): View
    {
        $tables = RestaurantTable::orderBy('section')->orderBy('table_no')->get();

        return view('tables.map', [
            'sections' => $tables->groupBy(fn ($t) => $t->section ?: 'Other'),
            'counts' => $tables->countBy('status'),
            'total' => $tables->count(),
        ]);
    }

    public function index(): View
    {
        return view('tables.index', [
            'tables' => RestaurantTable::orderBy('table_no')->get(),
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('tables.form', [
            'table' => new RestaurantTable(['capacity' => 4, 'status' => 'free']),
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        RestaurantTable::create($request->validate($this->rules()));

        return redirect()->route('tables.index')->with('success', 'Table added.');
    }

    public function edit(RestaurantTable $table): View
    {
        return view('tables.form', ['table' => $table, 'statuses' => self::STATUSES]);
    }

    public function update(Request $request, RestaurantTable $table): RedirectResponse
    {
        $table->update($request->validate($this->rules($table)));

        return redirect()->route('tables.index')->with('success', 'Table updated.');
    }

    public function destroy(RestaurantTable $table): RedirectResponse
    {
        if ($table->orders()->exists() || $table->reservations()->exists()) {
            return back()->with('error', 'This table has orders or reservations and cannot be deleted.');
        }

        $table->delete();

        return back()->with('success', 'Table deleted.');
    }

    private function rules(?RestaurantTable $table = null): array
    {
        return [
            'table_no' => ['required', 'string', 'max:10', Rule::unique('restaurant_tables', 'table_no')->ignore($table?->id)],
            'section' => ['nullable', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:30'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ];
    }
}
