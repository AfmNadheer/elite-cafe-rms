<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Kitchen display (FR-19 to FR-22).
 * The page asks /kitchen/items for new orders every 10 seconds (NFR-02).
 */
class KitchenController extends Controller
{
    /** Orders waiting longer than this many minutes are highlighted (FR-22). */
    public const DELAY_MINUTES = 20;

    public function index(): View
    {
        return view('kitchen.index', ['delayMinutes' => self::DELAY_MINUTES]);
    }

    /** JSON list of items to cook, oldest first. */
    public function items(): JsonResponse
    {
        $items = OrderItem::with(['order.table', 'menuItem'])
            ->whereIn('status', ['pending', 'preparing'])
            ->whereHas('order', fn ($q) => $q->where('status', 'open'))
            ->orderBy('created_at')
            ->get()
            ->map(fn (OrderItem $item) => [
                'id' => $item->id,
                'order_id' => $item->order_id,
                'name' => $item->menuItem->name,
                'quantity' => $item->quantity,
                'notes' => $item->notes,
                'status' => $item->status,
                'table' => $item->order->table?->table_no ?? 'Takeaway',
                'time' => $item->created_at->format('H:i'),
                'minutes' => (int) $item->created_at->diffInMinutes(now()),
            ]);

        return response()->json([
            'items' => $items,
            'updated_at' => now()->format('H:i:s'),
        ]);
    }

    /** Pending -> Preparing -> Ready. Ready deducts stock (FR-31). */
    public function updateStatus(Request $request, OrderItem $orderItem, StockService $stock): JsonResponse
    {
        $request->validate(['status' => ['required', 'in:preparing,ready']]);

        $allowed = [
            'pending' => ['preparing', 'ready'],
            'preparing' => ['ready'],
        ];

        if (! in_array($request->status, $allowed[$orderItem->status] ?? [], true)) {
            return response()->json(['message' => 'This item has already been updated.'], 422);
        }

        $orderItem->update(['status' => $request->status]);

        if ($request->status === 'ready') {
            $stock->deductForOrderItem($orderItem);
        }

        return response()->json(['message' => 'Updated', 'status' => $orderItem->status]);
    }
}
