{{-- resources/views/accounts/show.blade.php --}}
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
                <h3>Account Detail</h3>
                <p class="text-subtitle text-muted">Account information and relations</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('accounts.index') }}">Accounts</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Show</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Account Information</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Account Code</dt>
                    <dd class="col-sm-9"><span class="font-monospace">{{ $account->code }}</span></dd>

                    <dt class="col-sm-3">Name</dt>
                    <dd class="col-sm-9">{{ $account->name }}</dd>

                    <dt class="col-sm-3">Type</dt>
                    <dd class="col-sm-9"><span class="badge bg-info text-dark">{{ ucfirst($account->type) }}</span></dd>

                    <dt class="col-sm-3">Parent Account</dt>
                    <dd class="col-sm-9">{{ $account->parent ? $account->parent->name : '-' }}</dd>

                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">
                        @if ($account->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </dd>

                    <dt class="col-sm-3">Budgets</dt>
                    <dd class="col-sm-9">
                        @if ($account->budgets->count())
                            <ul class="mb-0">
                                @foreach ($account->budgets as $budget)
                                    <li>
                                        Year: {{ $budget->year }},
                                        Month: {{ $budget->month }},
                                        Amount: {{ number_format($budget->amount, 2) }},
                                        Actual: {{ number_format($budget->actual, 2) }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <em>No budgets linked</em>
                        @endif
                    </dd>

                    <dt class="col-sm-3">Child Accounts</dt>
                    <dd class="col-sm-9">
                        @if ($account->children->count())
                            <ul class="mb-0">
                                @foreach ($account->children as $child)
                                    <li>
                                        <a href="{{ route('accounts.show', $child->id) }}">{{ $child->name }} <span class="font-monospace">({{ $child->code }})</span></a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <em>No child accounts</em>
                        @endif
                    </dd>

                    <dt class="col-sm-3">Created At</dt>
                    <dd class="col-sm-9">{{ $account->created_at?->format('Y-m-d H:i:s') }}</dd>

                    <dt class="col-sm-3">Updated At</dt>
                    <dd class="col-sm-9">{{ $account->updated_at?->format('Y-m-d H:i:s') }}</dd>

                </dl>
                <div>
                        <a href="{{ route('accounts.edit', $account->id) }}" class="btn btn-primary btn-sm">Edit</a>
                        <a href="{{ route('accounts.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
