{{-- resources/views/costcenters/edit.blade.php --}}

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
                <h3>Edit Cost Center</h3>
                <p class="text-subtitle text-muted">Update cost center information here</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('costcenters.index') }}">Cost Centers</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Cost Center Form</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('costcenters.update', $costcenter->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Cost Center Name</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $costcenter->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $costcenter->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="is_active" class="form-label">Status</label>
                        <select name="is_active" id="is_active" class="form-select @error('is_active') is-invalid @enderror">
                            <option value="1" {{ old('is_active', $costcenter->is_active) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active', $costcenter->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('costcenters.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Cost Center</button>
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
