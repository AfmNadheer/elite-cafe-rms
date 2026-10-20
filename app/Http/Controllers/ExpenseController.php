<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Other expenses such as utilities and gas (FR-36).
 */
class ExpenseController extends Controller
{
    public const CATEGORIES = ['Utilities', 'Gas', 'Rent', 'Salaries', 'Maintenance', 'Transport', 'Other'];

    public function index(Request $request): View
    {
        $request->validate(['month' => ['nullable', 'date_format:Y-m']]);
        $month = $request->input('month', now()->format('Y-m'));

        $query = Expense::with('user')
            ->where('expense_date', 'like', $month.'%')
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category));

        return view('expenses.index', [
            'expenses' => (clone $query)->orderByDesc('expense_date')->orderByDesc('id')->paginate(25)->withQueryString(),
            'total' => (clone $query)->sum('amount'),
            'month' => $month,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function create(): View
    {
        return view('expenses.form', ['expense' => new Expense(['expense_date' => today()]), 'categories' => self::CATEGORIES]);
    }

    public function store(Request $request): RedirectResponse
    {
        Expense::create($request->validate($this->rules()) + ['user_id' => $request->user()->id]);

        return redirect()->route('expenses.index')->with('success', 'Expense saved.');
    }

    public function edit(Expense $expense): View
    {
        return view('expenses.form', ['expense' => $expense, 'categories' => self::CATEGORIES]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $expense->update($request->validate($this->rules()));

        return redirect()->route('expenses.index', ['month' => $expense->expense_date->format('Y-m')])->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return back()->with('success', 'Expense deleted.');
    }

    private function rules(): array
    {
        return [
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'amount' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
