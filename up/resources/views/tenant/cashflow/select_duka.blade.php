@extends('layouts.app')

@section('content')
    <div class="container py-5 card">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <h2 class="fw-bold text-dark">Cash Flow Management</h2>
                    <p class="text-muted">You have multiple business branches. Please select a Duka to view its specific
                        financial reports and transactions.</p>
                </div>

                <div class="row g-4">
                    @foreach ($dukas as $duka)
                        <div class="col-md-6">
                            <a href="{{ route('tenant.cashflow.index', ['duka_id' => $duka->id]) }}"
                                class="card h-100 border-0 shadow-sm text-decoration-none transition-all hover-lift">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-shape bg-soft-primary text-primary rounded-circle me-3">
                                            <i class="ri-store-2-line fs-4"></i>
                                        </div>
                                        <h4 class="mb-0 text-dark">{{ $duka->name }}</h4>
                                    </div>

                                    <p class="text-muted small mb-3">
                                        <i class="ri-map-pin-line me-1"></i> {{ $duka->location ?? 'Location not set' }}
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                        <span class="text-primary fw-semibold small">View Statements</span>
                                        <i class="ri-arrow-right-line text-primary"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="text-center mt-5 pt-4">
                    <p class="text-muted mb-3">Want to see how the whole business is performing?</p>
                    <a href="{{ route('tenant.reports.consolidated') }}"
                        class="btn btn-outline-dark rounded-pill px-4 shadow-sm transition-hover">
                        <i class="ri-pie-chart-line me-2"></i> View Consolidated Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .bg-soft-primary {
            background-color: rgba(13, 110, 253, 0.1);
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endsection
