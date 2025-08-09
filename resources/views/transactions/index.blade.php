{{-- resources/views/transactions/index.blade.php --}}
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
                <h3>Transactions</h3>
                <p class="text-subtitle text-muted">Manage finance journal entries and their details</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Transactions</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Filter bar (konsisten dengan TransactionController@index) --}}
    <form method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label for="journal_no" class="form-label">Journal No</label>
                <input type="text" name="journal_no" id="journal_no" class="form-control"
                       value="{{ request('journal_no') }}" placeholder="e.g. JV-2025-01-000123">
            </div>

            <div class="col-12 col-md-3">
                <label for="reference" class="form-label">Reference</label>
                <input type="text" name="reference" id="reference" class="form-control"
                       value="{{ request('reference') }}" placeholder="e.g. INV-2025-001">
            </div>

            <div class="col-6 col-md-2">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" name="date_from" id="date_from" class="form-control"
                       value="{{ request('date_from') }}">
            </div>

            <div class="col-6 col-md-2">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" name="date_to" id="date_to" class="form-control"
                       value="{{ request('date_to') }}">
            </div>

            <div class="col-6 col-md-2">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All</option>
                    @foreach (['draft' => 'Draft', 'posted' => 'Posted', 'voided' => 'Voided'] as $val => $label)
                        <option value="{{ $val }}" {{ request('status')===$val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label for="currency_id" class="form-label">Currency</label>
                <select name="currency_id" id="currency_id" class="form-select">
                    <option value="">All</option>
                    @foreach ($currencies as $c)
                        <option value="{{ $c->id }}" {{ (string)request('currency_id')===(string)$c->id ? 'selected' : '' }}>
                            {{ $c->code }} {{ $c->name ? '— '.$c->name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-4 d-flex gap-2">
                <button class="btn btn-outline-primary w-100" type="submit">
                    <i class="bi bi-search"></i> Search
                </button>
                <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary w-100">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Transaction List</h5>
                <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm">New Transaction</a>
            </div>

            <div class="card-body">
                {{-- Desktop table --}}
                <div class="table-responsive d-none d-sm-block">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Journal No</th>
                                <th>Reference</th>
                                <th>Status</th>
                                <th>Currency</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                                <th class="text-center">Lines</th>
                                <th class="text-center">Balanced</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($transactions as $tx)
                            @php
                                $isBalanced = $tx->is_balanced ?? (round($tx->total_debit,2) === round($tx->total_credit,2));
                                $badge = match($tx->status) {
                                    'posted' => 'success',
                                    'voided' => 'danger',
                                    default  => 'secondary'
                                };
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration + ($transactions->perPage() * ($transactions->currentPage() - 1)) }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($tx->date)->format('Y-m-d') }}</td>
                                <td class="text-monospace">{{ $tx->journal_no ?? '—' }}</td>
                                <td class="text-monospace">{{ $tx->reference ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-{{ $badge }}">{{ ucfirst($tx->status ?? 'draft') }}</span>
                                </td>
                                <td>{{ $tx->currency->code ?? '—' }}</td>
                                <td class="text-end">{{ number_format($tx->total_debit, 2) }}</td>
                                <td class="text-end">{{ number_format($tx->total_credit, 2) }}</td>
                                <td class="text-center">{{ $tx->details->count() }}</td>
                                <td class="text-center">
                                    @if($isBalanced)
                                        <span class="badge bg-success">Balanced</span>
                                    @else
                                        <span class="badge bg-danger" title="Diff: {{ number_format(($tx->total_debit - $tx->total_credit), 2) }}">
                                            Unbalanced
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('transactions.show', $tx->id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-box-arrow-up-right"></i> View
                                        </a>
                                        <a href="{{ route('transactions.edit', $tx->id) }}" class="btn btn-outline-warning btn-sm">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('transactions.destroy', $tx->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure to delete this record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">No transaction records found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile cards --}}
                <div class="d-block d-sm-none">
                    @forelse ($transactions as $tx)
                        @php
                            $isBalanced = $tx->is_balanced ?? (round($tx->total_debit,2) === round($tx->total_credit,2));
                            $badge = match($tx->status) {
                                'posted' => 'success',
                                'voided' => 'danger',
                                default  => 'secondary'
                            };
                        @endphp
                        <div class="border rounded mb-2 px-2 py-2 bg-white shadow-sm">
                            <div class="d-flex justify-content-between">
                                <div class="fw-bold text-monospace">{{ $tx->journal_no ?? '—' }}</div>
                                <span class="badge bg-{{ $badge }}">{{ ucfirst($tx->status ?? 'draft') }}</span>
                            </div>
                            <div class="small text-muted mb-1">
                                {{ \Illuminate\Support\Carbon::parse($tx->date)->format('Y-m-d') }}
                                • {{ $tx->currency->code ?? '—' }}
                                • {{ $tx->details->count() }} lines
                            </div>
                            <div class="mb-1 text-monospace">
                                Ref: {{ $tx->reference ?? '—' }}
                            </div>
                            <div class="mb-2">
                                <span class="small">Debit:</span>
                                <strong>{{ number_format($tx->total_debit, 2) }}</strong><br>
                                <span class="small">Credit:</span>
                                <strong>{{ number_format($tx->total_credit, 2) }}</strong>
                                <div class="mt-1">
                                    @if($isBalanced)
                                        <span class="badge bg-success">Balanced</span>
                                    @else
                                        <span class="badge bg-danger">Unbalanced</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="{{ route('transactions.show', $tx->id) }}" class="btn btn-outline-primary btn-sm">View</a>
                                <a href="{{ route('transactions.edit', $tx->id) }}" class="btn btn-outline-warning btn-sm">Edit</a>
                                <form action="{{ route('transactions.destroy', $tx->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure to delete this record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted">No transaction records found.</div>
                    @endforelse
                </div>

                <div class="mt-3">
                    {{ $transactions->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
