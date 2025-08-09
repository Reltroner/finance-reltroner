{{-- resources/views/transactions/edit.blade.php --}}
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
                <h3>Edit Transaction</h3>
                <p class="text-subtitle text-muted">Update header fields and journal lines below</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('transactions.index') }}">Transactions</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Edit Transaction Form</h5>
                <div class="text-end small">
                    <div class="text-muted">Journal No</div>
                    <div class="text-monospace fw-semibold">{{ $transaction->journal_no ?? '—' }}</div>
                </div>
            </div>

            <div class="card-body">
                {{-- Global validation errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('transactions.update', $transaction->id) }}" method="POST" autocomplete="off" id="tx-form">
                    @csrf
                    @method('PUT')

                    {{-- Header fields --}}
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            @php
                                $statusNow = old('status', $transaction->status ?? 'draft');
                            @endphp
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                @foreach (['draft' => 'Draft', 'posted' => 'Posted', 'voided' => 'Voided'] as $val => $label)
                                    <option value="{{ $val }}" @selected($statusNow===$val)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Changing to <strong>Posted</strong> will stamp posted_at/by.</div>
                        </div>

                        <div class="col-md-3">
                            <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date"
                                   class="form-control @error('date') is-invalid @enderror"
                                   value="{{ old('date', optional($transaction->date)->format('Y-m-d')) }}" required>
                            @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Fiscal period will follow this date.</div>
                        </div>

                        <div class="col-md-3">
                            <label for="currency_id" class="form-label">Currency <span class="text-danger">*</span></label>
                            <select name="currency_id" id="currency_id" class="form-select @error('currency_id') is-invalid @enderror" required>
                                <option value="">-- Select --</option>
                                @foreach ($currencies as $c)
                                    <option value="{{ $c->id }}" @selected((int)old('currency_id', $transaction->currency_id)===(int)$c->id)>
                                        {{ $c->code }} {{ $c->name ? '— '.$c->name : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('currency_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="exchange_rate" class="form-label">Exchange Rate to Base</label>
                            <input type="number" step="0.0000000001" min="0.0000000001"
                                   name="exchange_rate" id="exchange_rate"
                                   class="form-control @error('exchange_rate') is-invalid @enderror"
                                   value="{{ old('exchange_rate', $transaction->exchange_rate ?? 1) }}">
                            @error('exchange_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Leave as 1 if base currency.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="reference" class="form-label">Reference</label>
                            <input type="text" name="reference" id="reference" maxlength="255"
                                   class="form-control @error('reference') is-invalid @enderror"
                                   value="{{ old('reference', $transaction->reference) }}" placeholder="e.g. INV-2025-001">
                            @error('reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="2" maxlength="1000"
                                      class="form-control @error('description') is-invalid @enderror"
                            >{{ old('description', $transaction->description) }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Details table --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Journal Lines</h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-line">
                                <i class="bi bi-plus-lg"></i> Add Line
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="recalc">
                                <i class="bi bi-arrow-repeat"></i> Recalculate
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped align-middle" id="lines-table">
                            <thead>
                                <tr>
                                    <th style="min-width:220px;">Account</th>
                                    <th style="min-width:120px;" class="text-end">Debit</th>
                                    <th style="min-width:120px;" class="text-end">Credit</th>
                                    <th style="min-width:200px;">Cost Center</th>
                                    <th>Memo</th>
                                    <th style="width:70px;"></th>
                                </tr>
                            </thead>
                            <tbody id="lines-body">
                                @php
                                    $oldLines = collect(old('details', $transaction->details->map(function($d){
                                        return [
                                            'account_id'     => $d->account_id,
                                            'debit'          => $d->debit,
                                            'credit'         => $d->credit,
                                            'cost_center_id' => $d->cost_center_id,
                                            'memo'           => $d->memo,
                                        ];
                                    })->toArray()));
                                @endphp

                                @forelse ($oldLines as $i => $line)
                                    <tr data-index="{{ $i }}">
                                        <td>
                                            <select name="details[{{ $i }}][account_id]" class="form-select @error("details.$i.account_id") is-invalid @enderror" required>
                                                <option value="">-- Select --</option>
                                                @foreach ($accounts as $acc)
                                                    <option value="{{ $acc->id }}" @selected((int)($line['account_id'] ?? 0)===(int)$acc->id)>
                                                        {{ $acc->code ?? '' }} {{ $acc->name ? '— '.$acc->name : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("details.$i.account_id") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0"
                                                   name="details[{{ $i }}][debit]"
                                                   class="form-control text-end money-field details-debit @error("details.$i.debit") is-invalid @enderror"
                                                   value="{{ old("details.$i.debit", $line['debit'] ?? 0) }}">
                                            @error("details.$i.debit") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0"
                                                   name="details[{{ $i }}][credit]"
                                                   class="form-control text-end money-field details-credit @error("details.$i.credit") is-invalid @enderror"
                                                   value="{{ old("details.$i.credit", $line['credit'] ?? 0) }}">
                                            @error("details.$i.credit") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </td>
                                        <td>
                                            <select name="details[{{ $i }}][cost_center_id]" class="form-select @error("details.$i.cost_center_id") is-invalid @enderror">
                                                <option value="">—</option>
                                                @foreach ($costcenters as $cc)
                                                    <option value="{{ $cc->id }}" @selected((int)($line['cost_center_id'] ?? 0)===(int)$cc->id)>
                                                        {{ $cc->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("details.$i.cost_center_id") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </td>
                                        <td>
                                            <input type="text" maxlength="255"
                                                   name="details[{{ $i }}][memo]"
                                                   class="form-control @error("details.$i.memo") is-invalid @enderror"
                                                   value="{{ old("details.$i.memo", $line['memo'] ?? '') }}">
                                            @error("details.$i.memo") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-line" title="Remove">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    @for ($i = 0; $i < 2; $i++)
                                        <tr data-index="{{ $i }}">
                                            <td>
                                                <select name="details[{{ $i }}][account_id]" class="form-select" required>
                                                    <option value="">-- Select --</option>
                                                    @foreach ($accounts as $acc)
                                                        <option value="{{ $acc->id }}">{{ $acc->code ?? '' }} {{ $acc->name ? '— '.$acc->name : '' }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" step="0.01" min="0" name="details[{{ $i }}][debit]"  class="form-control text-end money-field details-debit" value="0"></td>
                                            <td><input type="number" step="0.01" min="0" name="details[{{ $i }}][credit]" class="form-control text-end money-field details-credit" value="0"></td>
                                            <td>
                                                <select name="details[{{ $i }}][cost_center_id]" class="form-select">
                                                    <option value="">—</option>
                                                    @foreach ($costcenters as $cc)
                                                        <option value="{{ $cc->id }}">{{ $cc->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="text" maxlength="255" name="details[{{ $i }}][memo]" class="form-control"></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-line"><i class="bi bi-x"></i></button>
                                            </td>
                                        </tr>
                                    @endfor
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th class="text-end">Totals</th>
                                    <th class="text-end"><span id="sum-debit">0.00</span></th>
                                    <th class="text-end"><span id="sum-credit">0.00</span></th>
                                    <th colspan="3"></th>
                                </tr>
                                <tr>
                                    <th class="text-end">Exchange Rate</th>
                                    <th colspan="2">
                                        <span class="text-monospace" id="sum-exrate">{{ old('exchange_rate', $transaction->exchange_rate ?? 1) }}</span>
                                    </th>
                                    <th colspan="3"></th>
                                </tr>
                                <tr>
                                    <th class="text-end">Totals (Base)</th>
                                    <th class="text-end"><span id="sum-debit-base">0.00</span></th>
                                    <th class="text-end"><span id="sum-credit-base">0.00</span></th>
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
                        <button type="submit" class="btn btn-primary" id="btn-submit">Update Transaction</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<template id="line-template">
    <tr data-index="__INDEX__">
        <td>
            <select name="details[__INDEX__][account_id]" class="form-select" required>
                <option value="">-- Select --</option>
                @foreach ($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->code ?? '' }} {{ $acc->name ? '— '.$acc->name : '' }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" step="0.01" min="0" name="details[__INDEX__][debit]"  class="form-control text-end money-field details-debit" value="">
        </td>
        <td>
            <input type="number" step="0.01" min="0" name="details[__INDEX__][credit]" class="form-control text-end money-field details-credit" value="">
        </td>
        <td>
            <select name="details[__INDEX__][cost_center_id]" class="form-select">
                <option value="">—</option>
                @foreach ($costcenters as $cc)
                    <option value="{{ $cc->id }}">{{ $cc->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="text" maxlength="255" name="details[__INDEX__][memo]" class="form-control"></td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm remove-line"><i class="bi bi-x"></i></button>
        </td>
    </tr>
</template>

{{-- Simple helpers --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tableBody  = document.querySelector('#lines-table tbody');
    const addBtn     = document.getElementById('add-line');
    const recalcBtn  = document.getElementById('recalc');
    const tplHtml    = document.getElementById('line-template').innerHTML;
    const exRateEl   = document.getElementById('exchange_rate');
    const submitBtn  = document.getElementById('btn-submit');

    const sumDebitEl = document.getElementById('sum-debit');
    const sumCreditEl= document.getElementById('sum-credit');
    const sumExrateEl= document.getElementById('sum-exrate');
    const sumDebitBEl= document.getElementById('sum-debit-base');
    const sumCreditBEl=document.getElementById('sum-credit-base');
    const balanceDeltaEl = document.getElementById('balance-delta');

    function getNextIndex() {
        const rows = [...tableBody.querySelectorAll('tr[data-index]')];
        const idxs = rows.map(r => parseInt(r.getAttribute('data-index'), 10)).filter(n => !isNaN(n));
        return (idxs.length ? Math.max(...idxs) + 1 : 0);
    }

    function recalc() {
        let d = 0, c = 0, lines = 0;
        tableBody.querySelectorAll('tr').forEach(tr => {
            const dv = parseFloat(tr.querySelector('.details-debit')?.value || 0);
            const cr = parseFloat(tr.querySelector('.details-credit')?.value || 0);
            if (dv > 0 || cr > 0) lines++;
            d += dv; c += cr;
        });

        const rate = parseFloat(exRateEl?.value || 1);
        const dBase = d * rate, cBase = c * rate;

        sumDebitEl.textContent   = d.toFixed(2);
        sumCreditEl.textContent  = c.toFixed(2);
        sumExrateEl.textContent  = rate > 0 ? rate : 1;
        sumDebitBEl.textContent  = dBase.toFixed(2);
        sumCreditBEl.textContent = cBase.toFixed(2);

        const diff = d - c;
        balanceDeltaEl.textContent = (diff === 0 && d > 0 && lines >= 2)
            ? '(Balanced)'
            : `(Diff: ${diff.toFixed(2)} | Lines: ${lines})`;
        balanceDeltaEl.className = (diff === 0 && d > 0 && lines >= 2) ? 'text-success' : 'text-danger';

        submitBtn.disabled = !(diff === 0 && d > 0 && lines >= 2);
    }

    function enforceOneSided(tr, changedEl) {
        const debit = tr.querySelector('.details-debit');
        const credit= tr.querySelector('.details-credit');
        if (changedEl === debit && parseFloat(debit.value || 0) > 0) credit.value = '';
        if (changedEl === credit && parseFloat(credit.value || 0) > 0) debit.value = '';
    }

    addBtn?.addEventListener('click', () => {
        const idx = getNextIndex();
        tableBody.insertAdjacentHTML('beforeend', tplHtml.replaceAll('__INDEX__', idx));
        recalc();
    });

    tableBody.addEventListener('click', (e) => {
        if (e.target.closest('.remove-line')) {
            e.target.closest('tr')?.remove();
            recalc();
        }
    });

    tableBody.addEventListener('input', (e) => {
        if (e.target.classList.contains('money-field')) {
            enforceOneSided(e.target.closest('tr'), e.target);
            recalc();
        }
    });

    exRateEl?.addEventListener('input', recalc);
    recalcBtn?.addEventListener('click', recalc);

    // initial
    recalc();
});
</script>

<style>
#lines-table td, #lines-table th { white-space: nowrap; }
#lines-table input[type="number"]::-webkit-outer-spin-button,
#lines-table input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
#lines-table input[type="number"] { -moz-appearance: textfield; }
.text-monospace { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace; }
.d-flex .btn { display:flex!important; align-items:center; justify-content:center; height:48px; }
</style>
@endsection
