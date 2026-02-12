@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4>Super Admin Dashboard</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Tenants</h5>
                                <h2>{{ \App\Models\Tenant::count() }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Users</h5>
                                <h2>{{ \App\Models\User::count() }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Active Tenants</h5>
                                <h2>{{ \App\Models\Tenant::where('status', 'active')->count() }}</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Management Sections</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-building"></i> Manage Tenants
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="{{ route('super-admin.users.index') }}" class="btn btn-outline-success w-100">
                                            <i class="fas fa-users"></i> Manage Users
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="{{ route('super-admin.features.index') }}" class="btn btn-outline-info w-100">
                                            <i class="fas fa-star"></i> Manage Features
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="{{ route('super-admin.plans.index') }}" class="btn btn-outline-warning w-100">
                                            <i class="fas fa-crown"></i> Manage Plans
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Recent Tenants</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(\App\Models\Tenant::latest()->take(5)->get() as $tenant)
                                            <tr>
                                                <td>{{ $tenant->name }}</td>
                                                <td>{{ $tenant->email }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $tenant->status == 'active' ? 'success' : 'secondary' }}">
                                                        {{ $tenant->status }}
                                                    </span>
                                                </td>
                                                <td>{{ $tenant->created_at->format('M d, Y') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>System Overview</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>PHP Version:</strong> {{ PHP_VERSION }}</p>
                                <p><strong>Laravel Version:</strong> {{ app()->version() }}</p>
                                <p><strong>Database:</strong> {{ config('database.default') }}</p>
                                <p><strong>Environment:</strong> {{ app()->environment() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
