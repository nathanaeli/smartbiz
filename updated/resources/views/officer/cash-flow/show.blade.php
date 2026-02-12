@extends(auth()->user()->hasRole('officer') ? 'layouts.officer' : 'layouts.app')

@section('content')
<div class="container-fluid card">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('officer.dashboard') }}">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('cash-flow.index') }}">Cash Flow</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Entry Details
            </li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8 mx-auto">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>Cash Flow Entry Details
                    </h4>
                    <div>
                        <a href="{{ route('cash-flow.edit', $cashFlow) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        <a href="{{ route('cash-flow.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Type Badge -->
                        <div class="col-md-6 mb-4">
                            <div class="d-flex align-items-center">
                                <span class="badge fs-6 bg-{{ $cashFlow->type === 'income' ? 'success' : 'danger' }} me-3">
                                    <i class="fas fa-{{ $cashFlow->type === 'income' ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                    {{ ucfirst($cashFlow->type) }}
                                </span>
                                <h5 class="mb-0 text-{{ $cashFlow->type === 'income' ? 'success' : 'danger' }}">
                                    {{ $cashFlow->type === 'income' ? '+' : '-' }}{{ number_format($cashFlow->amount, 2) }} TSH
                                </h5>
                            </div>
                        </div>

                        <!-- Transaction Date -->
                        <div class="col-md-6 mb-4 text-end">
                            <small class="text-muted">Transaction Date</small>
                            <h6 class="mb-0">{{ $cashFlow->transaction_date->format('F d, Y') }}</h6>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Category</label>
                                <p class="mb-0">{{ $cashFlow->category }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Duka</label>
                                <p class="mb-0">{{ $cashFlow->duka->name }} - {{ $cashFlow->duka->location }}</p>
                            </div>
                        </div>
                    </div>

                    @if($cashFlow->description)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <p class="mb-0">{{ $cashFlow->description }}</p>
                        </div>
                    @endif

                    @if($cashFlow->reference_number)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Reference Number</label>
                            <p class="mb-0">{{ $cashFlow->reference_number }}</p>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Recorded By</label>
                                <p class="mb-0">{{ $cashFlow->user->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Created At</label>
                                <p class="mb-0">{{ $cashFlow->created_at->format('F d, Y \a\t H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    @if($cashFlow->updated_at != $cashFlow->created_at)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Last Updated</label>
                            <p class="mb-0">{{ $cashFlow->updated_at->format('F d, Y \a\t H:i') }}</p>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('cash-flow.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to List
                        </a>

                        <div>
                            <a href="{{ route('cash-flow.edit', $cashFlow) }}" class="btn btn-warning me-2">
                                <i class="fas fa-edit me-1"></i>Edit Entry
                            </a>
                            <form action="{{ route('cash-flow.destroy', $cashFlow) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete this cash flow entry?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
