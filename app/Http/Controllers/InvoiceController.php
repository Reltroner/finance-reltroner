<?php
// app/Http/Controllers/InvoiceController.php
namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices, with optional filter by customer, status, date, or invoice_number.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with('customer');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->input('invoice_number') . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        $invoices = $query->orderByDesc('date')->paginate(20);

        return response()->json($invoices);
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|max:100|unique:invoices,invoice_number',
            'customer_id'    => 'required|exists:customers,id',
            'date'           => 'required|date',
            'due_date'       => 'nullable|date|after_or_equal:date',
            'total_amount'   => 'required|numeric|min:0',
            'status'         => 'required|in:draft,sent,paid,overdue,cancelled',
            'description'    => 'nullable|string|max:1000',
        ]);

        $invoice = Invoice::create($validated);

        return response()->json([
            'message' => 'Invoice created successfully!',
            'data'    => $invoice->load('customer'),
        ], 201);
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load('customer');
        return response()->json($invoice);
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'invoice_number' => 'sometimes|required|string|max:100|unique:invoices,invoice_number,' . $invoice->id,
            'customer_id'    => 'sometimes|required|exists:customers,id',
            'date'           => 'sometimes|required|date',
            'due_date'       => 'nullable|date|after_or_equal:date',
            'total_amount'   => 'sometimes|required|numeric|min:0',
            'status'         => 'sometimes|required|in:draft,sent,paid,overdue,cancelled',
            'description'    => 'nullable|string|max:1000',
        ]);

        $invoice->update($validated);

        return response()->json([
            'message' => 'Invoice updated successfully!',
            'data'    => $invoice->load('customer'),
        ]);
    }

    /**
     * Remove the specified invoice from storage (soft delete).
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        $invoice->delete();

        return response()->json([
            'message' => 'Invoice deleted successfully!',
        ]);
    }
}
