<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\OnlinePayment;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * What happens when PayHere reports a result.
 *  - Order : a restaurant bill → the bill is saved and the order closed
 *  - Cart  : an online delivery order → the order is placed and sent to the kitchen
 */
class OnlinePaymentService
{
    public function __construct(
        private BillingService $billing,
        private DeliveryService $delivery,
        private PayHereService $payhere,
        private RoomService $rooms,
    ) {
    }

    public function apply(OnlinePayment $payment, string $status, array $details): void
    {
        DB::transaction(function () use ($payment, $status, $details) {
            $payment = OnlinePayment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            // Already finished: a repeated notification changes nothing
            if (! $payment->isPending()) {
                return;
            }

            $payment->update($details + ['status' => $status, 'paid_at' => $status === 'success' ? now() : null]);

            if ($status !== 'success') {
                return;
            }

            match ($payment->payable_type) {
                'Order' => $this->closeRestaurantBill($payment),
                'Cart' => $this->placeDelivery($payment),
                'RoomRequest' => $this->placeRoomBooking($payment),
                default => null,
            };
        });
    }

    /** Card details saved on the bill's payment row. */
    public function cardPayment(OnlinePayment $payment): array
    {
        return [
            'method' => 'card',
            'card_type' => $this->payhere->cardType($payment->method),
            'card_last4' => $payment->card_no ? substr(preg_replace('/\D/', '', $payment->card_no), -4) : null,
            'approval_code' => 'PH'.$payment->gateway_payment_id,
            'amount_paid' => $payment->amount,
            'balance' => 0,
        ];
    }

    private function closeRestaurantBill(OnlinePayment $payment): void
    {
        $order = Order::whereKey($payment->payable_id)->lockForUpdate()->with('table')->first();
        if (! $order || $order->status !== 'open') {
            $payment->update(['status_message' => 'Paid, but the order was already closed. Refund needed.']);

            return;
        }

        $data = $payment->data;
        $this->billing->closeOrder($order, $data['amounts'], $data['cashier_id'], $data['approved_by'] ?? null, $this->cardPayment($payment));
    }

    private function placeRoomBooking(OnlinePayment $payment): void
    {
        $booking = $this->rooms->placeOnlineBooking($payment);

        $payment->update(['status_message' => $booking
            ? 'Booking '.$booking->booking_no.' confirmed'
            : 'Paid, but no room of this type was free any more. Refund needed.']);
    }

    private function placeDelivery(OnlinePayment $payment): void
    {
        $data = $payment->data;
        $customer = Customer::find($data['customer_id']);

        if (! $customer) {
            $payment->update(['status_message' => 'Paid, but the customer account is missing. Refund needed.']);

            return;
        }

        $this->delivery->place($customer, $data['details'], $data['lines'], $payment);
    }
}
