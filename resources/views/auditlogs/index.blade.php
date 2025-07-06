{{-- resources/views/attachments/index.blade.php --}}

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
                <h3>Attachments</h3>
                <p class="text-subtitle text-muted">Manage all uploaded transaction files here</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Attachments</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Attachment List</h5>
                <a href="{{ route('attachments.create') }}" class="btn btn-primary btn-md">Upload Attachment</a>
            </div>
            <div class="card-body">
                <!-- Desktop Table -->
                <div class="d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table1">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Transaction</th>
                                    <th>File Name</th>
                                    <th>Uploaded At</th>
                                    <th>File Size</th>
                                    <th>Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attachments as $attachment)
                                    <tr>
                                        <td>{{ $loop->iteration + ($attachments->perPage() * ($attachments->currentPage() - 1)) }}</td>
                                        <td>
                                            @if ($attachment->transaction)
                                                <a href="{{ route('transactions.show', $attachment->transaction->id) }}">
                                                    #{{ $attachment->transaction->id }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <span class="font-monospace">{{ $attachment->file_name }}</span>
                                        </td>
                                        <td>{{ $attachment->uploaded_at ? $attachment->uploaded_at->format('Y-m-d H:i:s') : '-' }}</td>
                                        <td>
                                            @php
                                                try {
                                                    $size = Storage::exists($attachment->file_path) ? Storage::size($attachment->file_path) : 0;
                                                } catch (\Throwable $e) {
                                                    $size = 0;
                                                }
                                            @endphp
                                            {{ $size ? number_format($size / 1024, 2) : 0 }} KB
                                        </td>
                                        <td>
                                            <a href="{{ route('attachments.show', $attachment->id) }}" class="btn btn-info btn-sm mb-1">View</a>
                                            <a href="{{ route('attachments.download', $attachment->id) }}" class="btn btn-success btn-sm mb-1">Download</a>
                                            <a href="{{ route('attachments.edit', $attachment->id) }}" class="btn btn-primary btn-sm mb-1">Edit</a>
                                            <form action="{{ route('attachments.destroy', $attachment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure to delete this attachment?');">
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
                            {{ $attachments->links() }}
                        </div>
                    </div>
                </div>
                <!-- Mobile Card Stack -->
                <div class="d-block d-md-none">
                    @foreach ($attachments as $attachment)
                        <div class="attachment-list-card mb-3 p-3 border rounded bg-white shadow-sm">
                            <div class="fw-bold mb-1 font-monospace">
                                {{ $attachment->file_name }}
                            </div>
                            <div style="font-size:15px;">
                                Transaction:
                                @if ($attachment->transaction)
                                    <a href="{{ route('transactions.show', $attachment->transaction->id) }}">
                                        #{{ $attachment->transaction->id }}
                                    </a>
                                @else
                                    -
                                @endif<br>
                                Uploaded At: <strong>{{ $attachment->uploaded_at ? $attachment->uploaded_at->format('Y-m-d H:i:s') : '-' }}</strong><br>
                                File Size:
                                @php
                                    try {
                                        $size = Storage::exists($attachment->file_path) ? Storage::size($attachment->file_path) : 0;
                                    } catch (\Throwable $e) {
                                        $size = 0;
                                    }
                                @endphp
                                <strong>{{ $size ? number_format($size / 1024, 2) : 0 }} KB</strong>
                            </div>
                            <div class="mt-2">
                                <a href="{{ route('attachments.show', $attachment->id) }}" class="btn btn-info btn-sm mb-1">View</a>
                                <a href="{{ route('attachments.download', $attachment->id) }}" class="btn btn-success btn-sm mb-1">Download</a>
                                <a href="{{ route('attachments.edit', $attachment->id) }}" class="btn btn-primary btn-sm mb-1">Edit</a>
                                <form action="{{ route('attachments.destroy', $attachment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure to delete this attachment?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm mb-1">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                    <div class="mt-3">
                        {{ $attachments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<style>
@media (max-width: 576px) {
    .attachment-list-card {
        border: 1px solid #eee;
        border-radius: 13px;
        background: #fff;
        margin-bottom: 16px;
        box-shadow: 0 1px 8px 0 rgba(180,200,230,0.07);
    }
}
</style>
@endsection
