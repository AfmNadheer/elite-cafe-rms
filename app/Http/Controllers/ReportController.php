<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Expense;
use App\Models\Ingredient;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Management reports (FR-38, FR-39) with PDF export (FR-40).
 * Every report can be opened with ?export=pdf to download it as a PDF.
 */
class ReportController extends Controller
{
    private const PDF = 'Barryvdh\\DomPDF\\Facade\\Pdf';

    public function index()
    {
        return view('reports.index');
    }

    /** FR-38: daily or monthly sales for any date range. */
    public function sales(Request $request)
    {
        [$from, $to] = $this->range($request);
        $group = $request->query('group') === 'month' ? 'month' : 'day';
        $format = $group === 'month' ? '%Y-%m' : '%Y-%m-%d';

        $rows = Bill::whereBetween('created_at', [$from, $to])
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as period, COUNT(*) as bills,
                SUM(subtotal) as subtotal, SUM(service_charge) as service_charge,
                SUM(discount) as discount, SUM(total) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $methods = Payment::join('bills', 'bills.id', '=', 'payments.bill_id')
            ->whereBetween('bills.created_at', [$from, $to])
            ->selectRaw('payments.method, COUNT(*) as bills, SUM(bills.total) as amount')
            ->groupBy('payments.method')
            ->get()
            ->keyBy('method');

        return $this->output($request, 'sales', 'Sales Report', compact('from', 'to', 'group', 'rows', 'methods') + [
            'chart' => [
                'labels' => $rows->pluck('period'),
                'values' => $rows->pluck('total')->map(fn ($v) => (float) $v),
                'label' => 'Sales (Rs.)',
            ],
        ]);
    }

    /** FR-39: most popular menu items. */
    public function items(Request $request)
    {
        [$from, $to] = $this->range($request);

        $rows = OrderItem::join('bills', 'bills.order_id', '=', 'order_items.order_id')
            ->join('menu_items', 'menu_items.id', '=', 'order_items.menu_item_id')
            ->join('categories', 'categories.id', '=', 'menu_items.category_id')
            ->where('order_items.status', '!=', 'cancelled')
            ->whereBetween('bills.created_at', [$from, $to])
            ->selectRaw('menu_items.name, categories.name as category,
                SUM(order_items.quantity) as qty, SUM(order_items.quantity * order_items.unit_price) as revenue')
            ->groupBy('menu_items.id', 'menu_items.name', 'categories.name')
            ->orderByDesc('qty')
            ->get();

        return $this->output($request, 'items', 'Popular Items Report', compact('from', 'to', 'rows') + [
            'chart' => [
                'labels' => $rows->take(10)->pluck('name'),
                'values' => $rows->take(10)->pluck('qty')->map(fn ($v) => (int) $v),
                'label' => 'Quantity sold (top 10)',
                'type' => 'bar',
            ],
        ]);
    }

    /** FR-39: current stock and its value (last purchase price). */
    public function inventory(Request $request)
    {
        $costs = $this->lastCosts();
        $rows = Ingredient::orderBy('name')->get()->map(function ($i) use ($costs) {
            $i->unit_cost = (float) ($costs[$i->id] ?? 0);
            $i->value = (float) $i->quantity * $i->unit_cost;

            return $i;
        });

        return $this->output($request, 'inventory', 'Inventory Report', ['rows' => $rows, 'noRange' => true]);
    }

    /** FR-39: wastage and stock removed, with estimated cost. */
    public function wastage(Request $request)
    {
        [$from, $to] = $this->range($request);
        $costs = $this->lastCosts();

        $rows = StockAdjustment::with('ingredient')
            ->where('quantity', '<', 0)
            ->whereBetween('adjusted_at', [$from, $to])
            ->orderBy('adjusted_at')
            ->get()
            ->map(function ($a) use ($costs) {
                $a->cost = abs((float) $a->quantity) * (float) ($costs[$a->ingredient_id] ?? 0);

                return $a;
            });

        return $this->output($request, 'wastage', 'Wastage Report', compact('from', 'to', 'rows'));
    }

    /** FR-39: expenses by category, with an income vs. spending summary. */
    public function expenses(Request $request)
    {
        [$from, $to] = $this->range($request);

        $expenses = Expense::whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('expense_date')
            ->get();

        $summary = [
            'sales' => (float) Bill::whereBetween('created_at', [$from, $to])->sum('total'),
            'purchases' => (float) Purchase::whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])->sum('total'),
            'expenses' => (float) $expenses->sum('amount'),
        ];
        $summary['net'] = $summary['sales'] - $summary['purchases'] - $summary['expenses'];

        $byCategory = $expenses->groupBy('category')->map->sum('amount')->sortDesc();

        return $this->output($request, 'expenses', 'Expenses & Profit Report', compact('from', 'to', 'expenses', 'summary', 'byCategory') + [
            'chart' => [
                'labels' => $byCategory->keys()->values(),
                'values' => $byCategory->values()->map(fn ($v) => (float) $v),
                'label' => 'Expenses (Rs.)',
                'type' => 'bar',
            ],
        ]);
    }

    /** Date range from the filter (default: this month). */
    private function range(Request $request): array
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return [
            $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : now()->startOfMonth(),
            $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : now()->endOfDay(),
        ];
    }

    /** Latest purchase unit cost for each ingredient. */
    private function lastCosts(): Collection
    {
        return PurchaseItem::whereIn('id', PurchaseItem::selectRaw('MAX(id)')->groupBy('ingredient_id'))
            ->pluck('unit_cost', 'ingredient_id');
    }

    /** Show the report on screen, or download it as a PDF (FR-40). */
    private function output(Request $request, string $report, string $title, array $data)
    {
        $data += ['title' => $title, 'report' => $report, 'partial' => 'reports.partials.'.$report];

        if ($request->query('export') === 'pdf') {
            if (! class_exists(self::PDF)) {
                return back()->with('error', 'PDF export needs the dompdf package. Run: composer require barryvdh/laravel-dompdf');
            }

            $pdf = call_user_func([self::PDF, 'loadView'], 'reports.pdf', $data)->setPaper('a4');

            return $pdf->download(Str::slug($title).'-'.now()->format('Ymd-His').'.pdf');
        }

        return view('reports.show', $data);
    }
}
