@extends('layouts.app')

@section('title', 'Account Setup')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Account Setup</h4>
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

                        @if ($account)
                            <!-- Account Details -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <img src="{{ $account->logo_url }}" alt="{{ $account->company_name }}"
                                            class="img-fluid rounded" style="max-height: 200px;">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h3>{{ $account->company_name }}</h3>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <p><strong>Phone:</strong> {{ $account->phone ?? 'Not set' }}</p>
                                            <p><strong>Email:</strong> {{ $account->email ?? 'Not set' }}</p>
                                            <p>
                                                <strong>Website:</strong>
                                                @if ($account->website)
                                                    <a href="{{ $account->website }}" target="_blank">
                                                        {{ $account->website }}
                                                    </a>
                                                @else
                                                    Not set
                                                @endif
                                            </p>

                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Currency:</strong> {{ $account->currency }}</p>
                                            <p><strong>Timezone:</strong> {{ $account->timezone }}</p>
                                            <p><strong>Address:</strong> {{ $account->address ?? 'Not set' }}</p>
                                        </div>
                                    </div>
                                    @if ($account->description)
                                        <div class="mt-3">
                                            <strong>Description:</strong>
                                            <p>{{ $account->description }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Security Settings -->
                            <div class="row mt-5">
                                <div class="col-12">
                                    <div class="card border-primary">
                                        <div class="card-header">
                                            <h5 class="mb-0">
                                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"
                                                    class="me-2">
                                                    <path
                                                        d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6zm3 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z" />
                                                </svg>
                                                Security Settings
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <form method="POST" action="{{ route('account.update-settings') }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label for="default_password" class="form-label fw-bold">Default
                                                            Officer Password</label>
                                                        <input type="text"
                                                            class="form-control @error('default_password') is-invalid @enderror"
                                                            id="default_password" name="default_password"
                                                            value="{{ old('default_password', auth()->user()->tenant->default_password ?? '123456') }}"
                                                            required maxlength="50">
                                                        <small class="form-text text-muted">
                                                            This password will be used when resetting officer passwords or
                                                            creating new accounts.
                                                        </small>
                                                        @error('default_password')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-6 d-flex align-items-end">
                                                        <div class="w-100">
                                                            <label class="form-label fw-bold">Current Default</label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control"
                                                                    value="{{ auth()->user()->tenant->default_password ?? '123456' }}"
                                                                    readonly>
                                                                <button type="submit" class="btn btn-primary">
                                                                    <svg width="16" height="16" fill="currentColor"
                                                                        viewBox="0 0 24 24" class="me-1">
                                                                        <path
                                                                            d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                                                                    </svg>
                                                                    Update Default Password
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('account.edit') }}" class="btn btn-primary">Edit Account</a>
                                <button class="btn btn-danger"
                                    onclick="if(confirm('Are you sure you want to delete your account? This action cannot be undone.')) { document.getElementById('delete-form').submit(); }">Delete
                                    Account</button>
                                <form id="delete-form" action="{{ route('account.destroy') }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        @else
                            <!-- No Account Setup -->
                            <div class="text-center py-5">
                                <svg width="64" height="64" fill="currentColor" viewBox="0 0 24 24"
                                    class="text-muted mb-3">
                                    <path
                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                                </svg>
                                <h5 class="text-muted">No Account Setup</h5>
                                <p class="text-muted">You haven't set up your account details yet. Set up your account to
                                    customize your business information.</p>
                                <a href="{{ route('account.create') }}" class="btn btn-primary">Setup Account</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
