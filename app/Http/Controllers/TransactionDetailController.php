<?php
// app/Http/Controllers/TransactionDetailController.php
namespace App\Http\Controllers;

use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TransactionDetailController extends Controller
{
    /**
     * Display a listing of transaction details, with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = TransactionDetail::with(['transaction', 'account', 'costCenter']);

        if ($request->filled('transaction_id')) {
            $query->where('transaction_id', $request->input('transaction_id'));
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->input('account_id'));
        }
        if ($request->filled('cost_center_id')) {
            $query->where('cost_center_id', $request->input('cost_center_id'));
        }
        if ($request->filled('min_debit')) {
            $query->where('debit', '>=', $request->input('min_debit'));
        }
        if ($request->filled('max_debit')) {
            $query->where('debit', '<=', $request->input('max_debit'));
        }
        if ($request->filled('min_credit')) {
            $query->where('credit', '>=', $request->input('min_credit'));
        }
        if ($request->filled('max_credit')) {
            $query->where('credit', '<=', $request->input('max_credit'));
        }

        $details = $query->orderByDesc('id')->paginate(20);

        return response()->json($details);
    }

    /**
     * Store a newly created transaction detail.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transaction_id'   => 'required|exists:transactions,id',
            'account_id'       => 'required|exists:accounts,id',
            'description'      => 'nullable|string|max:500',
            'debit'            => 'required|numeric|min:0',
            'credit'           => 'required|numeric|min:0',
            'cost_center_id'   => 'nullable|exists:cost_centers,id',
        ]);

        $detail = TransactionDetail::create($validated);

        return response()->json([
            'message' => 'Transaction detail created successfully!',
            'data'    => $detail->load(['transaction', 'account', 'costCenter']),
        ], 201);
    }

    /**
     * Display the specified transaction detail.
     */
    public function show(TransactionDetail $transactionDetail): JsonResponse
    {
        $transactionDetail->load(['transaction', 'account', 'costCenter']);
        return response()->json($transactionDetail);
    }

    /**
     * Update the specified transaction detail.
     */
    public function update(Request $request, TransactionDetail $transactionDetail): JsonResponse
    {
        $validated = $request->validate([
            'transaction_id'   => 'sometimes|required|exists:transactions,id',
            'account_id'       => 'sometimes|required|exists:accounts,id',
            'description'      => 'nullable|string|max:500',
            'debit'            => 'sometimes|required|numeric|min:0',
            'credit'           => 'sometimes|required|numeric|min:0',
            'cost_center_id'   => 'nullable|exists:cost_centers,id',
        ]);

        $transactionDetail->update($validated);

        return response()->json([
            'message' => 'Transaction detail updated successfully!',
            'data'    => $transactionDetail->load(['transaction', 'account', 'costCenter']),
        ]);
    }

    /**
     * Remove the specified transaction detail from storage (soft delete).
     */
    public function destroy(TransactionDetail $transactionDetail): JsonResponse
    {
        $transactionDetail->delete();

        return response()->json([
            'message' => 'Transaction detail deleted successfully!',
        ]);
    }
}
// End of app/Http/Controllers/TransactionDetailController.php