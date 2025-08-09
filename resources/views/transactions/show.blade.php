{{-- resources/views/transactions/show.blade.php --}}
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
                <h3>Transaction Detail</h3>
                <p class="text-subtitle text-muted">Header information and journal lines</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
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

        {{-- Header summary --}}
        @php
            $balanced   = ($transaction->is_balanced ?? false) || (round($transaction->total_debit,2) === round($transaction->total_credit,2));
            $badgeColor = match($transaction->status) {
                'posted' => 'success',
                'voided' => 'danger',
                default  => 'secondary'
            };
        @endphp

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Header</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('transactions.edit', $transaction->id) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Journal No</dt>
                    <dd class="col-sm-9"><span class="text-monospace fw-semibold">{{ $transaction->journal_no ?? '—' }}</span></dd>

                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">
                        <span class="badge bg-{{ $badgeColor }}">{{ ucfirst($transaction->status ?? 'draft') }}</span>
                        @if($transaction->status === 'posted' && $transaction->posted_at)
                            <span class="ms-2 text-muted small">at {{ optional($transaction->posted_at)->format('Y-m-d H:i') }}</span>
                        @endif
                    </dd>

                    <dt class="col-sm-3">Date</dt>
                    <dd class="col-sm-9">{{ optional($transaction->date)->format('Y-m-d') }}</dd>

                    <dt class="col-sm-3">Fiscal Period</dt>
                    <dd class="col-sm-9">{{ $transaction->fiscal_year ?? '—' }} / {{ $transaction->fiscal_period ?? '—' }}</dd>

                    <dt class="col-sm-3">Currency</dt>
                    <dd class="col-sm-9">
                        {{ $transaction->currency->code ?? '—' }}
                        <span class="text-muted ms-2">(Rate → Base: {{ rtrim(rtrim(number_format($transaction->exchange_rate ?? 1, 10), '0'), '.') }})</span>
                    </dd>

                    <dt class="col-sm-3">Description</dt>
                    <dd class="col-sm-9">{{ $transaction->description ?? '—' }}</dd>

                    <dt class="col-sm-3">Totals</dt>
                    <dd class="col-sm-9">
                        <div class="d-flex flex-wrap gap-3">
                            <span>Debit: <strong>{{ number_format($transaction->total_debit, 2) }}</strong></span>
                            <span>Credit: <strong>{{ number_format($transaction->total_credit, 2) }}</strong></span>
                            <span>Base Debit: <strong>{{ number_format($transaction->total_debit_base, 2) }}</strong></span>
                            <span>Base Credit: <strong>{{ number_format($transaction->total_credit_base, 2) }}</strong></span>
                            <span>
                                Balance:
                                <span class="badge bg-{{ $balanced ? 'success' : 'danger' }}">
                                    {{ $balanced ? 'Balanced' : 'Unbalanced' }}
                                </span>
                            </span>
                            <span>Lines: <strong>{{ $transaction->details->count() }}</strong></span>
                        </div>
                    </dd>

                    @if($transaction->reversal_of_id)
                        <dt class="col-sm-3">Reversal Of</dt>
                        <dd class="col-sm-9">
                            <a href="{{ route('transactions.show', $transaction->reversal_of_id) }}" class="text-decoration-underline">
                                #{{ $transaction->reversalOf?->journal_no ?? $transaction->reversal_of_id }}
                            </a>
                        </dd>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Journal lines --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Journal Lines</h5>
                @php
                    $firstLine = $transaction->details->first();
                @endphp
                @if($firstLine)
                    <a class="btn btn-outline-primary btn-sm"
                       href="{{ route('transactions.ledger', [
                            'account_id' => $firstLine->account_id,
                            'date_from'  => optional($transaction->date)->startOfMonth()->format('Y-m-d'),
                            'date_to'    => optional($transaction->date)->endOfMonth()->format('Y-m-d'),
                       ]) }}">
                        <i class="bi bi-journal-text"></i> View in Ledger
                    </a>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive d-none d-sm-block">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th style="width: 26%">Account</th>
                                <th style="width: 14%" class="text-end">Debit</th>
                                <th style="width: 14%" class="text-end">Credit</th>
                                <th style="width: 18%">Cost Center</th>
                                <th style="width: 22%">Memo</th>
                                <th style="width: 6%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transaction->details as $d)
                                <tr>
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
                                    <td class="text-end">
                                        <a href="{{ route('transactions.ledger', ['account_id' => $d->account_id, 'date_from' => optional($transaction->date)->startOfMonth()->format('Y-m-d'), 'date_to' => optional($transaction->date)->endOfMonth()->format('Y-m-d')]) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Ledger">
                                            <i class="bi bi-journal"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No journal lines.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th class="text-end">Totals</th>
                                <th class="text-end">{{ number_format($transaction->total_debit, 2) }}</th>
                                <th class="text-end">{{ number_format($transaction->total_credit, 2) }}</th>
                                <th colspan="3"></th>
                            </tr>
                            <tr>
                                <th class="text-end">Totals (Base)</th>
                                <th class="text-end">{{ number_format($transaction->total_debit_base, 2) }}</th>
                                <th class="text-end">{{ number_format($transaction->total_credit_base, 2) }}</th>
                                <th colspan="3"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Mobile list --}}
                <div class="d-block d-sm-none">
                    @forelse ($transaction->details as $d)
                        <div class="border rounded mb-2 px-2 py-2 bg-white shadow-sm">
                            <div class="fw-bold">
                                @if($d->account)
                                    <span class="text-monospace">{{ $d->account->code }}</span> — {{ $d->account->name }}
                                @else
                                    —
                                @endif
                            </div>
                            <div class="small text-muted">
                                Cost Center: {{ $d->costCenter->name ?? '—' }}
                            </div>
                            <div class="mt-1">
                                <span class="small">Debit:</span> <strong>{{ number_format($d->debit, 2) }}</strong>
                                <span class="mx-2">|</span>
                                <span class="small">Credit:</span> <strong>{{ number_format($d->credit, 2) }}</strong>
                            </div>
                            @if($d->memo)
                                <div class="small mt-1">Memo: {{ $d->memo }}</div>
                            @endif
                            <div class="mt-2">
                                <a class="btn btn-sm btn-outline-secondary"
                                   href="{{ route('transactions.ledger', [
                                        'account_id' => $d->account_id,
                                        'date_from'  => optional($transaction->date)->startOfMonth()->format('Y-m-d'),
                                        'date_to'    => optional($transaction->date)->endOfMonth()->format('Y-m-d'),
                                   ]) }}">
                                    <i class="bi bi-journal"></i> Ledger
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted">No journal lines.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Attachments --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Attachments</h5>
                <a href="{{ route('attachments.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-upload"></i> Upload Attachment
                </a>
            </div>
            <div class="card-body">
                @php $atts = $transaction->attachments ?? collect(); @endphp
                @if ($atts->count())
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>File Name</th>
                                    <th>Uploaded</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($atts as $i => $a)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td class="text-monospace">{{ $a->file_name }}</td>
                                        <td>{{ optional($a->uploaded_at)->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('attachments.show', $a->id) }}" class="btn btn-info btn-sm">View</a>
                                                <a href="{{ route('attachments.download', $a->id) }}" class="btn btn-success btn-sm">Download</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-muted">No attachments.</div>
                @endif
            </div>
        </div>

        {{-- Tax Applications --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tax Applications</h5>
            </div>
            <div class="card-body">
                @php $taxes = $transaction->taxApplications ?? collect(); @endphp
                @if ($taxes->count())
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tax</th>
                                    <th>Rate</th>
                                    <th>Base</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($taxes as $i => $t)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $t->tax->name ?? '—' }}</td>
                                        <td>{{ isset($t->rate) ? rtrim(rtrim(number_format($t->rate, 4), '0'), '.') : '—' }}%</td>
                                        <td>{{ isset($t->base_amount) ? number_format($t->base_amount, 2) : '—' }}</td>
                                        <td>{{ isset($t->tax_amount) ? number_format($t->tax_amount, 2) : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-muted">No tax applications.</div>
                @endif
            </div>
        </div>
    </section>
</div>

<style>
.d-flex .btn { display:flex!important; align-items:center; justify-content:center; height:42px; }
.text-monospace { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace; }
</style>
@endsection
