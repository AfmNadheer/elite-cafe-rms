<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Services\BillingService;
use App\Services\OnlinePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Staff side of online delivery orders: dispatch with a rider, mark delivered (bill), or cancel.
 */
class DeliveryController extends Controller
{
    public function index(): View
    {
        return view('deliveries.index', [
            'active' => Delivery::with(['order.items', 'customer'])->whereIn('status', ['placed', 'out_for_delivery'])->oldest()->get(),
            'finished' => Delivery::with(['order.items', 'customer'])->whereIn('status', ['delivered', 'cancelled'])
                ->whereDate('updated_at', today())->latest('updated_at')->get(),
        ]);
    }

    public function show(Delivery $delivery): View
    {
        return view('deliveries.show', ['delivery' => $delivery->load(['order.items.menuItem', 'order.bill', 'customer', 'onlinePayment'])]);
    }

    /** Hand the order to a rider. The kitchen must have finished every item. */
    public function dispatch(Request $request, Delivery $delivery): RedirectResponse
    {
        $data = $request->validate([
            'rider_name' => ['required', 'string', 'max:100'],
            'rider_phone' => ['required', 'regex:/^[0-9 +\-]{9,20}$/'],
        ]);

        if ($delivery->status !== 'placed') {
            return back()->with('error', 'This order has already been dispatched or closed.');
        }
        if ($delivery->order->items->whereIn('status', ['pending', 'preparing'])->isNotEmpty()) {
            return back()->with('error', 'The kitchen has not finished all items yet.');
        }

        $delivery->update($data + ['status' => 'out_for_delivery', 'dispatched_at' => now()]);

        return back()->with('success', 'Order is out for delivery with '.$data['rider_name'].'.');
    }

    /** The rider has delivered: save the bill and payment and close the order. */
    public function deliver(Request $request, Delivery $delivery, BillingService $billing, OnlinePaymentService $payments): RedirectResponse
    {
        if ($delivery->status !== 'out_for_delivery') {
            return back()->with('error', 'Only orders that are out for delivery can be marked delivered.');
        }

        $isCod = $delivery->payment_method === 'cod';
        $request->validate(['amount_received' => [$isCod ? 'required' : 'nullable', 'numeric', 'min:0']], [
            'amount_received.required' => 'Enter the cash the rider collected.',
        ]);

        $bill = DB::transaction(function () use ($request, $delivery, $billing, $payments, $isCod) {
            $delivery = Delivery::whereKey($delivery->id)->lockForUpdate()->firstOrFail();
            $order = $delivery->order()->with('items')->lockForUpdate()->first();

            if ($order->status !== 'open') {
                throw ValidationException::withMessages(['amount_received' => 'This order is already closed.']);
            }

            // No service charge on delivery; the delivery fee is added instead
            $amounts = $billing->calculate($order->subtotal(), false, 'none', 0, (float) $delivery->delivery_fee);

            if ($isCod) {
                $paid = round((float) $request->amount_received, 2);
                if ($paid < $amounts['total']) {
                    throw ValidationException::withMessages([
                        'amount_received' => 'Cash collected is less than the total (Rs. '.number_format($amounts['total'], 2).').',
                    ]);
                }
                $payment = ['method' => 'cash', 'amount_paid' => $paid, 'balance' => round($paid - $amounts['total'], 2)];
            } else {
                $payment = $payments->cardPayment($delivery->onlinePayment);
                $payment['amount_paid'] = $amounts['total'];
            }

            $bill = $billing->closeOrder($order, $amounts, $request->user()->id, null, $payment);
            $delivery->update(['status' => 'delivered', 'delivered_at' => now(), 'payment_status' => 'paid']);

            return $bill;
        });

        return redirect()->route('bills.show', $bill)->with('success', 'Delivered. Bill '.$bill->bill_no.' saved.');
    }

    /** Cancel an order that has not left the restaurant. */
    public function cancel(Request $request, Delivery $delivery): RedirectResponse
    {
        $data = $request->validate(['cancel_reason' => ['required', 'string', 'max:255']], [
            'cancel_reason.required' => 'Give a reason for cancelling.',
        ]);

        if ($delivery->status !== 'placed') {
            return back()->with('error', 'Only orders that have not left the restaurant can be cancelled.');
        }

        DB::transaction(function () use ($delivery, $data) {
            foreach ($delivery->order->items->where('status', '!=', 'cancelled') as $item) {
                $item->update(['status' => 'cancelled']);   // recorded in the audit log
            }
            $delivery->order->update(['status' => 'cancelled']);
            $delivery->update(['status' => 'cancelled', 'cancel_reason' => $data['cancel_reason']]);
        });

        $message = 'Order cancelled.';
        if ($delivery->payment_status === 'paid') {
            $message .= ' The customer paid online: refund Rs. '.number_format((float) $delivery->onlinePayment?->amount, 2).' from the PayHere portal.';
        }

        return redirect()->route('deliveries.index')->with('success', $message);
    }
}
