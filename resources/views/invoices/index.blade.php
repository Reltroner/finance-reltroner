{{-- resources/views/invoices/index.blade.php --}}

@extends('layouts.dashboard')
@section('content')

<header class="mb-3">
    <a href="#" class="burger-btn d-block d-xl-none">
        <i class="bi bi-justify fs-3"></i>
    </a>
</header>

@if (session('success')) 
<div class="alert alert-success alert-dismissible fade show" role="alert"> 
    <strong>Success:</strong> {{ session('success') }} 
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> 
</div>
@endif

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Invoices</h3>
                <p class="text-subtitle text-muted">Manage and review all customer invoices here</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Invoices</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label for="customer_id" class="form-label">Customer</label>
                <select name="customer_id" id="customer_id" class="form-select">
                    <option value="">All</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All</option>
                    @foreach (['draft','sent','paid','overdue','cancelled'] as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label for="invoice_number" class="form-label">Invoice #</label>
                <input type="text" name="invoice_number" id="invoice_number" class="form-control"
                    value="{{ request('invoice_number') }}" placeholder="INV-2024-001">
            </div>
            <div class="col-6 col-md-2 d-flex align-items-end">
                <button class="btn btn-outline-primary w-100" type="submit">Filter</button>
            </div>
        </div>
    </form>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Invoice List</h5>
                <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">Add Invoice</a>
            </div>
            <div class="card-body">
                <!-- Desktop Table (shown ≥576px) -->
                <div class="table-responsive d-none d-sm-block">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Due</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invoices as $invoice)
                                <tr>
                                    <td>{{ $loop->iteration + ($invoices->perPage() * ($invoices->currentPage() - 1)) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($invoice->date)->format('d M Y') }}</td>
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td>{{ $invoice->customer->name ?? '-' }}</td>
                                    <td>{{ number_format($invoice->total_amount, 2) }}</td>
                                    <td>
                                        @php
                                            $badge = [
                                                'draft' => 'secondary',
                                                'sent' => 'info',
                                                'paid' => 'success',
                                                'overdue' => 'danger',
                                                'cancelled' => 'dark',
                                            ][$invoice->status] ?? 'light';
                                        @endphp
                                        <span class="badge bg-{{ $badge }}">{{ ucfirst($invoice->status) }}</span>
                                    </td>
                                    <td>
                                        {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '-' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-info btn-sm">View</a>
                                        <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No invoice records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Mobile Card/List (shown <576px) -->
                <div class="d-block d-sm-none">
                    @forelse ($invoices as $invoice)
                        <div class="border rounded mb-2 px-2 py-2 bg-white shadow-sm">
                            <div class="fw-bold mb-1">{{ $invoice->invoice_number }}</div>
                            <div class="mb-1 small text-muted">
                                {{ $invoice->customer->name ?? '-' }}<br>
                                <span>{{ \Carbon\Carbon::parse($invoice->date)->format('d M Y') }}</span>
                            </div>
                            <div>
                                <span class="small">Total:</span>
                                <strong>{{ number_format($invoice->total_amount, 2) }}</strong>
                                <span class="float-end">
                                    @php
                                        $badge = [
                                            'draft' => 'secondary',
                                            'sent' => 'info',
                                            'paid' => 'success',
                                            'overdue' => 'danger',
                                            'cancelled' => 'dark',
                                        ][$invoice->status] ?? 'light';
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ ucfirst($invoice->status) }}</span>
                                </span>
                            </div>
                            <div class="small">Due: {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '-' }}</div>
                            <div class="mt-1">
                                <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-info btn-sm mb-1">View</a>
                                <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-warning btn-sm mb-1">Edit</a>
                                <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure to delete this invoice?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm mb-1">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted">No invoice records found.</div>
                    @endforelse
                </div>
                <div class="mt-3">
                    {{ $invoices->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
