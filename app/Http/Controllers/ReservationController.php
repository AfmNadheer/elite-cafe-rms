<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\RestaurantTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Table reservations (FR-12).
 */
class ReservationController extends Controller
{
    /** A booking holds the table for this many minutes before and after its time. */
    public const HOLD_MINUTES = 120;

    public const STATUSES = ['booked' => 'Booked', 'seated' => 'Seated', 'cancelled' => 'Cancelled', 'no_show' => 'No-show'];

    public function index(Request $request): View
    {
        $request->validate(['date' => ['nullable', 'date']]);
        $date = $request->filled('date') ? Carbon::parse($request->date) : today();

        $reservations = Reservation::with('table')
            ->whereDate('reserved_at', $date)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderBy('reserved_at')
            ->get();

        return view('reservations.index', [
            'reservations' => $reservations,
            'date' => $date,
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('reservations.form', [
            'reservation' => new Reservation(['party_size' => 2, 'reserved_at' => now()->addHour()->startOfHour()]),
            'tables' => RestaurantTable::orderBy('table_no')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Reservation::create($data + ['status' => 'booked']);

        return redirect()->route('reservations.index', ['date' => Carbon::parse($data['reserved_at'])->toDateString()])
            ->with('success', 'Reservation saved.');
    }

    public function edit(Reservation $reservation): View|RedirectResponse
    {
        if ($reservation->status !== 'booked') {
            return back()->with('error', 'Only booked reservations can be changed.');
        }

        return view('reservations.form', [
            'reservation' => $reservation,
            'tables' => RestaurantTable::orderBy('table_no')->get(),
        ]);
    }

    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        if ($reservation->status !== 'booked') {
            return back()->with('error', 'Only booked reservations can be changed.');
        }

        $data = $this->validated($request, $reservation);
        $reservation->update($data);

        return redirect()->route('reservations.index', ['date' => $reservation->reserved_at->toDateString()])
            ->with('success', 'Reservation updated.');
    }

    /** Seat, cancel or mark no-show. */
    public function status(Request $request, Reservation $reservation): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['seated', 'cancelled', 'no_show'])]]);

        if ($reservation->status !== 'booked') {
            return back()->with('error', 'This reservation has already been updated.');
        }

        $reservation->update($data);

        // Guest has arrived: waiter/admin goes straight to the order screen for that table
        if ($data['status'] === 'seated' && $request->user()->hasRole('admin', 'waiter')) {
            return redirect()->route('orders.create', ['table' => $reservation->table_id])
                ->with('success', $reservation->customer_name.' is seated. Take the order.');
        }

        return back()->with('success', 'Reservation marked as '.self::STATUSES[$data['status']].'.');
    }

    private function validated(Request $request, ?Reservation $reservation = null): array
    {
        $data = $request->validate([
            'table_id' => ['required', 'exists:restaurant_tables,id'],
            'customer_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9 +\-]{9,20}$/'],
            'reserved_at' => ['required', 'date', 'after:now'],
            'party_size' => ['required', 'integer', 'min:1', 'max:50'],
        ], [
            'reserved_at.after' => 'The reservation time must be in the future.',
        ]);

        $table = RestaurantTable::findOrFail($data['table_id']);
        $time = Carbon::parse($data['reserved_at']);

        if ($data['party_size'] > $table->capacity) {
            throw ValidationException::withMessages([
                'party_size' => "{$table->table_no} has only {$table->capacity} seats.",
            ]);
        }

        // No double booking: another booking on the same table within the hold time
        $clash = Reservation::where('table_id', $table->id)
            ->where('status', 'booked')
            ->when($reservation, fn ($q) => $q->where('id', '!=', $reservation->id))
            ->whereBetween('reserved_at', [
                $time->copy()->subMinutes(self::HOLD_MINUTES),
                $time->copy()->addMinutes(self::HOLD_MINUTES),
            ])
            ->first();

        if ($clash) {
            throw ValidationException::withMessages([
                'reserved_at' => "{$table->table_no} is already booked at {$clash->reserved_at->format('h:i A')} for {$clash->customer_name}.",
            ]);
        }

        return $data;
    }
}
