<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A table booking (FR-12).
 */
class Reservation extends Model
{
    protected $fillable = [
        'table_id',
        'customer_name',
        'phone',
        'reserved_at',
        'party_size',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'reserved_at' => 'datetime',
        ];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }
}
