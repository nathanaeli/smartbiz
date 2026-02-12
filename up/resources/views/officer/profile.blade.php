@extends('layouts.officer')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">My Profile</h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                @if($user->profile_picture)
                                    <img src="{{ asset('storage/profiles/' . $user->profile_picture) }}" alt="{{ $user->name }}" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                @else
                                    <img src="{{ asset('assets/images/avatars/01.png') }}" alt="Profile Picture" class="img-fluid rounded-circle" style="width: 150px; height: 150px;">
                                @endif
                                <h5 class="mt-3">{{ $user->name }}</h5>
                                <p class="text-muted">{{ ucfirst($user->role) }}</p>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h5>Personal Information</h5>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> {{ $user->name }}</p>
                                    <p><strong>Email:</strong> {{ $user->email }}</p>
                                    <p><strong>Phone:</strong> {{ $user->phone ?? 'Not provided' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Role:</strong> {{ ucfirst($user->role) }}</p>
                                    <p><strong>Status:</strong>
                                        <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </p>
                                    <p><strong>Joined:</strong> {{ $user->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>

                            @if($user->address)
                                <div class="mt-3">
                                    <strong>Address:</strong>
                                    <p>{{ $user->address }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <h5>My Assignments</h5>
                    @if($assignments->count() > 0)
                        <div class="row mt-3">
                            @foreach($assignments as $assignment)
                                <div class="col-md-6 mb-3">
                                    <div class="card border">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $assignment->duka->name }}</h6>
                                            <p class="card-text text-muted">{{ $assignment->duka->location }}</p>
                                            <p class="card-text">
                                                <strong>Role:</strong> {{ $assignment->role ?? 'Officer' }}<br>
                                                <strong>Assigned:</strong> {{ $assignment->created_at->format('M d, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-store-off display-4 text-muted"></i>
                            <p class="text-muted mt-2">No active assignments</p>
                        </div>
                    @endif

                    <!-- Edit Profile Form -->
                    <div class="mt-5">
                        <h5>Edit Profile</h5>
                        <form action="{{ route('officer.profile.update') }}" method="POST" enctype="multipart/form-data" class="mt-3">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Name *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="profile_picture" class="form-label">Profile Picture</label>
                                    <input type="file" class="form-control @error('profile_picture') is-invalid @enderror" id="profile_picture" name="profile_picture" accept="image/*">
                                    <small class="form-text text-muted">Upload a new profile picture (JPEG, PNG, GIF). Max size: 2MB. Leave empty to keep current picture.</small>
                                    @error('profile_picture')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                                <a href="{{ route('officer.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
