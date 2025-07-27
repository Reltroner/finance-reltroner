{{-- resources/views/payments/index.blade.php --}}

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

@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Error:</strong> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Payments</h3>
                <p class="text-subtitle text-muted">Manage and review all payments (linked to invoice, vendor, customer, or transaction)</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Payments</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label for="invoice_id" class="form-label">Invoice</label>
                <select name="invoice_id" id="invoice_id" class="form-select">
                    <option value="">All</option>
                    @foreach ($invoices as $invoice)
                        <option value="{{ $invoice->id }}" {{ request('invoice_id') == $invoice->id ? 'selected' : '' }}>
                            {{ $invoice->invoice_number }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label for="vendor_id" class="form-label">Vendor</label>
                <select name="vendor_id" id="vendor_id" class="form-select">
                    <option value="">All</option>
                    @foreach ($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
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
            <div class="col-6 col-md-2">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All</option>
                    @foreach (['pending','cleared','failed'] as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label for="date" class="form-label">Date</label>
                <input type="date" name="date" id="date" class="form-control"
                    value="{{ request('date') }}">
            </div>
            <div class="col-6 col-md-1 d-flex align-items-end">
                <button class="btn btn-outline-primary w-100" type="submit">Filter</button>
            </div>
        </div>
    </form>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Payment List</h5>
                <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">Add Payment</a>
            </div>
            <div class="card-body">
                <!-- Desktop Table (shown ≥576px) -->
                <div class="table-responsive d-none d-sm-block">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Invoice</th>
                                <th>Vendor</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Method</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                <tr>
                                    <td>{{ $loop->iteration + ($payments->perPage() * ($payments->currentPage() - 1)) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($payment->date)->format('d M Y') }}</td>
                                    <td>{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->invoice->invoice_number ?? '-' }}</td>
                                    <td>{{ $payment->vendor->name ?? '-' }}</td>
                                    <td>{{ $payment->customer->name ?? '-' }}</td>
                                    <td>
                                        @php
                                            $badge = [
                                                'pending' => 'secondary',
                                                'cleared' => 'success',
                                                'failed' => 'danger',
                                            ][$payment->status] ?? 'light';
                                        @endphp
                                        <span class="badge bg-{{ $badge }}">{{ ucfirst($payment->status) }}</span>
                                    </td>
                                    <td>{{ $payment->payment_method ?? '-' }}</td>
                                    <td>
                                        <div class="d-flex gap-1 mt-1">
                                            <a href="{{ route('payments.show', $payment->id) }}" class="btn btn-info btn-sm mb-1">View</a>
                                            <a href="{{ route('payments.edit', $payment->id) }}" class="btn btn-warning btn-sm mb-1">Edit</a>
                                            <form action="{{ route('payments.destroy', $payment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure to delete this payment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm mb-1">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No payment records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Mobile Card/List (shown <576px) -->
                <div class="d-block d-sm-none">
                    @forelse ($payments as $payment)
                        <div class="border rounded mb-2 px-2 py-2 bg-white shadow-sm">
                            <div class="fw-bold mb-1">{{ $payment->invoice->invoice_number ?? '-' }}</div>
                            <div class="mb-1 small text-muted">
                                Vendor: {{ $payment->vendor->name ?? '-' }}<br>
                                Customer: {{ $payment->customer->name ?? '-' }}<br>
                                <span>{{ \Carbon\Carbon::parse($payment->date)->format('d M Y') }}</span>
                            </div>
                            <div>
                                <span class="small">Amount:</span>
                                <strong>{{ number_format($payment->amount, 2) }}</strong>
                                <span class="float-end">
                                    @php
                                        $badge = [
                                            'pending' => 'secondary',
                                            'cleared' => 'success',
                                            'failed' => 'danger',
                                        ][$payment->status] ?? 'light';
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ ucfirst($payment->status) }}</span>
                                </span>
                            </div>
                            <div class="small">Method: {{ $payment->payment_method ?? '-' }}</div>
                            <div class="mt-1">
                                <a href="{{ route('payments.show', $payment->id) }}" class="btn btn-info btn-sm mb-1">View</a>
                                <a href="{{ route('payments.edit', $payment->id) }}" class="btn btn-warning btn-sm mb-1">Edit</a>
                                <form action="{{ route('payments.destroy', $payment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure to delete this payment?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm mb-1">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted">No payment records found.</div>
                    @endforelse
                </div>
                <div class="mt-3">
                    {{ $payments->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
