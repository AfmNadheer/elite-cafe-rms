<?php

namespace App\Services;

use App\Models\Bill;
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
     * @return array{subtotal: float, service_charge: float, discount: float, total: float}
     */
    public function calculate(float $subtotal, bool $isDineIn, string $discountType = 'none', float $discountValue = 0): array
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
            'discount' => $discount,
            'total' => round($subtotal + $serviceCharge - $discount, 2),
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
}
