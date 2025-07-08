{{-- resources/views/budgets/show.blade.php --}}

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
                <h3>Budget Detail</h3>
                <p class="text-subtitle text-muted">Detailed view of the selected budget</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('budgets.index') }}">Budgets</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Show</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Budget Information</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Account</dt>
                    <dd class="col-sm-9">{{ $budget->account->name ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Year</dt>
                    <dd class="col-sm-9">{{ $budget->year }}</dd>

                    <dt class="col-sm-3">Month</dt>
                    <dd class="col-sm-9">{{ $budget->month }}</dd>

                    <dt class="col-sm-3">Planned Budget</dt>
                    <dd class="col-sm-9">{{ number_format($budget->amount, 2) }}</dd>

                    <dt class="col-sm-3">Actual Spent</dt>
                    <dd class="col-sm-9">{{ number_format($budget->actual, 2) }}</dd>

                    <dt class="col-sm-3">Notes</dt>
                    <dd class="col-sm-9">{{ $budget->notes ?? '-' }}</dd>

                    <dt class="col-sm-3">Created At</dt>
                    <dd class="col-sm-9">{{ $budget->created_at->format('Y-m-d H:i:s') }}</dd>

                    <dt class="col-sm-3">Last Updated</dt>
                    <dd class="col-sm-9">{{ $budget->updated_at->format('Y-m-d H:i:s') }}</dd>
                </dl>
                <div>
                    <a href="{{ route('budgets.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                    <a href="{{ route('budgets.edit', $budget->id) }}" class="btn btn-primary btn-sm">Edit Budget</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
