<?php

namespace App\Http\Controllers;

use App\Models\OnlinePayment;
use App\Services\OnlinePaymentService;
use App\Services\PayHereService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Online card payments through PayHere (restaurant bills paid at the counter).
 *  1. checkout  – our page sends the customer to PayHere with a signed form
 *  2. notify    – PayHere tells our server the result (server to server, md5sig checked)
 *  3. return    – the customer comes back; the page waits for step 2
 */
class PayHereController extends Controller
{
    public function __construct(private PayHereService $payhere, private OnlinePaymentService $payments)
    {
    }

    /** Page that posts the signed form to PayHere. */
    public function checkout(OnlinePayment $onlinePayment): View|RedirectResponse
    {
        if (! $onlinePayment->isPending()) {
            return redirect()->route('payhere.return', $onlinePayment);
        }

        $order = $onlinePayment->order()->with('table')->first();
        $items = 'Elite Cafe bill - '.($order?->table?->table_no ?? 'Takeaway').' Order #'.$onlinePayment->payable_id;

        return view('payhere.checkout', [
            'payment' => $onlinePayment,
            'action' => $this->payhere->checkoutUrl(),
            'fields' => $this->payhere->checkoutFields($onlinePayment, $items),
            'sandbox' => $this->payhere->isSandbox(),
        ]);
    }

    /** PayHere calls this from its server (no login, no CSRF token). Used for bills AND delivery orders. */
    public function notify(Request $request): Response
    {
        $post = $request->all();

        if (! $this->payhere->verifyNotification($post)) {
            Log::warning('PayHere notification with an invalid signature', ['order_id' => $post['order_id'] ?? null]);

            return response('Invalid signature', 400);
        }

        $payment = OnlinePayment::where('order_ref', $post['order_id'] ?? '')->first();
        if (! $payment) {
            return response('Unknown order', 404);
        }

        $status = OnlinePayment::STATUS_CODES[(string) ($post['status_code'] ?? '')] ?? 'failed';

        // The amount must match what we asked for
        if ($status === 'success' && round((float) $post['payhere_amount'], 2) !== round((float) $payment->amount, 2)) {
            $status = 'failed';
            $post['status_message'] = 'Amount mismatch';
        }

        $this->payments->apply($payment, $status, [
            'gateway_payment_id' => $post['payment_id'] ?? null,
            'method' => $post['method'] ?? null,
            'card_no' => $post['card_no'] ?? null,
            'status_message' => $post['status_message'] ?? null,
        ]);

        return response('OK');
    }

    /** The customer is back from PayHere. */
    public function return(OnlinePayment $onlinePayment): View
    {
        return view('payhere.status', [
            'payment' => $onlinePayment,
            'sandbox' => $this->payhere->isSandbox(),
            'bill' => $onlinePayment->status === 'success' ? $onlinePayment->order?->bill : null,
        ]);
    }

    /** The customer pressed "Cancel" on PayHere. */
    public function cancel(OnlinePayment $onlinePayment): RedirectResponse
    {
        if ($onlinePayment->isPending()) {
            $onlinePayment->update(['status' => 'cancelled', 'status_message' => 'Cancelled by the customer']);
        }

        return redirect()->route('billing.create', $onlinePayment->payable_id)
            ->with('error', 'The online payment was cancelled. Choose another payment method or try again.');
    }

    /** Polled by the status page every 3 seconds. */
    public function status(OnlinePayment $onlinePayment): JsonResponse
    {
        return response()->json([
            'status' => $onlinePayment->status,
            'bill_url' => $onlinePayment->status === 'success' && $onlinePayment->order?->bill
                ? route('bills.show', $onlinePayment->order->bill) : null,
        ]);
    }

    /**
     * SANDBOX ONLY: on localhost PayHere cannot reach notify_url, so the Admin can confirm a test payment by hand.
     * Disabled automatically when PAYHERE_SANDBOX=false.
     */
    public function confirmSandbox(OnlinePayment $onlinePayment): RedirectResponse
    {
        abort_unless($this->payhere->isSandbox(), 403, 'Only available in sandbox mode.');

        $this->payments->apply($onlinePayment, 'success', $this->sandboxDetails($onlinePayment));

        return redirect()->route('payhere.return', $onlinePayment);
    }

    /** Fake card details used only for sandbox confirmation. */
    public static function sandboxDetails(OnlinePayment $payment): array
    {
        return [
            'gateway_payment_id' => 'SANDBOX'.$payment->id,
            'method' => 'VISA',
            'card_no' => '************4242',
            'status_message' => 'Confirmed manually (sandbox test)',
        ];
    }
}
