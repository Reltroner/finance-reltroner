{{-- resources/views/transactions/ledger.blade.php --}}
@extends('layouts.dashboard')

@section('content')
<header class="mb-3">
    <a href="#" class="burger-btn d-block d-xl-none">
        <i class="bi bi-justify fs-3"></i>
    </a>
</header>

<div class="page-heading">
    <div class="page-title">
        <div class="row align-items-center">
            <div class="col-12 col-md-7 order-md-1 order-last">
                <h3>General Ledger</h3>
                <p class="text-muted mb-0">Buku besar per akun dengan opening, running, dan closing balance</p>
            </div>
            <div class="col-12 col-md-5 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">General Ledger</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="card mt-3">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span class="fw-semibold">Filter</span>
            <div>
                <button id="btnPrint" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="bi bi-printer"></i> Print
                </button>
                <button id="btnCsv" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-download"></i> Export CSV
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('transactions.ledger') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Account <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select" required>
                        <option value="">— Choose Account —</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}"
                                @selected(old('account_id', $filters['account_id'] ?? null) == $acc->id)>
                                {{ $acc->code ?? '' }} — {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control"
                           value="{{ old('date_from', $filters['date_from'] ?? '') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control"
                           value="{{ old('date_to', $filters['date_to'] ?? '') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Cost Center</label>
                    <select name="cost_center_id" class="form-select">
                        <option value="">— All —</option>
                        @foreach($costcenters as $cc)
                            <option value="{{ $cc->id }}"
                                @selected(old('cost_center_id', $filters['cost_center_id'] ?? null) == $cc->id)>
                                {{ $cc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">— All —</option>
                        @foreach(['draft' => 'Draft', 'posted' => 'Posted', 'voided' => 'Voided'] as $val => $label)
                            <option value="{{ $val }}" @selected(($filters['status'] ?? '') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Reference</label>
                    <input type="text" name="reference" class="form-control"
                           placeholder="INV-001 / memo / ref contains..."
                           value="{{ old('reference', $filters['reference'] ?? '') }}">
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Apply
                    </button>
                    <a href="{{ route('transactions.ledger', ['account_id' => $filters['account_id'] ?? null]) }}"
                       class="btn btn-light border">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- LEDGER SUMMARY HEADER --}}
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div class="mb-2">
                    <div class="text-muted small">Account</div>
                    <div class="fw-semibold">
                        {{ $account->code ?? '' }} — {{ $account->name }}
                    </div>
                </div>
                <div class="mb-2">
                    <div class="text-muted small">Period</div>
                    <div class="fw-semibold">
                        {{ $filters['date_from'] ?: '—' }} s/d {{ $filters['date_to'] ?: '—' }}
                    </div>
                </div>
                <div class="mb-2">
                    <div class="text-muted small">Opening Balance</div>
                    <div class="fw-semibold">{{ number_format($opening, 2) }}</div>
                </div>
                <div class="mb-2">
                    <div class="text-muted small">Total Debit / Credit</div>
                    <div class="fw-semibold">
                        {{ number_format($totalDebit, 2) }} / {{ number_format($totalCredit, 2) }}
                    </div>
                </div>
                <div class="mb-2">
                    <div class="text-muted small">Closing Balance</div>
                    <div class="fw-bold">{{ number_format($closing, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- LEDGER TABLE --}}
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover align-middle" id="ledgerTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 110px;">Date</th>
                        <th style="width: 160px;">Journal No</th>
                        <th style="width: 140px;">Reference</th>
                        <th>Description</th>
                        <th style="width: 110px;">Status</th>
                        <th style="width: 140px;" class="text-end">Debit</th>
                        <th style="width: 140px;" class="text-end">Credit</th>
                        <th style="width: 160px;">Cost Center</th>
                        <th style="width: 200px;">Memo</th>
                        <th style="width: 160px;" class="text-end">Running Balance</th>
                        <th style="width: 60px;"></th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Opening balance row --}}
                    <tr class="table-secondary">
                        <td colspan="9"><em>Opening Balance</em></td>
                        <td class="text-end fw-semibold">{{ number_format($opening, 2) }}</td>
                        <td></td>
                    </tr>

                    {{-- Entries --}}
                    @forelse($entries as $row)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($row['date'])->format('Y-m-d') }}</td>
                            <td class="text-monospace">{{ $row['journal_no'] ?? '—' }}</td>
                            <td class="text-monospace">{{ $row['reference'] ?? '—' }}</td>
                            <td>{{ $row['description'] ?? '—' }}</td>
                            <td>
                                @php
                                    $badge = match($row['status'] ?? '') {
                                        'posted' => 'success',
                                        'voided' => 'danger',
                                        default  => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ ucfirst($row['status'] ?? 'draft') }}</span>
                            </td>
                            <td class="text-end">{{ number_format($row['debit'] ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($row['credit'] ?? 0, 2) }}</td>
                            <td>{{ $row['cost_center'] ?? '—' }}</td>
                            <td>{{ $row['memo'] ?? '—' }}</td>
                            <td class="text-end fw-semibold">{{ number_format($row['running'] ?? 0, 2) }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary"
                                   href="{{ route('transactions.show', $row['transaction_id']) }}"
                                   title="Open transaction">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted">No entries for selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="5" class="text-end">Total</th>
                        <th class="text-end">{{ number_format($totalDebit, 2) }}</th>
                        <th class="text-end">{{ number_format($totalCredit, 2) }}</th>
                        <th colspan="2"></th>
                        <th class="text-end fw-bold">{{ number_format($closing, 2) }}</th>
                        <th></th>
                    </tr>
                    <tr>
                        <th colspan="9" class="text-end">Closing Balance</th>
                        <th class="text-end fw-bold">{{ number_format($closing, 2) }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Simple utilities: Print & CSV --}}
@push('scripts')
<script>
document.getElementById('btnPrint')?.addEventListener('click', function () {
    window.print();
});

document.getElementById('btnCsv')?.addEventListener('click', function () {
    const table = document.getElementById('ledgerTable');
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');
    rows.forEach((row) => {
        const cols = row.querySelectorAll('th, td');
        let line = [];
        cols.forEach((cell, idx) => {
            // Skip action column (last)
            if (row.parentElement.tagName === 'TBODY' && idx === cols.length - 1) return;
            // Clean text
            let text = cell.innerText.replace(/\s+/g, ' ').trim().replace(/"/g, '""');
            // Force as string for numbers to keep trailing zeros
            if (!isNaN(text) && text !== '') text = '="' + text + '"';
            line.push('"' + text + '"');
        });
        csv.push(line.join(','));
    });

    const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url;
    a.download = 'general_ledger_{{ now()->format("Ymd_His") }}.csv';
    a.click();
    URL.revokeObjectURL(url);
});
</script>
@endpush
@endsection
