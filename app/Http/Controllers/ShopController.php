<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Delivery;
use App\Models\MenuItem;
use App\Models\OnlinePayment;
use App\Services\DeliveryService;
use App\Services\OnlinePaymentService;
use App\Services\PayHereService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Online ordering for delivery: menu, cart, checkout, payment and order tracking.
 * The cart lives in the session as [menu_item_id => quantity].
 */
class ShopController extends Controller
{
    public function __construct(private DeliveryService $delivery, private PayHereService $payhere)
    {
    }

    // ---------- Menu and cart (no login needed) ----------

    public function index(): View
    {
        $categories = Category::with(['menuItems' => fn ($q) => $q->where('is_available', true)->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->filter(fn ($c) => $c->menuItems->isNotEmpty());

        return view('shop.index', ['categories' => $categories, 'cart' => session('cart', [])]);
    }

    public function addToCart(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $item = MenuItem::findOrFail($data['menu_item_id']);
        if (! $item->is_available) {
            return back()->with('error', $item->name.' is not available right now.');
        }

        $cart = session('cart', []);
        $cart[$item->id] = min(20, ($cart[$item->id] ?? 0) + $data['quantity']);
        session(['cart' => $cart]);

        return back()->with('success', $item->name.' added to your cart.');
    }

    public function cart(): View
    {
        $lines = $this->cartLines();
        $subtotal = (float) $lines->sum('line_total');

        return view('shop.cart', [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'fee' => $this->delivery->fee(),
            'minOrder' => $this->delivery->minOrder(),
        ]);
    }

    public function updateCart(Request $request): RedirectResponse
    {
        $request->validate(['qty' => ['required', 'array'], 'qty.*' => ['integer', 'min:0', 'max:20']]);

        $cart = session('cart', []);
        foreach ($request->qty as $id => $qty) {
            if (! isset($cart[$id])) {
                continue;
            }
            if ($qty > 0) {
                $cart[$id] = (int) $qty;
            } else {
                $this->forget($cart, $id);
            }
        }
        session(['cart' => $cart]);

        return back()->with('success', 'Cart updated.');
    }

    public function removeFromCart(MenuItem $menuItem): RedirectResponse
    {
        $cart = session('cart', []);
        $this->forget($cart, $menuItem->id);
        session(['cart' => $cart]);

        return back()->with('success', $menuItem->name.' removed.');
    }

    // ---------- Checkout (customer login needed) ----------

    public function checkout(Request $request): View|RedirectResponse
    {
        $lines = $this->cartLines();
        if ($error = $this->cartProblem($lines)) {
            return redirect()->route('shop.cart')->with('error', $error);
        }

        $subtotal = (float) $lines->sum('line_total');

        return view('shop.checkout', [
            'customer' => $request->user('customer'),
            'lines' => $lines,
            'subtotal' => $subtotal,
            'fee' => $this->delivery->fee(),
            'onlineReady' => $this->payhere->isConfigured(),
        ]);
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^[0-9 +\-]{9,20}$/'],
            'notes' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cod,online'],
        ]);

        $lines = $this->cartLines();
        if ($error = $this->cartProblem($lines)) {
            return redirect()->route('shop.cart')->with('error', $error);
        }

        $customer = $request->user('customer');
        $details = ['address' => $data['address'], 'phone' => $data['phone'], 'notes' => $data['notes'] ?? null];
        $lineData = $lines->map(fn ($l) => collect($l)->only(['menu_item_id', 'name', 'quantity', 'unit_price'])->all())->all();

        // Cash on delivery: the order goes to the kitchen now
        if ($data['payment_method'] === 'cod') {
            $delivery = $this->delivery->place($customer, $details, $lineData);
            session()->forget('cart');

            return redirect()->route('shop.orders.show', $delivery)->with('success', 'Order placed! Pay in cash when it arrives.');
        }

        // Online: the order is placed only after PayHere confirms the payment
        if (! $this->payhere->isConfigured()) {
            return back()->withInput()->with('error', 'Online payment is not available. Please choose cash on delivery.');
        }

        $payment = OnlinePayment::create([
            'order_ref' => OnlinePayment::newReference('D', $customer->id),
            'payable_type' => 'Cart',
            'payable_id' => $customer->id,
            'amount' => round($lines->sum('line_total') + $this->delivery->fee(), 2),
            'currency' => config('payhere.currency'),
            'data' => ['customer_id' => $customer->id, 'details' => $details, 'lines' => $lineData],
            'customer_name' => $customer->name,
            'customer_phone' => $data['phone'],
            'customer_email' => $customer->email,
        ]);

        return redirect()->route('shop.pay', $payment);
    }

