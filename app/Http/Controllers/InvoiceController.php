<?php
// app/Http/Controllers/InvoiceController.php
namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices with filter.
     */
    public function index(Request $request)
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
        $customers = Customer::orderBy('name')->get();

        return view('invoices.index', compact('invoices', 'customers'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        return view('invoices.create', compact('customers'));
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(Request $request)
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

        return redirect()
            ->route('invoices.show', $invoice->id)
            ->with('success', 'Invoice created successfully!');
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load('customer');
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice)
    {
        $customers = Customer::orderBy('name')->get();
        return view('invoices.edit', compact('invoice', 'customers'));
    }

    /**
     * Update the specified invoice in storage.
     */
    public function update(Request $request, Invoice $invoice)
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

        return redirect()
            ->route('invoices.show', $invoice->id)
            ->with('success', 'Invoice updated successfully!');
    }

    /**
     * Remove the specified invoice from storage (soft delete).
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice deleted successfully!');
    }
}
