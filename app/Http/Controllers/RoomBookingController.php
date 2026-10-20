<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomBooking;
use App\Services\RoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Room bookings: room board, availability, booking, check-in, payments and check-out.
 */
class RoomBookingController extends Controller
{
    public function __construct(private RoomService $rooms)
    {
    }

    /** Today's picture of every room. */
    public function board(Request $request): View
    {
        $request->validate(['date' => ['nullable', 'date']]);
        $day = $request->filled('date') ? Carbon::parse($request->date) : today();
        $d = $day->toDateString();

        $rooms = Room::with(['type', 'bookings' => fn ($q) => $q->holding()
            ->where(fn ($q2) => $q2->where('status', 'checked_in')->orWhere(fn ($q3) => $q3->where('check_in', '<=', $d)->where('check_out', '>', $d)))])
            ->orderBy('room_no')->get();

        $tiles = $rooms->map(function (Room $room) use ($day) {
            [$status, $booking] = $this->rooms->dayStatus($room, $room->bookings, $day);

            return ['room' => $room, 'status' => $status, 'booking' => $booking];
        });

        return view('room-bookings.board', [
            'tiles' => $tiles,
            'counts' => $tiles->countBy('status'),
            'day' => $day,
            'arrivals' => RoomBooking::with('room')->where('status', 'booked')->whereDate('check_in', $d)->get(),
            'departures' => RoomBooking::with('room')->where('status', 'checked_in')->whereDate('check_out', '<=', $d)->get(),
        ]);
    }

