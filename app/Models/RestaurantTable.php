<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
