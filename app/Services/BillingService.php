<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Order;
use Illuminate\Validation\ValidationException;

/**
 * Bill calculations (FR-24, FR-25).
 * All amounts are rounded to 2 decimals.
 */
class BillingService
{
    /** Service charge for dine-in orders: 10% of the subtotal. */
    public const SERVICE_CHARGE_PERCENT = 10;

    /**
     * Work out every amount on the bill.
     *
     * @param  string  $discountType  none | percent | fixed
     * @param  float  $deliveryFee  only for online delivery orders
     * @return array{subtotal: float, service_charge: float, delivery_fee: float, discount: float, total: float}
     */
    public function calculate(float $subtotal, bool $isDineIn, string $discountType = 'none', float $discountValue = 0, float $deliveryFee = 0): array
    {
        $serviceCharge = $isDineIn ? round($subtotal * self::SERVICE_CHARGE_PERCENT / 100, 2) : 0.0;

        $discount = match ($discountType) {
            'percent' => round($subtotal * $discountValue / 100, 2),
            'fixed' => round($discountValue, 2),
            default => 0.0,
        };

        if ($discountType === 'percent' && $discountValue > 100) {
            throw ValidationException::withMessages(['discount_value' => 'A percentage discount cannot be more than 100%.']);
        }
        if ($discount > $subtotal) {
            throw ValidationException::withMessages(['discount_value' => 'The discount cannot be more than the subtotal.']);
        }

        return [
            'subtotal' => round($subtotal, 2),
            'service_charge' => $serviceCharge,
            'delivery_fee' => round($deliveryFee, 2),
            'discount' => $discount,
            'total' => round($subtotal + $serviceCharge + $deliveryFee - $discount, 2),
        ];
    }

    /**
     * Next bill number for today, e.g. EC261020-0007.
     * Must be called inside a database transaction.
     */
    public function nextBillNumber(): string
    {
        $prefix = 'EC'.now()->format('ymd').'-';

        $last = Bill::where('bill_no', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('bill_no')
            ->value('bill_no');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Save the bill and its payment, close the order and free the table.
     * Used for cash, card machine and online (PayHere) payments.
     * Must be called inside a database transaction.
     */
    public function closeOrder(Order $order, array $amounts, int $cashierId, ?int $approverId, array $payment): Bill
    {
        $bill = Bill::create($amounts + [
            'order_id' => $order->id,
            'user_id' => $cashierId,
            'approved_by' => $approverId,
            'bill_no' => $this->nextBillNumber(),
        ]);

        $bill->payments()->create($payment + ['paid_at' => now()]);

        // Items still waiting on the counter are handed over with the bill
        $order->items()->where('status', 'ready')->update(['status' => 'served']);
        $order->update(['status' => 'closed']);
        $order->table?->update(['status' => 'free']);

        return $bill;
    }
}
