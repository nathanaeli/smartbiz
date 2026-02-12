@extends('layouts.app')

@section('title', 'Officer Management')

@section('content')
<div class="container-fluid card p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Officer Management</h2>
            <p class="text-muted mb-0">Manage officers for {{ $tenant->name ?? 'your business' }}</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOfficerModal">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="me-1">
                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                </svg>
                Create Officer
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
             <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-white">
             <h5 class="mb-0">Assigned Officers ({{ $officers->count() }})</h5>
        </div>
        <div class="card-body p-0">
            @if($officers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Officer Name</th>
                                <th>Email</th>
                                <th>Assigned Duka</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($officers as $assignment)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <div class="avatar-initial rounded-circle bg-primary text-white p-2">
                                                    {{ strtoupper(substr($assignment->officer->name ?? 'U', 0, 1)) }}
                                                </div>
                                            </div>
                                            <div class="fw-bold">{{ $assignment->officer->name ?? 'Unknown' }}</div>
                                        </div>
                                    </td>
                                    <td>{{ $assignment->officer->email ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-info text-dark">{{ $assignment->duka->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>{{ ucfirst($assignment->role) }}</td>
                                    <td>
                                        @if($assignment->status)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('officers.show', $assignment->officer->id) }}" class="btn btn-outline-secondary" title="View">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>

                                            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#resetPasswordModal{{ $assignment->officer->id }}" title="Reset Password">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                                </svg>
                                            </button>

                                            <button class="btn btn-outline-danger" title="Unassign" onclick="if(confirm('Unassign this officer?')) { document.getElementById('unassign-form-{{ $assignment->id }}').submit(); }">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>

                                        <form id="unassign-form-{{ $assignment->id }}" action="{{ route('officers.unassign', $assignment->id) }}" method="POST" class="d-none">
                                            @csrf @method('DELETE')
                                        </form>

                                        <div class="modal fade" id="resetPasswordModal{{ $assignment->officer->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header border-bottom-0">
                                                        <h5 class="modal-title fw-bold">Reset Password</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('officers.reset-password', $assignment->officer->id) }}" method="POST">
                                                        @csrf @method('PUT')
                                                        <div class="modal-body py-0">
                                                            <p class="text-muted small mb-3">New password for <strong>{{ $assignment->officer->name }}</strong></p>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold text-muted text-uppercase">New Password</label>
                                                                <input type="password" name="password" class="form-control" required minlength="8">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold text-muted text-uppercase">Confirm Password</label>
                                                                <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-top-0 d-flex justify-content-between">
                                                            <button type="button" class="btn btn-sm btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-sm btn-dark px-3">Update Password</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <h5 class="text-muted">No Officers Assigned</h5>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="createOfficerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Create New Officer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('officers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign to Duka</label>
                        <select class="form-select" name="duka_id" required>
                            <option value="">Select a duka...</option>
                            @foreach($dukas as $duka)
                                <option value="{{ $duka->id }}">{{ $duka->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" name="password_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Officer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
