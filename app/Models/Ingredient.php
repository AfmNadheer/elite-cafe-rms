<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A stock item (FR-29, FR-32).
 */
class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'quantity',
        'reorder_level',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'reorder_level' => 'decimal:3',
        ];
    }

    public function recipeItems(): HasMany
    {
        return $this->hasMany(RecipeItem::class);
    }

    public function stockAlerts(): HasMany
    {
        return $this->hasMany(StockAlert::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /** True when stock is at or below the reorder level. */
    public function isLow(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }
}
