{{-- resources/views/budgets/create.blade.php --}}
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
                <h3>Create Budget</h3>
                <p class="text-subtitle text-muted">Define a new budget entry for a specific account, year, and month</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('budgets.index') }}">Budgets</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">New Budget Form</h5>
            </div>

            <div class="card-body">
                <form action="{{ route('budgets.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="account_id" class="form-label">Account</label>
                        <select name="account_id" id="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                            <option value="">-- Select Account --</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('account_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="year" class="form-label">Year</label>
                            <input type="number" name="year" id="year" class="form-control @error('year') is-invalid @enderror" value="{{ old('year') }}" required>
                            @error('year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="month" class="form-label">Month</label>
                            <input type="number" name="month" id="month" class="form-control @error('month') is-invalid @enderror" value="{{ old('month') }}" required>
                            @error('month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Planned Amount</label>
                        <input type="number" step="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="actual" class="form-label">Actual Amount (Optional)</label>
                        <input type="number" step="0.01" name="actual" id="actual" class="form-control @error('actual') is-invalid @enderror" value="{{ old('actual', 0) }}">
                        @error('actual')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('budgets.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Budget</button>
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
    .d-flex .btn {
        width: 32vw;
        min-width: unset;
        font-size: 1rem;
    }
}
</style>
@endsection
{{-- resources/views/budgets/create.blade.php --}}