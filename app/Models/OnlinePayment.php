<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * An online card payment through PayHere.
 */
class OnlinePayment extends Model
{
    /** PayHere status_code → our status */
    public const STATUS_CODES = ['2' => 'success', '0' => 'pending', '-1' => 'cancelled', '-2' => 'failed', '-3' => 'chargedback'];

    protected $fillable = [
        'order_ref', 'payable_type', 'payable_id', 'user_id', 'amount', 'currency', 'status', 'data',
        'customer_name', 'customer_phone', 'customer_email',
        'gateway_payment_id', 'method', 'card_no', 'status_message', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'data' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    /** Unique reference sent to PayHere, e.g. EC-O12-K3F9Q */
    public static function newReference(string $prefix, int $id): string
    {
        return 'EC-'.$prefix.$id.'-'.strtoupper(Str::random(5));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The restaurant order this payment is for (when payable_type = Order). */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'payable_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
