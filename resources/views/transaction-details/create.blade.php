{{-- resources/views/transaction-details/create.blade.php --}}
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
                <h3>Add Journal Line</h3>
                <p class="text-subtitle text-muted">Create a single GL line for an existing transaction</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('transactions.index') }}">Transactions</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add Line</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">New Journal Line</h5>
                <small class="text-muted">
                    Each line must be <strong>one‑sided</strong> (either Debit or Credit) &gt; 0.
                </small>
            </div>

            <div class="card-body">
                {{-- Global errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('transaction-details.store') }}" method="POST" id="detail-form" autocomplete="off">
                    @csrf

                    <div class="row g-3">
                        {{-- Transaction selector --}}
                        <div class="col-12 col-md-6">
                            <label for="transaction_id" class="form-label">Transaction <span class="text-danger">*</span></label>
                            <select name="transaction_id" id="transaction_id"
                                    class="form-select @error('transaction_id') is-invalid @enderror" required>
                                <option value="">-- Select Transaction --</option>
                                @php
                                    // Expecting $transactions (id, journal_no, date, reference) from controller.
                                    // If not provided, fallback to old value only.
                                @endphp
                                @isset($transactions)
                                    @foreach ($transactions as $t)
                                        <option value="{{ $t->id }}" {{ (string)old('transaction_id', request('transaction_id'))===(string)$t->id ? 'selected' : '' }}>
                                            {{ $t->journal_no ?? ('TX#'.$t->id) }}
                                            — {{ \Carbon\Carbon::parse($t->date)->format('d M Y') }}
                                            @if (!empty($t->reference)) — Ref: {{ $t->reference }} @endif
                                        </option>
                                    @endforeach
                                @endisset
                                @if (!isset($transactions) && old('transaction_id'))
                                    <option value="{{ old('transaction_id') }}" selected>#{{ old('transaction_id') }}</option>
                                @endif
                            </select>
                            @error('transaction_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Choose the journal (header) to which this line belongs.</div>
                        </div>

                        {{-- Optional line_no (unique per transaction) --}}
                        <div class="col-6 col-md-3">
                            <label for="line_no" class="form-label">Line No</label>
                            <input type="number" name="line_no" id="line_no" min="1" step="1"
                                   class="form-control @error('line_no') is-invalid @enderror"
                                   value="{{ old('line_no') }}" placeholder="Auto">
                            @error('line_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Leave empty to auto‑sequence.</div>
                        </div>

                        {{-- Account --}}
                        <div class="col-12 col-md-6">
                            <label for="account_id" class="form-label">Account <span class="text-danger">*</span></label>
                            <select name="account_id" id="account_id"
                                    class="form-select @error('account_id') is-invalid @enderror" required>
                                <option value="">-- Select Account --</option>
                                @foreach ($accounts ?? [] as $acc)
                                    <option value="{{ $acc->id }}" {{ (string)old('account_id')===(string)$acc->id ? 'selected' : '' }}>
                                        {{ $acc->code }} — {{ $acc->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Debit / Credit (one-sided) --}}
                        <div class="col-6 col-md-3">
                            <label for="debit" class="form-label">Debit</label>
                            <input type="number" name="debit" id="debit" min="0" step="0.01"
                                   class="form-control @error('debit') is-invalid @enderror money-field"
                                   value="{{ old('debit') }}">
                            @error('debit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="credit" class="form-label">Credit</label>
                            <input type="number" name="credit" id="credit" min="0" step="0.01"
                                   class="form-control @error('credit') is-invalid @enderror money-field"
                                   value="{{ old('credit') }}">
                            @error('credit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Cost Center --}}
                        <div class="col-12 col-md-6">
                            <label for="cost_center_id" class="form-label">Cost Center</label>
                            <select name="cost_center_id" id="cost_center_id"
                                    class="form-select @error('cost_center_id') is-invalid @enderror">
                                <option value="">—</option>
                                @foreach ($costcenters ?? [] as $cc)
                                    <option value="{{ $cc->id }}" {{ (string)old('cost_center_id')===(string)$cc->id ? 'selected' : '' }}>
                                        {{ $cc->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('cost_center_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Memo --}}
                        <div class="col-12">
                            <label for="memo" class="form-label">Memo</label>
                            <input type="text" name="memo" id="memo" maxlength="255"
                                   class="form-control @error('memo') is-invalid @enderror"
                                   value="{{ old('memo') }}" placeholder="Optional memo">
                            @error('memo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- One-sided / status hint --}}
                    <div class="alert alert-info mt-4" id="hint">
                        <i class="bi bi-info-circle me-2"></i>
                        The line must be <strong>one‑sided</strong> and &gt; 0. Current status:
                        <span id="oneSideStatus" class="fw-bold text-danger">Invalid</span>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ url()->previous() ?: route('transactions.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="btn-submit" disabled>Save Line</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<style>
.d-flex .btn { display:flex!important; align-items:center; justify-content:center; height:46px; }
.font-mono, .text-monospace {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace;
}
</style>

{{-- One-sided guard (client-side) --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const debit  = document.getElementById('debit');
    const credit = document.getElementById('credit');
    const submit = document.getElementById('btn-submit');
    const status = document.getElementById('oneSideStatus');

    function validateOneSided() {
        const d = parseFloat(debit.value || 0);
        const c = parseFloat(credit.value || 0);

        // normalize negatives on UI level
        if (d < 0) debit.value = Math.abs(d).toFixed(2);
        if (c < 0) credit.value = Math.abs(c).toFixed(2);

        // one-sided rule
        const ok = ((d > 0 && c === 0) || (c > 0 && d === 0));

        submit.disabled = !ok;
        status.textContent = ok ? 'OK' : 'Invalid';
        status.classList.toggle('text-danger', !ok);
        status.classList.toggle('text-success', ok);
    }

    // prevent typing both sides
    debit.addEventListener('input', () => {
        if (parseFloat(debit.value || 0) > 0) {
            credit.value = '';
        }
        validateOneSided();
    });
    credit.addEventListener('input', () => {
        if (parseFloat(credit.value || 0) > 0) {
            debit.value = '';
        }
        validateOneSided();
    });

    validateOneSided();
});
</script>
@endsection
