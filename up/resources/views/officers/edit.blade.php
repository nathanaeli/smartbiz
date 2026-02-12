@extends('layouts.app')

@section('title', 'Edit Officer')

@section('content')
<div class="container-fluid card p-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Edit Officer</h2>
            <p class="text-muted mb-0">Update officer information</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('officers.show', $officer->id) }}" class="btn btn-outline-info">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="me-1">
                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                </svg>
                View Profile
            </a>
            <a href="{{ route('officers.index') }}" class="btn btn-outline-secondary">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="me-1">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                </svg>
                Back to Officers
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('officers.update', $officer->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- Basic Information -->
                            <div class="col-12">
                                <h5 class="mb-3">
                                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" class="me-2">
                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                    </svg>
                                    Basic Information
                                </h5>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $officer->name) }}" required>
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $officer->email) }}" required>
                                @error('email')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Contact Information -->
                            <div class="col-12 mt-4">
                                <h6 class="mb-3">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="me-2">
                                        <path d="M6.62 10.79c1.44 2.83 3.76 5.15 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                                    </svg>
                                    Contact Information
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" value="{{ old('phone', $officer->phone ?? '') }}" placeholder="+255 XXX XXX XXX">
                                @error('phone')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address', $officer->address ?? '') }}" placeholder="Physical address">
                                @error('address')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password Change (Optional) -->
                            <div class="col-12 mt-4">
                                <h6 class="mb-3">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="me-2">
                                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6zm3 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>
                                    </svg>
                                    Change Password (Optional)
                                </h6>
                                <div class="alert alert-info small">
                                    Leave password fields empty if you don't want to change the current password.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control">
                                @error('password')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Minimum 8 characters required</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control">
                                @error('password_confirmation')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Set Default Password Button -->
                            <div class="col-12 mt-3">
                                <div class="border rounded p-3 bg-light">
                                    <h6 class="mb-3 text-primary">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="me-2">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                        </svg>
                                        Quick Password Reset
                                    </h6>
                                    <p class="text-muted small mb-3">
                                        Reset this officer's password to your organization's default password
                                        (<strong>{{ auth()->user()->tenant->default_password ?? '123456' }}</strong>).
                                        This will override any custom password set above.
                                    </p>
                                    <form method="POST" action="{{ route('officers.set-default-password', $officer->id) }}" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-warning btn-sm"
                                                onclick="return confirm('Are you sure you want to reset this officer\'s password to the default?')">
                                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24" class="me-1">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                            </svg>
                                            Set Default Password
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    <strong>Last updated:</strong> {{ $officer->updated_at->format('M d, Y H:i') }}
                                </div>
                                <div>
                                    <a href="{{ route('officers.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="me-1">
                                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                        </svg>
                                        Update Officer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
