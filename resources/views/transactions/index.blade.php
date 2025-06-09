@extends('layouts.dashboard')
{{-- recources/views/finance/transactions/index.blade.php --}}
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
                <h3>Transactions</h3>
                <p class="text-subtitle text-muted">You can manage finance transactions here</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Transactions</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Transaction List</h5>
                <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm">New Transaction</a>
            </div>

            <div class="card-body">
                <div class="table-responsive d-none d-sm-block">
                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->transaction_code }}</td>
                                    <td>{{ $transaction->employee->fullname ?? '-' }}</td>
                                    <td>{{ ucfirst($transaction->type) }}</td>
                                    <td>{{ $transaction->category }}</td>
                                    <td>{{ number_format($transaction->amount, 2) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('transactions.show', $transaction->id) }}" class="btn btn-info btn-sm">View</a>
                                        <a href="{{ route('transactions.edit', $transaction->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                        <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure to delete this record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-block d-sm-none">
                    @foreach ($transactions as $transaction)
                        <div class="border rounded mb-2 px-2 py-2 bg-white shadow-sm">
                            <div class="fw-bold mb-1">{{ $transaction->transaction_code }}</div>
                            <div class="small text-muted mb-1">{{ $transaction->employee->fullname ?? '-' }}</div>
                            <div class="mb-1">
                                <span class="small">Type:</span> <strong>{{ ucfirst($transaction->type) }}</strong>
                                <span class="mx-1">|</span>
                                <span class="small">Category:</span> <strong>{{ $transaction->category }}</strong>
                            </div>
                            <div class="mb-1">
                                <span class="small">Amount:</span> <strong>{{ number_format($transaction->amount, 2) }}</strong>
                            </div>
                            <div class="mb-1">
                                <span class="small">Date:</span> <strong>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') }}</strong>
                            </div>
                            <div class="mb-2">
                                <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </div>
                            <div>
                                <a href="{{ route('transactions.show', $transaction->id) }}" class="btn btn-info btn-sm mb-1">View</a>
                                <a href="{{ route('transactions.edit', $transaction->id) }}" class="btn btn-primary btn-sm mb-1">Edit</a>
                                <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure to delete this record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm mb-1">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
