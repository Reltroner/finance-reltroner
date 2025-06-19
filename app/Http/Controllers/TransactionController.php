<?php
// app/Http/Controllers/TransactionController.php
namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions, with optional filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::with(['currency', 'details', 'attachments', 'taxApplications']);

        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->input('currency_id'));
        }
        if ($request->filled('reference')) {
            $query->where('reference', 'like', '%' . $request->input('reference') . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        }
        if ($request->filled('min_total')) {
            $query->where('total_debit', '>=', $request->input('min_total'));
        }
        if ($request->filled('max_total')) {
            $query->where('total_debit', '<=', $request->input('max_total'));
        }

        $transactions = $query->orderByDesc('date')->paginate(20);

        return response()->json($transactions);
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference'    => 'nullable|string|max:255|unique:transactions,reference',
            'description'  => 'nullable|string',
            'date'         => 'required|date',
            'currency_id'  => 'required|exists:currencies,id',
            'total_debit'  => 'required|numeric|min:0',
            'total_credit' => 'required|numeric|min:0',
            'created_by'   => 'nullable|integer',
        ]);

        $transaction = Transaction::create($validated);

        return response()->json([
            'message' => 'Transaction created successfully!',
            'data'    => $transaction->load(['currency']),
        ], 201);
    }

    /**
     * Display the specified transaction.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        $transaction->load(['currency', 'details', 'attachments', 'taxApplications']);
        return response()->json($transaction);
    }

    /**
     * Update the specified transaction.
     */
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        $validated = $request->validate([
            'reference'    => 'sometimes|nullable|string|max:255|unique:transactions,reference,' . $transaction->id,
            'description'  => 'nullable|string',
            'date'         => 'sometimes|required|date',
            'currency_id'  => 'sometimes|required|exists:currencies,id',
            'total_debit'  => 'sometimes|required|numeric|min:0',
            'total_credit' => 'sometimes|required|numeric|min:0',
            'created_by'   => 'nullable|integer',
        ]);

        $transaction->update($validated);

        return response()->json([
            'message' => 'Transaction updated successfully!',
            'data'    => $transaction->load(['currency']),
        ]);
    }

    /**
     * Remove the specified transaction from storage (soft delete).
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        $transaction->delete();

        return response()->json([
            'message' => 'Transaction deleted successfully!',
        ]);
    }
}
// End of app/Http/Controllers/TransactionController.php