<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

/**
 * Automatic stock control (FR-08, FR-31, FR-32).
 * When the kitchen marks an item Ready, the ingredients in its recipe
 * are deducted from stock.
 */
class StockService
{
    public function deductForOrderItem(OrderItem $orderItem): void
    {
        $menuItem = MenuItem::with('ingredients')->find($orderItem->menu_item_id);

        if (! $menuItem) {
            return;
        }

        DB::transaction(function () use ($menuItem, $orderItem) {
            foreach ($menuItem->ingredients as $ingredient) {
                // e.g. 0.200 kg noodles x 3 portions = 0.600 kg
                $used = $ingredient->pivot->quantity_required * $orderItem->quantity;
                $ingredient->decrement('quantity', $used);
                $ingredient->refresh();

                // Low-stock alert, only one open alert per ingredient (FR-32)
                if ($ingredient->isLow() && ! $ingredient->stockAlerts()->where('is_resolved', false)->exists()) {
                    $ingredient->stockAlerts()->create(['is_resolved' => false]);
                }

                // Out of stock: hide every dish that needs it (FR-08)
                if ($ingredient->quantity <= 0) {
                    MenuItem::whereHas('ingredients', fn ($q) => $q->where('ingredients.id', $ingredient->id))
                        ->update(['is_available' => false]);
                }
            }
        });
    }
}
