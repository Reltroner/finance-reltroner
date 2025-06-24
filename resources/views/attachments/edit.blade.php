{{-- resources/views/attachments/edit.blade.php --}}

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
                <h3>Edit Attachment</h3>
                <p class="text-subtitle text-muted">You can update the attachment details here</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('attachments.index') }}">Attachments</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Attachment Form</h5>
            </div>

            <div class="card-body">
                <form action="{{ route('attachments.update', $attachment->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="file_name" class="form-label">File Name</label>
                        <input type="text" class="form-control @error('file_name') is-invalid @enderror"
                            name="file_name" id="file_name" value="{{ old('file_name', $attachment->file_name) }}">
                        @error('file_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="transaction_id" class="form-label">Related Transaction</label>
                        <select name="transaction_id" id="transaction_id" class="form-select @error('transaction_id') is-invalid @enderror">
                            <option value="">-- Select Transaction --</option>
                            @foreach ($transactions as $transaction)
                                <option value="{{ $transaction->id }}"
                                    {{ old('transaction_id', $attachment->transaction_id) == $transaction->id ? 'selected' : '' }}>
                                    #{{ $transaction->id }} {{ $transaction->description ? '— ' . $transaction->description : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('transaction_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current File Path</label>
                        <div>
                            <code>{{ $attachment->file_path }}</code>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('attachments.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Attachment</button>
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
