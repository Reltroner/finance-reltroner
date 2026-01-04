{{-- resources/views/transaction-details/show.blade.php --}}
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

@php
    /** @var \App\Models\TransactionDetail $detail */
    $trx = $detail->transaction;
    $statusBadge = match($trx->status ?? 'draft') {
        'posted' => 'success',
        'voided' => 'danger',
        default  => 'secondary'
    };
    $oneSidedOk = ($detail->debit > 0 && $detail->credit == 0) || ($detail->credit > 0 && $detail->debit == 0);
@endphp

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Journal Line Detail</h3>
                <p class="text-subtitle text-muted">Single GL line with parent transaction summary</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('transaction-details.index') }}">Journal Lines</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Show</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">

        {{-- Parent transaction summary --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Parent Transaction</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('transactions.show', $trx->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-box-arrow-up-right"></i> Open Transaction
                    </a>
                    <a href="{{ route('transactions.edit', $trx->id) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit Transaction
                    </a>
                    <a href="{{ route('transactions.ledger', [
                        'account_id' => $detail->account_id,
                        'date_from'  => optional($trx->date)->startOfMonth()->format('Y-m-d'),
                        'date_to'    => optional($trx->date)->endOfMonth()->format('Y-m-d'),
                    ]) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-journal-text"></i> View in Ledger
                    </a>
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Journal No</dt>
                    <dd class="col-sm-9"><span class="text-monospace fw-semibold">{{ $trx->journal_no ?? '—' }}</span></dd>

                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">
                        <span class="badge bg-{{ $statusBadge }}">{{ ucfirst($trx->status ?? 'draft') }}</span>
                        @if($trx->status === 'posted' && $trx->posted_at)
                            <span class="ms-2 text-muted small">at {{ optional($trx->posted_at)->format('Y-m-d H:i') }}</span>
                        @endif
                    </dd>

                    <dt class="col-sm-3">Date</dt>
                    <dd class="col-sm-9">{{ optional($trx->date)->format('Y-m-d') }}</dd>

                    <dt class="col-sm-3">Fiscal Period</dt>
                    <dd class="col-sm-9">{{ $trx->fiscal_year ?? '—' }} / {{ $trx->fiscal_period ?? '—' }}</dd>

                    <dt class="col-sm-3">Currency</dt>
                    <dd class="col-sm-9">
                        {{ $trx->currency->code ?? '—' }}
                        <span class="text-muted ms-2">(Rate → Base: {{ rtrim(rtrim(number_format($trx->exchange_rate ?? 1, 10), '0'), '.') }})</span>
                    </dd>

                    <dt class="col-sm-3">Reference</dt>
                    <dd class="col-sm-9"><span class="text-monospace">{{ $trx->reference ?? '—' }}</span></dd>

                    <dt class="col-sm-3">Description</dt>
                    <dd class="col-sm-9">{{ $trx->description ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        {{-- Single line detail --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Line #{{ $detail->line_no }}</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('transaction-details.edit', $detail->id) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit Line
                    </a>
                    <a href="{{ route('transaction-details.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Lines
                    </a>
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Account</dt>
                    <dd class="col-sm-9">
                        @if($detail->account)
                            <span class="text-monospace">{{ $detail->account->code }}</span> — {{ $detail->account->name }}
                        @else
                            —
                        @endif
                    </dd>

                    <dt class="col-sm-3">Amounts</dt>
                    <dd class="col-sm-9">
                        <div class="d-flex flex-wrap gap-3">
                            <span>Debit: <strong>{{ number_format($detail->debit, 2) }}</strong></span>
                            <span>Credit: <strong>{{ number_format($detail->credit, 2) }}</strong></span>
                            <span>
                                One‑Sided:
                                <span class="badge bg-{{ $oneSidedOk ? 'success' : 'danger' }}">
                                    {{ $oneSidedOk ? 'OK' : 'Invalid' }}
                                </span>
                            </span>
                        </div>
                    </dd>

                    <dt class="col-sm-3">Cost Center</dt>
                    <dd class="col-sm-9">{{ $detail->costCenter->name ?? '—' }}</dd>

                    <dt class="col-sm-3">Memo</dt>
                    <dd class="col-sm-9">{{ $detail->memo ?? '—' }}</dd>

                    <dt class="col-sm-3">Created / Updated</dt>
                    <dd class="col-sm-9">
                        <span class="text-muted">{{ optional($detail->created_at)->format('Y-m-d H:i') }}</span>
                        <span class="mx-1">•</span>
                        <span class="text-muted">{{ optional($detail->updated_at)->format('Y-m-d H:i') }}</span>
                    </dd>
                </dl>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <a class="btn btn-outline-secondary btn-sm"
                   href="{{ route('transactions.ledger', [
                        'account_id' => $detail->account_id,
                        'date_from'  => optional($trx->date)->startOfMonth()->format('Y-m-d'),
                        'date_to'    => optional($trx->date)->endOfMonth()->format('Y-m-d'),
                   ]) }}">
                    <i class="bi bi-journal"></i> Open Ledger
                </a>
                <form action="{{ route('transaction-details.destroy', $detail->id) }}" method="POST"
                      onsubmit="return confirm('Delete this line? This cannot be undone.');" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash"></i> Delete Line
                    </button>
                </form>
            </div>
        </div>

    </section>
</div>

<style>
.text-monospace { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace; }
.d-flex .btn { display:flex!important; align-items:center; justify-content:center; height:42px; }
</style>
@endsection
