<div class="p-3">
    {{-- Customer List --}}
    <div class="card shadow-sm">
        <div class="card-header mb-10">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">Customers ({{ $customers->total() }})</h5>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-primary btn-sm"
                            wire:click="openAddModal"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50"
                            {{ $loading ? 'disabled' : '' }}>
                        @if($loading)
                            <div class="spinner-border spinner-border-sm me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            Loading...
                        @else
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="me-1">
                                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                            </svg>
                            Add Customer
                        @endif
                    </button>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                            </svg>
                        </span>
                        <input type="text"
                               class="form-control"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Search customers...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="perPage">
                        <option value="5">5 per page</option>
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-1">
                        <button class="btn btn-outline-secondary btn-sm {{ $sortField === 'name' ? 'active' : '' }}"
                                wire:click="sortBy('name')">
                            Name {{ $sortField === 'name' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                        </button>
                        <button class="btn btn-outline-secondary btn-sm {{ $sortField === 'created_at' ? 'active' : '' }}"
                                wire:click="sortBy('created_at')">
                            Date {{ $sortField === 'created_at' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $index => $customer)
                        <tr>
                            <td class="fw-bold text-primary">{{ $customers->firstItem() + $index }}</td>
                            <td class="fw-bold">{{ $customer->name }}</td>
                            <td>{{ $customer->email ?? 'N/A' }}</td>
                            <td>{{ $customer->phone ?? 'N/A' }}</td>
                            <td>{{ $customer->address ?? 'N/A' }}</td>
                            <td>{{ $customer->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary"
                                            wire:click="openEditModal({{ $customer->id }})"
                                            wire:loading.attr="disabled"
                                            wire:loading.class="opacity-50"
                                            title="Edit Customer"
                                            {{ $loading ? 'disabled' : '' }}>
                                        @if($loading)
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        @else
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                            </svg>
                                        @endif
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger"
                                            wire:click="deleteCustomer({{ $customer->id }})"
                                            wire:loading.attr="disabled"
                                            wire:loading.class="opacity-50"
                                            onclick="return confirm('Are you sure you want to delete this customer?')"
                                            title="Delete Customer"
                                            {{ $loading ? 'disabled' : '' }}>
                                        @if($loading)
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        @else
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                            </svg>
                                        @endif
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">
                                No customers found for this tenant.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION INFO AND LINKS --}}
        @if ($customers->hasPages() || $customers->total() > 0)
            <div class="border-top p-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <small class="text-muted">
                            Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }}
                            of {{ $customers->total() }} customers
                            @if($search)
                                (filtered from {{ $duka->customers()->count() }} total)
                            @endif
                        </small>
                    </div>
                    <div class="col-md-6 text-end">
                        {{ $customers->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Add Customer Modal --}}
    @if ($showAddModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.45);" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Customer</h5>
                        <button type="button" wire:click="closeModals" class="btn-close"></button>
                    </div>

                    <form wire:submit.prevent="saveCustomer">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Customer Name *</label>
                                    <input type="text" class="form-control" wire:model="name">
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" wire:model="email">
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control" wire:model="phone">
                                    @error('phone')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" wire:model="address" rows="2"></textarea>
                                    @error('address')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" wire:click="closeModals" class="btn btn-outline-secondary" {{ $loading ? 'disabled' : '' }}>
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:loading.class="opacity-50" {{ $loading ? 'disabled' : '' }}>
                                @if($loading)
                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    Saving...
                                @else
                                    Save Customer
                                @endif
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Customer Modal --}}
    @if ($showEditModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.45);" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Customer</h5>
                        <button type="button" wire:click="closeModals" class="btn-close"></button>
                    </div>

                    <form wire:submit.prevent="saveCustomer">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Customer Name *</label>
                                    <input type="text" class="form-control" wire:model="name">
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" wire:model="email">
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control" wire:model="phone">
                                    @error('phone')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" wire:model="address" rows="2"></textarea>
                                    @error('address')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" wire:click="closeModals" class="btn btn-outline-secondary" {{ $loading ? 'disabled' : '' }}>
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:loading.class="opacity-50" {{ $loading ? 'disabled' : '' }}>
                                @if($loading)
                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    Updating...
                                @else
                                    Update Customer
                                @endif
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
