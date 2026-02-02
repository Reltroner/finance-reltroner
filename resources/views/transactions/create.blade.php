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
            <div class="col-12 col-md-6">
                <h3>Create Transaction</h3>
                <p class="text-subtitle text-muted">
                    General journal entry (STEP 5.2B.4 compliant)
                </p>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">New Transaction</h5>
                <small class="text-muted">
                    Journal No will be generated automatically
                </small>
            </div>

            <div class="card-body">

                {{-- GLOBAL ERROR --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('transactions.store') }}" method="POST" id="tx-form">
                    @csrf

                    {{-- Explicit transaction type (contract) --}}
                    <input type="hidden" name="type" value="general">

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date"
                                   name="date"
                                   class="form-control @error('date') is-invalid @enderror"
                                   value="{{ old('date', now()->toDateString()) }}"
                                   required>
                            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Currency *</label>
                            <select name="currency_id"
                                    class="form-select @error('currency_id') is-invalid @enderror"
                                    required>
                                <option value="">-- Select --</option>
                                @foreach($currencies as $c)
                                    <option value="{{ $c->id }}" @selected(old('currency_id')==$c->id)>
                                        {{ $c->code }} {{ $c->symbol ? "({$c->symbol})" : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('currency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Exchange Rate</label>
                            <input type="number"
                                   name="exchange_rate"
                                   step="0.0000000001"
                                   min="0.0000000001"
                                   class="form-control"
                                   value="{{ old('exchange_rate', '1') }}">
                            <div class="form-text">1 if base currency</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="draft" @selected(old('status','draft')==='draft')>Draft</option>
                                <option value="posted" @selected(old('status')==='posted')>
                                    Posted (Final)
                                </option>
                            </select>
                            <div class="form-text text-warning">
                                ⚠ Posted journal is immutable after save
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reference</label>
                        <input type="text"
                               name="reference"
                               class="form-control"
                               value="{{ old('reference') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description"
                                  class="form-control"
                                  rows="2"
                                  maxlength="1000">{{ old('description') }}</textarea>
                    </div>

                    <hr>

                    {{-- LINES --}}
                    <div class="d-flex justify-content-between mb-2">
                        <h6>Transaction Lines</h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-line">
                                + Add Line
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-lines">
                                Clear
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle" id="lines-table">
                            <thead>
                                <tr>
                                    <th>Account</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                    <th>Cost Center</th>
                                    <th>Memo</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="lines-body">
                                <tr>
                                    <td>
                                        <select name="details[0][account_id]" class="form-select" required>
                                            <option value="">-- Select --</option>
                                            @foreach($accounts as $a)
                                                <option value="{{ $a->id }}">
                                                    {{ $a->code }} - {{ $a->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" step="0.01" min="0" name="details[0][debit]" class="form-control details-debit"></td>
                                    <td><input type="number" step="0.01" min="0" name="details[0][credit]" class="form-control details-credit"></td>
                                    <td>
                                        <select name="details[0][cost_center_id]" class="form-select">
                                            <option value="">-- None --</option>
                                            @foreach($costcenters as $cc)
                                                <option value="{{ $cc->id }}">{{ $cc->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="details[0][memo]" class="form-control"></td>
                                    <td></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-end">Totals</th>
                                    <th id="total-debit">0.00</th>
                                    <th id="total-credit">0.00</th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="alert alert-info" id="balance-info">
                        Total debit must equal total credit (≥ 2 lines)
                        <span id="balance-delta" class="ms-2"></span>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('transactions.index') }}" class="btn btn-secondary me-2">
                            Cancel
                        </a>
                        <button class="btn btn-primary" id="btn-submit" disabled>
                            Save Transaction
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

{{-- JS LOGIC UNCHANGED, ONLY GUARDED --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const body = document.getElementById('lines-body');
    const add = document.getElementById('add-line');
    const clear = document.getElementById('clear-lines');
    const btn = document.getElementById('btn-submit');
    const td = document.getElementById('total-debit');
    const tc = document.getElementById('total-credit');
    const delta = document.getElementById('balance-delta');

    function recalc() {
        let d=0,c=0,l=0;
        body.querySelectorAll('tr').forEach(tr=>{
            const dv=+tr.querySelector('.details-debit').value||0;
            const cr=+tr.querySelector('.details-credit').value||0;
            if(dv||cr) l++;
            d+=dv; c+=cr;
        });
        td.textContent=d.toFixed(2);
        tc.textContent=c.toFixed(2);
        const ok=(d===c && d>0 && l>=2);
        delta.textContent = ok ? '(Balanced)' : `(Diff: ${(d-c).toFixed(2)})`;
        delta.className = ok ? 'text-success' : 'text-danger';
        btn.disabled = !ok;
    }

    body.addEventListener('input', e=>{
        const tr=e.target.closest('tr');
        if(e.target.classList.contains('details-debit') && +e.target.value>0)
            tr.querySelector('.details-credit').value='';
        if(e.target.classList.contains('details-credit') && +e.target.value>0)
            tr.querySelector('.details-debit').value='';
        recalc();
    });

    add.onclick=()=>{
        const i=body.children.length;
        body.insertAdjacentHTML('beforeend', body.children[0].outerHTML.replace(/\[0\]/g,`[${i}]`));
        recalc();
    };

    clear.onclick=()=>{
        body.innerHTML=body.children[0].outerHTML;
        recalc();
    };

    recalc();
});
</script>
@endsection
