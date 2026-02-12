@extends('layouts.app')

@section('content')
<div class="container-fluid card p-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Edit Duka: {{ $duka->name }}</h2>
            <p class="text-muted mb-0">Update your duka information</p>
        </div>
        <div>
            <a href="{{ route('duka.show', $duka->id) }}" class="btn btn-outline-secondary">
                Back to Duka
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="row justify-content-center">
        <div class="col-12"> <!-- FULL WIDTH -->
            <div class="card shadow w-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Duka Information</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('duka.update', $duka->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Duka Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="name" name="name" value="{{ old('name', $duka->name) }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Location -->
                        <div class="mb-3">
                            <label for="location" class="form-label fw-bold">Location</label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror"
                                id="location" name="location" value="{{ old('location', $duka->location) }}">
                            @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Manager Name -->
                        <div class="mb-3">
                            <label for="manager_name" class="form-label fw-bold">Manager Name</label>
                            <input type="text" class="form-control @error('manager_name') is-invalid @enderror"
                                id="manager_name" name="manager_name" value="{{ old('manager_name', $duka->manager_name) }}">
                            @error('manager_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Business Type -->
                        <div class="mb-3">
                            <label for="business_type" class="form-label fw-bold">Business Type <span class="text-danger">*</span></label>
                            <select name="business_type" id="business_type" class="form-select @error('business_type') is-invalid @enderror" required>
                                <option value="product" {{ old('business_type', $duka->business_type) == 'product' ? 'selected' : '' }}>Retail / Products Only</option>
                                <option value="service" {{ old('business_type', $duka->business_type) == 'service' ? 'selected' : '' }}>Services Only</option>
                                <option value="both" {{ old('business_type', $duka->business_type) == 'both' ? 'selected' : '' }}>Both Products & Services</option>
                            </select>
                            @error('business_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Coordinates -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="latitude" class="form-label fw-bold">Latitude</label>
                                <input type="number" step="any" class="form-control @error('latitude') is-invalid @enderror"
                                    id="latitude" name="latitude" value="{{ old('latitude', $duka->latitude) }}">
                                @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="longitude" class="form-label fw-bold">Longitude</label>
                                <input type="number" step="any" class="form-control @error('longitude') is-invalid @enderror"
                                    id="longitude" name="longitude" value="{{ old('longitude', $duka->longitude) }}">
                                @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <input type="text" class="form-control" value="{{ ucfirst($duka->status) }}" readonly>
                            <small class="text-muted">Status cannot be changed here</small>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('duka.show', $duka->id) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Duka</button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection