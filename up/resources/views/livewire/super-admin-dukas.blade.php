<div>
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 card">
        <div>
            <h1 class="h3 mb-0">Dukas Management</h1>
            <p class="text-muted mb-0">Manage and monitor all dukas in the system</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('super-admin.subscriptions.analytics') }}" class="btn btn-info">
                <i class="fas fa-chart-line me-2"></i>View Analytics
            </a>
            <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-users me-1"></i>Manage Tenants
            </a>
        </div>
    </div>


    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-store fa-2x mb-2"></i>
                    <h4>{{ $totalDukas }}</h4>
                    <small>Total Dukas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h4>{{ $activeDukas }}</h4>
                    <small>Active Dukas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h4>{{ $activePlans }}</h4>
                    <small>Active Plans</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h4>{{ $expiredPlans }}</h4>
                    <small>Expired Plans</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search dukas..."
                               wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="planStatusFilter">
                        <option value="">All Plans</option>
                        <option value="active">Active Plan</option>
                        <option value="expired">Expired Plan</option>
                        <option value="no_plan">No Plan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary w-100" wire:click="clearFilters">
                        <i class="fas fa-times me-1"></i>Clear
                    </button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-primary w-100" wire:click="toggleViewMode">
                        <i class="fas fa-th me-1"></i>{{ $viewMode === 'table' ? 'Grid' : 'Table' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if(count($selectedDukas) > 0)
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <span><strong>{{ count($selectedDukas) }}</strong> dukas selected</span>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-danger" wire:click="bulkDelete" wire:confirm="Are you sure you want to delete {{ count($selectedDukas) }} selected dukas? This action cannot be undone.">
                    <i class="fas fa-trash me-1"></i>Delete Selected
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="$set('selectedDukas', [])">
                    <i class="fas fa-times me-1"></i>Clear Selection
                </button>
            </div>
        </div>
    @endif

    <!-- Dukas Table -->
    @if($viewMode === 'table')
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" class="form-check-input"
                                       wire:model.live="selectAll">
                            </th>
                            <th>ID</th>
                            <th>Duka Name</th>
                            <th>Location</th>
                            <th>Tenant</th>
                            <th>Status</th>
                            <th>Plan</th>
                            <th>Plan Status</th>
                            <th>Days Remaining</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dukas as $duka)
                            <tr class="duka-row {{ $duka->plan_status === 'expired' ? 'table-danger' : ($duka->plan_status === 'active' ? 'table-success' : '') }}">
                                <td>
                                    <input type="checkbox" class="form-check-input"
                                           wire:model.live="selectedDukas"
                                           value="{{ $duka->id }}">
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $duka->id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <div>
                                            <strong>{{ $duka->name }}</strong>
                                            @if($duka->manager_name)
                                            <br><small class="text-muted">{{ $duka->manager_name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <i class="fas fa-map-marker-alt text-muted me-1"></i>{{ $duka->location ?: 'N/A' }}
                                </td>
                                <td>
                                    @if($duka->tenant && $duka->tenant->user)
                                        <a href="{{ route('super-admin.tenants.show', $duka->tenant->id) }}" class="text-decoration-none">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 12px;">
                                                    {{ strtoupper(substr($duka->tenant->user->name, 0, 1)) }}
                                                </div>
                                                <span>{{ $duka->tenant->user->name }}</span>
                                            </div>
                                        </a>
                                    @else
                                        <span class="text-muted"><i class="fas fa-user-slash me-1"></i>No Tenant</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $duka->status === 'active' ? 'success' : 'danger' }}">
                                        <i class="fas fa-{{ $duka->status === 'active' ? 'check-circle' : 'times-circle' }} me-1"></i>
                                        {{ ucfirst($duka->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($duka->plan_name)
                                        <span class="badge bg-primary">{{ $duka->plan_name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($duka->plan_status === 'active')
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Active
                                        </span>
                                    @elseif($duka->plan_status === 'expired')
                                        <span class="badge bg-danger">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Expired
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="fas fa-exclamation-circle me-1"></i>No Plan
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($duka->plan_status === 'active')
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-2" style="width: 60px; height: 8px;">
                                                <div class="progress-bar bg-success" style="width: {{ min(100, max(0, (30 - $duka->days_remaining) / 30 * 100)) }}%"></div>
                                            </div>
                                            <span class="text-success fw-bold">{{ $duka->days_remaining }}</span>
                                        </div>
                                    @elseif($duka->plan_status === 'expired')
                                        <span class="text-danger fw-bold">Expired</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('super-admin.dukas.show', $duka->id) }}" class="btn btn-sm btn-info" title="View Details">
                                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                            </svg>
                                        </a>
                                        @if($duka->plan_status === 'expired')
                                        <button class="btn btn-sm btn-warning" title="Renew Plan">
                                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                                                <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                                            </svg>
                                        </button>
                                        @endif
                                        <form method="POST" action="{{ route('super-admin.dukas.destroy', $duka->id) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger" title="Delete Duka"
                                                    data-delete-duka="{{ $duka->name }}">
                                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-store fa-3x mb-3"></i>
                                        <p>No dukas found</p>
                                        @if($this->search || $this->statusFilter || $this->planStatusFilter)
                                            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="clearFilters">
                                                Clear filters
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($dukas->hasPages())
            <div class="card-footer">
                {{ $dukas->links() }}
            </div>
        @endif
    </div>

    @else

    <!-- Grid View -->
    <div class="row">
        @forelse($dukas as $duka)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 duka-card {{ $duka->plan_status === 'expired' ? 'border-danger' : ($duka->plan_status === 'active' ? 'border-success' : 'border-warning') }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">{{ $duka->name }}</h6>
                        <div class="d-flex align-items-center gap-2">
                            <input type="checkbox" class="form-check-input"
                                   wire:model.live="selectedDukas"
                                   value="{{ $duka->id }}">
                            <span class="badge bg-secondary">{{ $duka->id }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <i class="fas fa-map-marker-alt text-muted me-1"></i>
                            <small>{{ $duka->location ?: 'N/A' }}</small>
                        </div>
                        @if($duka->manager_name)
                        <div class="mb-2">
                            <i class="fas fa-user text-muted me-1"></i>
                            <small>{{ $duka->manager_name }}</small>
                        </div>
                        @endif
                        <div class="mb-2">
                            <i class="fas fa-user-tie text-muted me-1"></i>
                            @if($duka->tenant && $duka->tenant->user)
                                <small>{{ $duka->tenant->user->name }}</small>
                            @else
                                <small class="text-muted">No Tenant</small>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-{{ $duka->status === 'active' ? 'success' : 'danger' }}">
                                {{ ucfirst($duka->status) }}
                            </span>
                            @if($duka->plan_status === 'active')
                                <span class="badge bg-success">{{ $duka->days_remaining }} days</span>
                            @elseif($duka->plan_status === 'expired')
                                <span class="badge bg-danger">Expired</span>
                            @else
                                <span class="badge bg-warning">No Plan</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex gap-1">
                            <a href="{{ route('super-admin.dukas.show', $duka->id) }}" class="btn btn-info btn-sm flex-fill">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                </svg>View
                            </a>
                            <form method="POST" action="{{ route('super-admin.dukas.destroy', $duka->id) }}" class="flex-fill">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-danger btn-sm w-100"
                                        data-delete-duka="{{ $duka->name }}">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                        <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                    </svg>Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-store fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No dukas found</h5>
                        <p class="text-muted">There are no dukas matching your criteria.</p>
                        @if($this->search || $this->statusFilter || $this->planStatusFilter)
                            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="clearFilters">
                                Clear filters
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination for Grid -->
    @if($dukas->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $dukas->links() }}
        </div>
    @endif

    @endif

    <!-- Smart Success/Error Messages -->
    @if (session()->has('success'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
            <div class="toast show" role="alert" style="min-width: 350px;">
                <div class="toast-header bg-success text-white">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong class="me-auto">Success!</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    <i class="fas fa-check text-success me-2"></i>{{ session('success') }}
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
            <div class="toast show" role="alert" style="min-width: 350px;">
                <div class="toast-header bg-danger text-white">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong class="me-auto">Error!</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    <i class="fas fa-times text-danger me-2"></i>{{ session('error') }}
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-store fa-3x text-danger mb-3"></i>
                        <h5>Delete Duka</h5>
                    </div>
                    <p class="mb-2">Are you sure you want to delete <strong id="delete-duka-name"></strong>?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action cannot be undone. The duka and all associated data will be permanently removed.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirm-delete">
                        <i class="fas fa-trash me-1"></i>Delete Duka
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const deleteDukaName = document.getElementById('delete-duka-name');
        const confirmDeleteBtn = document.getElementById('confirm-delete');

        let deleteForm = null;

        // Delete functionality
        document.addEventListener('click', function(e) {
            if (e.target.closest('[data-delete-duka]')) {
                e.preventDefault();
                const button = e.target.closest('[data-delete-duka]');
                const dukaName = button.getAttribute('data-delete-duka');
                const form = button.closest('form');

                deleteDukaName.textContent = dukaName;
                deleteForm = form;
                deleteModal.show();
            }
        });

        confirmDeleteBtn.addEventListener('click', function() {
            if (deleteForm) {
                deleteForm.submit();
            }
            deleteModal.hide();
        });
    });
    </script>
</div>

@push('styles')
<style>
.table th, .table td {
    vertical-align: middle;
}

.btn-group .btn {
    border-radius: 0.375rem !important;
}

.dropdown-menu {
    min-width: 200px;
}

.toast {
    min-width: 300px;
}

/* Simple hover effects */
.table tbody tr:hover {
    background-color: #f8f9fa;
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

/* Modal responsiveness */
.modal-dialog {
    margin: 1rem;
}

@media (max-width: 576px) {
    .modal-dialog {
        margin: 0.5rem;
        max-width: calc(100vw - 1rem);
    }

    .modal-content {
        border-radius: 0.5rem;
    }

    .modal-header, .modal-footer {
        padding: 0.75rem;
    }

    .modal-body {
        padding: 1rem 0.75rem;
    }
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }

    .table-responsive {
        font-size: 0.875rem;
    }

    .btn-group {
        flex-direction: column;
    }

    .btn-group .btn {
        margin-bottom: 0.25rem;
    }
}

.duka-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.duka-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.duka-row:hover {
    background-color: rgba(0,123,255,0.1) !important;
}

.empty-state {
    padding: 40px 20px;
}

.progress {
    background-color: #e9ecef;
}
</style>
@endpush
