<?php

namespace App\Services;

use App\Models\OnlinePayment;
use App\Models\Room;
use App\Models\RoomBooking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Room availability, prices and booking numbers.
 */
class RoomService
{
    /** Rooms free for every night from $in to $out (rooms under maintenance are left out). */
    public function availableRooms(string $in, string $out, int $guests = 1, ?int $ignoreBookingId = null): Collection
    {
        return Room::with('type')
            ->where('status', 'available')
            ->whereHas('type', fn ($q) => $q->where('capacity', '>=', $guests))
            ->whereDoesntHave('bookings', fn ($q) => $q->holding()->overlapping($in, $out)
                ->when($ignoreBookingId, fn ($q2) => $q2->where('id', '!=', $ignoreBookingId)))
            ->orderBy('room_no')
            ->get();
    }

    public function isAvailable(Room $room, string $in, string $out, ?int $ignoreBookingId = null): bool
    {
        return $room->status === 'available'
            && ! $room->bookings()->holding()->overlapping($in, $out)
                ->when($ignoreBookingId, fn ($q) => $q->where('id', '!=', $ignoreBookingId))
                ->exists();
    }

    /** Number of nights and the room price. */
    public function quote(Room $room, string $in, string $out): array
    {
        $nights = (int) Carbon::parse($in)->diffInDays(Carbon::parse($out));

        if ($nights < 1) {
            throw ValidationException::withMessages(['check_out' => 'Check-out must be at least one day after check-in.']);
        }
        if ($nights > config('rooms.max_nights')) {
            throw ValidationException::withMessages(['check_out' => 'A booking can be at most '.config('rooms.max_nights').' nights.']);
        }

        $price = (float) $room->type->price_per_night;

        return ['nights' => $nights, 'price_per_night' => $price, 'room_total' => round($price * $nights, 2)];
    }

    /** RB261021-0001 style number. Call inside a transaction. */
    public function nextBookingNumber(): string
    {
        $prefix = 'RB'.now()->format('ymd').'-';
        $last = RoomBooking::where('booking_no', 'like', $prefix.'%')->lockForUpdate()->orderByDesc('booking_no')->value('booking_no');

        return $prefix.str_pad((string) ($last ? (int) substr($last, -4) + 1 : 1), 4, '0', STR_PAD_LEFT);
    }

    /** What a room looks like on a given day, for the room board. */
    public function dayStatus(Room $room, Collection $bookings, Carbon $day): array
    {
        if ($room->status === 'maintenance') {
            return ['maintenance', null];
        }

        $d = $day->toDateString();
        $inHouse = $bookings->first(fn ($b) => $b->status === 'checked_in');
        if ($inHouse) {
            return [$inHouse->check_out->toDateString() <= $d ? 'departing' : 'occupied', $inHouse];
        }

        $arriving = $bookings->first(fn ($b) => $b->status === 'booked' && $b->check_in->toDateString() <= $d && $b->check_out->toDateString() > $d);

        return $arriving ? ['arriving', $arriving] : ['available', null];
    }

    /** A free room of this type for the dates, or null. */
    public function freeRoomOfType(int $roomTypeId, string $in, string $out): ?Room
    {
        return $this->availableRooms($in, $out)->firstWhere('room_type_id', $roomTypeId);
    }

    /**
     * Create the booking after PayHere confirms an online room payment.
     * Must be called inside a transaction. Returns null when every room of the type was taken meanwhile.
     */
    public function placeOnlineBooking(OnlinePayment $payment): ?RoomBooking
    {
        $d = $payment->data;

        // Lock the rooms of this type so two payments cannot get the same room
        Room::where('room_type_id', $d['room_type_id'])->lockForUpdate()->get();
        $room = $this->freeRoomOfType((int) $d['room_type_id'], $d['check_in'], $d['check_out']);

        if (! $room) {
            return null;
        }

        $booking = RoomBooking::create([
            'booking_no' => $this->nextBookingNumber(),
            'room_id' => $room->id,
            'customer_id' => $d['customer_id'],
            'user_id' => null,
            'guest_name' => $d['guest_name'],
            'guest_phone' => $d['guest_phone'],
            'guest_email' => $d['guest_email'],
            'adults' => $d['adults'],
            'children' => $d['children'],
            'check_in' => $d['check_in'],
            'check_out' => $d['check_out'],
            'nights' => $d['nights'],
            'price_per_night' => $d['price_per_night'],
            'room_total' => $d['room_total'],
            'total' => $d['room_total'],
            'amount_paid' => 0,
            'status' => 'booked',
            'source' => 'online',
            'notes' => $d['notes'] ?? null,
        ]);

        $booking->payments()->create([
            'method' => 'online',
            'amount' => $payment->amount,
            'card_last4' => $payment->card_no ? substr(preg_replace('/\D/', '', $payment->card_no), -4) : null,
            'approval_code' => 'PH'.$payment->gateway_payment_id,
            'note' => 'Paid online',
            'paid_at' => now(),
        ]);
        $booking->increment('amount_paid', (float) $payment->amount);

        return $booking;
    }
}
