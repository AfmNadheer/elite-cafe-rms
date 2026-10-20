<?php

namespace App\Http\Controllers;

use App\Models\OnlinePayment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\BillingService;
use App\Services\PayHereService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Billing and payments (FR-23 to FR-26): cash, card machine, or online card through PayHere.
 */
class BillingController extends Controller
{
    public function __construct(private BillingService $billing)
    {
    }

    /** Open orders waiting for a bill. */
    public function index(): View
    {
        $orders = Order::with(['table', 'waiter', 'items'])
            ->where('status', 'open')
            ->where('order_type', '!=', 'delivery')   // deliveries are billed from the Deliveries page
            ->oldest()
            ->get();

        return view('billing.index', ['orders' => $orders]);
    }

    /** Bill screen for one order (FR-23: no re-entering of items). */
    public function create(Order $order, PayHereService $payhere): View|RedirectResponse
    {
        if ($order->status !== 'open') {
            return $order->bill
                ? redirect()->route('bills.show', $order->bill)
                : redirect()->route('billing.index')->with('error', 'This order is not open.');
        }

        if ($order->order_type === 'delivery' && $order->delivery) {
            return redirect()->route('deliveries.show', $order->delivery);
        }

        $order->load(['items.menuItem', 'table', 'waiter']);

        return view('billing.create', [
            'order' => $order,
            'items' => $order->items->where('status', '!=', 'cancelled'),
            'unfinished' => $this->unfinishedCount($order),
            'serviceRate' => $order->order_type === 'dine_in' ? BillingService::SERVICE_CHARGE_PERCENT : 0,
            'onlineReady' => $payhere->isConfigured(),
            'pendingOnline' => OnlinePayment::where('payable_type', 'Order')->where('payable_id', $order->id)
                ->where('status', 'pending')->latest('id')->first(),
        ]);
    }

    /** Save the bill and the payment, or send the customer to PayHere for an online payment. */
    public function store(Request $request, Order $order, PayHereService $payhere): RedirectResponse
    {
        $data = $request->validate([
            'discount_type' => ['required', 'in:none,percent,fixed'],
            'discount_value' => ['nullable', 'required_unless:discount_type,none', 'numeric', 'min:0'],
            'approver_username' => ['nullable', 'string', 'max:50'],
            'approver_password' => ['nullable', 'string'],
            'method' => ['required', 'in:cash,card,online'],
            'amount_paid' => ['nullable', 'required_if:method,cash', 'numeric', 'min:0'],
            // Card machine slip details (only the last 4 digits, never the full number)
            'card_type' => ['nullable', 'required_if:method,card', 'in:'.implode(',', array_keys(Payment::CARD_TYPES))],
            'card_last4' => ['nullable', 'required_if:method,card', 'digits:4'],
            'approval_code' => ['nullable', 'required_if:method,card', 'alpha_num', 'max:12'],
            // Online payment: PayHere needs the customer's details
            'customer_name' => ['nullable', 'required_if:method,online', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'required_if:method,online', 'regex:/^[0-9 +\-]{9,20}$/'],
            'customer_email' => ['nullable', 'required_if:method,online', 'email', 'max:100'],
        ], [
            'card_type.required_if' => 'Select the card type.',
            'card_last4.required_if' => 'Enter the last 4 digits of the card.',
            'card_last4.digits' => 'Enter exactly 4 digits.',
            'approval_code.required_if' => 'Enter the approval code from the card machine slip.',
            'discount_value.required_unless' => 'Enter the discount amount.',
            'amount_paid.required_if' => 'Enter the cash received from the customer.',
            'customer_name.required_if' => 'Enter the customer name for the online payment.',
            'customer_phone.required_if' => 'Enter the customer phone number.',
            'customer_email.required_if' => 'Enter the customer email (PayHere sends the receipt there).',
        ]);

        if ($data['method'] === 'online' && ! $payhere->isConfigured()) {
            return back()->withInput()->with('error', 'Online payment is not set up. Add PAYHERE_MERCHANT_ID and PAYHERE_MERCHANT_SECRET to the .env file.');
        }

        $user = $request->user();
        $hasDiscount = $data['discount_type'] !== 'none' && (float) $data['discount_value'] > 0;
        if (! $hasDiscount) {
            $data['discount_type'] = 'none';
            $data['discount_value'] = 0;
        }

        // FR-25: a discount needs an Admin. A cashier must enter an Admin's login.
        $approverId = null;
        if ($hasDiscount) {
            $approverId = $user->hasRole('admin') ? $user->id : $this->adminApproval($data);
        }

        $result = DB::transaction(function () use ($order, $data, $user, $approverId) {
            // Lock the order so two cashiers cannot bill it at the same time
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $order->load(['items', 'table']);
            $this->checkOrder($order);

            $amounts = $this->billing->calculate(
                $order->subtotal(),
                $order->order_type === 'dine_in',
                $data['discount_type'],
                (float) $data['discount_value'],
            );

            // Online: nothing is saved as paid until PayHere confirms the payment
            if ($data['method'] === 'online') {
                OnlinePayment::where('payable_type', 'Order')->where('payable_id', $order->id)
                    ->where('status', 'pending')->update(['status' => 'cancelled', 'status_message' => 'Replaced by a new attempt']);

                return OnlinePayment::create([
                    'order_ref' => OnlinePayment::newReference('O', $order->id),
                    'payable_type' => 'Order',
                    'payable_id' => $order->id,
                    'user_id' => $user->id,
                    'amount' => $amounts['total'],
                    'currency' => config('payhere.currency'),
                    'data' => ['amounts' => $amounts, 'approved_by' => $approverId, 'cashier_id' => $user->id],
                    'customer_name' => $data['customer_name'],
                    'customer_phone' => $data['customer_phone'],
                    'customer_email' => $data['customer_email'],
                ]);
            }

            // FR-26: cash must cover the total; card is paid exactly
            $paid = $data['method'] === 'cash' ? round((float) $data['amount_paid'], 2) : $amounts['total'];
            if ($paid < $amounts['total']) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'Cash received is less than the total (Rs. '.number_format($amounts['total'], 2).').',
                ]);
            }

