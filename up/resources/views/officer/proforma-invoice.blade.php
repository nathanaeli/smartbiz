@extends('layouts.officer')

@section('content')
<div class="container-fluid card">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">{{ __('proforma.title') }}</h1>
                    <p class="text-muted">{{ __('proforma.subtitle') }}</p>
                </div>
                <div>
                    <a href="{{ route('officer.dashboard') }}" class="btn btn-secondary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                            <path d="M19 12H5M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ __('proforma.back_to_dashboard') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @livewire('officer-proforma-invoice')
</div>
@endsection
