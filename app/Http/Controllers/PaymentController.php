<?php
// app/Http/Controllers/PaymentController.php
namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments, with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with(['invoice', 'vendor', 'customer', 'transaction']);

        if ($request->filled('invoice_id')) {
            $query->where('invoice_id', $request->input('invoice_id'));
        }
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->input('vendor_id'));
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        $payments = $query->orderByDesc('date')->paginate(20);

        return response()->json($payments);
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id'     => 'nullable|exists:invoices,id',
            'vendor_id'      => 'nullable|exists:vendors,id',
            'customer_id'    => 'nullable|exists:customers,id',
            'transaction_id' => 'nullable|exists:transactions,id',
            'amount'         => 'required|numeric|min:0',
            'date'           => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'status'         => 'required|in:pending,cleared,failed',
            'description'    => 'nullable|string|max:1000',
        ]);

        $payment = Payment::create($validated);

        return response()->json([
            'message' => 'Payment created successfully!',
            'data'    => $payment->load(['invoice', 'vendor', 'customer', 'transaction']),
        ], 201);
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment): JsonResponse
    {
        $payment->load(['invoice', 'vendor', 'customer', 'transaction']);
        return response()->json($payment);
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id'     => 'nullable|exists:invoices,id',
            'vendor_id'      => 'nullable|exists:vendors,id',
            'customer_id'    => 'nullable|exists:customers,id',
            'transaction_id' => 'nullable|exists:transactions,id',
            'amount'         => 'sometimes|required|numeric|min:0',
            'date'           => 'sometimes|required|date',
            'payment_method' => 'nullable|string|max:50',
            'status'         => 'sometimes|required|in:pending,cleared,failed',
            'description'    => 'nullable|string|max:1000',
        ]);

        $payment->update($validated);

        return response()->json([
            'message' => 'Payment updated successfully!',
            'data'    => $payment->load(['invoice', 'vendor', 'customer', 'transaction']),
        ]);
    }

    /**
     * Remove the specified payment from storage (soft delete).
     */
    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully!',
        ]);
    }
}
