{{-- resources/views/transaction-details/index.blade.php --}}
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
                <h3>Journal Lines</h3>
                <p class="text-subtitle text-muted">Browse and manage GL detail lines</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Journal Lines</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label for="transaction_id" class="form-label">Transaction / Journal</label>
                <select name="transaction_id" id="transaction_id" class="form-select">
                    <option value="">All</option>
                    @foreach(($transactionsLite ?? []) as $t)
                        <option value="{{ $t->id }}" {{ (string)request('transaction_id')===(string)$t->id ? 'selected' : '' }}>
                            {{ $t->journal_no ?? ('TX#'.$t->id) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label for="account_id" class="form-label">Account</label>
                <select name="account_id" id="account_id" class="form-select">
                    <option value="">All</option>
                    @foreach(($accounts ?? []) as $a)
                        <option value="{{ $a->id }}" {{ (string)request('account_id')===(string)$a->id ? 'selected' : '' }}>
                            {{ $a->code }} — {{ $a->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label for="cost_center_id" class="form-label">Cost Center</label>
                <select name="cost_center_id" id="cost_center_id" class="form-select">
                    <option value="">All</option>
                    @foreach(($costcenters ?? []) as $cc)
                        <option value="{{ $cc->id }}" {{ (string)request('cost_center_id')===(string)$cc->id ? 'selected' : '' }}>
                            {{ $cc->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-6 col-md-2">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
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

            <div class="col-6 col-md-2">
                <label for="currency_id" class="form-label">Currency</label>
                <select name="currency_id" id="currency_id" class="form-select">
                    <option value="">All</option>
                    @foreach(($currencies ?? []) as $c)
                        <option value="{{ $c->id }}" {{ (string)request('currency_id')===(string)$c->id ? 'selected' : '' }}>
                            {{ $c->code }} {{ $c->name ? '— '.$c->name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label for="min_debit" class="form-label">Min Debit</label>
                <input type="number" step="0.01" name="min_debit" id="min_debit" class="form-control" value="{{ request('min_debit') }}">
            </div>
            <div class="col-6 col-md-2">
                <label for="max_debit" class="form-label">Max Debit</label>
                <input type="number" step="0.01" name="max_debit" id="max_debit" class="form-control" value="{{ request('max_debit') }}">
            </div>
            <div class="col-6 col-md-2">
                <label for="min_credit" class="form-label">Min Credit</label>
                <input type="number" step="0.01" name="min_credit" id="min_credit" class="form-control" value="{{ request('min_credit') }}">
            </div>
            <div class="col-6 col-md-2">
                <label for="max_credit" class="form-label">Max Credit</label>
                <input type="number" step="0.01" name="max_credit" id="max_credit" class="form-control" value="{{ request('max_credit') }}">
            </div>

            <div class="col-12 col-md-3">
                <label for="memo" class="form-label">Memo Contains</label>
                <input type="text" name="memo" id="memo" class="form-control" value="{{ request('memo') }}" placeholder="keyword in memo">
            </div>

            <div class="col-12 col-md-4 d-flex gap-2">
                <button class="btn btn-outline-primary w-100" type="submit">
                    <i class="bi bi-search"></i> Search
                </button>
                <a href="{{ route('transaction-details.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </div>
    </form>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Journal Line List</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('transaction-details.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg"></i> New Line
                    </a>
                    <a href="{{ route('transactions.create') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-journal-plus"></i> New Transaction
                    </a>
                </div>
            </div>

            <div class="card-body">
                {{-- Desktop table --}}
                <div class="table-responsive d-none d-sm-block">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Journal</th>
                                <th class="text-center">Line</th>
                                <th>Account</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                                <th>Cost Center</th>
                                <th>Memo</th>
                                <th>Status</th>
                                <th>Currency</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($details as $d)
                            @php
                                $tx = $d->transaction;
                                $badge = match($tx->status ?? 'draft') {
                                    'posted' => 'success',
                                    'voided' => 'danger',
                                    default  => 'secondary',
                                };
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration + ($details->perPage() * ($details->currentPage() - 1)) }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($tx->date)->format('Y-m-d') }}</td>
                                <td class="text-monospace">{{ $tx->journal_no ?? ('TX#'.$tx->id) }}</td>
                                <td class="text-center">{{ $d->line_no }}</td>
                                <td>
                                    @if($d->account)
                                        <span class="text-monospace">{{ $d->account->code }}</span> — {{ $d->account->name }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($d->debit, 2) }}</td>
                                <td class="text-end">{{ number_format($d->credit, 2) }}</td>
                                <td>{{ $d->costCenter->name ?? '—' }}</td>
                                <td>{{ $d->memo ?? '—' }}</td>
                                <td><span class="badge bg-{{ $badge }}">{{ ucfirst($tx->status ?? 'draft') }}</span></td>
                                <td>{{ $tx->currency->code ?? '—' }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('transactions.show', $tx->id) }}" class="btn btn-outline-primary btn-sm">
                                            View Tx
                                        </a>
                                        <a href="{{ route('transactions.edit', $tx->id) }}" class="btn btn-outline-warning btn-sm">
                                            Edit Tx
                                        </a>
                                        <form action="{{ route('transaction-details.destroy', $d->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this line?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted">No journal lines found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile cards --}}
                <div class="d-block d-sm-none">
                    @forelse ($details as $d)
                        @php
                            $tx = $d->transaction;
                            $badge = match($tx->status ?? 'draft') {
                                'posted' => 'success',
                                'voided' => 'danger',
                                default  => 'secondary',
                            };
                        @endphp
                        <div class="border rounded mb-2 px-2 py-2 bg-white shadow-sm">
                            <div class="d-flex justify-content-between">
                                <div class="fw-bold text-monospace">{{ $tx->journal_no ?? ('TX#'.$tx->id) }}</div>
                                <span class="badge bg-{{ $badge }}">{{ ucfirst($tx->status ?? 'draft') }}</span>
                            </div>
                            <div class="small text-muted">
                                {{ \Illuminate\Support\Carbon::parse($tx->date)->format('Y-m-d') }}
                                • {{ $tx->currency->code ?? '—' }}
                                • Line #{{ $d->line_no }}
                            </div>
                            <div class="mt-1">
                                @if($d->account)
                                    <div class="fw-semibold">
                                        <span class="text-monospace">{{ $d->account->code }}</span> — {{ $d->account->name }}
                                    </div>
                                @endif
                                <div>Debit: <strong>{{ number_format($d->debit, 2) }}</strong></div>
                                <div>Credit: <strong>{{ number_format($d->credit, 2) }}</strong></div>
                                <div>CC: {{ $d->costCenter->name ?? '—' }}</div>
                                @if($d->memo)
                                    <div class="small text-muted">Memo: {{ $d->memo }}</div>
                                @endif
                            </div>
                            <div class="d-flex gap-1 mt-2">
                                <a href="{{ route('transactions.show', $tx->id) }}" class="btn btn-outline-primary btn-sm">View Tx</a>
                                <a href="{{ route('transactions.edit', $tx->id) }}" class="btn btn-outline-warning btn-sm">Edit Tx</a>
                                <form action="{{ route('transaction-details.destroy', $d->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this line?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted">No journal lines found.</div>
                    @endforelse
                </div>

                <div class="mt-3">
                    {{ $details->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.text-monospace { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace; }
.d-flex .btn { display:flex!important; align-items:center; justify-content:center; height:42px; }
</style>
@endsection
