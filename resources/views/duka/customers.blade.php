@extends('layouts.app')

@section('title', $duka->name . ' - Customers')

@section('content')
<div class="container-fluid py-4 card">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 pb-4 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 small">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('duka.show', $duka->id) }}" class="text-decoration-none">{{ $duka->name }}</a></li>
                    <li class="breadcrumb-item active">Customer Directory</li>
                </ol>
            </nav>
            <h1 class="h2 fw-bold text-dark mb-1">Customer Management</h1>
            <p class="text-muted small mb-0">View and manage customers associated with {{ $duka->name }}.</p>
        </div>
        <div class="mt-3 mt-md-0">
             <a href="{{ route('duka.show', $duka->id) }}" class="btn btn-outline-secondary btn-sm rounded-2 px-3">
                <i class="ri-arrow-left-line me-1"></i> Back to Duka
            </a>
        </div>
    </div>

    @livewire('duka-customers', ['dukaId' => $duka->id])
</div>
@endsection
