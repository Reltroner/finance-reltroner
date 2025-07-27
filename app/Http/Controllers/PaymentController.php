<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments, with optional filters.
     */
    public function index(Request $request)
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

        // Data tambahan untuk filter & relasi select
        $invoices = Invoice::all();
        $vendors = Vendor::all();
        $customers = Customer::all();
        $transactions = Transaction::all();

        return view('payments.index', compact('payments', 'invoices', 'vendors', 'customers', 'transactions'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create()
    {
        $invoices = Invoice::all();
        $vendors = Vendor::all();
        $customers = Customer::all();
        $transactions = Transaction::all();
        return view('payments.create', compact('invoices', 'vendors', 'customers', 'transactions'));
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
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

        return redirect()->route('payments.index')
            ->with('success', 'Payment created successfully!');
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        $payment->load(['invoice', 'vendor', 'customer', 'transaction']);
        return view('payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit(Payment $payment)
    {
        $invoices = Invoice::all();
        $vendors = Vendor::all();
        $customers = Customer::all();
        $transactions = Transaction::all();
        return view('payments.edit', compact('payment', 'invoices', 'vendors', 'customers', 'transactions'));
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, Payment $payment)
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

        return redirect()->route('payments.index')
            ->with('success', 'Payment updated successfully!');
    }

    /**
     * Remove the specified payment from storage (soft delete).
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('payments.index')
            ->with('success', 'Payment deleted successfully!');
    }
}
