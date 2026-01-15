<div class="d-flex justify-content-center w-100 position-relative">
    <div class="input-group search-input position-relative" style="max-width: 500px;">
        <span class="input-group-text" id="search-input">
            <svg class="icon-18" width="18" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <circle cx="11.7669" cy="11.7666" r="8.98856" stroke="currentColor"
                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></circle>
                <path d="M18.0186 18.4851L21.5426 22" stroke="currentColor" stroke-width="1.5"
                    stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </span>
        <input type="search" class="form-control" placeholder="Search customers and orders..."
               wire:model.live.debounce.300ms="search">

        @if(!empty($results))
        <div class="position-absolute top-100 start-50 translate-middle-x w-100 bg-white border rounded shadow-lg mt-2"
             style="max-height: 500px; overflow-y: auto; z-index: 1050; min-width: 600px;">
            <div class="p-2 border-bottom bg-light">
                <small class="fw-bold text-muted">Search Results</small>
            </div>
            @foreach($results as $customer)
            <div class="p-3 border-bottom hover-bg-light">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="fw-bold text-primary mb-1">{{ $customer->name }}</div>
                        <div class="small text-muted mb-2">
                            <i class="fas fa-phone me-1"></i>{{ $customer->phone }}
                            @if($customer->email)
                            <span class="mx-2">•</span>
                            <i class="fas fa-envelope me-1"></i>{{ $customer->email }}
                            @endif
                        </div>

                        @if($customer->sales->count() > 0)
                        <div class="mt-3">
                            <div class="small fw-bold text-dark mb-2">Recent Orders:</div>
                            @foreach($customer->sales->take(3) as $sale)
                            <div class="d-flex justify-content-between align-items-center py-1 px-2 bg-light rounded mb-1">
                                <div class="small">
                                    <a href="{{ route('officer.sales.invoice', $sale->id) }}" class="text-decoration-none text-dark fw-bold">
                                        <i class="fas fa-receipt me-1"></i>Order #{{ $sale->id }}
                                    </a>
                                    <span class="text-muted ms-2">{{ $sale->created_at->format('M d, Y') }}</span>
                                </div>
                                <div class="small text-success fw-bold">{{ number_format($sale->total_amount) }} TZS</div>
                            </div>
                            @endforeach
                            @if($customer->sales->count() > 3)
                            <div class="small text-muted mt-1">
                                ... and {{ $customer->sales->count() - 3 }} more orders
                            </div>
                            @endif
                        </div>
                        @else
                        <div class="mt-2">
                            <small class="text-muted"><i class="fas fa-info-circle me-1"></i>No orders found for this customer</small>
                        </div>
                        @endif
                    </div>
                    <div class="ms-3">
                        <a href="{{ route('officer.customers.manage') }}?search={{ urlencode($customer->name) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>View Profile
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
            <div class="p-2 text-center border-top bg-light">
                <a href="{{ route('officer.sales') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-list me-1"></i>View All Sales
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
