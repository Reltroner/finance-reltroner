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
                <h3>Audit Log Detail</h3>
                <p class="text-subtitle text-muted">Detailed view of the audit record and changes</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('auditlogs.index') }}">Audit Logs</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Show</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Audit Information</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Model</dt>
                    <dd class="col-sm-9">{{ $auditlog->auditable_type }}</dd>

                    <dt class="col-sm-3">Model ID</dt>
                    <dd class="col-sm-9">{{ $auditlog->auditable_id }}</dd>

                    <dt class="col-sm-3">Action</dt>
                    <dd class="col-sm-9">{{ ucfirst($auditlog->event) }}</dd>

                    <dt class="col-sm-3">User</dt>
                    <dd class="col-sm-9">{{ $auditlog->user?->name ?? 'System' }}</dd>

                    <dt class="col-sm-3">IP Address</dt>
                    <dd class="col-sm-9">{{ $auditlog->ip_address }}</dd>

                    <dt class="col-sm-3">Changes</dt>
                    <dd class="col-sm-9">
                        <pre class="bg-light p-3 border rounded">{{ json_encode($auditlog->changes, JSON_PRETTY_PRINT) }}</pre>
                    </dd>

                    <dt class="col-sm-3">Created At</dt>
                    <dd class="col-sm-9">{{ $auditlog->created_at?->format('Y-m-d H:i:s') }}</dd>
                </dl>
                <div>
                    <a href="{{ route('auditlogs.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
