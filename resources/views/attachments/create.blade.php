@extends('layouts.dashboard')
{{-- resources/views/attachments/create.blade.php --}}
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
                <h3>Upload Attachment</h3>
                <p class="text-subtitle text-muted">You can upload a new attachment for a transaction here</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('attachments.index') }}">Attachments</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Upload</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">New Attachment Form</h5>
            </div>

            <div class="card-body">
                <form action="{{ route('attachments.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="transaction_id" class="form-label">Transaction</label>
                        <select name="transaction_id" id="transaction_id" class="form-select @error('transaction_id') is-invalid @enderror">
                            <option value="">-- Select Transaction --</option>
                            @foreach ($transactions as $transaction)
                                <option value="{{ $transaction->id }}" {{ old('transaction_id') == $transaction->id ? 'selected' : '' }}>
                                    #{{ $transaction->id }} &mdash; {{ $transaction->description ?? '(No description)' }}
                                </option>
                            @endforeach
                        </select>
                        @error('transaction_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="file" class="form-label">Attachment File</label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror"
                            name="file" id="file" required>
                        <small class="text-muted">Max: 4MB. Allowed: PDF, images, doc, xls, etc.</small>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('attachments.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Upload Attachment</button>
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
