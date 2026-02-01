<?php
// app/Http/Controllers/InvoiceController.php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * READ-ONLY list of invoices
     */
    public function index(Request $request)
    {
        $query = Invoice::with('customer');

        $query
            ->when($request->filled('customer_id'), fn($q) =>
                $q->where('customer_id', $request->customer_id)
            )
            ->when($request->filled('status'), fn($q) =>
                $q->where('status', $request->status)
            )
            ->when($request->filled('invoice_number'), fn($q) =>
                $q->where('invoice_number', 'like', '%' . $request->invoice_number . '%')
            )
            ->when($request->filled('date'), fn($q) =>
                $q->whereDate('date', $request->date)
            );

        $invoices = $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('invoices.index', [
            'invoices'  => $invoices,
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }

    /**
     * READ-ONLY show
     */
    public function show(Invoice $invoice)
    {
        $invoice->load('customer');

        return view('invoices.show', compact('invoice'));
    }

    /**
     * UI-only create page
     * Form TIDAK BOLEH submit ke controller ini
     */
    public function create()
    {
        return view('invoices.create', [
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }

    /**
     * UI-only edit page
     * Mutasi invoice HARUS lewat InvoiceService
     */
    public function edit(Invoice $invoice)
    {
        $invoice->load('customer');

        return view('invoices.edit', [
            'invoice'   => $invoice,
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }

    /**
     * ================================
     * WRITE OPERATIONS — FORBIDDEN
     * ================================
     * Semua mutasi invoice WAJIB lewat Service Layer
     */

    public function store()
    {
        abort(403, 'Direct invoice creation is forbidden. Use InvoiceService.');
    }

    public function update()
    {
        abort(403, 'Direct invoice mutation is forbidden. Use InvoiceService.');
    }

    public function destroy()
    {
        abort(403, 'Direct invoice deletion is forbidden. Use InvoiceService.');
    }
}
