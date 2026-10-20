<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * List of saved bills and the printable bill (FR-27).
 */
class BillController extends Controller
{
    public function index(Request $request): View
    {
        $date = $this->date($request);

        $bills = Bill::with(['order.table', 'cashier', 'payments'])
            ->whereDate('created_at', $date)
            ->when($request->filled('search'), fn ($q) => $q->where('bill_no', 'like', '%'.$request->search.'%'))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('bills.index', ['bills' => $bills, 'date' => $date]);
    }

    /** Printable bill (80 mm receipt). */
    public function show(Bill $bill): View
    {
        $bill->load(['order.items.menuItem', 'order.table', 'order.waiter', 'cashier', 'approver', 'payments']);

        return view('bills.show', ['bill' => $bill]);
    }

    private function date(Request $request): Carbon
    {
        $request->validate(['date' => ['nullable', 'date', 'before_or_equal:today']]);

        return $request->filled('date') ? Carbon::parse($request->date) : today();
    }
}
