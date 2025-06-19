<?php
// app/Http/Controllers/BudgetController.php
namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BudgetController extends Controller
{
    /**
     * Display a listing of budgets, with optional filtering by account, year, or month.
     */
    public function index(Request $request): JsonResponse
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

        return response()->json($budgets);
    }

    /**
     * Store a newly created budget.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'year'       => 'required|integer|min:2000|max:2100',
            'month'      => 'required|integer|min:1|max:12',
            'amount'     => 'required|numeric|min:0',
            'actual'     => 'nullable|numeric|min:0',
        ]);

        $budget = Budget::create($validated);

        return response()->json([
            'message' => 'Budget created successfully!',
            'data'    => $budget,
        ], 201);
    }

    /**
     * Display the specified budget.
     */
    public function show(Budget $budget): JsonResponse
    {
        $budget->load('account');
        return response()->json($budget);
    }

    /**
     * Update the specified budget.
     */
    public function update(Request $request, Budget $budget): JsonResponse
    {
        $validated = $request->validate([
            'account_id' => 'sometimes|required|exists:accounts,id',
            'year'       => 'sometimes|required|integer|min:2000|max:2100',
            'month'      => 'sometimes|required|integer|min:1|max:12',
            'amount'     => 'sometimes|required|numeric|min:0',
            'actual'     => 'sometimes|nullable|numeric|min:0',
        ]);

        $budget->update($validated);

        return response()->json([
            'message' => 'Budget updated successfully!',
            'data'    => $budget,
        ]);
    }

    /**
     * Remove the specified budget from storage (soft delete).
     */
    public function destroy(Budget $budget): JsonResponse
    {
        $budget->delete();

        return response()->json([
            'message' => 'Budget deleted successfully!'
        ]);
    }
}
