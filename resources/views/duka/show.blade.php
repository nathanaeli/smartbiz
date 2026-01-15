@extends('layouts.app')
@section('title', $duka->name . ' - Duka Details')
@section('content')
    <div class="container-fluid card p-4">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">{{ $duka->name }}</h2>
                <p class="text-muted mb-0">
                    <i class="ri-map-pin-line me-1"></i>{{ $duka->location ?? 'Location not set' }}
                    • Manager: {{ $duka->manager_name ?? 'Not assigned' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="ri-arrow-left-line me-1"></i>Back to Dashboard
                </a>
                <a href="{{ route('duka.edit', $duka->id) }}" class="btn btn-primary">
                    <i class="ri-settings-4-line me-1"></i>Edit Duka
                </a>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Products</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $duka->products_with_stock_count ?? $duka->products->count() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="ri-archive-line fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Stock</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $duka->stocks->sum('quantity') }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="ri-stack-line fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Stock Value</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($duka->stocks->sum(fn($s) => $s->quantity * ($s->product->base_price ?? 0))) }}
                                    TZS
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="ri-money-dollar-circle-line fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Customers</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $duka->customers->count() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="ri-user-line fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscription Status -->
        @php
            $subscription = $duka->dukaSubscriptions->sortByDesc('id')->first();
            $isExpired = $subscription ? now()->greaterThan($subscription->end_date) : true;
        @endphp
        @if ($subscription)
            <div class="card mb-4 {{ $isExpired ? 'border-danger' : 'border-success' }} border">
                <div class="card-header">
                    <h5 class="mb-0"><i class="ri-calendar-check-line me-2"></i>Subscription Details</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center text-md-start">
                        <div class="col-md-3"><strong>Plan:</strong> {{ $subscription->plan_name }}</div>
                        <div class="col-md-3"><strong>Amount:</strong> {{ number_format($subscription->amount) }} TZS</div>
                        <div class="col-md-3"><strong>Status:</strong>
                            <span class="badge {{ $isExpired ? 'bg-danger' : 'bg-success' }}">
                                {{ $isExpired ? 'Expired' : 'Active' }}
                            </span>
                        </div>
                        <div class="col-md-3"><strong>Expires:</strong> {{ $subscription->end_date->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tabs -->
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#products"><i
                                class="ri-archive-line me-1"></i>Products</button>
                    </li>

                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#customers"><i
                                class="ri-user-line me-1"></i>Customers</button>
                    </li>

                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sales">
                            <svg class="me-1" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                            </svg>
                            Sales
                        </button>
                    </li>

                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#aging"><i
                                class="ri-bar-chart-line me-1"></i>Aging Analysis</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">

                    <!-- Products Tab -->
                    <div class="tab-pane fade show active" id="products">
                        @livewire('duka-products', ['dukaId' => $duka->id])
                    </div>

                    <!-- Stock Tab -->

                    <!-- Customers Tab -->
                    <div class="tab-pane fade" id="customers">
                        @livewire('duka-customers', ['dukaId' => $duka->id])
                    </div>

                    <!-- Sales Tab -->
                    <div class="tab-pane fade" id="sales">
                        <div class="text-center">
                            <a href="{{ route('sales.index', ['duka_id' => $duka->id]) }}" class="btn btn-primary btn-lg">
                                <svg class="me-2" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                                </svg>
                                View Sales for this Duka
                            </a>
                            <p class="text-muted mt-2">Click to view all sales records for {{ $duka->name }}</p>
                        </div>
                    </div>

                    <!-- Aging Analysis Tab -->
                    <div class="tab-pane fade" id="aging">
                        <div class="text-center">
                            <a href="{{ route('duka.aging.analysis', $duka->id) }}" class="btn btn-primary btn-lg">
                                <i class="ri-bar-chart-line me-2"></i>Generate Loan Aging Analysis Report
                            </a>
                            <p class="text-muted mt-2">Click to view detailed loan aging analysis for this duka</p>
                        </div>
                    </div>
                </div>products
            </div>
        </div>
    </div>

@endsection
