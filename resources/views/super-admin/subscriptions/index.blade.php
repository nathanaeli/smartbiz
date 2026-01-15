@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Subscriptions Management</h4>
                <a href="{{ route('super-admin.subscriptions.analytics') }}" class="btn btn-primary">View Analytics</a>
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
                        <form method="GET" action="{{ route('super-admin.subscriptions.index') }}" class="d-flex">
                            <input type="text" name="search" class="form-control me-2"
                                   placeholder="Search subscriptions by duka or plan..."
                                   value="{{ request('search') }}">
                            <button type="submit" class="btn btn-outline-primary">Search</button>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form method="GET" action="{{ route('super-admin.subscriptions.index') }}">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                        </form>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('super-admin.subscriptions.index') }}" class="btn btn-outline-secondary">Clear Filters</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Duka</th>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Payment Method</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscriptions as $subscription)
                            <tr>
                                <td>{{ $subscription->id }}</td>
                                <td>{{ $subscription->duka->name ?? 'N/A' }}</td>
                                <td>{{ $subscription->plan->name ?? $subscription->plan_name ?? 'N/A' }}</td>
                                <td>TZS {{ number_format($subscription->amount, 2) }}</td>
                                <td>{{ $subscription->start_date ? $subscription->start_date->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ $subscription->end_date ? $subscription->end_date->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    @php
                                        $statusInfo = $subscription->getStatusWithDays();
                                        $status = $statusInfo['status'];
                                        $daysRemaining = $statusInfo['days_remaining'];
                                    @endphp
                                    @if($status === 'active')
                                        <span class="badge bg-success">
                                            Active
                                            @if($daysRemaining > 0)
                                                ({{ $daysRemaining }} days left)
                                            @endif
                                        </span>
                                    @elseif($status === 'expired')
                                        <span class="badge bg-danger">Expired</span>
                                    @else
                                        <span class="badge bg-{{ $subscription->status === 'pending' ? 'warning' : ($subscription->status === 'cancelled' ? 'danger' : 'secondary') }}">
                                            {{ ucfirst($subscription->status ?? 'unknown') }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $subscription->payment_method ?? 'N/A' }}</td>
                                <td>{{ $subscription->created_at->format('M d, Y') }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info">View</button>
                                    @if($subscription->status === 'active')
                                        <button class="btn btn-sm btn-warning">Cancel</button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">No subscriptions found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $subscriptions->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
