<div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Tenants Management</h4>
                    <a href="#" class="btn btn-primary">Add New Tenant</a>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" wire:model.live="search" class="form-control"
                                   placeholder="Search tenants by name or email...">
                        </div>
                        <div class="col-md-3">
                            <select wire:model.live="statusFilter" class="form-select">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                        <div class="col-md-3">
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
                                    <th>Phone</th>
                                    <th>Dukas</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tenants as $tenant)
                                <tr>
                                    <td>{{ $tenant->id }}</td>
                                    <td>{{ $tenant->name }}</td>
                                    <td>{{ $tenant->email }}</td>
                                    <td>{{ $tenant->phone ?: 'N/A' }}</td>
                                    <td>{{ $tenant->dukas->count() }}</td>
                                    <td>
                                        <span class="badge bg-{{ $tenant->status == 'active' ? 'success' : ($tenant->status == 'inactive' ? 'secondary' : 'warning') }}">
                                            {{ $tenant->status }}
                                        </span>
                                    </td>
                                    <td>{{ $tenant->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('super-admin.tenants.show', $tenant->id) }}" class="btn btn-sm btn-info">View</a>
                                        <button class="btn btn-sm btn-warning">Edit</button>
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No tenants found</td>
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