    public function index(Request $request): View
    {
        $bookings = RoomBooking::with('room.type')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status),
                fn ($q) => $q->whereIn('status', ['booked', 'checked_in']))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%'.$request->search.'%';
                $q->where(fn ($q2) => $q2->where('guest_name', 'like', $s)->orWhere('guest_phone', 'like', $s)->orWhere('booking_no', 'like', $s));
            })
            ->orderBy('check_in')
            ->paginate(25)
            ->withQueryString();

        return view('room-bookings.index', ['bookings' => $bookings, 'statuses' => RoomBooking::STATUSES]);
    }

    /** Which rooms are free for the chosen dates? */
    public function availability(Request $request): View
    {
        $data = $request->validate([
            'check_in' => ['nullable', 'date', 'after_or_equal:today'],
            'check_out' => ['nullable', 'date', 'after:check_in'],
            'guests' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $in = $data['check_in'] ?? today()->toDateString();
        $out = $data['check_out'] ?? today()->addDay()->toDateString();
        $guests = (int) ($data['guests'] ?? 1);

        return view('room-bookings.availability', [
            'checkIn' => $in,
            'checkOut' => $out,
            'guests' => $guests,
            'nights' => (int) Carbon::parse($in)->diffInDays(Carbon::parse($out)),
            'rooms' => $this->rooms->availableRooms($in, $out, $guests)->groupBy(fn ($r) => $r->type->name),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $data = $request->validate([
            'room' => ['required', 'exists:rooms,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests' => ['nullable', 'integer', 'min:1'],
        ]);

        $room = Room::with('type')->findOrFail($data['room']);
        if (! $this->rooms->isAvailable($room, $data['check_in'], $data['check_out'])) {
            return redirect()->route('room-bookings.availability', $request->only('check_in', 'check_out', 'guests'))
                ->with('error', 'Room '.$room->room_no.' was just booked. Please choose another room.');
        }

        return view('room-bookings.create', [
            'room' => $room,
            'checkIn' => $data['check_in'],
            'checkOut' => $data['check_out'],
            'guests' => (int) ($data['guests'] ?? 1),
            'quote' => $this->rooms->quote($room, $data['check_in'], $data['check_out']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guest_name' => ['required', 'string', 'max:100'],
            'guest_phone' => ['required', 'regex:/^[0-9 +\-]{9,20}$/'],
            'guest_email' => ['nullable', 'email', 'max:100'],
            'guest_nic' => ['nullable', 'string', 'max:20'],
            'adults' => ['required', 'integer', 'min:1', 'max:20'],
            'children' => ['nullable', 'integer', 'min:0', 'max:20'],
            'source' => ['required', 'in:walk_in,phone'],
            'notes' => ['nullable', 'string', 'max:255'],
            'advance' => ['nullable', 'numeric', 'min:0'],
        ] + $this->paymentRules('advance'));
        $data['children'] = (int) ($data['children'] ?? 0);

        $booking = DB::transaction(function () use ($data, $request) {
            // Lock the room so two receptionists cannot book it at the same time
            $room = Room::with('type')->whereKey($data['room_id'])->lockForUpdate()->firstOrFail();

            if (! $this->rooms->isAvailable($room, $data['check_in'], $data['check_out'])) {
                throw ValidationException::withMessages(['room_id' => 'Room '.$room->room_no.' is not free for these dates.']);
            }
            $guests = $data['adults'] + $data['children'];
            if ($guests > $room->type->capacity) {
                throw ValidationException::withMessages(['adults' => $room->type->name.' takes at most '.$room->type->capacity.' guest(s).']);
            }

            $quote = $this->rooms->quote($room, $data['check_in'], $data['check_out']);
            $advance = round((float) ($data['advance'] ?? 0), 2);
            if ($advance > $quote['room_total']) {
                throw ValidationException::withMessages(['advance' => 'The advance cannot be more than the room total.']);
            }

            $booking = RoomBooking::create(collect($data)->except(['advance', 'method', 'card_type', 'card_last4', 'approval_code'])->all() + $quote + [
                'booking_no' => $this->rooms->nextBookingNumber(),
                'user_id' => $request->user()->id,
                'total' => $quote['room_total'],
                'amount_paid' => 0,
                'status' => 'booked',
            ]);

            if ($advance > 0) {
                $this->recordPayment($booking, $data, $advance, 'Advance', $request->user()->id);
            }

            return $booking;
        });

        return redirect()->route('room-bookings.show', $booking)->with('success', 'Booking '.$booking->booking_no.' saved for room '.$booking->room->room_no.'.');
    }

    public function show(RoomBooking $roomBooking): View
    {
        return view('room-bookings.show', ['booking' => $roomBooking->load(['room.type', 'payments.user', 'staff', 'customer'])]);
    }

    public function checkIn(Request $request, RoomBooking $roomBooking): RedirectResponse
    {
        $data = $request->validate(['guest_nic' => ['required', 'string', 'max:20']], [
            'guest_nic.required' => 'Enter the guest\'s NIC or passport number at check-in.',
        ]);

        if ($roomBooking->status !== 'booked') {
            return back()->with('error', 'This booking cannot be checked in.');
        }
        if ($roomBooking->check_in->isFuture()) {
            return back()->with('error', 'Check-in date is '.$roomBooking->check_in->format('d M Y').'. Change the booking for an early arrival.');
        }

        $roomBooking->update($data + ['status' => 'checked_in', 'checked_in_at' => now()]);

        return back()->with('success', $roomBooking->guest_name.' checked in to room '.$roomBooking->room->room_no.'.');
    }

    /** Advance or part payment while the booking is open. */
    public function addPayment(Request $request, RoomBooking $roomBooking): RedirectResponse
    {
        $data = $request->validate(['amount' => ['required', 'numeric', 'gt:0']] + $this->paymentRules('amount'));

        if (! in_array($roomBooking->status, ['booked', 'checked_in'])) {
            return back()->with('error', 'This booking is closed.');
        }
        if ($data['amount'] > $roomBooking->balance()) {
            return back()->withErrors(['amount' => 'The balance is only Rs. '.number_format($roomBooking->balance(), 2).'.']);
        }

        DB::transaction(fn () => $this->recordPayment($roomBooking, $data, round((float) $data['amount'], 2), 'Part payment', $request->user()->id));

        return back()->with('success', 'Payment saved.');
    }

    /** Final bill: room + extras − discount − paid. */
    public function checkOut(Request $request, RoomBooking $roomBooking): RedirectResponse
    {
        $data = $request->validate([
            'extra_charges' => ['nullable', 'numeric', 'min:0'],
            'extra_notes' => ['nullable', 'string', 'max:255'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'cash_received' => ['nullable', 'numeric', 'min:0'],
        ] + $this->paymentRules('balance'));

        if ($roomBooking->status !== 'checked_in') {
            return back()->with('error', 'Only checked-in guests can be checked out.');
        }

        $discount = (float) ($data['discount'] ?? 0);
        if ($discount > 0 && ! $request->user()->hasRole('admin')) {
            return back()->withInput()->withErrors(['discount' => 'Only the Admin can give a room discount.']);
        }

        $extra = round((float) ($data['extra_charges'] ?? 0), 2);
        $total = round((float) $roomBooking->room_total + $extra - $discount, 2);
        if ($total < (float) $roomBooking->amount_paid) {
            return back()->withInput()->withErrors(['discount' => 'The discount makes the total less than the amount already paid.']);
        }
        $balance = round($total - (float) $roomBooking->amount_paid, 2);

        if ($balance > 0 && $data['method'] === 'cash' && (float) ($data['cash_received'] ?? 0) < $balance) {
            return back()->withInput()->withErrors(['cash_received' => 'Cash received is less than the balance (Rs. '.number_format($balance, 2).').']);
        }

        DB::transaction(function () use ($roomBooking, $data, $extra, $discount, $total, $balance, $request) {
            $roomBooking->update([
                'extra_charges' => $extra,
                'extra_notes' => $data['extra_notes'] ?? null,
                'discount' => $discount,
                'total' => $total,
            ]);
            if ($balance > 0) {
                $this->recordPayment($roomBooking, $data, $balance, 'Final payment', $request->user()->id);
            }
            $roomBooking->update(['status' => 'checked_out', 'checked_out_at' => now()]);
        });

        $change = $data['method'] === 'cash' && $balance > 0 ? round((float) $data['cash_received'] - $balance, 2) : 0;

        return redirect()->route('room-bookings.invoice', $roomBooking)
            ->with('success', 'Checked out.'.($change > 0 ? ' Give back change: Rs. '.number_format($change, 2) : ''));
    }

    public function cancel(Request $request, RoomBooking $roomBooking): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:cancelled,no_show'],
            'reason' => ['required', 'string', 'max:200'],
        ]);

        if ($roomBooking->status !== 'booked') {
            return back()->with('error', 'Only bookings that are not checked in can be cancelled.');
        }

        $roomBooking->update([
            'status' => $data['status'],
            'notes' => trim(($roomBooking->notes ? $roomBooking->notes.' | ' : '').RoomBooking::STATUSES[$data['status']].': '.$data['reason']),
        ]);

        $msg = 'Booking marked as '.RoomBooking::STATUSES[$data['status']].'.';
        if ($roomBooking->amount_paid > 0) {
            $msg .= ' Rs. '.number_format($roomBooking->amount_paid, 2).' was paid: refund it or keep it as per hotel policy.';
        }

        return redirect()->route('room-bookings.index')->with('success', $msg);
    }

    public function invoice(RoomBooking $roomBooking): View
    {
        return view('room-bookings.invoice', ['booking' => $roomBooking->load(['room.type', 'payments'])]);
    }

    // ---------- helpers ----------

    /** Validation for a payment whose amount field is $field. */
    private function paymentRules(string $field): array
    {
        return [
            'method' => ['nullable', 'required_with:'.$field, 'in:cash,card'],
            'card_type' => ['nullable', 'required_if:method,card', 'in:'.implode(',', array_keys(Payment::CARD_TYPES))],
            'card_last4' => ['nullable', 'required_if:method,card', 'digits:4'],
            'approval_code' => ['nullable', 'required_if:method,card', 'alpha_num', 'max:12'],
        ];
    }

    private function recordPayment(RoomBooking $booking, array $data, float $amount, string $note, int $userId): void
    {
        $isCard = ($data['method'] ?? 'cash') === 'card';

        $booking->payments()->create([
            'method' => $isCard ? 'card' : 'cash',
            'amount' => $amount,
            'card_type' => $isCard ? $data['card_type'] : null,
            'card_last4' => $isCard ? $data['card_last4'] : null,
            'approval_code' => $isCard ? strtoupper($data['approval_code']) : null,
            'note' => $note,
            'user_id' => $userId,
            'paid_at' => now(),
        ]);

        $booking->increment('amount_paid', $amount);
    }
}
