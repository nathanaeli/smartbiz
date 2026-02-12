@extends('layouts.app')

@section('title', 'Officer Management')

@section('content')
<div class="container-fluid card p-4">

    <!-- Header -->
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

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
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

    <div class="card">
        <div class="card-header">
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
                                                <div class="avatar-initial rounded-circle bg-primary text-white">
                                                    {{ strtoupper(substr($assignment->officer->name ?? 'U', 0, 1)) }}
                                                </div>
                                            </div>
                                            <div class="fw-bold">{{ $assignment->officer->name ?? 'Unknown' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="mailto:{{ $assignment->officer->email ?? '' }}" class="text-decoration-none">
                                            {{ $assignment->officer->email ?? 'N/A' }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark">{{ $assignment->duka->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        {{ ucfirst($assignment->role) }}
                                    </td>
                                    <td>
                                        @if($assignment->status)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('officers.show', $assignment->officer->id) }}" class="btn btn-outline-secondary" title="View Details">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <!-- Since we don't have lists for re-assignment, we only verify view/delete -->
                                            <button class="btn btn-outline-danger" title="Unassign"
                                                    onclick="if(confirm('Are you sure you want to unassign this officer?')) {
                                                        document.getElementById('unassign-form-{{ $assignment->id }}').submit();
                                                    }">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                            <form id="unassign-form-{{ $assignment->id }}" 
                                                  action="{{ route('officers.unassign', $assignment->id) }}" 
                                                  method="POST" class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <svg width="64" height="64" fill="currentColor" viewBox="0 0 24 24" class="text-muted mb-3">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                    <h5 class="text-muted">No Officers Assigned</h5>
                    <p class="text-muted">Create a new officer to get started.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Officer Modal -->
<div class="modal fade" id="createOfficerModal" tabindex="-1" aria-labelledby="createOfficerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createOfficerModalLabel">Create New Officer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('officers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="duka_id" class="form-label">Assign to Duka</label>
                        <select class="form-select" id="duka_id" name="duka_id" required>
                            <option value="">Select a duka...</option>
                            @foreach($dukas as $duka)
                                <option value="{{ $duka->id }}">{{ $duka->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
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