    // ---------- Online payment for a delivery order ----------

    public function pay(OnlinePayment $onlinePayment): View|RedirectResponse
    {
        $this->authorizePayment($onlinePayment);

        if (! $onlinePayment->isPending()) {
            return redirect()->route('shop.pay.return', $onlinePayment);
        }

        return view('shop.pay', [
            'payment' => $onlinePayment,
            'action' => $this->payhere->checkoutUrl(),
            'fields' => $this->payhere->checkoutFields(
                $onlinePayment,
                'Elite Cafe delivery order',
                route('shop.pay.return', $onlinePayment),
                route('shop.pay.cancel', $onlinePayment),
            ),
            'sandbox' => $this->payhere->isSandbox(),
        ]);
    }

    public function payReturn(OnlinePayment $onlinePayment): View
    {
        $this->authorizePayment($onlinePayment);

        $delivery = Delivery::where('online_payment_id', $onlinePayment->id)->first();
        if ($onlinePayment->status === 'success') {
            session()->forget('cart');
        }

        return view('shop.payment-status', [
            'payment' => $onlinePayment,
            'delivery' => $delivery,
            'sandbox' => $this->payhere->isSandbox(),
        ]);
    }

    public function payCancel(OnlinePayment $onlinePayment): RedirectResponse
    {
        $this->authorizePayment($onlinePayment);

        if ($onlinePayment->isPending()) {
            $onlinePayment->update(['status' => 'cancelled', 'status_message' => 'Cancelled by the customer']);
        }

        return redirect()->route('shop.cart')->with('error', 'Payment cancelled. Your cart is still here.');
    }

    public function payStatus(OnlinePayment $onlinePayment): JsonResponse
    {
        $this->authorizePayment($onlinePayment);

        return response()->json(['status' => $onlinePayment->status]);
    }

    /** SANDBOX ONLY: lets a localhost test finish without PayHere's server notification. */
    public function confirmSandbox(OnlinePayment $onlinePayment, OnlinePaymentService $payments): RedirectResponse
    {
        $this->authorizePayment($onlinePayment);
        abort_unless($this->payhere->isSandbox(), 403, 'Only available in sandbox mode.');

        $payments->apply($onlinePayment, 'success', PayHereController::sandboxDetails($onlinePayment));

        return redirect()->route('shop.pay.return', $onlinePayment);
    }

    // ---------- My orders and tracking ----------

    public function orders(Request $request): View
    {
        return view('shop.orders', [
            'deliveries' => $request->user('customer')->deliveries()->with('order.items')->latest()->paginate(10),
        ]);
    }

    public function showOrder(Request $request, Delivery $delivery): View
    {
        abort_unless((int) $delivery->customer_id === (int) $request->user('customer')->id, 404);

        return view('shop.order', ['delivery' => $delivery->load('order.items.menuItem')]);
    }

    public function orderStatus(Request $request, Delivery $delivery): JsonResponse
    {
        abort_unless((int) $delivery->customer_id === (int) $request->user('customer')->id, 404);

        return response()->json(['stage' => $delivery->load('order.items')->stage()]);
    }

    // ---------- Helpers ----------

    private function cartLines()
    {
        $cart = session('cart', []);
        $lines = $this->delivery->lines($cart);

        // Drop items that became unavailable
        if ($lines->count() !== count($cart)) {
            session(['cart' => $lines->pluck('quantity', 'menu_item_id')->all()]);
        }

        return $lines;
    }

    private function cartProblem($lines): ?string
    {
        if ($lines->isEmpty()) {
            return 'Your cart is empty.';
        }
        if ($lines->sum('line_total') < $this->delivery->minOrder()) {
            return 'The minimum order for delivery is Rs. '.number_format($this->delivery->minOrder(), 2).'.';
        }

        return null;
    }

    private function forget(array &$cart, int|string $id): void
    {
        unset($cart[$id]);
    }

    /** A customer may only see their own payments. */
    private function authorizePayment(OnlinePayment $payment): void
    {
        abort_unless(
            $payment->payable_type === 'Cart' && (int) ($payment->data['customer_id'] ?? 0) === (int) auth('customer')->id(),
            404
        );
    }
}
