{{-- resources/views/invoices/show.blade.php --}}

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
                <h3>Invoice Detail</h3>
                <p class="text-subtitle text-muted">Detailed view of the selected invoice</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Show</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Invoice Information</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Invoice Number</dt>
                    <dd class="col-sm-9">{{ $invoice->invoice_number }}</dd>

                    <dt class="col-sm-3">Customer</dt>
                    <dd class="col-sm-9">{{ $invoice->customer->name ?? '-' }}</dd>

                    <dt class="col-sm-3">Invoice Date</dt>
                    <dd class="col-sm-9">{{ \Carbon\Carbon::parse($invoice->date)->format('Y-m-d') }}</dd>

                    <dt class="col-sm-3">Due Date</dt>
                    <dd class="col-sm-9">{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : '-' }}</dd>

                    <dt class="col-sm-3">Total Amount</dt>
                    <dd class="col-sm-9">{{ number_format($invoice->total_amount, 2) }}</dd>

                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">
                        @php
                            $badgeClass = [
                                'draft'    => 'bg-secondary',
                                'sent'     => 'bg-info',
                                'paid'     => 'bg-success',
                                'overdue'  => 'bg-danger',
                                'cancelled'=> 'bg-dark'
                            ][$invoice->status] ?? 'bg-secondary';
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </dd>

                    <dt class="col-sm-3">Description</dt>
                    <dd class="col-sm-9">{{ $invoice->description ?? '-' }}</dd>

                    <dt class="col-sm-3">Created At</dt>
                    <dd class="col-sm-9">{{ $invoice->created_at?->format('Y-m-d H:i:s') }}</dd>

                    <dt class="col-sm-3">Last Updated</dt>
                    <dd class="col-sm-9">{{ $invoice->updated_at?->format('Y-m-d H:i:s') }}</dd>
                </dl>
                <div>
                    <a href="{{ route('invoices.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                    <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-primary btn-sm">Edit Invoice</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
