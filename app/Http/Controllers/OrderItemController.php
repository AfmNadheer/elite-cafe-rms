<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Waiter actions on single order lines (FR-16, FR-17).
 */
class OrderItemController extends Controller
{
    /**
     * Cancel an item. Pending items: waiter or admin.
     * Items already being prepared: admin only (ingredients may be used).
     */
    public function cancel(Request $request, OrderItem $orderItem): RedirectResponse
    {
        $user = $request->user();

        if ($orderItem->status === 'preparing' && ! $user->hasRole('admin')) {
            return back()->with('error', 'This item is already being prepared. Ask the Admin to cancel it.');
        }

        if (! in_array($orderItem->status, ['pending', 'preparing'], true)) {
            return back()->with('error', 'Only items that are not ready yet can be cancelled.');
        }

        $orderItem->update(['status' => 'cancelled']);

        // If nothing is left on the order, cancel it and free the table
        $order = $orderItem->order;
        if (! $order->items()->where('status', '!=', 'cancelled')->exists()) {
            $order->update(['status' => 'cancelled']);
            $order->table?->update(['status' => 'free']);

            return redirect()->route('orders.index')->with('success', 'All items were cancelled, so the order was cancelled.');
        }

        return back()->with('success', 'Item cancelled.');
    }

    /** Waiter has taken a Ready item to the table. */
    public function serve(OrderItem $orderItem): RedirectResponse
    {
        if ($orderItem->status !== 'ready') {
            return back()->with('error', 'Only Ready items can be marked as served.');
        }

        $orderItem->update(['status' => 'served']);

        return back()->with('success', 'Item marked as served.');
    }
}
