{{-- resources/views/attachments/show.blade.php --}}
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
                <h3>Attachment Detail</h3>
                <p class="text-subtitle text-muted">Attachment file information and related transaction</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('attachments.index') }}">Attachments</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Show</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Attachment Information</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">File Name</dt>
                    <dd class="col-sm-9">
                        <span class="font-monospace">{{ $attachment->file_name }}</span>
                    </dd>

                    <dt class="col-sm-3">Transaction</dt>
                    <dd class="col-sm-9">
                        @if ($attachment->transaction)
                            <a href="{{ route('transactions.show', $attachment->transaction->id) }}">
                                #{{ $attachment->transaction->id }} 
                                {{ $attachment->transaction->description ? '— ' . $attachment->transaction->description : '' }}
                            </a>
                        @else
                            <em>Not linked</em>
                        @endif
                    </dd>

                    <dt class="col-sm-3">File Path</dt>
                    <dd class="col-sm-9">
                        <code>{{ $attachment->file_path }}</code>
                    </dd>

                    <dt class="col-sm-3">Download</dt>
                    <dd class="col-sm-9">
                        <a href="{{ route('attachments.download', $attachment->id) }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-download"></i> Download File
                        </a>
                    </dd>

                    <dt class="col-sm-3">Uploaded At</dt>
                    <dd class="col-sm-9">{{ $attachment->uploaded_at?->format('Y-m-d H:i:s') }}</dd>

                    <dt class="col-sm-3">Created At</dt>
                    <dd class="col-sm-9">{{ $attachment->created_at?->format('Y-m-d H:i:s') }}</dd>

                    <dt class="col-sm-3">Updated At</dt>
                    <dd class="col-sm-9">{{ $attachment->updated_at?->format('Y-m-d H:i:s') }}</dd>
                </dl>
                <div>
                    <a href="{{ route('attachments.edit', $attachment->id) }}" class="btn btn-primary btn-sm">Edit</a>
                    <a href="{{ route('attachments.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
