<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A low-stock warning (FR-32).
 */
class StockAlert extends Model
{
    protected $fillable = [
        'ingredient_id',
        'is_resolved',
    ];

    protected function casts(): array
    {
        return [
            'is_resolved' => 'boolean',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
