{{-- resources/views/budgets/index.blade.php --}}

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
                <h3>Budgets</h3>
                <p class="text-subtitle text-muted">Manage and review budgets here</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Budgets</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Budget Records</h5>
                <a href="{{ route('budgets.create') }}" class="btn btn-primary">Add Budget</a>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Account</th>
                                <th>Year</th>
                                <th>Month</th>
                                <th>Budget</th>
                                <th>Actual</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($budgets as $budget)
                                <tr>
                                    <td>{{ $loop->iteration + ($budgets->perPage() * ($budgets->currentPage() - 1)) }}</td>
                                    <td>{{ $budget->account->name ?? 'N/A' }}</td>
                                    <td>{{ $budget->year }}</td>
                                    <td>{{ $budget->month }}</td>
                                    <td>{{ number_format($budget->amount, 2) }}</td>
                                    <td>{{ number_format($budget->actual, 2) }}</td>
                                    <td>
                                        <a href="{{ route('budgets.edit', $budget->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <form action="{{ route('budgets.destroy', $budget->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this budget?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No budget records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $budgets->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
