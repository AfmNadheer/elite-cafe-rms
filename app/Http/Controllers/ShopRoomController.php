<?php

namespace App\Http\Controllers;

use App\Models\OnlinePayment;
use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\RoomType;
use App\Services\PayHereService;
use App\Services\RoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Customers book a room type online and pay with PayHere.
 * The booking is created (and a room number given) only after the payment is confirmed.
 */
class ShopRoomController extends Controller
{
    public function __construct(private RoomService $rooms, private PayHereService $payhere)
    {
    }

    /** Search page: room types with how many rooms are free for the dates. */
    public function index(Request $request): View
    {
        $data = $request->validate([
            'check_in' => ['nullable', 'date', 'after_or_equal:today'],
            'check_out' => ['nullable', 'date', 'after:check_in'],
            'guests' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $in = $data['check_in'] ?? today()->toDateString();
        $out = $data['check_out'] ?? today()->addDay()->toDateString();
        $guests = (int) ($data['guests'] ?? 2);
        $nights = (int) Carbon::parse($in)->diffInDays(Carbon::parse($out));

        $free = $this->rooms->availableRooms($in, $out)->countBy('room_type_id');

        return view('shop.rooms.index', [
            'types' => RoomType::orderBy('price_per_night')->get(),
            'free' => $free,
            'checkIn' => $in,
            'checkOut' => $out,
            'guests' => $guests,
            'nights' => $nights,
            'onlineReady' => $this->payhere->isConfigured(),
        ]);
    }

    public function book(Request $request): View|RedirectResponse
    {
        $data = $this->validatedSearch($request);
        $type = RoomType::findOrFail($data['type']);

        if ($error = $this->problem($type, $data)) {
            return redirect()->route('shop.rooms.index', $request->only('check_in', 'check_out', 'guests'))->with('error', $error);
        }

        return view('shop.rooms.book', [
            'type' => $type,
            'customer' => $request->user('customer'),
            'checkIn' => $data['check_in'],
            'checkOut' => $data['check_out'],
            'guests' => (int) $data['guests'],
            'quote' => $this->quote($type, $data),
        ]);
    }

    /** Hold nothing yet: send the customer to PayHere; the booking is made when the payment succeeds. */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedSearch($request) + $request->validate([
            'guest_name' => ['required', 'string', 'max:100'],
            'guest_phone' => ['required', 'regex:/^[0-9 +\-]{9,20}$/'],
            'adults' => ['required', 'integer', 'min:1', 'max:20'],
            'children' => ['nullable', 'integer', 'min:0', 'max:20'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);
        $data['children'] = (int) ($data['children'] ?? 0);

        $type = RoomType::findOrFail($data['type']);
        if ($data['adults'] + $data['children'] > $type->capacity) {
            return back()->withInput()->withErrors(['adults' => $type->name.' takes at most '.$type->capacity.' guest(s).']);
        }
        if ($error = $this->problem($type, $data)) {
            return redirect()->route('shop.rooms.index')->with('error', $error);
        }
        if (! $this->payhere->isConfigured()) {
            return back()->withInput()->with('error', 'Online payment is not available right now. Please call the hotel to book.');
        }

        $customer = $request->user('customer');
        $quote = $this->quote($type, $data);

        $payment = OnlinePayment::create([
            'order_ref' => OnlinePayment::newReference('R', $customer->id),
            'payable_type' => 'RoomRequest',
            'payable_id' => $customer->id,
            'amount' => $quote['room_total'],
            'currency' => config('payhere.currency'),
            'data' => $quote + [
                'customer_id' => $customer->id,
                'room_type_id' => $type->id,
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'guest_name' => $data['guest_name'],
                'guest_phone' => $data['guest_phone'],
                'guest_email' => $customer->email,
                'adults' => $data['adults'],
                'children' => $data['children'],
                'notes' => $data['notes'] ?? null,
            ],
            'customer_name' => $data['guest_name'],
            'customer_phone' => $data['guest_phone'],
            'customer_email' => $customer->email,
        ]);

        return redirect()->route('shop.pay', $payment);
    }

    public function myBookings(Request $request): View
    {
        return view('shop.rooms.my-bookings', [
            'bookings' => RoomBooking::with('room.type')->where('customer_id', $request->user('customer')->id)->latest()->paginate(10),
        ]);
    }

    public function show(Request $request, RoomBooking $roomBooking): View
    {
        abort_unless((int) $roomBooking->customer_id === (int) $request->user('customer')->id, 404);

        return view('shop.rooms.booking', ['booking' => $roomBooking->load(['room.type', 'payments'])]);
    }

    // ---------- helpers ----------

    private function validatedSearch(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'exists:room_types,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests' => ['required', 'integer', 'min:1', 'max:20'],
        ]);
    }

    private function problem(RoomType $type, array $data): ?string
    {
        if ((int) $data['guests'] > $type->capacity) {
            return $type->name.' takes at most '.$type->capacity.' guest(s).';
        }
        if (! $this->rooms->freeRoomOfType($type->id, $data['check_in'], $data['check_out'])) {
            return 'Sorry, no '.$type->name.' is free for these dates.';
        }

        return null;
    }

    private function quote(RoomType $type, array $data): array
    {
        $room = Room::with('type')->where('room_type_id', $type->id)->firstOrFail();

        return $this->rooms->quote($room, $data['check_in'], $data['check_out']);
    }
}
