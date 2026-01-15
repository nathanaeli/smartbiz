@extends('layouts.app')

@section('title', 'Officer Management')

@section('content')
<div class="container-fluid card p-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Officer Management</h2>
            <p class="text-muted mb-0">Create, manage and assign officers to your dukas</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('officers.create') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="me-1">
                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                </svg>
                Create Officer
            </a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#assignModal">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="me-1">
                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                </svg>
                Assign Officer
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

    <!-- All Officers Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">All Officers</h5>
        </div>
        <div class="card-body">
            @if($allOfficers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Officer</th>
                                <th>Contact</th>
                                <th>Assignments</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allOfficers as $officer)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <div class="avatar-initial rounded-circle bg-primary text-white">
                                                    {{ strtoupper(substr($officer->name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $officer->name }}</h6>
                                                <small class="text-muted">{{ $officer->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($officer->phone)
                                            <div>{{ $officer->phone }}</div>
                                        @endif
                                        @if($officer->address)
                                            <small class="text-muted">{{ $officer->address }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $assignmentCount = \App\Models\TenantOfficer::where('officer_id', $officer->id)->count();
                                        @endphp
                                        <span class="badge bg-info">{{ $assignmentCount }} duka{{ $assignmentCount !== 1 ? 's' : '' }}</span>
                                    </td>
                                    <td>{{ $officer->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('officers.show', $officer->id) }}" class="btn btn-outline-info btn-sm" title="View Profile">
                                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                                </svg>
                                            </a>
                                            <a href="{{ route('officers.edit', $officer->id) }}" class="btn btn-outline-primary btn-sm" title="Edit Officer">
                                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/>
                                                </svg>
                                            </a>
                                            <button class="btn btn-outline-danger btn-sm" title="Delete Officer"
                                                    onclick="if(confirm('Are you sure you want to delete this officer? This will remove all their assignments.')) {
                                                        document.getElementById('delete-form-{{ $officer->id }}').submit();
                                                    }">
                                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                                                </svg>
                                            </button>
                                            <form id="delete-form-{{ $officer->id }}" action="{{ route('officers.destroy', $officer->id) }}" method="POST" class="d-none">
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
                <div class="text-center py-4">
                    <svg width="48" height="48" fill="currentColor" viewBox="0 0 24 24" class="text-muted mb-3">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                    <h6 class="text-muted">No Officers Created</h6>
                    <p class="text-muted small">Create your first officer to get started.</p>
                    <a href="{{ route('officers.create') }}" class="btn btn-primary btn-sm">Create Officer</a>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Assign Officer Card -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" class="me-2">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        Assign Officer
                    </h5>
                </div>
                <div class="card-body">
                    <form id="assignOfficerForm" action="{{ route('officers.assign') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Select Officer *</label>
                            <select name="officer_id" class="form-select" required>
                                <option value="">Choose an officer...</option>
                                @foreach($allOfficers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            @error('officer_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assign to Duka *</label>
                            <select name="duka_id" class="form-select" required>
                                <option value="">Choose a duka...</option>
                                @foreach($dukas as $duka)
                                    <option value="{{ $duka->id }}">{{ $duka->name }}</option>
                                @endforeach
                            </select>
                            @error('duka_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" name="role" class="form-control" placeholder="e.g., Manager, Cashier, Sales Rep" value="Officer">
                            @error('role')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <button id="assignOfficerSubmit" type="submit" class="btn btn-primary w-100">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="me-1">
                                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                            </svg>
                            Assign Officer
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Officers List -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Assigned Officers ({{ $officers->total() }})</h5>
                </div>
                <div class="card-body p-0">
                    @if($officers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Officer</th>
                                        <th>Duka</th>
                                        <th>Role</th>
                                        <th>Assigned Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($officers as $assignment)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-3">
                                                        <div class="avatar-initial rounded-circle bg-primary">
                                                            {{ strtoupper(substr($assignment->officer->name, 0, 1)) }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $assignment->officer->name }}</div>
                                                        <small class="text-muted">{{ $assignment->officer->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $assignment->duka->name }}</span>
                                            </td>
                                            <td>
                                                <form action="{{ route('officers.update-role', $assignment->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="input-group input-group-sm" style="width: 120px;">
                                                        <input type="text" name="role" class="form-control form-control-sm"
                                                               value="{{ $assignment->role }}" required>
                                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td>{{ $assignment->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger unassign-btn"
                                                        data-target="unassign-form-{{ $assignment->id }}"
                                                        data-duka="{{ $assignment->duka->name }}">
                                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                                                    </svg>
                                                    Unassign
                                                </button>
                                                <form id="unassign-form-{{ $assignment->id }}"
                                                      action="{{ route('officers.unassign', $assignment->id) }}"
                                                      method="POST" class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($officers->hasPages())
                            <div class="border-top p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted small">
                                        Showing {{ $officers->firstItem() }} to {{ $officers->lastItem() }}
                                        of {{ $officers->total() }} officers
                                    </div>
                                    {{ $officers->links() }}
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <svg width="64" height="64" fill="currentColor" viewBox="0 0 24 24" class="text-muted mb-3">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                            <h5 class="text-muted">No Officers Assigned</h5>
                            <p class="text-muted">Assign officers to your dukas using the form on the left.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<div id="assignLoader" class="assign-loader d-none">
    <div class="assign-loader__backdrop"></div>
    <div class="assign-loader__content">
        <div class="spinner-border text-primary mb-2" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="fw-semibold text-primary">Processing...</div>
        <small class="text-muted">Please wait while we process your request.</small>
    </div>
</div>

<style>
.assign-loader {
    position: fixed;
    inset: 0;
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: auto;
}
.assign-loader__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.35);
}
.assign-loader__content {
    position: relative;
    background: #fff;
    border-radius: 12px;
    padding: 20px 28px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    text-align: center;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('assignOfficerForm');
    const loader = document.getElementById('assignLoader');
    const submitBtn = document.getElementById('assignOfficerSubmit');
    const unassignButtons = document.querySelectorAll('.unassign-btn');

    if (form && loader && submitBtn) {
        form.addEventListener('submit', function () {
            loader.classList.remove('d-none');
            submitBtn.disabled = true;
            submitBtn.classList.add('disabled');
        });
    }

    if (loader && unassignButtons) {
        unassignButtons.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.dataset.target;
                const dukaName = this.dataset.duka || 'this duka';
                const formEl = document.getElementById(targetId);

                if (!formEl) return;

                if (confirm(`Are you sure you want to unassign this officer from ${dukaName}?`)) {
                    loader.classList.remove('d-none');
                    this.disabled = true;
                    formEl.submit();
                }
            });
        });
    }
});
</script>
@endsection
