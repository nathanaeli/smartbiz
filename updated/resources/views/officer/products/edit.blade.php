@extends('layouts.officer')

@section('content')
<div class="container-fluid card">
    <!-- Smart Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('officer.dashboard') }}">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('manageproduct') }}">Product Management</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Edit: {{ $product->name }}
            </li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <!-- Smart Product Summary Card -->
            <div class="card mb-4 border-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            @if($product->image)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                     class="img-fluid rounded" style="max-height: 80px;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                     style="height: 80px; width: 80px;">
                                    <i class="fas fa-box fa-2x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h5 class="card-title mb-1">{{ $product->name }}</h5>
                            <p class="text-muted mb-2">{{ $product->sku }}</p>
                            <div class="row g-2">
                                <div class="col-auto">
                                    <span class="badge bg-secondary">{{ $product->category->name ?? 'No Category' }}</span>
                                </div>
                                <div class="col-auto">
                                    <span class="badge bg-info">{{ $product->stocks->sum('quantity') }} {{ $product->unit }}</span>
                                </div>
                                <div class="col-auto">
                                    <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                                    </span>

                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <div class="small text-muted">Last Updated</div>
                            <div>{{ $product->updated_at ? $product->updated_at->diffForHumans() : 'Never' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-edit me-2 text-primary"></i>Edit Product Details
                    </h4>
                    <a href="{{ route('manageproduct') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Products
                    </a>
                </div>
                <div class="card-body">
                    @livewire('officer-product-form', ['product' => $product])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
