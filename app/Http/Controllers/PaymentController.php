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
     * READ-ONLY list of payments
     * Tidak ada mutasi data (STEP 5.2B.4)
     */
    public function index(Request $request)
    {
        $query = Payment::with([
            'invoice',
            'vendor',
            'customer',
            'transaction',
        ]);

        $query
            ->when($request->filled('invoice_id'), fn($q) => $q->where('invoice_id', $request->invoice_id))
            ->when($request->filled('vendor_id'), fn($q) => $q->where('vendor_id', $request->vendor_id))
            ->when($request->filled('customer_id'), fn($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('date'), fn($q) => $q->whereDate('date', $request->date));

        $payments = $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // data untuk filter UI
        return view('payments.index', [
            'payments'     => $payments,
            'invoices'     => Invoice::select('id')->limit(200)->get(),
            'vendors'      => Vendor::select('id','name')->get(),
            'customers'    => Customer::select('id','name')->get(),
            'transactions' => Transaction::select('id','journal_no')->limit(200)->get(),
        ]);
    }

    /**
     * READ-ONLY show
     */
    public function show(Payment $payment)
    {
        $payment->load([
            'invoice',
            'vendor',
            'customer',
            'transaction',
        ]);

        return view('payments.show', compact('payment'));
    }

    /**
     * UI-only create page
     * FORM TIDAK BOLEH submit ke controller ini
     */
    public function create()
    {
        return view('payments.create', [
            'invoices'     => Invoice::all(),
            'vendors'      => Vendor::all(),
            'customers'    => Customer::all(),
            'transactions' => Transaction::all(),
        ]);
    }

    /**
     * UI-only edit page
     * Mutasi payment HARUS lewat PaymentService
     */
    public function edit(Payment $payment)
    {
        $payment->load(['invoice', 'vendor', 'customer', 'transaction']);

        return view('payments.edit', [
            'payment'      => $payment,
            'invoices'     => Invoice::all(),
            'vendors'      => Vendor::all(),
            'customers'    => Customer::all(),
            'transactions' => Transaction::all(),
        ]);
    }

    /**
     * ================================
     * WRITE OPERATIONS — FORBIDDEN
     * ================================
     * Semua mutasi pembayaran WAJIB lewat Service Layer
     */

    public function store()
    {
        abort(403, 'Direct payment creation is forbidden. Use PaymentService.');
    }

    public function update()
    {
        abort(403, 'Direct payment mutation is forbidden. Use PaymentService.');
    }

    public function destroy()
    {
        abort(403, 'Direct payment deletion is forbidden. Use PaymentService.');
    }
}
