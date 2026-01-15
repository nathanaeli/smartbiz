@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4>Customers Overview</h4>
            </div>
            <div class="card-body">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3>{{ $totalCustomers }}</h3>
                                <p class="mb-0">Total Customers</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3>{{ $activeCustomers }}</h3>
                                <p class="mb-0">Active Customers</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-secondary text-white">
                            <div class="card-body text-center">
                                <h3>{{ $inactiveCustomers }}</h3>
                                <p class="mb-0">Inactive Customers</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <form method="GET" action="{{ route('super-admin.customers.index') }}" class="d-flex">
                            <input type="text" name="search" class="form-control me-2"
                                   placeholder="Search customers by name, phone, or email..."
                                   value="{{ request('search') }}">
                            <button type="submit" class="btn btn-outline-primary">Search</button>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form method="GET" action="{{ route('super-admin.customers.index') }}">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </form>
                    </div>
                    <div class="col-md-5 text-end">
                        <a href="{{ route('super-admin.customers.index') }}" class="btn btn-outline-secondary">Clear Filters</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Duka</th>
                                <th>Tenant</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                            <tr>
                                <td>{{ $customer->id }}</td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->phone ?? 'N/A' }}</td>
                                <td>{{ $customer->email ?? 'N/A' }}</td>
                                <td>
                                    @if($customer->duka)
                                        <a href="{{ route('super-admin.dukas.show', $customer->duka->id) }}">
                                            {{ $customer->duka->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">No Duka</span>
                                    @endif
                                </td>
                                <td>
                                    @if($customer->tenant && $customer->tenant->user)
                                        {{ $customer->tenant->user->name }}
                                    @else
                                        <span class="text-muted">No Tenant</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $customer->status === 'active' ? 'success' : 'danger' }}">
                                        {{ ucfirst($customer->status) }}
                                    </span>
                                </td>
                                <td>{{ $customer->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('super-admin.customers.show', $customer->id) }}" class="btn btn-sm btn-info">View</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">No customers found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $customers->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
