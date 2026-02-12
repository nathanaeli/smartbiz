<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <!-- Header -->
            <div class="text-center mb-4">
                <h2 class="fw-bold text-dark">Start Your Sale</h2>
                <p class="text-muted">Select a store to begin processing sales</p>
            </div>

            <!-- Search Bar -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-8 mx-auto">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" wire:model.live.debounce.300ms="search"
                                       class="form-control border-start-0 ps-0"
                                       placeholder="Search stores by name, location, or manager...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($dukaList->isEmpty())
                <!-- Empty State -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-store text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="text-muted mb-3">No Stores Available</h5>
                        <p class="text-muted mb-4">You need to create a store first before you can process sales.</p>
                        <a href="{{ route('duka.create.plan') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Create New Store
                        </a>
                    </div>
                </div>
            @else
                <!-- Store List -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($dukaList as $duka)
                                <div wire:key="{{ $duka->id }}"
                                     class="list-group-item list-group-item-action d-flex align-items-center p-3 {{ $selectedDukaId == $duka->id ? 'bg-primary bg-opacity-10' : '' }}"
                                     style="cursor: pointer; transition: all 0.2s ease;"
                                     wire:click="selectDuka({{ $duka->id }})">

                                    <!-- Store Icon -->
                                    <div class="me-3">
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                             style="width: 48px; height: 48px;">
                                            <i class="fas fa-store text-primary" style="font-size: 1.2rem;"></i>
                                        </div>
                                    </div>

                                    <!-- Store Info -->
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1 fw-semibold">{{ $duka->name }}</h6>
                                                <p class="mb-1 text-muted small">
                                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $duka->location }}
                                                </p>
                                                <span class="badge bg-success bg-opacity-10 text-success">{{ ucfirst($duka->status) }}</span>
                                            </div>
                                            <div class="text-end">
                                                <div class="d-flex gap-2">
                                                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ $duka->products_count }} Products</span>
                                                    <span class="badge bg-success bg-opacity-10 text-success">{{ $duka->customers_count }} Customers</span>
                                                </div>
                                                <small class="text-muted mt-1 d-block">{{ $duka->recent_sales_count }} sales (30d)</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="ms-3">
                                        <button wire:click.stop="selectDuka({{ $duka->id }})"
                                                class="btn {{ $selectedDukaId == $duka->id ? 'btn-primary' : 'btn-outline-primary' }} btn-sm rounded-pill px-4">
                                            {{ $selectedDukaId == $duka->id ? 'Selected' : 'Select' }}
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Selection Summary -->
                @if($selectedDukaId)
                    <div class="card border-0 shadow-sm mt-4" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white;">
                        <div class="card-body text-center py-4">
                            <h5 class="mb-2">Ready to Process Sale</h5>
                            <p class="mb-3 opacity-75">You've selected <strong>{{ $dukaList->find($selectedDukaId)->name }}</strong></p>
                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                <button wire:click="selectDuka({{ $selectedDukaId }})"
                                        class="btn btn-light btn-lg px-4">
                                    <i class="fas fa-shopping-cart me-2"></i>Start Sale
                                </button>
                                <button wire:click="$set('selectedDukaId', null)"
                                        class="btn btn-outline-light btn-lg px-4">
                                    <i class="fas fa-redo me-2"></i>Change Store
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
