<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\MenuItem;
use App\Models\OnlinePayment;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Online delivery orders: the cart and placing the order.
 * A placed order goes straight to the kitchen display like any other order.
 */
class DeliveryService
{
    public function fee(): float
    {
        return (float) config('delivery.fee');
    }

    public function minOrder(): float
    {
        return (float) config('delivery.min_order');
    }

    /**
     * Turn the session cart [menu_item_id => qty] into priced lines.
     * Items that are no longer available are left out.
     */
    public function lines(array $cart): Collection
    {
        if (! $cart) {
            return collect();
        }

        return MenuItem::whereIn('id', array_keys($cart))
            ->where('is_available', true)
            ->orderBy('name')
            ->get()
            ->map(fn (MenuItem $item) => [
                'menu_item_id' => $item->id,
                'name' => $item->name,
                'photo' => $item->photo,
                'quantity' => (int) $cart[$item->id],
                'unit_price' => (float) $item->price,
                'line_total' => round($cart[$item->id] * (float) $item->price, 2),
            ])
            ->values();
    }

    /**
     * Create the order, its items (sent to the kitchen) and the delivery record.
     *
     * @param  array  $lines  from lines(): menu_item_id, quantity, unit_price
     * @param  array  $details  address, phone, notes, payment_method
     */
    public function place(Customer $customer, array $details, array $lines, ?OnlinePayment $paid = null): Delivery
    {
        return DB::transaction(function () use ($customer, $details, $lines, $paid) {
            $order = Order::create([
                'table_id' => null,
                'user_id' => null,
                'customer_id' => $customer->id,
                'order_type' => 'delivery',
                'status' => 'open',
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'menu_item_id' => $line['menu_item_id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],   // price when the customer ordered (FR-09)
                    'notes' => null,
                    'status' => 'pending',
                ]);
            }

            // Remember the address for next time
            $customer->update(['address' => $details['address']]);

            return Delivery::create([
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'address' => $details['address'],
                'phone' => $details['phone'],
                'notes' => $details['notes'] ?? null,
                'delivery_fee' => $this->fee(),
                'payment_method' => $paid ? 'online' : 'cod',
                'payment_status' => $paid ? 'paid' : 'unpaid',
                'online_payment_id' => $paid?->id,
                'status' => 'placed',
            ]);
        });
    }
}
