{{-- resources/views/transaction-details/edit.blade.php --}}
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
                <h3>Edit Journal Line</h3>
                <p class="text-subtitle text-muted">Update a single GL detail line</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('transaction-details.index') }}">Journal Lines</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        {{-- Header summary of the parent transaction --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Parent Transaction</h5>
                <div class="text-end small">
                    <div class="text-muted">Journal No</div>
                    <div class="text-monospace fw-semibold">{{ $detail->transaction->journal_no ?? ('TX#'.$detail->transaction_id) }}</div>
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Date</dt>
                    <dd class="col-sm-9">{{ \Illuminate\Support\Carbon::parse($detail->transaction->date)->format('Y-m-d') }}</dd>

                    <dt class="col-sm-3">Status</dt>
                    @php
                        $badge = match($detail->transaction->status ?? 'draft') {
                            'posted' => 'success',
                            'voided' => 'danger',
                            default  => 'secondary'
                        };
                    @endphp
                    <dd class="col-sm-9">
                        <span class="badge bg-{{ $badge }}">{{ ucfirst($detail->transaction->status ?? 'draft') }}</span>
                    </dd>

                    <dt class="col-sm-3">Currency</dt>
                    <dd class="col-sm-9">{{ $detail->transaction->currency->code ?? '—' }}</dd>

                    <dt class="col-sm-3">Reference</dt>
                    <dd class="col-sm-9"><span class="text-monospace">{{ $detail->transaction->reference ?? '—' }}</span></dd>
                </dl>
            </div>
        </div>

        {{-- Edit form --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Line #{{ $detail->line_no }}</h5>
                <small class="text-muted">A line must be <strong>one‑sided</strong> (either Debit or Credit) and &gt; 0.</small>
            </div>
            <div class="card-body">
                {{-- Errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('transaction-details.update', $detail->id) }}" method="POST" id="detail-form" autocomplete="off">
                    @csrf
                    @method('PUT')

                    {{-- keep transaction_id immutable --}}
                    <input type="hidden" name="transaction_id" value="{{ $detail->transaction_id }}"/>

                    <div class="row g-3">
                        <div class="col-6 col-md-2">
                            <label for="line_no" class="form-label">Line No</label>
                            <input type="number" name="line_no" id="line_no" min="1" step="1"
                                   class="form-control @error('line_no') is-invalid @enderror"
                                   value="{{ old('line_no', $detail->line_no) }}">
                            @error('line_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Unique per transaction.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="account_id" class="form-label">Account <span class="text-danger">*</span></label>
                            <select name="account_id" id="account_id"
                                    class="form-select @error('account_id') is-invalid @enderror" required>
                                <option value="">-- Select Account --</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}" @selected((int)old('account_id', $detail->account_id)===(int)$acc->id)>
                                        {{ $acc->code }} — {{ $acc->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-6 col-md-2">
                            <label for="debit" class="form-label">Debit</label>
                            <input type="number" name="debit" id="debit" min="0" step="0.01"
                                   class="form-control @error('debit') is-invalid @enderror money-field"
                                   value="{{ old('debit', $detail->debit) }}">
                            @error('debit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-6 col-md-2">
                            <label for="credit" class="form-label">Credit</label>
                            <input type="number" name="credit" id="credit" min="0" step="0.01"
                                   class="form-control @error('credit') is-invalid @enderror money-field"
                                   value="{{ old('credit', $detail->credit) }}">
                            @error('credit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="cost_center_id" class="form-label">Cost Center</label>
                            <select name="cost_center_id" id="cost_center_id"
                                    class="form-select @error('cost_center_id') is-invalid @enderror">
                                <option value="">—</option>
                                @foreach ($costcenters as $cc)
                                    <option value="{{ $cc->id }}" @selected((int)old('cost_center_id', $detail->cost_center_id)===(int)$cc->id)>
                                        {{ $cc->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('cost_center_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label for="memo" class="form-label">Memo</label>
                            <input type="text" name="memo" id="memo" maxlength="255"
                                   class="form-control @error('memo') is-invalid @enderror"
                                   value="{{ old('memo', $detail->memo) }}" placeholder="Optional memo">
                            @error('memo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="alert alert-info mt-4" id="hint">
                        <i class="bi bi-info-circle me-2"></i>
                        This line must be <strong>one‑sided</strong> and &gt; 0. Current status:
                        <span id="oneSideStatus" class="fw-bold text-danger">Invalid</span>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ url()->previous() ?: route('transaction-details.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="btn-submit" disabled>Update Line</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<style>
.text-monospace { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace; }
.d-flex .btn { display:flex!important; align-items:center; justify-content:center; height:46px; }
</style>

{{-- One-sided client guard --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const debit  = document.getElementById('debit');
    const credit = document.getElementById('credit');
    const submit = document.getElementById('btn-submit');
    const status = document.getElementById('oneSideStatus');

    function validateOneSided() {
        const d = parseFloat(debit.value || 0);
        const c = parseFloat(credit.value || 0);

        // normalize negatives
        if (d < 0) debit.value = Math.abs(d).toFixed(2);
        if (c < 0) credit.value = Math.abs(c).toFixed(2);

        const ok = ((d > 0 && c === 0) || (c > 0 && d === 0));

        submit.disabled = !ok;
        status.textContent = ok ? 'OK' : 'Invalid';
        status.classList.toggle('text-danger', !ok);
        status.classList.toggle('text-success', ok);
    }

    debit.addEventListener('input', () => {
        if (parseFloat(debit.value || 0) > 0) credit.value = '';
        validateOneSided();
    });
    credit.addEventListener('input', () => {
        if (parseFloat(credit.value || 0) > 0) debit.value = '';
        validateOneSided();
    });

    validateOneSided();
});
</script>
@endsection
