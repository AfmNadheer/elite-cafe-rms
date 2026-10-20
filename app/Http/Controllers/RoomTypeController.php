<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Room types and their prices (Admin).
 */
class RoomTypeController extends Controller
{
    public function index(): View
    {
        return view('room-types.index', ['types' => RoomType::withCount('rooms')->orderBy('price_per_night')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        RoomType::create($request->validate($this->rules()));

        return back()->with('success', 'Room type added.');
    }

    public function update(Request $request, RoomType $roomType): RedirectResponse
    {
        $roomType->update($request->validate($this->rules($roomType)));

        return back()->with('success', $roomType->name.' updated. New bookings use the new price.');
    }

    public function destroy(RoomType $roomType): RedirectResponse
    {
        if ($roomType->rooms()->exists()) {
            return back()->with('error', 'Move or delete the rooms of this type first.');
        }

        $roomType->delete();

        return back()->with('success', 'Room type deleted.');
    }

    private function rules(?RoomType $type = null): array
    {
        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('room_types', 'name')->ignore($type?->id)],
            'price_per_night' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
