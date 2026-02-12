<div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Users Management</h4>
                    <a href="#" class="btn btn-primary">Add New User</a>
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

                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" wire:model.live="search" class="form-control"
                                   placeholder="Search users by name or email...">
                        </div>
                        <div class="col-md-3">
                            <select wire:model.live="roleFilter" class="form-select">
                                <option value="">All Roles</option>
                                <option value="superadmin">Super Admin</option>
                                <option value="tenant">Tenant</option>
                                <option value="officer">Officer</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select wire:model.live="statusFilter" class="form-select">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select wire:model.live="perPage" class="form-select">
                                <option value="10">10 per page</option>
                                <option value="25">25 per page</option>
                                <option value="50">50 per page</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Email Verified</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->roles->count() > 0)
                                            @foreach($user->roles as $role)
                                                <span class="badge bg-primary me-1">{{ $role->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="badge bg-secondary">No Role</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                                            {{ ucfirst($user->status ?? 'active') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->email_verified_at)
                                            <span class="badge bg-success">Verified</span>
                                        @else
                                            <span class="badge bg-warning">Unverified</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                    <td>
                                        @if($user->status === 'active')
                                            <button wire:click="toggleStatus({{ $user->id }})"
                                                    class="btn btn-sm btn-warning"
                                                    onclick="return confirm('Are you sure you want to deactivate this user?')">
                                                Deactivate
                                            </button>
                                        @else
                                            <button wire:click="toggleStatus({{ $user->id }})"
                                                    class="btn btn-sm btn-success"
                                                    onclick="return confirm('Are you sure you want to activate this user?')">
                                                Activate
                                            </button>
                                        @endif
                                        <button class="btn btn-sm btn-info">Edit</button>
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No users found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