            $isCard = $data['method'] === 'card';

            return $this->billing->closeOrder($order, $amounts, $user->id, $approverId, [
                'method' => $data['method'],
                'card_type' => $isCard ? $data['card_type'] : null,
                'card_last4' => $isCard ? $data['card_last4'] : null,
                'approval_code' => $isCard ? strtoupper($data['approval_code']) : null,
                'amount_paid' => $paid,
                'balance' => round($paid - $amounts['total'], 2),
            ]);
        });

        if ($result instanceof OnlinePayment) {
            return redirect()->route('payhere.checkout', $result);
        }

        return redirect()->route('bills.show', $result)->with('success', 'Payment saved. Bill '.$result->bill_no.' is ready to print.');
    }

    /** Stop billing an order that is closed, unfinished or empty. */
    private function checkOrder(Order $order): void
    {
        if ($order->status !== 'open') {
            throw ValidationException::withMessages(['order' => 'This order has already been billed or closed.']);
        }
        if ($this->unfinishedCount($order) > 0) {
            throw ValidationException::withMessages(['order' => 'The kitchen has not finished all items. Ask the waiter to cancel them or wait until they are ready.']);
        }
        if ($order->items->where('status', '!=', 'cancelled')->isEmpty()) {
            throw ValidationException::withMessages(['order' => 'This order has no items to bill.']);
        }
    }

    /** Items the kitchen is still working on. */
    private function unfinishedCount(Order $order): int
    {
        return $order->items->whereIn('status', ['pending', 'preparing'])->count();
    }

    /** Check the Admin username and password typed on the bill screen. Returns the Admin's id. */
    private function adminApproval(array $data): int
    {
        $admin = User::where('username', $data['approver_username'] ?? '')
            ->where('role', 'admin')
            ->where('is_active', true)
            ->first();

        if (! $admin || ! Hash::check($data['approver_password'] ?? '', $admin->password)) {
            throw ValidationException::withMessages([
                'approver_username' => 'Discount not approved: enter a valid Admin username and password.',
            ]);
        }

        return $admin->id;
    }
}
