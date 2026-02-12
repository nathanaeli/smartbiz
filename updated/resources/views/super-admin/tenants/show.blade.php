@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4>Tenant Details: {{ $tenant->name }}</h4>
                <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-secondary">Back to Tenants</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Basic Information</h5>
                        <p><strong>Name:</strong> {{ $tenant->name }}</p>
                        <p><strong>Email:</strong> {{ $tenant->email }}</p>
                        <p><strong>Phone:</strong> {{ $tenant->phone }}</p>
                        <p><strong>Address:</strong> {{ $tenant->address }}</p>
                        <p><strong>Status:</strong>
                            <span class="badge bg-{{ $tenant->status == 'active' ? 'success' : 'secondary' }}">
                                {{ $tenant->status }}
                            </span>
                        </p>
                        <p><strong>Created:</strong> {{ $tenant->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        <h5>Statistics</h5>
                        <p><strong>Total Dukas:</strong> {{ $tenant->dukas->count() }}</p>
                        <p><strong>Total Customers:</strong> {{ $tenant->customers->count() }}</p>
                        <p><strong>Total Products:</strong> {{ $tenant->productCategories->count() }}</p>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5>Dukas</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tenant->dukas as $duka)
                                    <tr>
                                        <td>{{ $duka->name }}</td>
                                        <td>{{ $duka->location }}</td>
                                        <td>
                                            <span class="badge bg-{{ $duka->status == 'active' ? 'success' : 'secondary' }}">
                                                {{ $duka->status }}
                                            </span>
                                        </td>
                                        <td>{{ $duka->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No dukas found</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
