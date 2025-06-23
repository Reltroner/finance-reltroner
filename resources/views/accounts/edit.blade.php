{{-- resources/views/accounts/edit.blade.php --}}

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
                <h3>Edit Account</h3>
                <p class="text-subtitle text-muted">You can update the account details here</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('accounts.index') }}">Accounts</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Account Form</h5>
            </div>

            <div class="card-body">
                <form action="{{ route('accounts.update', $account->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="code" class="form-label">Account Code</label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror"
                            name="code" id="code" value="{{ old('code', $account->code) }}">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Account Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                            name="name" id="name" value="{{ old('name', $account->name) }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror">
                            <option value="">-- Select Type --</option>
                            <option value="asset" {{ old('type', $account->type) == 'asset' ? 'selected' : '' }}>Asset</option>
                            <option value="liability" {{ old('type', $account->type) == 'liability' ? 'selected' : '' }}>Liability</option>
                            <option value="equity" {{ old('type', $account->type) == 'equity' ? 'selected' : '' }}>Equity</option>
                            <option value="income" {{ old('type', $account->type) == 'income' ? 'selected' : '' }}>Income</option>
                            <option value="expense" {{ old('type', $account->type) == 'expense' ? 'selected' : '' }}>Expense</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Parent Account</label>
                        <select name="parent_id" id="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                            <option value="">-- No Parent / Root --</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}"
                                    {{ old('parent_id', $account->parent_id) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }} ({{ $parent->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                            {{ old('is_active', $account->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('accounts.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Account</button>
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
