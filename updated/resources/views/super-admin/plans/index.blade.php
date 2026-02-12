@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="bg-primary rounded-circle p-2 me-3">
                        <svg width="24" height="24" fill="white" viewBox="0 0 16 16">
                            <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l2.147 2.146a.5.5 0 0 0 .707-.708L10.707 8l2.147-2.146a.5.5 0 0 0-.707-.708L10.293 7.5H4.5z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="mb-0">Plans Management</h4>
                        <small class="text-muted">Manage subscription plans and pricing</small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <div class="text-end me-3">
                        <div class="fw-bold text-primary">{{ $plans->total() }}</div>
                        <small class="text-muted">Total Plans</small>
                    </div>
                    <a href="{{ route('super-admin.plans.create') }}" class="btn btn-primary d-flex align-items-center">
                        <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                        </svg>
                        Add New Plan
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Success/Error Messages -->
                @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <form method="GET" action="{{ route('super-admin.plans.index') }}" class="d-flex">
                            <input type="text" name="search" class="form-control me-2"
                                   placeholder="Search plans by name or description..."
                                   value="{{ request('search') }}">
                            <button type="submit" class="btn btn-outline-primary">Search</button>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form method="GET" action="{{ route('super-admin.plans.index') }}">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </form>
                    </div>
                    <div class="col-md-5 text-end">
                        <a href="{{ route('super-admin.plans.index') }}" class="btn btn-outline-secondary">Clear Filters</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th class="border-0">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M5 4a1 1 0 0 0-.894.553L2.382 8H1a1 1 0 0 0 0 2h1.618l1.447 2.894A1 1 0 0 0 5 13h6a1 1 0 0 0 .894-.553L13.618 10H15a1 1 0 0 0 0-2h-1.382l-1.724-3.447A1 1 0 0 0 11 4H5zM2.5 9h11l-1.5 3H4l-1.5-3z"/>
                                    </svg>
                                    ID
                                </th>
                                <th class="border-0">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z"/>
                                    </svg>
                                    Name
                                </th>
                                <th class="border-0">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H8.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.129-1.84H4zm3.012-7.298c-1.282-.12-2.148-.735-2.148-1.62 0-.864.912-1.572 2.148-1.653v3.273zm0 8.274V8.63c1.349.122 2.196.72 2.196 1.636 0 .895-.962 1.647-2.196 1.717z"/>
                                    </svg>
                                    Price
                                </th>
                                <th class="border-0">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                    </svg>
                                    Billing Cycle
                                </th>
                                <th class="border-0">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
                                    </svg>
                                    Limits
                                </th>
                                <th class="border-0">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M8 9.05a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/>
                                        <path d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H2zm10.5 5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5z"/>
                                    </svg>
                                    Status
                                </th>
                                <th class="border-0">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M2.5 3A1.5 1.5 0 0 0 1 4.5v.793c.026.009.051.02.076.032L7.674 8.51c.206.1.446.1.652 0l6.598-3.185A.755.755 0 0 1 15 5.293V4.5A1.5 1.5 0 0 0 13.5 3h-11Z"/>
                                        <path d="M15 6.954 8.978 9.86a2.25 2.25 0 0 1-1.956 0L1 6.954V11.5A1.5 1.5 0 0 0 2.5 13h11a1.5 1.5 0 0 0 1.5-1.5V6.954Z"/>
                                    </svg>
                                    Features
                                </th>
                                <th class="border-0">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                    </svg>
                                    Created
                                </th>
                                <th class="border-0 text-center">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/>
                                    </svg>
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plans as $plan)
                            <tr>
                                <td>{{ $plan->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="bg-{{ $plan->is_active ? 'success' : 'secondary' }} rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                <svg width="16" height="16" fill="white" viewBox="0 0 16 16">
                                                    <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l2.147 2.146a.5.5 0 0 0 .707-.708L10.707 8l2.147-2.146a.5.5 0 0 0-.707-.708L10.293 7.5H4.5z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div>
                                            <strong>{{ $plan->name }}</strong>
                                            @if($plan->description)
                                                <br><small class="text-muted">{{ Str::limit($plan->description, 40) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary">${{ number_format($plan->price, 2) }}</span>
                                    <br><small class="text-muted">{{ ucfirst($plan->billing_cycle) }}</small>
                                </td>
                                <td>{{ ucfirst($plan->billing_cycle) }}</td>
                                <td>
                                    <div class="small">
                                        <div><strong>Dukas:</strong> {{ $plan->max_dukas }}</div>
                                        <div><strong>Products:</strong> {{ $plan->max_products }}</div>
                                    </div>
                                </td>
                                <td>
                                    @if($plan->is_active)
                                        <span class="badge bg-success d-inline-flex align-items-center">
                                            <svg width="10" height="10" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                                <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                                            </svg>
                                            Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger d-inline-flex align-items-center">
                                            <svg width="10" height="10" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                                <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                                            </svg>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($plan->planFeatures->count() > 0)
                                        <span class="badge bg-info">{{ $plan->planFeatures->count() }} features</span>
                                    @else
                                        <span class="badge bg-secondary">No features</span>
                                    @endif
                                </td>
                                <td>{{ $plan->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('super-admin.plans.show', $plan->id) }}"
                                           class="btn btn-sm btn-outline-info"
                                           data-bs-toggle="tooltip" data-bs-placement="top" title="View Plan">
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                                                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('super-admin.plans.edit', $plan->id) }}"
                                           class="btn btn-sm btn-outline-warning"
                                           data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Plan">
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 1 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2-2a.5.5 0 0 0-.65.65l2 2a.5.5 0 0 0 .168.11l.178-.178z"/>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('super-admin.plans.destroy', $plan->id) }}" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to delete this plan?')"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Plan">
                                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <svg width="48" height="48" fill="currentColor" class="text-muted mb-3" viewBox="0 0 16 16">
                                        <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
                                    </svg>
                                    <h5 class="text-muted">No Plans Found</h5>
                                    <p class="text-muted mb-3">There are no plans matching your search criteria.</p>
                                    <a href="{{ route('super-admin.plans.create') }}" class="btn btn-primary">
                                        <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                        </svg>
                                        Create Your First Plan
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $plans->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.btn-group .btn {
    border-radius: 0.375rem !important;
    margin-right: 2px;
    transition: all 0.2s ease-in-out;
}
.btn-group .btn:first-child {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}
.btn-group .btn:last-child {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}
.btn-group .btn:not(:first-child):not(:last-child) {
    border-radius: 0 !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease-in-out;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 0.5rem 0.5rem 0 0 !important;
}

.card-header .bg-primary {
    background: rgba(255, 255, 255, 0.2) !important;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.badge {
    font-weight: 500;
    padding: 0.4em 0.8em;
    border-radius: 20px;
}

.table-dark th {
    background-color: #343a40 !important;
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.table-dark th svg {
    opacity: 0.8;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
@endsection
