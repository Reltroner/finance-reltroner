{{-- resources/views/accounts/index.blade.php --}}

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
                <h3>Accounts</h3>
                <p class="text-subtitle text-muted">For account management</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Accounts</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Account List</h5>
                <a href="{{ route('accounts.create') }}" class="btn btn-primary btn-md">New Account</a>
            </div>
            <div class="card-body">
                <!-- Desktop Table -->
                <div class="d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table1">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Parent</th>
                                    <th>Status</th>
                                    <th>Budgets</th>
                                    <th>Children</th>
                                    <th>Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($accounts as $account)
                                    <tr>
                                        <td><span class="font-monospace">{{ $account->code }}</span></td>
                                        <td>{{ $account->name }}</td>
                                        <td>{{ ucfirst($account->type) }}</td>
                                        <td>{{ $account->parent ? $account->parent->name : '-' }}</td>
                                        <td>
                                            @if ($account->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $account->budgets->count() }}
                                        </td>
                                        <td>
                                            {{ $account->children->count() }}
                                        </td>
                                        <td>
                                            <a href="{{ route('accounts.show', $account->id) }}" class="btn btn-info btn-sm mb-1">View</a>
                                            <a href="{{ route('accounts.edit', $account->id) }}" class="btn btn-primary btn-sm mb-1">Edit</a>
                                            <form action="{{ route('accounts.destroy', $account->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure to delete this account?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm mb-1">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{-- Pagination --}}
                        <div class="mt-3">
                            {{ $accounts->links() }}
                        </div>
                    </div>
                </div>
                <!-- Mobile Card Stack -->
                <div class="d-block d-md-none">
                    @foreach ($accounts as $account)
                        <div class="account-list-card mb-3 p-3 border rounded bg-white shadow-sm">
                            <div class="fw-bold mb-1">
                                <span class="font-monospace">{{ $account->code }}</span> - {{ $account->name }}
                            </div>
                            <div style="font-size:15px;">
                                Type: <strong>{{ ucfirst($account->type) }}</strong><br>
                                Parent: <strong>{{ $account->parent ? $account->parent->name : '-' }}</strong><br>
                                Status:
                                @if ($account->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif<br>
                                Budgets: <strong>{{ $account->budgets->count() }}</strong><br>
                                Children: <strong>{{ $account->children->count() }}</strong>
                            </div>
                            <div class="mt-2">
                                <a href="{{ route('accounts.show', $account->id) }}" class="btn btn-info btn-sm mb-1">View</a>
                                <a href="{{ route('accounts.edit', $account->id) }}" class="btn btn-primary btn-sm mb-1">Edit</a>
                                <form action="{{ route('accounts.destroy', $account->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure to delete this account?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm mb-1">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                    <div class="mt-3">
                        {{ $accounts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<style>
@media (max-width: 576px) {
    .account-list-card {
        border: 1px solid #eee;
        border-radius: 13px;
        background: #fff;
        margin-bottom: 16px;
        box-shadow: 0 1px 8px 0 rgba(180,200,230,0.07);
    }
}
</style>
@endsection
