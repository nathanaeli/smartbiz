@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <div class="bg-soft-primary p-3 rounded-circle d-inline-block mb-3">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 9L12 3L21 9V20C21 20.5523 20.5523 21 20 21H4C3.44772 21 3 20.5523 3 20V9Z" stroke="#0d6efd" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 21V12H15V21" stroke="#0d6efd" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h2 class="fw-bold text-dark">Profit & Loss Analysis</h2>
                <p class="text-muted">Please select which Duka's financial performance you want to review.</p>
            </div>

            <div class="row g-4">
                @foreach($dukas as $duka)
                <div class="col-md-6">
                    <a href="{{ route('tenant.reports.pl', ['duka_id' => $duka->id]) }}"
                       class="card h-100 border-0 shadow-sm text-decoration-none transition-up">
                        <div class="card-body p-4 text-center">
                            <h4 class="fw-bold text-dark mb-1">{{ $duka->name }}</h4>
                            <p class="text-muted small mb-3">
                                <i class="ri-map-pin-line me-1"></i> {{ $duka->location ?? 'General Branch' }}
                            </p>
                            <span class="btn btn-sm btn-outline-primary rounded-pill px-4">Generate Report</span>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>

            <div class="text-center mt-5">
    <a href="{{ route('tenant.reports.consolidated_pl') }}" class="btn btn-dark rounded-pill px-5 shadow">
        <i class="ri-pie-chart-line me-2"></i> View Consolidated Report for all Shops
    </a>
</div>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    .transition-up {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .transition-up:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }
</style>
@endsection
