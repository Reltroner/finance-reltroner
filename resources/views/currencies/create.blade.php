{{-- resources/views/currencies/create.blade.php --}}
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
                <h3>Create Currency</h3>
                <p class="text-subtitle text-muted">Add a new currency for use in financial transactions</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('currencies.index') }}">Currencies</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">New Currency Form</h5>
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

                <form action="{{ route('currencies.store') }}" method="POST" autocomplete="off">
                    @csrf

                    <div class="mb-3">
                        <label for="code" class="form-label">Currency Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="code" maxlength="10"
                            class="form-control @error('code') is-invalid @enderror"
                            value="{{ old('code') }}" required autofocus>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Currency Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" maxlength="100"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="symbol" class="form-label">Symbol (Optional)</label>
                        <input type="text" name="symbol" id="symbol" maxlength="10"
                            class="form-control @error('symbol') is-invalid @enderror"
                            value="{{ old('symbol') }}">
                        @error('symbol')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="rate" class="form-label">Currency Rate <span class="text-danger">*</span></label>
                        <input type="number" name="rate" id="rate" step="0.0001" min="0"
                            class="form-control @error('rate') is-invalid @enderror"
                            value="{{ old('rate', 1) }}" required>
                        @error('rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input"
                            value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                        <label for="is_active" class="form-check-label">Active</label>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('currencies.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Currency</button>
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
