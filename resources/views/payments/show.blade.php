{{-- resources/views/payments/show.blade.php --}}

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
                <h3>Payment Detail</h3>
                <p class="text-subtitle text-muted">Detailed view of the selected payment</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Payments</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Show</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Payment Information</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Date</dt>
                    <dd class="col-sm-9">{{ \Carbon\Carbon::parse($payment->date)->format('Y-m-d') }}</dd>

                    <dt class="col-sm-3">Amount</dt>
                    <dd class="col-sm-9">{{ number_format($payment->amount, 2) }}</dd>

                    <dt class="col-sm-3">Invoice</dt>
                    <dd class="col-sm-9">{{ $payment->invoice->invoice_number ?? '-' }}</dd>

                    <dt class="col-sm-3">Vendor</dt>
                    <dd class="col-sm-9">{{ $payment->vendor->name ?? '-' }}</dd>

                    <dt class="col-sm-3">Customer</dt>
                    <dd class="col-sm-9">{{ $payment->customer->name ?? '-' }}</dd>

                    <dt class="col-sm-3">Transaction</dt>
                    <dd class="col-sm-9">{{ $payment->transaction->id ?? '-' }}</dd>

                    <dt class="col-sm-3">Payment Method</dt>
                    <dd class="col-sm-9">{{ $payment->payment_method ?? '-' }}</dd>

                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">
                        @php
                            $badgeClass = [
                                'pending' => 'bg-secondary',
                                'cleared' => 'bg-success',
                                'failed'  => 'bg-danger'
                            ][$payment->status] ?? 'bg-secondary';
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </dd>

                    <dt class="col-sm-3">Description</dt>
                    <dd class="col-sm-9">{{ $payment->description ?? '-' }}</dd>

                    <dt class="col-sm-3">Created At</dt>
                    <dd class="col-sm-9">{{ $payment->created_at?->format('Y-m-d H:i:s') }}</dd>

                    <dt class="col-sm-3">Last Updated</dt>
                    <dd class="col-sm-9">{{ $payment->updated_at?->format('Y-m-d H:i:s') }}</dd>
                </dl>
                <div>
                    <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                    <a href="{{ route('payments.edit', $payment->id) }}" class="btn btn-primary btn-sm">Edit Payment</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
