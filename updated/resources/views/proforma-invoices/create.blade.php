@extends('layouts.app')

@section('title', 'Create Proforma Invoice')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Select Duka to Create Proforma Invoice</h4>
                    <a href="{{ route('proforma.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Invoices
                    </a>
                </div>

                <div class="card-body">
                    @if($dukas->count() === 0)
                        <div class="alert alert-warning">
                            No Duka found. Please create a Duka first.
                        </div>
                    @else
                        <div class="row">
                            @foreach($dukas as $duka)
                            <div class="col-md-4 mb-3">
                                <div class="card border border-2 p-3 text-center"
                                     style="cursor:pointer;"
                                     onclick="window.location.href='{{ route('proforma-invoices.create.for.duka', $duka->id) }}'">

                                    <div class="mb-2">
                                        <i class="fas fa-store text-primary" style="font-size: 2rem;"></i>
                                    </div>

                                    <h5 class="fw-bold">{{ $duka->name }}</h5>
                                    <p class="text-muted mb-1">{{ $duka->location ?? 'No location set' }}</p>
                                    <p class="text-muted small">Manager: {{ $duka->manager_name ?? 'N/A' }}</p>

                                    <div class="mt-2">
                                        <span class="badge bg-primary">Select</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
