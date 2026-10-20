<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Wastage or a manual stock correction (FR-33).
 */
class StockAdjustment extends Model
{
    protected $fillable = [
        'ingredient_id',
        'quantity',
        'reason',
        'adjusted_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'adjusted_at' => 'datetime',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
