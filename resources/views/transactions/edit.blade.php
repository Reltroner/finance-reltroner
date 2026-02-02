{{-- resources/views/transactions/edit.blade.php --}}
@extends('layouts.dashboard')

@section('content')
@php
    $isImmutable = in_array($transaction->status, ['posted','voided'], true);
@endphp

<header class="mb-3">
    <a href="#" class="burger-btn d-block d-xl-none">
        <i class="bi bi-justify fs-3"></i>
    </a>
</header>

{{-- IMMUTABILITY WARNING --}}
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
                <h3>Edit Transaction</h3>
                <p class="text-muted mb-0">
                    {{ $isImmutable ? 'View journal details (locked)' : 'Update draft transaction' }}
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="small text-muted">Journal No</div>
                <div class="text-monospace fw-semibold">{{ $transaction->journal_no }}</div>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('transactions.update', $transaction->id) }}"
                      method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')

                    {{-- HEADER --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select"
                                    {{ $isImmutable ? 'disabled' : '' }}>
                                @foreach (['draft','posted','voided'] as $s)
                                    <option value="{{ $s }}" @selected($transaction->status === $s)>
                                        {{ ucfirst($s) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date"
                                   class="form-control"
                                   value="{{ optional($transaction->date)->format('Y-m-d') }}"
                                   {{ $isImmutable ? 'disabled' : '' }}>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Currency</label>
                            <select name="currency_id" class="form-select"
                                    {{ $isImmutable ? 'disabled' : '' }}>
                                @foreach ($currencies as $c)
                                    <option value="{{ $c->id }}"
                                        @selected($transaction->currency_id === $c->id)>
                                        {{ $c->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Exchange Rate</label>
                            <input type="number" step="0.0000000001"
                                   name="exchange_rate"
                                   class="form-control"
                                   value="{{ $transaction->exchange_rate }}"
                                   {{ $isImmutable ? 'disabled' : '' }}>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Reference</label>
                            <input type="text" name="reference"
                                   class="form-control"
                                   value="{{ $transaction->reference }}"
                                   {{ $isImmutable ? 'disabled' : '' }}>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="2"
                                      class="form-control"
                                      {{ $isImmutable ? 'disabled' : '' }}>{{ $transaction->description }}</textarea>
                        </div>
                    </div>

                    {{-- DETAILS --}}
                    <h6 class="mb-2">Journal Lines</h6>
                    <div class="table-responsive">
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
                                @foreach ($transaction->details as $i => $d)
                                    <tr>
                                        <td>
                                            {{ $d->account->code }} — {{ $d->account->name }}
                                        </td>
                                        <td class="text-end">{{ number_format($d->debit,2) }}</td>
                                        <td class="text-end">{{ number_format($d->credit,2) }}</td>
                                        <td>{{ $d->costCenter->name ?? '—' }}</td>
                                        <td>{{ $d->memo ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- ACTIONS --}}
                    <div class="d-flex justify-content-end mt-3 gap-2">
                        <a href="{{ route('transactions.index') }}"
                           class="btn btn-secondary">
                            Back
                        </a>

                        @unless($isImmutable)
                            <button type="submit" class="btn btn-primary">
                                Update Transaction
                            </button>
                        @endunless
                    </div>

                </form>
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
