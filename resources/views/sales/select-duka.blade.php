@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-dark">Select a Duka to Start Selling</h2>
                <p class="text-muted">Choose a location to launch the Point of Sale</p>
            </div>

            <div class="row g-4 justify-content-center">
                @forelse($dukas as $duka)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('sale.process', ['dukaId' => $duka->id]) }}" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                            <div class="card-body p-4 text-center">
                                <div class="mb-3">
                                    <div class="avatar avatar-xl bg-primary-subtle text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                        <i class="fas fa-store fa-2x"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">{{ $duka->name }}</h5>
                                <p class="small text-muted mb-3">{{ $duka->location ?? 'No Location' }}</p>
                                <span class="btn btn-outline-primary rounded-pill px-4">
                                    Enter POS <i class="fas fa-arrow-right ms-2"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                @empty
                <div class="col-12 text-center">
                    <div class="alert alert-warning">
                        No Dukas found. Please create a Duka first.
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .transition-all {
        transition: all 0.3s ease;
    }
</style>
@endsection