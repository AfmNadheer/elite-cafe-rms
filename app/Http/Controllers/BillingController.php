<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Order;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Billing and payments (FR-23 to FR-26).
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
            ->oldest()
            ->get();

        return view('billing.index', ['orders' => $orders]);
    }

    /** Bill screen for one order (FR-23: no re-entering of items). */
    public function create(Order $order): View|RedirectResponse
    {
        if ($order->status !== 'open') {
            return $order->bill
                ? redirect()->route('bills.show', $order->bill)
                : redirect()->route('billing.index')->with('error', 'This order is not open.');
        }

        $order->load(['items.menuItem', 'table', 'waiter']);

        return view('billing.create', [
            'order' => $order,
            'items' => $order->items->where('status', '!=', 'cancelled'),
            'unfinished' => $this->unfinishedCount($order),
            'serviceRate' => $order->order_type === 'dine_in' ? BillingService::SERVICE_CHARGE_PERCENT : 0,
        ]);
    }

    /** Save the bill and the payment, close the order and free the table. */
    public function store(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'discount_type' => ['required', 'in:none,percent,fixed'],
            'discount_value' => ['nullable', 'required_unless:discount_type,none', 'numeric', 'min:0'],
            'approver_username' => ['nullable', 'string', 'max:50'],
            'approver_password' => ['nullable', 'string'],
            'method' => ['required', 'in:cash,card'],
            'amount_paid' => ['nullable', 'required_if:method,cash', 'numeric', 'min:0'],
        ], [
            'discount_value.required_unless' => 'Enter the discount amount.',
            'amount_paid.required_if' => 'Enter the cash received from the customer.',
        ]);

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

        $bill = DB::transaction(function () use ($order, $data, $user, $approverId) {
            // Lock the order so two cashiers cannot bill it at the same time
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $order->load(['items', 'table']);

            if ($order->status !== 'open') {
                throw ValidationException::withMessages(['order' => 'This order has already been billed or closed.']);
            }
            if ($this->unfinishedCount($order) > 0) {
                throw ValidationException::withMessages(['order' => 'The kitchen has not finished all items. Ask the waiter to cancel them or wait until they are ready.']);
            }
            if ($order->items->where('status', '!=', 'cancelled')->isEmpty()) {
                throw ValidationException::withMessages(['order' => 'This order has no items to bill.']);
            }

            $amounts = $this->billing->calculate(
                $order->subtotal(),
                $order->order_type === 'dine_in',
                $data['discount_type'],
                (float) $data['discount_value'],
            );

            // FR-26: cash must cover the total; card is paid exactly
            $paid = $data['method'] === 'cash' ? round((float) $data['amount_paid'], 2) : $amounts['total'];
            if ($paid < $amounts['total']) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'Cash received is less than the total (Rs. '.number_format($amounts['total'], 2).').',
                ]);
            }

            $bill = Bill::create($amounts + [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'approved_by' => $approverId,
                'bill_no' => $this->billing->nextBillNumber(),
            ]);

            $bill->payments()->create([
                'method' => $data['method'],
                'amount_paid' => $paid,
                'balance' => round($paid - $amounts['total'], 2),
                'paid_at' => now(),
            ]);

            // Items still waiting on the counter are handed over with the bill
            $order->items()->where('status', 'ready')->update(['status' => 'served']);
            $order->update(['status' => 'closed']);
            $order->table?->update(['status' => 'free']);

            return $bill;
        });

        return redirect()->route('bills.show', $bill)->with('success', 'Payment saved. Bill '.$bill->bill_no.' is ready to print.');
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
