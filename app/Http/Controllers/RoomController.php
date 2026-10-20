<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Rooms 101–115 (Admin).
 */
class RoomController extends Controller
{
    public function index(): View
    {
        return view('rooms.index', ['rooms' => Room::with('type')->withCount('bookings')->orderBy('room_no')->get()]);
    }

    public function create(): View
    {
        return view('rooms.form', ['room' => new Room(['status' => 'available']), 'types' => RoomType::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Room::create($request->validate($this->rules()));

        return redirect()->route('rooms.index')->with('success', 'Room added.');
    }

    public function edit(Room $room): View
    {
        return view('rooms.form', ['room' => $room, 'types' => RoomType::orderBy('name')->get()]);
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $room->update($request->validate($this->rules($room)));

        return redirect()->route('rooms.index')->with('success', 'Room '.$room->room_no.' updated.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        if ($room->bookings()->exists()) {
            return back()->with('error', 'This room has bookings. Set it to Maintenance instead of deleting it.');
        }

        $room->delete();

        return back()->with('success', 'Room deleted.');
    }

    private function rules(?Room $room = null): array
    {
        return [
            'room_no' => ['required', 'string', 'max:10', Rule::unique('rooms', 'room_no')->ignore($room?->id)],
            'room_type_id' => ['required', 'exists:room_types,id'],
            'status' => ['required', 'in:available,maintenance'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
