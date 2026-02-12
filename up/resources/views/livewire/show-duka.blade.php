<div>
    <div class="container-fluid py-4 card">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 pb-4 border-bottom">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2 small">
                        <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}"
                                class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active">Duka Details</li>
                    </ol>
                </nav>
                <h1 class="h2 fw-bold text-dark mb-1">{{ $duka->name }}</h1>
                <div class="text-secondary small d-flex align-items-center gap-3">
                    <span><i class="ri-map-pin-line me-1"></i> {{ $duka->location ?? 'No location set' }}</span>
                    <span><i class="ri-user-settings-line me-1"></i> {{ $duka->manager_name ?? 'No Manager' }}</span>
                </div>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('duka.edit', $duka->id) }}" class="btn btn-outline-dark btn-sm rounded-2 px-3">
                    <i class="ri-settings-3-line me-1"></i> Configure Branch
                </a>
            </div>
        </div>

        @php
            $subscription = $duka->dukaSubscriptions->sortByDesc('id')->first();
            $isExpired = $subscription ? now()->greaterThan($subscription->end_date) : true;
        @endphp
        @if ($subscription)
            <div
                class="alert {{ $isExpired ? 'alert-danger' : 'alert-light' }} border shadow-sm d-flex justify-content-between align-items-center px-4 py-2 mb-4">
                <div class="small">
                    <i
                        class="ri-checkbox-blank-circle-fill me-2 {{ $isExpired ? 'text-danger' : 'text-success' }}"></i>
                    <span class="fw-bold">{{ $subscription->plan_name }} Plan</span>
                    <span class="text-muted ms-2">Valid until {{ $subscription->end_date->format('M d, Y') }}</span>
                </div>
                <span class="badge {{ $isExpired ? 'bg-danger' : 'bg-success' }} rounded-pill">
                    {{ $isExpired ? 'Expired' : 'Active' }}
                </span>
            </div>
        @endif

        <div class="row g-3 mb-5">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm bg-white p-3">
                    <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.05rem;">
                        Products</div>
                    <div class="h4 fw-bold mb-0 text-dark">
                        {{ $duka->products_with_stock_count ?? $duka->products->count() }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm bg-white p-3">
                    <div class="text-uppercase text-muted fw-bold mb-1"
                        style="font-size: 0.7rem; letter-spacing: 0.05rem;">Available Stock</div>
                    <div class="h4 fw-bold mb-0 text-primary">{{ number_format($duka->stocks->sum('quantity')) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm bg-white p-3">
                    <div class="text-uppercase text-muted fw-bold mb-1"
                        style="font-size: 0.7rem; letter-spacing: 0.05rem;">Stock Value</div>
                    <div class="h4 fw-bold mb-0 text-dark">
                        <span class="small opacity-50">TZS</span>
                        {{ number_format($duka->stocks->sum(fn($s) => $s->quantity * ($s->product->base_price ?? 0))) }}
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm bg-white p-3">
                    <div class="text-uppercase text-muted fw-bold mb-1"
                        style="font-size: 0.7rem; letter-spacing: 0.05rem;">Customers</div>
                    <div class="h4 fw-bold mb-0 text-dark">{{ $duka->customers->count() }}</div>
                </div>
            </div>
        </div>

    <div class="row g-4 justify-content-center">
        <!-- Inventory/Products Card -->
        <div class="col-md-4">
            <a href="{{ route('duka.inventory', $duka->id) }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm hover-elevate transition-all">
                    <div class="card-body p-4 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center bg-soft-primary rounded-circle mb-3" style="width: 64px; height: 64px;">
                            <i class="ri-store-3-line ri-2x text-primary"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Inventory Management</h5>
                        <p class="text-muted small mb-0">Manage your product catalog, update stock levels, prices, and view item details.</p>
                    </div>
                    <div class="card-footer bg-white border-top-0 pb-4 pt-0 text-center">
                        <span class="btn btn-outline-primary btn-sm rounded-pill px-4">View Inventory</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Customers Card -->
        <div class="col-md-4">
            <a href="{{ route('duka.customers', $duka->id) }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm hover-elevate transition-all">
                    <div class="card-body p-4 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center bg-soft-info rounded-circle mb-3" style="width: 64px; height: 64px;">
                            <i class="ri-group-line ri-2x text-info"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Customer Directory</h5>
                        <p class="text-muted small mb-0">View registered customers, manage contact details, and track customer history.</p>
                    </div>
                    <div class="card-footer bg-white border-top-0 pb-4 pt-0 text-center">
                        <span class="btn btn-outline-info btn-sm rounded-pill px-4">Manage Customers</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Reports Card -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-soft-warning rounded-circle mb-3" style="width: 64px; height: 64px;">
                        <i class="ri-bar-chart-groupped-line ri-2x text-warning"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Reports & Analysis</h5>
                    <p class="text-muted small mb-4">Access detailed sales ledgers and analyze credit/aging reports.</p>
                    
                    <div class="d-grid gap-2">
                         <a href="{{ route('sales.index', ['duka_id' => $duka->id]) }}" class="btn btn-warning btn-sm rounded-pill">
                            <i class="ri-file-list-3-line me-1"></i> Sales Ledger
                        </a>
                        <a href="{{ route('duka.aging.analysis', $duka->id) }}" class="btn btn-outline-warning btn-sm rounded-pill">
                            <i class="ri-pie-chart-2-line me-1"></i> Aging Analysis
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
