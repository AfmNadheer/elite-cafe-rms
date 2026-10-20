<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\RestaurantTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Orders taken by waiters (FR-13 to FR-18).
 */
class OrderController extends Controller
{
    /** Open orders, newest first. */
    public function index(): View
    {
        $orders = Order::with(['table', 'waiter', 'items'])
            ->where('status', 'open')
            ->latest()
            ->get();

        return view('orders.index', ['orders' => $orders]);
    }

    /** New order screen. Opened from the table map with ?table=ID. */
    public function create(Request $request): View|RedirectResponse
    {
        $selectedTable = $request->filled('table') ? RestaurantTable::find($request->table) : null;

        // Table already has an open order -> add items to that order instead
        if ($selectedTable && $selectedTable->openOrder) {
            return redirect()->route('orders.add-items', $selectedTable->openOrder);
        }

        return view('orders.form', [
            'order' => null,
            'categories' => $this->menu(),
            'tables' => RestaurantTable::whereIn('status', ['free', 'reserved'])->orderBy('table_no')->get(),
            'selectedTable' => $selectedTable,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'order_type' => ['required', 'in:dine_in,takeaway'],
            'table_id' => ['nullable', 'required_if:order_type,dine_in', 'exists:restaurant_tables,id'],
        ], [
            'table_id.required_if' => 'Please select a table for a dine-in order.',
        ]);

        $lines = $this->selectedItems($request);
        $tableId = $request->order_type === 'dine_in' ? (int) $request->table_id : null;

        // Someone else opened an order on this table a moment ago
        if ($tableId && ($open = Order::where('table_id', $tableId)->where('status', 'open')->first())) {
            return redirect()->route('orders.add-items', $open)
                ->with('error', 'This table already has an open order. Add the items to it here.');
        }

        $order = DB::transaction(function () use ($request, $lines, $tableId) {
            $order = Order::create([
                'table_id' => $tableId,
                'user_id' => $request->user()->id,
                'order_type' => $request->order_type,
                'status' => 'open',
            ]);

            $this->addLines($order, $lines);

            if ($tableId) {
                RestaurantTable::whereKey($tableId)->update(['status' => 'occupied']);   // FR-11
            }

            return $order;
        });

        return redirect()->route('orders.show', $order)->with('success', 'Order sent to the kitchen.');
    }

    public function show(Order $order): View
    {
        $order->load(['items.menuItem', 'table', 'waiter']);

        return view('orders.show', ['order' => $order]);
    }

    /** Add more items to an open order (FR-15). */
    public function addItems(Order $order): View|RedirectResponse
    {
        if ($order->status !== 'open') {
            return redirect()->route('orders.show', $order)->with('error', 'This order is already closed.');
        }

        return view('orders.form', [
            'order' => $order->load('table'),
            'categories' => $this->menu(),
            'tables' => collect(),
            'selectedTable' => $order->table,
        ]);
    }

    public function storeItems(Request $request, Order $order): RedirectResponse
    {
        if ($order->status !== 'open') {
            return redirect()->route('orders.show', $order)->with('error', 'This order is already closed.');
        }

        $lines = $this->selectedItems($request);
        DB::transaction(fn () => $this->addLines($order, $lines));

        return redirect()->route('orders.show', $order)->with('success', 'New items sent to the kitchen.');
    }

    /** Menu grouped by category for the order screen. */
    private function menu()
    {
        return Category::with(['menuItems' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->filter(fn ($category) => $category->menuItems->isNotEmpty());
    }

    /**
     * Read the quantities typed on the order screen.
     * Returns [menu_item_id => ['item' => MenuItem, 'quantity' => n, 'notes' => '...']].
     */
    private function selectedItems(Request $request): array
    {
        $request->validate([
            'qty' => ['required', 'array'],
            'qty.*' => ['nullable', 'integer', 'min:0', 'max:50'],
            'notes' => ['nullable', 'array'],
            'notes.*' => ['nullable', 'string', 'max:255'],
        ]);

        $quantities = array_filter($request->input('qty', []), fn ($q) => (int) $q > 0);

        if (empty($quantities)) {
            throw ValidationException::withMessages(['qty' => 'Please add at least one item to the order.']);
        }

        $items = MenuItem::whereIn('id', array_keys($quantities))->get()->keyBy('id');

        // Block items that are unavailable (FR-18)
        $unavailable = $items->where('is_available', false)->pluck('name');
        if ($unavailable->isNotEmpty()) {
            throw ValidationException::withMessages([
                'qty' => 'Not available now: '.$unavailable->implode(', ').'. Please choose another item.',
            ]);
        }

        $lines = [];
        foreach ($quantities as $id => $quantity) {
            if ($items->has($id)) {
                $lines[$id] = [
                    'item' => $items[$id],
                    'quantity' => (int) $quantity,
                    'notes' => $request->input("notes.$id"),
                ];
            }
        }

        return $lines;
    }

    /** Save order lines with the price at the time of sale (FR-09). */
    private function addLines(Order $order, array $lines): void
    {
        foreach ($lines as $line) {
            $order->items()->create([
                'menu_item_id' => $line['item']->id,
                'quantity' => $line['quantity'],
                'unit_price' => $line['item']->price,
                'notes' => $line['notes'],
                'status' => 'pending',
            ]);
        }
    }
}
