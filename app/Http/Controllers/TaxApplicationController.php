<?php
// app/Http/Controllers/TaxApplicationController.php
namespace App\Http\Controllers;

use App\Models\TaxApplication;
use App\Models\Tax;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaxApplicationController extends Controller
{
    /**
     * Display a listing of tax applications, with optional filter by tax, transaction, or amount.
     */
    public function index(Request $request): JsonResponse
    {
        $query = TaxApplication::with(['tax', 'transaction']);

        if ($request->filled('tax_id')) {
            $query->where('tax_id', $request->input('tax_id'));
        }
        if ($request->filled('transaction_id')) {
            $query->where('transaction_id', $request->input('transaction_id'));
        }
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->input('min_amount'));
        }
        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->input('max_amount'));
        }

        $taxApps = $query->orderByDesc('id')->paginate(20);

        return response()->json($taxApps);
    }

    /**
     * Store a newly created tax application.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tax_id'        => 'required|exists:taxes,id',
            'transaction_id'=> 'required|exists:transactions,id',
            'amount'        => 'required|numeric|min:0',
        ]);

        $taxApp = TaxApplication::create($validated);

        return response()->json([
            'message' => 'Tax application created successfully!',
            'data'    => $taxApp->load(['tax', 'transaction']),
        ], 201);
    }

    /**
     * Display the specified tax application.
     */
    public function show(TaxApplication $taxApplication): JsonResponse
    {
        $taxApplication->load(['tax', 'transaction']);
        return response()->json($taxApplication);
    }

    /**
     * Update the specified tax application.
     */
    public function update(Request $request, TaxApplication $taxApplication): JsonResponse
    {
        $validated = $request->validate([
            'tax_id'        => 'sometimes|required|exists:taxes,id',
            'transaction_id'=> 'sometimes|required|exists:transactions,id',
            'amount'        => 'sometimes|required|numeric|min:0',
        ]);

        $taxApplication->update($validated);

        return response()->json([
            'message' => 'Tax application updated successfully!',
            'data'    => $taxApplication->load(['tax', 'transaction']),
        ]);
    }

    /**
     * Remove the specified tax application from storage (soft delete).
     */
    public function destroy(TaxApplication $taxApplication): JsonResponse
    {
        $taxApplication->delete();

        return response()->json([
            'message' => 'Tax application deleted successfully!',
        ]);
    }
}
