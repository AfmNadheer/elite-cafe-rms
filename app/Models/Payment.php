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
}
