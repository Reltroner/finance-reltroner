<?php
// app/Http/Controllers/BudgetController.php
namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Account;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    /**
     * Display a listing of budgets in Blade view, with optional filters.
     */
    public function index(Request $request)
    {
        $query = Budget::with('account');

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->input('account_id'));
        }
        if ($request->filled('year')) {
            $query->where('year', $request->input('year'));
        }
        if ($request->filled('month')) {
            $query->where('month', $request->input('month'));
        }

        $budgets = $query->orderBy('year')->orderBy('month')->paginate(20);
        $accounts = Account::all();

        return view('budgets.index', compact('budgets', 'accounts'));
    }

    /**
     * Show the form for creating a new budget.
     */
    public function create()
    {
        $accounts = Account::all();
        return view('budgets.create', compact('accounts'));
    }

    /**
     * Store a newly created budget.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'year'       => 'required|integer|min:2000|max:2100',
            'month'      => 'required|integer|min:1|max:12',
            'amount'     => 'required|numeric|min:0',
            'actual'     => 'nullable|numeric|min:0',
        ]);

        // SET DEFAULT VALUE IF NULL
        $validated['actual'] = $validated['actual'] ?? 0;

        Budget::create($validated);

        return redirect()->route('budgets.index')->with('success', 'Budget created successfully!');
    }


    /**
     * Show the form for editing the specified budget.
     */
    public function edit(Budget $budget)
    {
        $accounts = Account::all();
        return view('budgets.edit', compact('budget', 'accounts'));
    }

    /**
     * Update the specified budget.
     */
    public function update(Request $request, Budget $budget)
    {
        $validated = $request->validate([
            'account_id' => 'sometimes|required|exists:accounts,id',
            'year'       => 'sometimes|required|integer|min:2000|max:2100',
            'month'      => 'sometimes|required|integer|min:1|max:12',
            'amount'     => 'sometimes|required|numeric|min:0',
            'actual'     => 'sometimes|nullable|numeric|min:0',
        ]);

        $validated['actual'] = $validated['actual'] ?? 0;

        $budget->update($validated);

        return redirect()->route('budgets.index')->with('success', 'Budget updated successfully!');
    }

    /**
     * Remove the specified budget.
     */
    public function destroy(Budget $budget)
    {
        $budget->delete();
        return redirect()->route('budgets.index')->with('success', 'Budget deleted successfully!');
    }
}
