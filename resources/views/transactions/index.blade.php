{{-- resources/views/transactions/index.blade.php --}}
@extends('layouts.dashboard')

@section('content')

<header class="mb-3">
    <a href="#" class="burger-btn d-block d-xl-none">
        <i class="bi bi-justify fs-3"></i>
    </a>
</header>

@if (session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <strong>Success:</strong> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <strong>Error:</strong> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Transactions</h3>
                <p class="text-subtitle text-muted">
                    Finance journal entries (STEP 5.2B.4 compliant)
                </p>
            </div>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <form method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Journal No</label>
                <input type="text" name="journal_no" class="form-control"
                       value="{{ request('journal_no') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Reference</label>
                <input type="text" name="reference" class="form-control"
                       value="{{ request('reference') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control"
                       value="{{ request('date_from') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control"
                       value="{{ request('date_to') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach(['draft','posted','voided'] as $s)
                        <option value="{{ $s }}" @selected(request('status')===$s)>
                            {{ ucfirst($s) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Currency</label>
                <select name="currency_id" class="form-select">
                    <option value="">All</option>
                    @foreach($currencies as $c)
                        <option value="{{ $c->id }}" @selected(request('currency_id')==$c->id)>
                            {{ $c->code }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-outline-primary w-100">
                    <i class="bi bi-search"></i> Search
                </button>
                <a href="{{ route('transactions.index') }}"
                   class="btn btn-outline-secondary w-100">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- TABLE --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Transaction List</h5>
            <a href="{{ route('transactions.create') }}"
               class="btn btn-primary btn-sm">
                New Transaction
            </a>
        </div>

        <div class="card-body table-responsive">
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
                @forelse($transactions as $tx)
                    @php
                        $immutable = in_array($tx->status, ['posted','voided'], true);
                        $balanced  = $tx->is_balanced;
                        $badge     = match($tx->status) {
                            'posted' => 'success',
                            'voided' => 'danger',
                            default  => 'secondary'
                        };
                    @endphp

                    <tr>
                        <td>{{ $loop->iteration + ($transactions->perPage() * ($transactions->currentPage()-1)) }}</td>
                        <td>{{ $tx->date->format('Y-m-d') }}</td>
                        <td class="text-monospace">{{ $tx->journal_no }}</td>
                        <td class="text-monospace">{{ $tx->reference ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $badge }}">
                                {{ ucfirst($tx->status) }}
                            </span>
                        </td>
                        <td>{{ $tx->currency->code }}</td>
                        <td class="text-end">{{ number_format($tx->total_debit,2) }}</td>
                        <td class="text-end">{{ number_format($tx->total_credit,2) }}</td>
                        <td class="text-center">{{ $tx->details_count ?? $tx->details->count() }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $balanced ? 'success':'danger' }}">
                                {{ $balanced ? 'Balanced' : 'Unbalanced' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('transactions.show',$tx) }}"
                                   class="btn btn-outline-primary btn-sm">
                                    View
                                </a>

                                @unless($immutable)
                                    <a href="{{ route('transactions.edit',$tx) }}"
                                       class="btn btn-outline-warning btn-sm">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          action="{{ route('transactions.destroy',$tx) }}"
                                          onsubmit="return confirm('Delete this transaction?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm">
                                            Delete
                                        </button>
                                    </form>
                                @else
                                    <span class="badge bg-light text-muted"
                                          title="Immutable journal (STEP 5.2B.4)">
                                        Locked
                                    </span>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted">
                            No transactions found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
