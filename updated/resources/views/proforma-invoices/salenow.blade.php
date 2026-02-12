@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <!-- Header -->
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-indigo text-white rounded-circle mb-3" style="width: 70px; height: 70px;">
                    <i class="fas fa-cash-register fa-2x"></i>
                </div>
                <h2 class="fw-bold text-dark mb-2">Start a New Sale</h2>
                <p class="text-muted">Select the store you want to process sales for</p>
            </div>

            <!-- Store Selection Grid -->
            @php
            $dukas = \App\Models\Duka::where('tenant_id', auth()->user()->tenant->id)
            ->withCount(['stocks' => function($q) { $q->where('quantity', '>', 0); }])
            ->get();
            @endphp

            @if($dukas->count() == 0)
            <div class="text-center py-5">
                <i class="fas fa-store-slash fa-4x text-muted mb-3 opacity-25"></i>
                <h5 class="text-muted">No Stores Available</h5>
                <p class="text-muted small">Create a store first to start processing sales.</p>
                <a href="{{ route('duka.create.plan') }}" class="btn btn-primary mt-2">
                    <i class="fas fa-plus me-2"></i> Create Store
                </a>
            </div>
            @elseif($dukas->count() == 1)
            @php
            // Auto-redirect if only one store
            header("Location: " . route('sale.process', ['dukaId' => $dukas->first()->id]));
            exit;
            @endphp
            @else
            <div class="row g-4">
                @foreach($dukas as $duka)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('sale.process', ['dukaId' => $duka->id]) }}" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm store-card" style="transition: transform 0.2s, box-shadow 0.2s;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start justify-content-between mb-3">
                                    <div class="bg-indigo text-white rounded-3 p-3">
                                        <i class="fas fa-store fa-lg"></i>
                                    </div>
                                    <span class="badge {{ $duka->status == 'active' ? 'bg-success' : 'bg-secondary' }} rounded-pill">
                                        {{ ucfirst($duka->status) }}
                                    </span>
                                </div>

                                <h5 class="fw-bold text-dark mb-1">{{ $duka->name }}</h5>
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-map-marker-alt me-1"></i> {{ $duka->location ?? 'No location set' }}
                                </p>

                                <div class="d-flex gap-3 text-muted small">
                                    <span>
                                        <i class="fas fa-box text-primary me-1"></i>
                                        {{ $duka->stocks_count }} Products
                                    </span>
                                </div>
                            </div>
                            <div class="card-footer bg-light border-0 text-center py-3">
                                <span class="text-primary fw-bold">
                                    <i class="fas fa-arrow-right me-1"></i> Start Selling
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .bg-indigo {
        background-color: #4f46e5;
    }

    .store-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12) !important;
    }
</style>
@endsection