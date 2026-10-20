<?php

namespace App\Services;

use App\Models\OnlinePayment;

/**
 * PayHere Checkout API (https://support.payhere.lk/api-&-mobile-sdk/checkout-api).
 * The hash and the md5sig check are made on the server, so the merchant secret never reaches the browser.
 */
class PayHereService
{
    public function isConfigured(): bool
    {
        return filled(config('payhere.merchant_id')) && filled(config('payhere.merchant_secret'));
    }

    public function isSandbox(): bool
    {
        return (bool) config('payhere.sandbox');
    }

    public function checkoutUrl(): string
    {
        return $this->isSandbox()
            ? 'https://sandbox.payhere.lk/pay/checkout'
            : 'https://www.payhere.lk/pay/checkout';
    }

    /** hash = UPPER(md5(merchant_id + order_id + amount + currency + UPPER(md5(merchant_secret)))) */
    public function hash(string $orderRef, float $amount, string $currency): string
    {
        return strtoupper(md5(
            config('payhere.merchant_id').$orderRef.$this->money($amount).$currency.$this->secretHash()
        ));
    }

    /**
     * Check that a notification really came from PayHere.
     * md5sig = UPPER(md5(merchant_id + order_id + payhere_amount + payhere_currency + status_code + UPPER(md5(merchant_secret))))
     */
    public function verifyNotification(array $post): bool
    {
        $expected = strtoupper(md5(
            ($post['merchant_id'] ?? '').($post['order_id'] ?? '').($post['payhere_amount'] ?? '')
            .($post['payhere_currency'] ?? '').($post['status_code'] ?? '').$this->secretHash()
        ));

        return ($post['merchant_id'] ?? null) === (string) config('payhere.merchant_id')
            && hash_equals($expected, strtoupper($post['md5sig'] ?? ''));
    }

    /** All form fields for the PayHere checkout page. */
    public function checkoutFields(OnlinePayment $payment, string $items, ?string $returnUrl = null, ?string $cancelUrl = null): array
    {
        $names = explode(' ', trim($payment->customer_name ?: 'Customer'), 2);

        return [
            'merchant_id' => config('payhere.merchant_id'),
            'return_url' => $returnUrl ?? route('payhere.return', $payment),
            'cancel_url' => $cancelUrl ?? route('payhere.cancel', $payment),
            'notify_url' => config('payhere.notify_url') ?: route('payhere.notify'),
            'order_id' => $payment->order_ref,
            'items' => $items,
            'currency' => $payment->currency,
            'amount' => $this->money((float) $payment->amount),
            'first_name' => $names[0],
            'last_name' => $names[1] ?? '-',
            'email' => $payment->customer_email,
            'phone' => $payment->customer_phone,
            'address' => config('payhere.address'),
            'city' => config('payhere.city'),
            'country' => config('payhere.country'),
            'hash' => $this->hash($payment->order_ref, (float) $payment->amount, $payment->currency),
        ];
    }

    /** PayHere card method → our card type */
    public function cardType(?string $method): string
    {
        return match (strtoupper((string) $method)) {
            'VISA' => 'visa',
            'MASTER' => 'mastercard',
            'AMEX' => 'amex',
            default => 'other',
        };
    }

    private function secretHash(): string
    {
        return strtoupper(md5((string) config('payhere.merchant_secret')));
    }

    private function money(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
