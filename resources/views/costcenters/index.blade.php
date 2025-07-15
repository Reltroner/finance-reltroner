{{-- resources/views/costcenters/index.blade.php --}}

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
                <h3>Cost Centers</h3>
                <p class="text-subtitle text-muted">Manage and review cost center groups here</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Cost Centers</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Cost Center Records</h5>
                <a href="{{ route('costcenters.create') }}" class="btn btn-primary">Add Cost Center</a>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Active</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($costcenters as $costcenter)
                                <tr>
                                    <td>{{ $loop->iteration + ($costcenters->perPage() * ($costcenters->currentPage() - 1)) }}</td>
                                    <td>{{ $costcenter->name }}</td>
                                    <td>{{ $costcenter->description ?? '-' }}</td>
                                    <td>
                                        @if ($costcenter->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('costcenters.edit', $costcenter->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <form action="{{ route('costcenters.destroy', $costcenter->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this cost center?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No cost center records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $costcenters->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
