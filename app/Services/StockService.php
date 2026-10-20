<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

/**
 * Automatic stock control (FR-08, FR-31, FR-32, FR-33, FR-35).
 * Every stock change goes through this class so alerts and
 * menu availability always stay correct.
 */
class StockService
{
    /** Kitchen marked an item Ready: deduct its recipe (FR-31). */
    public function deductForOrderItem(OrderItem $orderItem): void
    {
        $menuItem = MenuItem::with('ingredients')->find($orderItem->menu_item_id);

        if (! $menuItem) {
            return;
        }

        DB::transaction(function () use ($menuItem, $orderItem) {
            foreach ($menuItem->ingredients as $ingredient) {
                // e.g. 0.200 kg noodles x 3 portions = 0.600 kg
                $this->change($ingredient, -1 * $ingredient->pivot->quantity_required * $orderItem->quantity);
            }
        });
    }

    /**
     * Add (positive) or remove (negative) stock, then update alerts and menu availability.
     */
    public function change(Ingredient $ingredient, float $quantity): Ingredient
    {
        if ($quantity >= 0) {
            $ingredient->increment('quantity', $quantity);
        } else {
            $ingredient->decrement('quantity', abs($quantity));
        }
        $ingredient->refresh();

        $this->checkLevel($ingredient);

        return $ingredient;
    }

    /** Create or close the low-stock alert and show/hide dishes for one ingredient. */
    public function checkLevel(Ingredient $ingredient): void
    {
        $openAlert = $ingredient->stockAlerts()->where('is_resolved', false);

        if ($ingredient->isLow()) {
            // FR-32: only one open alert per ingredient
            if (! $openAlert->exists()) {
                $ingredient->stockAlerts()->create(['is_resolved' => false]);
            }
        } else {
            // Stock is fine again: close the alert automatically
            $openAlert->update(['is_resolved' => true]);
        }

        $dishes = MenuItem::whereHas('ingredients', fn ($q) => $q->where('ingredients.id', $ingredient->id));

        if ($ingredient->quantity <= 0) {
            // FR-08: out of stock -> hide every dish that needs it
            $dishes->update(['is_available' => false]);
        } else {
            // Back in stock -> show dishes again if ALL their ingredients are in stock
            $dishes->where('is_available', false)
                ->whereDoesntHave('ingredients', fn ($q) => $q->where('ingredients.quantity', '<=', 0))
                ->update(['is_available' => true]);
        }
    }
}
