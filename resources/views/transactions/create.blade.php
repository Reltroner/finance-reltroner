{{-- resources/views/transactions/create.blade.php --}}
@extends('layouts.dashboard')

@section('content')
<header class="mb-3">
    <a href="#" class="burger-btn d-block d-xl-none">
        <i class="bi bi-justify fs-3"></i>
    </a>
</header>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Create Transaction</h3>
                <p class="text-subtitle text-muted">Record a new journal transaction with one or more lines</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('transactions.index') }}">Transactions</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">New Transaction Form</h5>
                <small class="text-muted">Journal No will be generated automatically when saved.</small>
            </div>

            <div class="card-body">
                {{-- Global error alert --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('transactions.store') }}" method="POST" autocomplete="off" id="tx-form">
                    @csrf

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date"
                                   class="form-control @error('date') is-invalid @enderror"
                                   value="{{ old('date', date('Y-m-d')) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="currency_id" class="form-label">Currency <span class="text-danger">*</span></label>
                            <select name="currency_id" id="currency_id"
                                    class="form-select @error('currency_id') is-invalid @enderror" required>
                                <option value="">-- Select Currency --</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}" {{ old('currency_id') == $currency->id ? 'selected' : '' }}>
                                        {{ $currency->code }} {{ $currency->symbol ? "({$currency->symbol})" : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('currency_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="exchange_rate" class="form-label">Exchange Rate to Base</label>
                            <input type="number" step="0.0000000001" min="0.0000000001"
                                   name="exchange_rate" id="exchange_rate"
                                   class="form-control @error('exchange_rate') is-invalid @enderror"
                                   value="{{ old('exchange_rate', '1') }}">
                            @error('exchange_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Leave as 1 if currency equals base currency.</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                @foreach (['draft' => 'Draft', 'posted' => 'Posted'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('status', 'draft') === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">If set to Posted, journal will be posted immediately.</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="reference" class="form-label">Reference</label>
                            <input type="text" name="reference" id="reference"
                                   class="form-control @error('reference') is-invalid @enderror"
                                   value="{{ old('reference') }}" placeholder="e.g. INV-2025-001">
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea name="description" id="description" rows="2" maxlength="1000"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Short description of this transaction">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Transaction Lines</h6>
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-outline-primary btn-sm" type="button" id="add-line">
                                <i class="bi bi-plus-lg"></i> Add Line
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" type="button" id="clear-lines">
                                <i class="bi bi-trash"></i> Clear All
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle" id="lines-table">
                            <thead>
                                <tr>
                                    <th style="min-width:220px;">Account</th>
                                    <th style="min-width:120px;">Debit</th>
                                    <th style="min-width:120px;">Credit</th>
                                    <th style="min-width:200px;">Cost Center</th>
                                    <th>Memo</th>
                                    <th style="width:70px;"></th>
                                </tr>
                            </thead>
                            <tbody id="lines-body">
                                @php
                                    $oldLines = old('details', [
                                        ['account_id'=>'','debit'=>'','credit'=>'','cost_center_id'=>'','memo'=>'']
                                    ]);
                                @endphp
                                @foreach ($oldLines as $i => $line)
                                    <tr>
                                        <td>
                                            <select name="details[{{ $i }}][account_id]" class="form-select details-account @error("details.$i.account_id") is-invalid @enderror" required>
                                                <option value="">-- Select Account --</option>
                                                @foreach ($accounts as $acc)
                                                    <option value="{{ $acc->id }}" {{ (string)($line['account_id'] ?? '') === (string)$acc->id ? 'selected' : '' }}>
                                                        {{ $acc->code ?? '' }} {{ $acc->name ? " - {$acc->name}" : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("details.$i.account_id")
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0"
                                                   name="details[{{ $i }}][debit]"
                                                   class="form-control details-debit @error("details.$i.debit") is-invalid @enderror"
                                                   value="{{ $line['debit'] ?? '' }}">
                                            @error("details.$i.debit")
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0"
                                                   name="details[{{ $i }}][credit]"
                                                   class="form-control details-credit @error("details.$i.credit") is-invalid @enderror"
                                                   value="{{ $line['credit'] ?? '' }}">
                                            @error("details.$i.credit")
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <select name="details[{{ $i }}][cost_center_id]" class="form-select">
                                                <option value="">-- None --</option>
                                                @foreach ($costcenters as $cc)
                                                    <option value="{{ $cc->id }}" {{ (string)($line['cost_center_id'] ?? '') === (string)$cc->id ? 'selected' : '' }}>
                                                        {{ $cc->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="details[{{ $i }}][memo]" maxlength="255"
                                                   class="form-control"
                                                   value="{{ $line['memo'] ?? '' }}"
                                                   placeholder="Memo (optional)">
                                        </td>
                                        <td>
                                            <button class="btn btn-outline-danger btn-sm remove-line" type="button">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-end">Totals:</th>
                                    <th><input type="text" readonly class="form-control-plaintext fw-bold" id="total-debit" value="0.00"></th>
                                    <th><input type="text" readonly class="form-control-plaintext fw-bold" id="total-credit" value="0.00"></th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="alert alert-info d-flex align-items-center" id="balance-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <div>
                            Ensure <strong>total debit</strong> equals <strong>total credit</strong> before saving.
                            <span id="balance-delta" class="ms-2"></span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('transactions.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="btn-submit">Save Transaction</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<style>
.d-flex .btn {
    display: flex !important;
    align-items: center;
    justify-content: center;
    height: 55px;
    font-size: 1rem;
    padding: 0 24px;
    min-width: 120px;
}
@media (max-width: 576px) {
    .d-flex .btn { width: 32vw; min-width: unset; font-size: 1rem; }
}
#lines-table tfoot input.form-control-plaintext { padding-left: 0; }
.text-monospace { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
</style>

{{-- Simple repeater + totals + guard balanced --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.getElementById('lines-body');
    const addBtn = document.getElementById('add-line');
    const clearBtn = document.getElementById('clear-lines');
    const totalDebitEl = document.getElementById('total-debit');
    const totalCreditEl = document.getElementById('total-credit');
    const balanceDeltaEl = document.getElementById('balance-delta');
    const submitBtn = document.getElementById('btn-submit');

    function templateRow(index) {
        return `
        <tr>
            <td>
                <select name="details[${index}][account_id]" class="form-select details-account" required>
                    <option value="">-- Select Account --</option>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->code ?? '' }} {{ $acc->name ? " - {$acc->name}" : '' }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="details[${index}][debit]" class="form-control details-debit" value="">
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="details[${index}][credit]" class="form-control details-credit" value="">
            </td>
            <td>
                <select name="details[${index}][cost_center_id]" class="form-select">
                    <option value="">-- None --</option>
                    @foreach ($costcenters as $cc)
                        <option value="{{ $cc->id }}">{{ $cc->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="details[${index}][memo]" maxlength="255" class="form-control" placeholder="Memo (optional)">
            </td>
            <td>
                <button class="btn btn-outline-danger btn-sm remove-line" type="button"><i class="bi bi-x"></i></button>
            </td>
        </tr>`;
    }

    function renumberRows() {
        [...body.querySelectorAll('tr')].forEach((tr, idx) => {
            tr.querySelectorAll('select, input').forEach(el => {
                const name = el.getAttribute('name');
                if (!name) return;
                el.setAttribute('name', name.replace(/details\[\d+\]/, `details[${idx}]`));
            });
        });
    }

    function recalc() {
        let d = 0, c = 0, lines = 0;
        body.querySelectorAll('tr').forEach(tr => {
            const dv = parseFloat(tr.querySelector('.details-debit')?.value || 0);
            const cr = parseFloat(tr.querySelector('.details-credit')?.value || 0);
            if (dv > 0 || cr > 0) lines++;
            d += dv; c += cr;
        });
        totalDebitEl.value = d.toFixed(2);
        totalCreditEl.value = c.toFixed(2);
        const diff = d - c;

        // UI hint
        balanceDeltaEl.textContent = diff === 0 && d > 0 && lines >= 2
            ? '(Balanced)'
            : `(Diff: ${diff.toFixed(2)} | Lines: ${lines})`;
        balanceDeltaEl.className = (diff === 0 && d > 0 && lines >= 2) ? 'text-success' : 'text-danger';

        // Guard submit
        submitBtn.disabled = !(diff === 0 && d > 0 && lines >= 2);
    }

    addBtn.addEventListener('click', () => {
        const idx = body.querySelectorAll('tr').length;
        body.insertAdjacentHTML('beforeend', templateRow(idx));
        recalc();
    });

    clearBtn.addEventListener('click', () => {
        body.innerHTML = templateRow(0);
        recalc();
    });

    body.addEventListener('click', (e) => {
        if (e.target.closest('.remove-line')) {
            const rows = body.querySelectorAll('tr');
            if (rows.length > 1) {
                e.target.closest('tr').remove();
                renumberRows();
                recalc();
            }
        }
    });

    body.addEventListener('input', (e) => {
        if (e.target.classList.contains('details-debit') || e.target.classList.contains('details-credit')) {
            // one-sided per row
            const tr = e.target.closest('tr');
            const debit = tr.querySelector('.details-debit');
            const credit = tr.querySelector('.details-credit');
            if (e.target === debit && parseFloat(debit.value || 0) > 0) credit.value = '';
            if (e.target === credit && parseFloat(credit.value || 0) > 0) debit.value = '';
        }
        recalc();
    });

    // initial
    recalc();
});
</script>
@endsection
