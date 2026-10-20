<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A payment against a bill (FR-26).
 */
class Payment extends Model
{
    protected $fillable = [
        'bill_id',
        'method',
        'card_type',
        'card_last4',
        'approval_code',
        'amount_paid',
        'balance',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:2',
            'balance' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public const CARD_TYPES = ['visa' => 'Visa', 'mastercard' => 'Mastercard', 'amex' => 'American Express', 'other' => 'Other'];

    /** e.g. "Visa **** 4242" */
    public function cardLabel(): ?string
    {
        if ($this->method !== 'card' || ! $this->card_type) {
            return null;
        }

        return (self::CARD_TYPES[$this->card_type] ?? 'Card').' **** '.$this->card_last4;
    }
}
