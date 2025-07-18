{{-- resources/views/currencies/show.blade.php --}}

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
                <h3>Currency Detail</h3>
                <p class="text-subtitle text-muted">Detailed view of the selected currency</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('currencies.index') }}">Currencies</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Show</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Currency Information</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Code</dt>
                    <dd class="col-sm-9">{{ $currency->code }}</dd>

                    <dt class="col-sm-3">Name</dt>
                    <dd class="col-sm-9">{{ $currency->name }}</dd>

                    <dt class="col-sm-3">Symbol</dt>
                    <dd class="col-sm-9">{{ $currency->symbol ?? '-' }}</dd>

                    <dt class="col-sm-3">Active</dt>
                    <dd class="col-sm-9">
                        @if ($currency->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </dd>

                    <dt class="col-sm-3">Created At</dt>
                    <dd class="col-sm-9">{{ $currency->created_at?->format('Y-m-d H:i:s') }}</dd>

                    <dt class="col-sm-3">Last Updated</dt>
                    <dd class="col-sm-9">{{ $currency->updated_at?->format('Y-m-d H:i:s') }}</dd>
                </dl>
                <div>
                    <a href="{{ route('currencies.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                    <a href="{{ route('currencies.edit', $currency->id) }}" class="btn btn-primary btn-sm">Edit Currency</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
