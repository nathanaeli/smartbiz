@extends('layouts.app')

@section('content')

    <div class="container-fluid py-5">
        <div class="row justify-content-center">

            <div class="col-12 col-lg-10">
                <div class="card shadow border-0" style="border-radius: 20px;">

                    <!-- Header -->
                    <div class="card-header text-white py-4"
                        style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 20px 20px 0 0;">
                        <h3 class="mb-0">
                            <i class="fas fa-store me-2"></i> Register Your Duka
                        </h3>
                        <p class="mb-0 opacity-75 fs-6">Set up your business location to start using stockflowkp</p>
                    </div>


                    <!-- Body -->
                    <div class="card-body px-4 px-md-5 py-5">

                        {{-- Errors --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Success --}}
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <!-- FORM START -->
                        <form action="{{ route('duka.store') }}" method="POST">
                            @csrf

                            <!-- DUKA DETAILS -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="fw-bold mb-3">
                                        <i class="fas fa-info-circle me-2 text-primary"></i> Duka Details
                                    </h5>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Duka Name *</label>
                                    <input type="text"
                                        class="form-control form-control-lg @error('name') is-invalid @enderror"
                                        name="name" value="{{ old('name') }}" placeholder="e.g., Petson Supermarket"
                                        required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Location *</label>
                                    <input type="text"
                                        class="form-control form-control-lg @error('location') is-invalid @enderror"
                                        name="location" value="{{ old('location') }}" placeholder="e.g., Kigoma – Mwanga"
                                        required>
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr>

                            <!-- MANAGER INFO -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="fw-bold mb-3">
                                        <i class="fas fa-user-tie me-2 text-success"></i> Manager Information
                                    </h5>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Manager Name (Optional)</label>
                                    <input type="text"
                                        class="form-control form-control-lg @error('manager_name') is-invalid @enderror"
                                        name="manager_name" value="{{ old('manager_name', auth()->user()->name) }}"
                                        placeholder="Enter manager name">
                                    @error('manager_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">If empty, your name will be used.</small>
                                </div>
                            </div>

                            <hr>

                            <!-- GEO LOCATION -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="fw-bold mb-3">
                                        <i class="fas fa-map-marker-alt me-2 text-danger"></i> Coordinates (Optional)
                                    </h5>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Latitude</label>
                                    <input type="number" step="any"
                                        class="form-control form-control-lg @error('latitude') is-invalid @enderror"
                                        name="latitude" id="latitude" value="{{ old('latitude') }}" placeholder="-6.1620">
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Longitude</label>
                                    <input type="number" step="any"
                                        class="form-control form-control-lg @error('longitude') is-invalid @enderror"
                                        name="longitude" id="longitude" value="{{ old('longitude') }}"
                                        placeholder="35.7516">
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 mt-2">
                                    <button type="button" id="getLocationBtn" class="btn btn-outline-info">
                                        <i class="fas fa-crosshairs me-2"></i> Get My Current Location
                                    </button>

                                    <!-- Smart message placeholder -->
                                    <div id="locationMessage" class="mt-3" style="display:none;"></div>
                                </div>
                            </div>

                            <hr>

                            <!-- SUBMIT -->
                            <div class="row">
                                <div class="col-12 d-grid">
                                    <button type="submit" class="btn btn-lg text-white py-3"
                                        style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius:12px;">
                                        <i class="fas fa-save me-2"></i> Save Duka
                                    </button>
                                </div>
                            </div>


                        </form>
                        <!-- FORM END -->

                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- GEOLOCATION SMART SCRIPT --}}
    <script>
        document.getElementById('getLocationBtn').addEventListener('click', function() {

            const locationMessage = document.getElementById('locationMessage');

            locationMessage.style.display = "none";
            locationMessage.innerHTML = "";

            if (!navigator.geolocation) {
                locationMessage.style.display = "block";
                locationMessage.className = "alert alert-danger mt-3";
                locationMessage.innerHTML =
                    "<strong>Error:</strong> Your browser does not support location services.";
                return;
            }

            // Loading message
            locationMessage.style.display = "block";
            locationMessage.className = "alert alert-info mt-3";
            locationMessage.innerHTML = "<strong>Retrieving location...</strong> Please wait.";

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    document.getElementById('latitude').value = position.coords.latitude;
                    document.getElementById('longitude').value = position.coords.longitude;

                    locationMessage.className = "alert alert-success mt-3";
                    locationMessage.innerHTML = `
                <strong>Location Found!</strong><br>
                Latitude: ${position.coords.latitude}<br>
                Longitude: ${position.coords.longitude}
            `;
                },
                function(error) {

                    let message = "";

                    switch (error.code) {

                        case error.PERMISSION_DENIED:
                            message = `
                        <strong>Permission Denied</strong><br>
                        Please allow location access for more accurate results.<br><br>

                        <strong>Tips for accurate location:</strong>
                        <ul>
                            <li>Turn ON GPS (High Accuracy Mode)</li>
                            <li>Use your phone instead of laptop</li>
                            <li>Ensure the page is loaded over <b>HTTPS</b></li>
                            <li>Allow “Precise Location” in browser settings</li>
                        </ul>
                    `;
                            break;

                        case error.POSITION_UNAVAILABLE:
                            message = `
                        <strong>Location Unavailable</strong><br>
                        Move outside or enable GPS on your device.
                    `;
                            break;

                        case error.TIMEOUT:
                            message = `
                        <strong>Timeout</strong><br>
                        Location request took too long. Please try again.
                    `;
                            break;

                        default:
                            message = `
                        <strong>Error:</strong> Unable to retrieve location.
                    `;
                    }

                    locationMessage.className = "alert alert-warning mt-3";
                    locationMessage.innerHTML = message;
                }, {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0
                }
            );
        });
    </script>

@endsection
