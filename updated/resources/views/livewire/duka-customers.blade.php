<div class="duka-customers-container">
    <style>
        .customer-avatar {
            width: 40px;
            height: 40px;
            background: #f1f5f9;
            color: #3b82f6;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.9rem;
            border: 1px solid #e2e8f0;
        }

        .filter-bar {
            background: #f8fafc;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }

        .search-input-group .input-group-text {
            background: white;
            border-right: none;
            color: #64748b;
        }

        .search-input-group .form-control {
            border-left: none;
        }

        .search-input-group .form-control:focus {
            box-shadow: none;
            border-color: #e2e8f0;
        }

        .table-premium thead th {
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 1px;
            background: #f8fafc;
            border-top: none;
            cursor: pointer;
            transition: color 0.2s;
        }

        .table-premium thead th:hover {
            color: #3b82f6;
        }

        .table-premium tbody td {
            vertical-align: middle;
            color: #334155;
            font-size: 0.9rem;
        }

        .btn-action {
            width: 34px;
            height: 34px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .loader-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(2px);
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }
    </style>

    {{-- Top Controls --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h4 class="fw-bold mb-0 text-slate-800">
            <span class="text-primary">{{ $customers->total() }}</span> Registered Customers
        </h4>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary shadow-sm rounded-pill px-4" wire:click="openImportModal">
                <i class="ri-upload-cloud-2-line me-1"></i> Import Customers
            </button>
            <button class="btn btn-primary shadow-sm rounded-pill px-4" wire:click="openAddModal">
                <i class="ri-user-add-line me-1"></i> Add New Customer
            </button>
        </div>
    </div>

    {{-- Filter Bar --}}
    <style>
        .custom-data-table-controls {
            padding: 1rem;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
    </style>
    <div class="card border-0 shadow-sm position-relative overflow-hidden" style="border-radius: 16px;">
        <div class="custom-data-table-controls">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="d-flex align-items-center">
                        <label class="me-2 text-muted small">Show</label>
                        <select wire:model.live="perPage" class="form-select form-select-sm d-inline-block w-auto shadow-none border-gray-300">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <label class="ms-2 text-muted small">entries</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-md-end align-items-center">
                        <label class="me-2 text-muted small">Search:</label>
                        <input type="search" wire:model.live.debounce.300ms="search" class="form-control form-control-sm w-auto shadow-none border-gray-300" placeholder="">
                    </div>
                </div>
            </div>
        </div>
        <div wire:loading.flex wire:target="search, perPage, sortBy, resetFilters" class="loader-overlay">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-premium mb-0">
                <thead>
                    <tr>
                        <th class="ps-4 cursor-pointer" wire:click="sortBy('name')">
                            Name <i class="ri-arrow-up-down-line small ms-1 {{ $sortBy === 'name' ? 'text-primary' : 'text-muted' }}"></i>
                        </th>
                        <th class="cursor-pointer" wire:click="sortBy('phone')">
                            Phone <i class="ri-arrow-up-down-line small ms-1 {{ $sortBy === 'phone' ? 'text-primary' : 'text-muted' }}"></i>
                        </th>
                        <th>Address</th>
                        <th class="cursor-pointer" wire:click="sortBy('created_at')">
                            Start date <i class="ri-arrow-up-down-line small ms-1 {{ $sortBy === 'created_at' ? 'text-primary' : 'text-muted' }}"></i>
                        </th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $index => $customer)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="customer-avatar me-3 bg-soft-primary text-primary">
                                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold fs-6 text-dark">{{ $customer->name }}</div>
                                    <div class="text-muted small">{{ $customer->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($customer->phone)
                            <span class="fw-500">{{ $customer->phone }}</span>
                            @else
                            <span class="text-muted small italic">N/A</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 200px;" title="{{ $customer->address }}">
                                {{ $customer->address ?? 'No address' }}
                            </div>
                        </td>
                        <td>
                            <div class="small fw-500">{{ $customer->created_at->format('Y/m/d') }}</div>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <button class="btn btn-action btn-soft-primary"
                                    wire:click="openEditModal({{ $customer->id }})"
                                    title="Edit Profile">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </button>
                                <button class="btn btn-action btn-soft-danger"
                                    wire:click="deleteCustomer({{ $customer->id }})"
                                    wire:confirm="Are you sure you want to delete this customer?"
                                    title="Remove Customer">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="bg-soft-secondary rounded-circle d-inline-flex p-4 mb-3">
                                    <i class="ri-group-line ri-4x text-muted"></i>
                                </div>
                                <h4 class="fw-bold">No Customers Found</h4>
                                <p class="text-muted mx-auto" style="max-width: 400px;">
                                    @if($search)
                                    No matches for "{{ $search }}".
                                    @else
                                    No customers added yet.
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($customers->hasPages())
        <div class="card-footer bg-transparent border-0 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} entries
                </div>
                <div>
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>


    {{-- ---------------- MODALS REDESIGN ---------------- --}}

    {{-- IMPORT MODAL --}}
    @if ($showImportModal)
    <div class="modal fade show d-block" style="background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
                <div class="modal-header border-0 pt-4 px-4">
                    <div class="bg-soft-primary rounded-circle p-2 me-3">
                        <i class="ri-upload-cloud-2-line ri-xl text-primary"></i>
                    </div>
                    <h4 class="modal-title fw-bold">Import Customers</h4>
                    <button type="button" wire:click="closeModals" class="btn-close shadow-none"></button>
                </div>

                <form wire:submit.prevent="importCustomers">
                    <div class="modal-body p-4">
                        <div class="text-center mb-4">
                            <p class="text-muted small">Upload a CSV or Excel file to bulk import customer records.</p>
                            <a href="{{ route('customers.import-template') }}" class="text-primary small fw-bold text-decoration-none">Download Sample Template</a>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-600">Select File</label>
                            <input type="file" class="form-control form-control-lg rounded-pill shadow-none" wire:model="importFile" accept=".csv, .xlsx, .xls">
                            @error('importFile') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div wire:loading wire:target="importFile" class="text-center w-100 mt-2">
                            <span class="text-muted small"><i class="ri-loader-4-line ri-spin me-1"></i> Uploading file...</span>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pb-4 px-4">
                        <button type="button" wire:click="closeModals" class="btn btn-light rounded-pill px-4 me-2">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm" {{ $importFile ? '' : 'disabled' }}>
                            <span wire:loading.remove wire:target="importCustomers">Import Data</span>
                            <span wire:loading wire:target="importCustomers">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- ADD / EDIT MODAL --}}
    @if ($showAddModal || $showEditModal)
    <div class="modal fade show d-block" style="background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
                <div class="modal-header border-0 pt-4 px-4">
                    <div class="bg-soft-primary rounded-circle p-2 me-3">
                        <i class="ri-user-{{ $showEditModal ? 'edit' : 'add' }}-line ri-xl text-primary"></i>
                    </div>
                    <h4 class="modal-title fw-bold">{{ $showEditModal ? 'Update Customer' : 'Add New Customer' }}</h4>
                    <button type="button" wire:click="closeModals" class="btn-close shadow-none"></button>
                </div>

                <form wire:submit.prevent="saveCustomer">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-600">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 rounded-start-pill"><i class="ri-user-line text-primary"></i></span>
                                    <input type="text" class="form-control rounded-end-pill shadow-none border-start-0 ps-0" wire:model="name" placeholder="John Doe">
                                </div>
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-600">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 rounded-start-pill"><i class="ri-mail-line text-primary"></i></span>
                                    <input type="email" class="form-control rounded-end-pill shadow-none border-start-0 ps-0" wire:model="email" placeholder="john@example.com">
                                </div>
                                @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-600">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 rounded-start-pill"><i class="ri-phone-line text-primary"></i></span>
                                    <input type="text" class="form-control rounded-end-pill shadow-none border-start-0 ps-0" wire:model="phone" placeholder="+255 123 456 789">
                                </div>
                                @error('phone') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-600">Physical Address</label>
                                <textarea class="form-control rounded-4 shadow-none border-light-subtle" wire:model="address" rows="3" placeholder="Street, City, Country..."></textarea>
                                @error('address') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pb-4 px-4">
                        <button type="button" wire:click="closeModals" class="btn btn-light rounded-pill px-4 me-2">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                            <span wire:loading.remove wire:target="saveCustomer">
                                {{ $showEditModal ? 'Update Information' : 'Register Customer' }}
                            </span>
                            <span wire:loading wire:target="saveCustomer">
                                {{ $showEditModal ? 'Updating...' : 'Registering...' }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>