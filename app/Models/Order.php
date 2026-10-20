<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A customer order taken by a waiter (FR-13).
 */
class Order extends Model
{
    protected $fillable = [
        'table_id',
        'user_id',
        'order_type',
        'status',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function bill(): HasOne
    {
        return $this->hasOne(Bill::class);
    }

    /** Total of all items that were not cancelled. */
    public function subtotal(): float
    {
        return (float) $this->items
            ->where('status', '!=', 'cancelled')
            ->sum(fn ($item) => $item->quantity * $item->unit_price);
    }
}
