@extends('layouts.app')

@section('title', 'Officer Profile')

@section('content')
<div class="container-fluid card p-4">

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

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">Profile Information</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="avatar avatar-xl mx-auto">
                            <div class="avatar-initial rounded-circle bg-primary text-white" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; font-size: 32px;">
                                {{ strtoupper(substr($officer->name, 0, 1)) }}
                            </div>
                        </div>
                    </div>

                    <h4 class="mb-1 fw-bold">{{ $officer->name }}</h4>
                    <p class="text-muted mb-3">{{ $officer->email }}</p>

                    <div class="row text-start px-3">
                        <div class="col-12 mb-2">
                            <span class="text-muted small text-uppercase fw-bold">Role:</span>
                            <span class="badge bg-info text-dark float-end">Officer</span>
                        </div>
                        <div class="col-12 mb-2">
                            <span class="text-muted small text-uppercase fw-bold">Joined:</span>
                            <span class="float-end text-dark">{{ $officer->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Duka Assignments</h5>
                    <button class="btn btn-success btn-sm px-3" data-bs-toggle="modal" data-bs-target="#assignModal">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="me-1">
                            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                        </svg>
                        New Assignment
                    </button>
                </div>
                <div class="card-body p-0">
                    @if($assignments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Duka Name</th>
                                        <th>Role</th>
                                        <th>Assigned Date</th>
                                        <th>Status</th>
                                        <th class="text-end px-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignments as $assignment)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $assignment->duka->name }}</div>
                                                <small class="text-muted">{{ $assignment->duka->location }}</small>
                                            </td>
                                            <td><span class="badge bg-secondary">{{ $assignment->role }}</span></td>
                                            <td>{{ $assignment->created_at->format('M d, Y') }}</td>
                                            <td>
                                                @if($assignment->status)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-end px-4">
                                                <div class="btn-group btn-group-sm">
                                                    @if($assignment->status)
                                                        <button class="btn btn-outline-danger unassign-btn"
                                                                data-id="{{ $assignment->id }}"
                                                                data-duka="{{ $assignment->duka->name }}"
                                                                title="Unassign Officer">
                                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    @else
                                                        <button class="btn btn-dark reassign-btn"
                                                                data-id="{{ $assignment->id }}"
                                                                data-duka="{{ $assignment->duka->name }}"
                                                                title="Reassign Officer">
                                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="me-1">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                            </svg>
                                                            Reassign
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <h5 class="text-muted">No Assignments Yet</h5>
                            <p class="small text-muted">This officer hasn't been linked to any dukas.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">New Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('officers.assign') }}" method="POST">
                @csrf
                <input type="hidden" name="officer_id" value="{{ $officer->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Duka</label>
                        <select name="duka_id" class="form-select" required>
                            <option value="">Choose a duka...</option>
                            @foreach(\App\Models\Duka::where('tenant_id', auth()->user()->tenant->id)->get() as $duka)
                                <option value="{{ $duka->id }}">{{ $duka->name }} ({{ $duka->location }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Role in Duka</label>
                        <input type="text" name="role" class="form-control" placeholder="e.g. Sales Officer" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Create Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Function to submit hidden forms for PATCH requests
    function handleAction(id, dukaName, type) {
        const actionText = type === 'unassign' ? 'unassign (deactivate)' : 'reassign (activate)';
        const route = type === 'unassign' ? `/officers/unassign/${id}` : `/officers/reassign/${id}`;

        if (confirm(`Are you sure you want to ${actionText} this officer for ${dukaName}?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = route;
            form.innerHTML = `
                @csrf
                <input type="hidden" name="_method" value="PATCH">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Unassign Event
    document.querySelectorAll('.unassign-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            handleAction(this.dataset.id, this.dataset.duka, 'unassign');
        });
    });

    // Reassign Event
    document.querySelectorAll('.reassign-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            handleAction(this.dataset.id, this.dataset.duka, 'reassign');
        });
    });
});
</script>
@endsection
