<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12">
            <!-- Header with Context -->
            <div class="text-center mb-4">
                <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                        <i class="fas fa-shopping-cart text-primary fs-4"></i>
                    </div>
                    <div>
                        <h2 class="fw-bold text-dark mb-0">Smart Sale Processing</h2>
                        <p class="text-muted mb-0">Select your store and start selling immediately</p>
                    </div>
                </div>

                <!-- Quick Stats -->
                @if($dukaList->isNotEmpty())
                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-store text-primary fs-3 mb-2"></i>
                                    <h6 class="card-title">{{ $dukaList->count() }}</h6>
                                    <small class="text-muted">Active Stores</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-box text-success fs-3 mb-2"></i>
                                    <h6 class="card-title">{{ $dukaList->sum('products_count') }}</h6>
                                    <small class="text-muted">Total Products</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-users text-info fs-3 mb-2"></i>
                                    <h6 class="card-title">{{ $dukaList->sum('customers_count') }}</h6>
                                    <small class="text-muted">Total Customers</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Search and Quick Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" wire:model.live.debounce.300ms="search"
                                       class="form-control border-start-0 ps-0"
                                       placeholder="Search stores by name, location, or manager...">
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <div class="btn-group" role="group">
                                <a href="{{ route('tenant.dukas.index') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-list me-2"></i>Manage Stores
                                </a>
                                <a href="{{ route('duka.create.plan') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-2"></i>Add Store
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($dukaList->isEmpty())
                <!-- Empty State -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="row justify-content-center">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <i class="fas fa-store-slash text-muted" style="font-size: 4rem;"></i>
                                </div>
                                <h5 class="text-muted mb-3">No Stores Available</h5>
                                <p class="text-muted mb-4">You need to create a store first before you can process sales.</p>
                                <div class="d-flex gap-3 justify-content-center flex-wrap">
                                    <a href="{{ route('duka.create.plan') }}" class="btn btn-primary btn-lg">
                                        <i class="fas fa-plus me-2"></i>Create New Store
                                    </a>
                                    <a href="{{ route('tenant.dukas.index') }}" class="btn btn-outline-secondary btn-lg">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Stores
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Smart Store Selection -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                            Choose Your Store
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($dukaList as $duka)
                                <div wire:key="{{ $duka->id }}"
                                     class="list-group-item list-group-item-action d-flex align-items-center p-4 {{ $selectedDukaId == $duka->id ? 'bg-primary bg-opacity-10 border-start border-primary border-4' : '' }}"
                                     style="cursor: pointer; transition: all 0.2s ease;"
                                     wire:click="selectDuka({{ $duka->id }})">

                                    <!-- Store Visual -->
                                    <div class="me-4">
                                        <div class="position-relative">
                                            <div class="bg-gradient rounded-circle d-flex align-items-center justify-content-center"
                                                 style="width: 60px; height: 60px; background: linear-gradient(135deg, #4f46e5, #7c3aed);">
                                                <i class="fas fa-store text-white fs-4"></i>
                                            </div>
                                            @if($duka->status == 'active')
                                                <div class="position-absolute top-0 end-0 bg-success rounded-circle p-1" style="width: 12px; height: 12px;"></div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Store Details -->
                                    <div class="flex-grow-1">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <h5 class="mb-1 fw-semibold">{{ $duka->name }}</h5>
                                                <p class="mb-2 text-muted small">
                                                    <i class="fas fa-map-marker-alt me-2"></i>{{ $duka->location }}
                                                </p>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <span class="badge bg-success bg-opacity-10 text-success">{{ ucfirst($duka->status) }}</span>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ $duka->manager_name }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row text-center">
                                                    <div class="col-4">
                                                        <div class="p-2 bg-primary bg-opacity-5 rounded">
                                                            <div class="fw-bold text-primary">{{ $duka->products_count }}</div>
                                                            <small class="text-muted">Products</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="p-2 bg-success bg-opacity-5 rounded">
                                                            <div class="fw-bold text-success">{{ $duka->customers_count }}</div>
                                                            <small class="text-muted">Customers</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="p-2 bg-warning bg-opacity-5 rounded">
                                                            <div class="fw-bold text-warning">{{ $duka->recent_sales_count }}</div>
                                                            <small class="text-muted">Sales (30d)</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Area -->
                                    <div class="ms-3 text-end">
                                        <div class="d-flex flex-column align-items-end gap-2">
                                            <button wire:click.stop="selectDuka({{ $duka->id }})"
                                                    class="btn {{ $selectedDukaId == $duka->id ? 'btn-primary' : 'btn-outline-primary' }} btn-lg rounded-pill px-4">
                                                <i class="fas fa-shopping-cart me-2"></i>
                                                {{ $selectedDukaId == $duka->id ? 'Selected' : 'Start Sale' }}
                                            </button>
                                            <small class="text-muted">
                                                {{ $duka->products_count }} items available • {{ $duka->customers_count }} regular customers
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Smart Selection Summary -->
                @if($selectedDukaId)
                    <div class="card border-0 shadow-sm mt-4" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white;">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-2">
                                            <i class="fas fa-check-circle text-white fs-4"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">Ready to Process Sale</h5>
                                            <p class="mb-0 opacity-75">
                                                You've selected <strong>{{ $dukaList->find($selectedDukaId)->name }}</strong>
                                                with {{ $dukaList->find($selectedDukaId)->products_count }} products and
                                                {{ $dukaList->find($selectedDukaId)->customers_count }} customers
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <div class="d-flex gap-2 justify-content-md-end flex-wrap">
                                        <button wire:click="selectDuka({{ $selectedDukaId }})"
                                                class="btn btn-light btn-lg px-4">
                                            <i class="fas fa-play me-2"></i>Start Processing
                                        </button>
                                        <button wire:click="$set('selectedDukaId', null)"
                                                class="btn btn-outline-light btn-lg px-4">
                                            <i class="fas fa-redo me-2"></i>Change Store
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
