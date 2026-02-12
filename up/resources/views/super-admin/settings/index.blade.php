@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>System Settings</h4>
            </div>
            <div class="card-body">
                <!-- Success/Error Messages -->
                @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Default Password Settings -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" class="me-2">
                                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6zm3 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>
                                    </svg>
                                    Default Password Management
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-4">
                                    Manage default passwords for all tenants in the system. This allows you to set organization-wide password standards.
                                </p>

                                <!-- Bulk Set Default Password -->
                                <div class="row mb-4">
                                    <div class="col-lg-8">
                                        <div class="card border-warning">
                                            <div class="card-header bg-warning">
                                                <h6 class="mb-0">Bulk Set Default Password</h6>
                                            </div>
                                            <div class="card-body">
                                                <form method="POST" action="{{ route('super-admin.settings.bulk-set-password') }}">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="bulk_default_password" class="form-label fw-bold">New Default Password</label>
                                                            <input type="text" class="form-control @error('bulk_default_password') is-invalid @enderror"
                                                                   id="bulk_default_password" name="bulk_default_password"
                                                                   value="{{ old('bulk_default_password', '123456') }}" required>
                                                            <small class="form-text text-muted">This will be set for all tenants</small>
                                                            @error('bulk_default_password')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-6 d-flex align-items-end">
                                                            <button type="submit" class="btn btn-warning me-2"
                                                                    onclick="return confirm('Are you sure you want to set this password for ALL tenants? This action cannot be undone.')">
                                                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="me-1">
                                                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                                                </svg>
                                                                Set for All Tenants
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Individual Tenant Management -->
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Tenant Name</th>
                                                <th>Email</th>
                                                <th>Current Default Password</th>
                                                <th>Last Updated</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($tenants as $tenant)
                                            <tr>
                                                <td>
                                                    <strong>{{ $tenant->name }}</strong>
                                                    @if($tenant->slug)
                                                        <br><small class="text-muted">{{ $tenant->slug }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $tenant->email ?? 'Not set' }}</td>
                                                <td>
                                                    <code class="bg-light px-2 py-1 rounded">{{ $tenant->default_password ?? '123456' }}</code>
                                                </td>
                                                <td>{{ $tenant->updated_at->format('M d, Y H:i') }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="editTenantPassword({{ $tenant->id }}, '{{ $tenant->name }}', '{{ $tenant->default_password ?? '123456' }}')">
                                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24" class="me-1">
                                                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                                        </svg>
                                                        Edit
                                                    </button>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No tenants found</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="d-flex justify-content-center mt-3">
                                    {{ $tenants->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Tenant Password Modal -->
<div class="modal fade" id="editPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Default Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPasswordForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tenant_name" class="form-label">Tenant</label>
                        <input type="text" class="form-control" id="tenant_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="tenant_default_password" class="form-label fw-bold">Default Password</label>
                        <input type="text" class="form-control" id="tenant_default_password" name="default_password" required>
                        <small class="form-text text-muted">Minimum 4 characters</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTenantPassword(tenantId, tenantName, currentPassword) {
    document.getElementById('tenant_name').value = tenantName;
    document.getElementById('tenant_default_password').value = currentPassword;
    document.getElementById('editPasswordForm').action = `/super-admin/settings/tenant/${tenantId}/set-password`;
    new bootstrap.Modal(document.getElementById('editPasswordModal')).show();
}
</script>
@endsection
