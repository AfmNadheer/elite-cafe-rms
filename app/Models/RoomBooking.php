<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A room booking: booked → checked in → checked out (or cancelled / no-show).
 */
class RoomBooking extends Model
{
    public const STATUSES = [
        'booked' => 'Booked',
        'checked_in' => 'Checked in',
        'checked_out' => 'Checked out',
        'cancelled' => 'Cancelled',
        'no_show' => 'No-show',
    ];

    public const SOURCES = ['walk_in' => 'Walk-in', 'phone' => 'Phone', 'online' => 'Online'];

    protected $fillable = [
        'booking_no', 'room_id', 'customer_id', 'user_id',
        'guest_name', 'guest_phone', 'guest_email', 'guest_nic', 'adults', 'children',
        'check_in', 'check_out', 'nights', 'price_per_night', 'room_total',
        'extra_charges', 'extra_notes', 'discount', 'total', 'amount_paid',
        'status', 'source', 'checked_in_at', 'checked_out_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'price_per_night' => 'decimal:2',
            'room_total' => 'decimal:2',
            'extra_charges' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(RoomPayment::class);
    }

    /** Bookings that still hold the room. */
    public function scopeHolding(Builder $query): Builder
    {
        return $query->whereIn('status', ['booked', 'checked_in']);
    }

    /** Bookings that overlap the nights from $in to $out (the check-out day is free for the next guest). */
    public function scopeOverlapping(Builder $query, string $in, string $out): Builder
    {
        return $query->where('check_in', '<', $out)->where('check_out', '>', $in);
    }

    public function balance(): float
    {
        return round((float) $this->total - (float) $this->amount_paid, 2);
    }
}
