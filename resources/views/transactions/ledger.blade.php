{{-- resources/views/transactions/ledger.blade.php --}}
@extends('layouts.dashboard')

@section('content')
<header class="mb-3">
    <a href="#" class="burger-btn d-block d-xl-none">
        <i class="bi bi-justify fs-3"></i>
    </a>
</header>

<div class="page-heading">
    <div class="page-title mb-2">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h3>General Ledger</h3>
                <p class="text-muted mb-0">
                    Read-only ledger view (STEP 5.2B.4 compliant)
                </p>
            </div>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Filter</span>
            <div class="d-flex gap-2">
                <button id="btnPrint" type="button" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-printer"></i> Print
                </button>
                <button id="btnCsv" type="button" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-download"></i> CSV
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('transactions.ledger') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Account *</label>
                    <select name="account_id" class="form-select" required>
                        <option value="">— Select —</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}"
                                @selected(($filters['account_id'] ?? null) == $acc->id)>
                                {{ $acc->code }} — {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control"
                           value="{{ $filters['date_from'] ?? '' }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control"
                           value="{{ $filters['date_to'] ?? '' }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Cost Center</label>
                    <select name="cost_center_id" class="form-select">
                        <option value="">— All —</option>
                        @foreach ($costcenters as $cc)
                            <option value="{{ $cc->id }}"
                                @selected(($filters['cost_center_id'] ?? null) == $cc->id)>
                                {{ $cc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">— All —</option>
                        @foreach (['draft'=>'Draft','posted'=>'Posted','voided'=>'Voided'] as $v=>$l)
                            <option value="{{ $v }}" @selected(($filters['status'] ?? '') === $v)>
                                {{ $l }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Reference</label>
                    <input type="text" name="reference" class="form-control"
                           value="{{ $filters['reference'] ?? '' }}">
                </div>

                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary">
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

    {{-- LEDGER HEADER --}}
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between flex-wrap">
            <div>
                <div class="text-muted small">Account</div>
                <div class="fw-semibold">
                    {{ $account->code }} — {{ $account->name }}
                </div>
            </div>
            <div>
                <div class="text-muted small">Closing Balance</div>
                <div class="fw-bold">
                    {{ number_format($closing, 2) }}
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
                        <th>Date</th>
                        <th>Journal No</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th>Memo</th>
                        <th class="text-end">Running</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $row)
                        @php
                            $badge = match($row['status']) {
                                'posted' => 'success',
                                'voided' => 'danger',
                                default  => 'secondary',
                            };
                        @endphp
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($row['date'])->format('Y-m-d') }}</td>
                            <td class="text-monospace">{{ $row['journal_no'] }}</td>
                            <td class="text-monospace">{{ $row['reference'] ?? '—' }}</td>
                            <td>{{ $row['description'] ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $badge }}">
                                    {{ ucfirst($row['status']) }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format($row['debit'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['credit'], 2) }}</td>
                            <td>{{ $row['memo'] ?? '—' }}</td>
                            <td class="text-end fw-semibold">{{ number_format($row['running'], 2) }}</td>
                            <td class="text-end">
                                <a href="{{ route('transactions.show', $row['transaction_id']) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="View Transaction">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">
                                No ledger entries found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('btnPrint')?.addEventListener('click', () => window.print());

document.getElementById('btnCsv')?.addEventListener('click', () => {
    const table = document.getElementById('ledgerTable');
    if (!table) return;

    let csv = [];
    table.querySelectorAll('tr').forEach(tr => {
        let row = [];
        tr.querySelectorAll('th,td').forEach((td,i,arr)=>{
            if (i === arr.length - 1) return; // skip action
            let t = td.innerText.replace(/\s+/g,' ').trim().replace(/"/g,'""');
            if (!isNaN(t) && t !== '') t = '="' + t + '"';
            row.push(`"${t}"`);
        });
        csv.push(row.join(','));
    });

    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'ledger_{{ now()->format("Ymd_His") }}.csv';
    a.click();
});
</script>
@endpush
@endsection
