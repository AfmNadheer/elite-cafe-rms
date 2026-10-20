<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomPayment extends Model
{
    protected $fillable = [
        'room_booking_id', 'method', 'amount', 'card_type', 'card_last4', 'approval_code', 'note', 'user_id', 'paid_at',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'datetime'];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(RoomBooking::class, 'room_booking_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return match ($this->method) {
            'card' => 'Card'.($this->card_last4 ? ' **** '.$this->card_last4 : ''),
            'online' => 'Online (PayHere)',
            default => 'Cash',
        };
    }
}
