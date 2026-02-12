<div>
    <div class="container-fluid py-4 card">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 pb-4 border-bottom">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2 small">
                        <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}"
                                class="text-decoration-none">{{ __('messages.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('messages.duka_details') }}</li>
                    </ol>
                </nav>
                <h1 class="h2 fw-bold text-dark mb-1">{{ $duka->name }}</h1>
                <div class="text-secondary small d-flex align-items-center gap-3">
                    <span><i class="ri-map-pin-line me-1"></i> {{ $duka->location ?? __('messages.no_location_set') }}</span>
                    <span><i class="ri-user-settings-line me-1"></i> {{ $duka->manager_name ?? __('messages.no_manager') }}</span>
                </div>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('duka.edit', $duka->id) }}" class="btn btn-outline-dark btn-sm rounded-2 px-3">
                    <i class="ri-settings-3-line me-1"></i> {{ __('messages.configure_branch') }}
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
                <span class="fw-bold">{{ $subscription->plan_name }} {{ __('messages.plan') }}</span>
                <span class="text-muted ms-2">{{ __('messages.valid_until') }} {{ $subscription->end_date->format('M d, Y') }}</span>
            </div>
            <span class="badge {{ $isExpired ? 'bg-danger' : 'bg-success' }} rounded-pill">
                {{ $isExpired ? __('messages.inactive') : __('messages.active') }}
            </span>
        </div>
        @endif

        <div class="row g-3 mb-5">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm bg-white p-3">
                    <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.05rem;">
                        {{ __('messages.products') }}
                    </div>
                    <div class="h4 fw-bold mb-0 text-dark">
                        {{ $duka->products_with_stock_count ?? $duka->products->count() }}
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm bg-white p-3">
                    <div class="text-uppercase text-muted fw-bold mb-1"
                        style="font-size: 0.7rem; letter-spacing: 0.05rem;">{{ __('messages.available_stock') }}</div>
                    <div class="h4 fw-bold mb-0 text-primary">{{ number_format($duka->stocks->sum('quantity')) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm bg-white p-3">
                    <div class="text-uppercase text-muted fw-bold mb-1"
                        style="font-size: 0.7rem; letter-spacing: 0.05rem;">{{ __('messages.stock_value') }}</div>
                    <div class="h4 fw-bold mb-0 text-dark">
                        <span class="small opacity-50">TZS</span>
                        {{ number_format($duka->stocks->sum(fn($s) => $s->quantity * ($s->product->base_price ?? 0))) }}
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm bg-white p-3">
                    <div class="text-uppercase text-muted fw-bold mb-1"
                        style="font-size: 0.7rem; letter-spacing: 0.05rem;">{{ __('messages.customers') }}</div>
                    <div class="h4 fw-bold mb-0 text-dark">{{ $duka->customers->count() }}</div>
                </div>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Inventory/Products Card -->
            @if($duka->supportsProducts())
            <div class="col-md-4">
                <a href="{{ route('duka.inventory', $duka->id) }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm hover-elevate transition-all">
                        <div class="card-body p-4 text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-soft-primary rounded-circle mb-3" style="width: 64px; height: 64px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                                    <path d="M3 6h18" />
                                    <path d="M16 10a4 4 0 0 1-8 0" />
                                </svg>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">{{ __('messages.inventory_management') }}</h5>
                            <p class="text-muted small mb-0">{{ __('messages.manage_inventory_desc') }}</p>
                        </div>
                        <div class="card-footer bg-white border-top-0 pb-4 pt-0 text-center">
                            <span class="btn btn-outline-primary btn-sm rounded-pill px-4">{{ __('messages.view_inventory') }}</span>
                        </div>
                    </div>
                </a>
            </div>
            @endif

            <!-- Customers Card -->
            <div class="col-md-4">
                <a href="{{ route('duka.customers', $duka->id) }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm hover-elevate transition-all">
                        <div class="card-body p-4 text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-soft-info rounded-circle mb-3" style="width: 64px; height: 64px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-info">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">{{ __('messages.customer_directory') }}</h5>
                            <p class="text-muted small mb-0">{{ __('messages.view_customers_desc') }}</p>
                        </div>
                        <div class="card-footer bg-white border-top-0 pb-4 pt-0 text-center">
                            <span class="btn btn-outline-info btn-sm rounded-pill px-4">{{ __('messages.manage_customers') }}</span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Service Management Card (Conditionally shown) -->
            @if($duka->supportsServices())
            <div class="col-md-4">
                <a href="{{ route('duka.services.index', $duka->id) }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm hover-elevate transition-all">
                        <div class="card-body p-4 text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-soft-success rounded-circle mb-3" style="width: 64px; height: 64px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success">
                                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                                </svg>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">{{ __('messages.service_management') }}</h5>
                            <p class="text-muted small mb-0">{{ __('messages.configure_services_desc') }}</p>
                        </div>
                        <div class="card-footer bg-white border-top-0 pb-4 pt-0 text-center">
                            <span class="btn btn-outline-success btn-sm rounded-pill px-4">{{ __('messages.manage_services') }}</span>
                        </div>
                    </div>
                </a>
            </div>
            @endif

            <!-- Reports Card -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center bg-soft-warning rounded-circle mb-3" style="width: 64px; height: 64px;">
                            <i class="ri-bar-chart-groupped-line ri-2x text-warning"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">{{ __('messages.reports_analysis') }}</h5>
                        <p class="text-muted small mb-4">{{ __('messages.access_reports_desc') }}</p>

                        <div class="d-grid gap-2">
                            <a href="{{ route('sales.index', ['duka_id' => $duka->id]) }}" class="btn btn-warning btn-sm rounded-pill">
                                <i class="ri-file-list-3-line me-1"></i> {{ __('messages.sales_ledger') }}
                            </a>
                            <a href="{{ route('duka.aging.analysis', $duka->id) }}" class="btn btn-outline-warning btn-sm rounded-pill">
                                <i class="ri-pie-chart-2-line me-1"></i> {{ __('messages.aging_analysis') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>