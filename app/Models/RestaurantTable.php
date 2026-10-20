<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A dining table (FR-10, FR-11).
 */
class RestaurantTable extends Model
{
    protected $table = 'restaurant_tables';

    protected $fillable = [
        'table_no',
        'section',
        'capacity',
        'status',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'table_id');
    }

    /** The order currently open on this table (if any). */
    public function openOrder(): HasOne
    {
        return $this->hasOne(Order::class, 'table_id')->where('status', 'open');
    }

    /** Next booking for today that has not started yet (or started up to 15 minutes ago). */
    public function nextReservation(): HasOne
    {
        return $this->hasOne(Reservation::class, 'table_id')
            ->where('status', 'booked')
            ->whereBetween('reserved_at', [now()->subMinutes(15), now()->endOfDay()])
            ->orderBy('reserved_at');
    }
}
