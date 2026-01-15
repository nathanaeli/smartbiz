<div class="container-fluid mt-4 card">
    <!-- Header Section -->
    <div class="row mb-4 ">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="mb-1" style="color: #4f46e5; font-weight: 700;">Select Store for Sale</h2>
                    <p class="text-muted mb-0">Choose a store to start processing your sale</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary fs-6 px-3 py-2">{{ $dukaList->count() }} Store{{ $dukaList->count() !== 1 ? 's' : '' }} Available</span>
                </div>
            </div>

            <!-- Search and Filter Bar -->
            <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
                <div class="card-body py-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                                    </svg>
                                </span>
                                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0"
                                       placeholder="Search stores by name, location, or manager..." style="border-radius: 0 8px 8px 0;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select wire:model.live="statusFilter" class="form-select" style="border-radius: 8px;">
                                <option value="all">All Statuses</option>
                                <option value="active">Active Only</option>
                                <option value="inactive">Inactive Only</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button wire:click="$set('search', ''); $set('statusFilter', 'all')" class="btn btn-outline-secondary w-100" style="border-radius: 8px;">
                                <svg class="me-1" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                                </svg>
                                Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($dukaList->isEmpty())
        <!-- Empty State -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
                    <div class="card-body text-center py-5">
                        <div style="width: 80px; height: 80px; background: linear-gradient(45deg, #4f46e5, #7c3aed); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);">
                            <svg width="40" height="40" fill="white" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                        <h4 class="text-muted mb-3">No Stores Available</h4>
                        <p class="text-muted mb-4">You need to create a store first before you can process sales.</p>
                        <a href="{{ route('duka.create.plan') }}" class="btn btn-primary">
                            <svg class="me-2" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/>
                            </svg>
                            Create New Store
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Store Cards Grid -->
        <div class="row g-4">
            @foreach($dukaList as $duka)
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                    <div class="card h-100 border-0 shadow-sm duka-card {{ $selectedDukaId == $duka->id ? 'selected-card' : '' }}"
                         style="transition: all 0.3s ease; cursor: pointer; {{ $selectedDukaId == $duka->id ? 'border: 3px solid #4f46e5 !important; box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3) !important;' : '' }}">
                        <div class="card-body p-4">
                            <!-- Store Icon & Status -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div style="width: 60px; height: 60px; background: linear-gradient(45deg, #4f46e5, #7c3aed); border-radius: 15px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.2);">
                                    <svg width="30" height="30" fill="white" viewBox="0 0 24 24">
                                        <path d="M19 7h-3V6a3 3 0 0 0-3-3H5a3 3 0 0 0-3 3v14a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3V9a3 3 0 0 0-3-3zM5 6h9v1H5V6zm14 12H5v-1h14v1z"/>
                                    </svg>
                                </div>
                                <span class="badge fs-6 px-3 py-1 {{ $duka->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($duka->status) }}
                                </span>
                            </div>

                            <!-- Store Details -->
                            <h5 class="card-title mb-2" style="color: #4f46e5; font-weight: 600;">{{ $duka->name }}</h5>

                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <svg class="me-2 text-muted" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                    </svg>
                                    <small class="text-muted">{{ $duka->location }}</small>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <svg class="me-2 text-muted" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                    </svg>
                                    <small class="text-muted">{{ $duka->manager_name }}</small>
                                </div>

                                <!-- Store Statistics -->
                                <div class="row g-2 mb-3">
                                    <div class="col-4 text-center">
                                        <div class="p-2 rounded" style="background: rgba(79, 70, 229, 0.1);">
                                            <div class="fw-bold text-primary fs-6">{{ $duka->products_count }}</div>
                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Products</small>
                                        </div>
                                    </div>
                                    <div class="col-4 text-center">
                                        <div class="p-2 rounded" style="background: rgba(34, 197, 94, 0.1);">
                                            <div class="fw-bold text-success fs-6">{{ $duka->customers_count }}</div>
                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Customers</small>
                                        </div>
                                    </div>
                                    <div class="col-4 text-center">
                                        <div class="p-2 rounded" style="background: rgba(245, 158, 11, 0.1);">
                                            <div class="fw-bold text-warning fs-6">{{ $duka->recent_sales_count }}</div>
                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Sales (30d)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <button wire:click="selectDuka({{ $duka->id }})"
                                    class="btn w-100 {{ $selectedDukaId == $duka->id ? 'btn-primary' : 'btn-outline-primary' }} d-flex align-items-center justify-content-center"
                                    style="border-radius: 10px; padding: 10px; font-weight: 600; transition: all 0.3s ease;">
                                @if($selectedDukaId == $duka->id)
                                    <svg class="me-2" width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                    </svg>
                                    Selected
                                @else
                                    <svg class="me-2" width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                    Select Store
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Selection Summary -->
        @if($selectedDukaId)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white;">
                        <div class="card-body text-center py-4">
                            <h5 class="mb-2">Ready to Process Sale</h5>
                            <p class="mb-3 opacity-75">You've selected <strong>{{ $dukaList->find($selectedDukaId)->name }}</strong> for your sale</p>
                            <div class="d-flex justify-content-center gap-3">
                                <button wire:click="selectDuka({{ $selectedDukaId }})" class="btn btn-light btn-lg px-4">
                                    <svg class="me-2" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                    Continue to Sale
                                </button>
                                <button wire:click="$set('selectedDukaId', null)" class="btn btn-outline-light btn-lg px-4">
                                    Change Selection
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

@push('styles')
<style>
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.1) !important;
}

.selected-card {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3); }
    50% { box-shadow: 0 8px 35px rgba(79, 70, 229, 0.5); }
    100% { box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3); }
}

@media (max-width: 768px) {
    .hover-card:hover {
        transform: none;
    }
}
</style>
@endpush
