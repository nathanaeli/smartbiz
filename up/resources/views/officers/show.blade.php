@extends('layouts.app')

@section('title', 'Officer Profile')

@section('content')
<div class="container-fluid card p-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ $officer->name }}</h2>
            <p class="text-muted mb-0">Officer Profile & Assignments</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('officers.edit', $officer->id) }}" class="btn btn-primary">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="me-1">
                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                </svg>
                Edit Officer
            </a>
            <a href="{{ route('officers.index') }}" class="btn btn-outline-secondary">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="me-1">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                </svg>
                Back to Officers
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Officer Profile -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="avatar avatar-xl mx-auto">
                            <div class="avatar-initial rounded-circle bg-primary text-white" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; font-size: 32px;">
                                {{ strtoupper(substr($officer->name, 0, 1)) }}
                            </div>
                        </div>
                    </div>

                    <h4 class="mb-1">{{ $officer->name }}</h4>
                    <p class="text-muted mb-3">{{ $officer->email }}</p>

                    <div class="row text-start">
                        <div class="col-12 mb-2">
                            <strong>Role:</strong>
                            <span class="badge bg-info ms-1">Officer</span>
                        </div>
                        @if($officer->phone)
                        <div class="col-12 mb-2">
                            <strong>Phone:</strong>
                            <span class="ms-1">{{ $officer->phone }}</span>
                        </div>
                        @endif
                        @if($officer->address)
                        <div class="col-12 mb-2">
                            <strong>Address:</strong>
                            <span class="ms-1">{{ $officer->address }}</span>
                        </div>
                        @endif
                        <div class="col-12 mb-2">
                            <strong>Joined:</strong>
                            <span class="ms-1">{{ $officer->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="col-12 mb-3">
                            <strong>Last Updated:</strong>
                            <span class="ms-1">{{ $officer->updated_at->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="col-12 pt-2 border-top text-center">
                            <form action="{{ route('officers.set-default-password', $officer->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to reset this officer\'s password to the default?');">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-warning btn-sm w-100">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="me-1">
                                        <path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/>
                                    </svg>
                                    Reset Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignments -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Duka Assignments</h5>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#assignModal">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="me-1">
                            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                        </svg>
                        Assign to Duka
                    </button>
                </div>
                <div class="card-body">
                    @if($assignments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Duka Name</th>
                                        <th>Location</th>
                                        <th>Role</th>
                                        <th>Assigned Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignments as $assignment)
                                        <tr>
                                            <td>
                                                <strong>{{ $assignment->duka->name }}</strong>
                                            </td>
                                            <td>{{ $assignment->duka->location }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $assignment->role }}</span>
                                            </td>
                                            <td>{{ $assignment->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge {{ $assignment->status ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $assignment->status ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-danger btn-sm unassign-btn"
                                                                data-id="{{ $assignment->id }}"
                                                                data-duka="{{ $assignment->duka->name }}">
                                                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                                                            </svg>
                                                        </button>
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
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <h5 class="text-muted">No Assignments Yet</h5>
                            <p class="text-muted">This officer hasn't been assigned to any dukas yet.</p>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#assignModal">
                                Assign to Duka
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Officer to Duka</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('officers.assign') }}" method="POST">
                @csrf
                <input type="hidden" name="officer_id" value="{{ $officer->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Duka *</label>
                        <select name="duka_id" class="form-select" required>
                            <option value="">Choose a duka...</option>
                            @foreach(\App\Models\Duka::where('tenant_id', auth()->id())->get() as $duka)
                                <option value="{{ $duka->id }}">{{ $duka->name }} - {{ $duka->location }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <input type="text" name="role" class="form-control" placeholder="e.g., Manager, Cashier, Sales Rep" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Officer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Officer Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="#" method="POST" id="editRoleForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Role *</label>
                        <input type="text" name="role" class="form-control" id="roleInput" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit role functionality
    document.querySelectorAll('.edit-role-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const role = this.dataset.role;

            document.getElementById('roleInput').value = role;
            document.getElementById('editRoleForm').action = `/officers/update-role/${id}`;
            new bootstrap.Modal(document.getElementById('editRoleModal')).show();
        });
    });

    // Unassign functionality
    document.querySelectorAll('.unassign-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const dukaName = this.dataset.duka;

            if (confirm(`Are you sure you want to unassign this officer from ${dukaName}?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/officers/unassign/${id}`;
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endsection
