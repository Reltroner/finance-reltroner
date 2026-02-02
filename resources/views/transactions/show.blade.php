{{-- resources/views/transactions/show.blade.php --}}
@extends('layouts.dashboard')

@section('content')
@php
    $isImmutable = in_array($transaction->status, ['posted','voided'], true);
    $balanced = ($transaction->is_balanced ?? false)
        || (round($transaction->total_debit,2) === round($transaction->total_credit,2));
    $badgeColor = match($transaction->status) {
        'posted' => 'success',
        'voided' => 'danger',
        default  => 'secondary'
    };
@endphp

<header class="mb-3">
    <a href="#" class="burger-btn d-block d-xl-none">
        <i class="bi bi-justify fs-3"></i>
    </a>
</header>

{{-- IMMUTABILITY NOTICE --}}
@if ($isImmutable)
<div class="alert alert-warning d-flex align-items-center">
    <i class="bi bi-lock-fill me-2 fs-5"></i>
    <div>
        <strong>Read-only:</strong>
        This transaction is <b>{{ strtoupper($transaction->status) }}</b>.
        Editing is locked by accounting rules (STEP 5.2B.4).
    </div>
</div>
@endif

<div class="page-heading">
    <div class="page-title mb-3">
        <div class="row">
            <div class="col-md-6">
                <h3>Transaction Detail</h3>
                <p class="text-muted mb-0">
                    Canonical journal record (read-only)
                </p>
            </div>
            <div class="col-md-6">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('transactions.index') }}">Transactions</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Show</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">

        {{-- HEADER --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Header</h5>
                <div class="d-flex gap-2">
                    @unless($isImmutable)
                        <a href="{{ route('transactions.edit', $transaction->id) }}"
                           class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                    @endunless
                    <a href="{{ route('transactions.index') }}"
                       class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Journal No</dt>
                    <dd class="col-sm-9 text-monospace fw-semibold">
                        {{ $transaction->journal_no }}
                    </dd>

                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">
                        <span class="badge bg-{{ $badgeColor }}">
                            {{ ucfirst($transaction->status) }}
                        </span>
                        @if($transaction->posted_at)
                            <span class="text-muted small ms-2">
                                {{ optional($transaction->posted_at)->format('Y-m-d H:i') }}
                            </span>
                        @endif
                    </dd>

                    <dt class="col-sm-3">Date</dt>
                    <dd class="col-sm-9">{{ optional($transaction->date)->format('Y-m-d') }}</dd>

                    <dt class="col-sm-3">Fiscal Period</dt>
                    <dd class="col-sm-9">
                        {{ $transaction->fiscal_year }} / {{ $transaction->fiscal_period }}
                    </dd>

                    <dt class="col-sm-3">Currency</dt>
                    <dd class="col-sm-9">
                        {{ $transaction->currency->code ?? '—' }}
                        <span class="text-muted ms-2">
                            (Rate → Base:
                            {{ rtrim(rtrim(number_format($transaction->exchange_rate ?? 1,10),'0'),'.') }})
                        </span>
                    </dd>

                    <dt class="col-sm-3">Description</dt>
                    <dd class="col-sm-9">{{ $transaction->description ?? '—' }}</dd>

                    <dt class="col-sm-3">Totals</dt>
                    <dd class="col-sm-9">
                        <div class="d-flex flex-wrap gap-3">
                            <span>Debit <strong>{{ number_format($transaction->total_debit,2) }}</strong></span>
                            <span>Credit <strong>{{ number_format($transaction->total_credit,2) }}</strong></span>
                            <span>Base D <strong>{{ number_format($transaction->total_debit_base,2) }}</strong></span>
                            <span>Base C <strong>{{ number_format($transaction->total_credit_base,2) }}</strong></span>
                            <span>
                                <span class="badge bg-{{ $balanced ? 'success' : 'danger' }}">
                                    {{ $balanced ? 'Balanced' : 'Unbalanced' }}
                                </span>
                            </span>
                            <span>Lines <strong>{{ $transaction->details->count() }}</strong></span>
                        </div>
                    </dd>
                </dl>
            </div>
        </div>

        {{-- JOURNAL LINES --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Journal Lines</h5>
                @if($transaction->details->first())
                    <a class="btn btn-outline-primary btn-sm"
                       href="{{ route('transactions.ledger', [
                            'account_id' => $transaction->details->first()->account_id
                       ]) }}">
                        <i class="bi bi-journal-text"></i> Ledger
                    </a>
                @endif
            </div>
            <div class="card-body table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Account</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                            <th>Cost Center</th>
                            <th>Memo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transaction->details as $d)
                            <tr>
                                <td>
                                    <span class="text-monospace">{{ $d->account->code }}</span>
                                    — {{ $d->account->name }}
                                </td>
                                <td class="text-end">{{ number_format($d->debit,2) }}</td>
                                <td class="text-end">{{ number_format($d->credit,2) }}</td>
                                <td>{{ $d->costCenter->name ?? '—' }}</td>
                                <td>{{ $d->memo ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    No journal lines
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </section>
</div>

<style>
.text-monospace {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace;
}
</style>
@endsection
