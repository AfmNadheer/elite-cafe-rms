<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Delivery details of an online order.
 */
class Delivery extends Model
{
    public const STATUSES = [
        'placed' => 'Order placed',
        'out_for_delivery' => 'Out for delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'order_id', 'customer_id', 'address', 'phone', 'notes', 'delivery_fee',
        'payment_method', 'payment_status', 'online_payment_id', 'status',
        'rider_name', 'rider_phone', 'dispatched_at', 'delivered_at', 'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'delivery_fee' => 'decimal:2',
            'dispatched_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function onlinePayment(): BelongsTo
    {
        return $this->belongsTo(OnlinePayment::class);
    }

    /** Food value + delivery fee (no service charge on deliveries). */
    public function total(): float
    {
        return round($this->order->subtotal() + (float) $this->delivery_fee, 2);
    }

    /** Step shown to the customer: placed → preparing → ready → out for delivery → delivered */
    public function stage(): string
    {
        if ($this->status !== 'placed') {
            return $this->status;
        }

        $items = $this->order->items->where('status', '!=', 'cancelled');

        if ($items->isNotEmpty() && $items->every(fn ($i) => in_array($i->status, ['ready', 'served']))) {
            return 'ready';
        }

        return $items->contains(fn ($i) => $i->status !== 'pending') ? 'preparing' : 'placed';
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['placed', 'out_for_delivery']);
    }
}
