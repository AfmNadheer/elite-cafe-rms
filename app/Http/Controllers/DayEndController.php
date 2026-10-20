<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Day-end summary (FR-28): sales by payment method, number of bills and discounts.
 */
class DayEndController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['date' => ['nullable', 'date', 'before_or_equal:today']]);
        $date = $request->filled('date') ? Carbon::parse($request->date) : today();

        $bills = Bill::whereDate('created_at', $date);

        $summary = [
            'bills' => (clone $bills)->count(),
            'subtotal' => (float) (clone $bills)->sum('subtotal'),
            'service_charge' => (float) (clone $bills)->sum('service_charge'),
            'discount' => (float) (clone $bills)->sum('discount'),
            'discount_bills' => (clone $bills)->where('discount', '>', 0)->count(),
            'total' => (float) (clone $bills)->sum('total'),
        ];

        // Sales by payment method (bill totals, not cash handed in)
        $byMethod = Payment::query()
            ->join('bills', 'bills.id', '=', 'payments.bill_id')
            ->whereDate('bills.created_at', $date)
            ->selectRaw('payments.method, COUNT(*) as bills, SUM(bills.total) as amount')
            ->groupBy('payments.method')
            ->get()
            ->keyBy('method');

        $cancelled = OrderItem::where('status', 'cancelled')
            ->whereDate('updated_at', $date)
            ->get();

        $discountBills = Bill::with(['approver', 'cashier'])
            ->whereDate('created_at', $date)
            ->where('discount', '>', 0)
            ->get();

        return view('day-end.index', [
            'date' => $date,
            'summary' => $summary,
            'byMethod' => $byMethod,
            'cancelledCount' => $cancelled->sum('quantity'),
            'cancelledValue' => $cancelled->sum(fn ($i) => $i->quantity * $i->unit_price),
            'discountBills' => $discountBills,
            'openOrders' => $date->isToday() ? Order::where('status', 'open')->count() : 0,
        ]);
    }
}
