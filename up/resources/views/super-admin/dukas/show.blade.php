@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Duka Details: {{ $duka->name }}</h4>
                <a href="{{ route('super-admin.dukas.index') }}" class="btn btn-secondary">Back to Dukas</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Duka Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Name:</th>
                                        <td>{{ $duka->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Location:</th>
                                        <td>{{ $duka->location }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            <span class="badge bg-{{ $duka->status === 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($duka->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Tenant:</th>
                                        <td>
                                            @if($duka->tenant && $duka->tenant->user)
                                                <a href="{{ route('super-admin.tenants.show', $duka->tenant->id) }}">
                                                    {{ $duka->tenant->user->name }}
                                                </a>
                                            @else
                                                <span class="text-muted">No Tenant</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Total Revenue:</th>
                                        <td><strong>Tsh{{ number_format($totalRevenue, 2) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Created:</th>
                                        <td>{{ $duka->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Current Plan Status</h5>
                            </div>
                            <div class="card-body">
                                @php
                                    $activeSubscription = $subscriptionHistory->where('status', 'active')->first();
                                @endphp
                                @if($activeSubscription)
                                    <table class="table table-borderless">
                                        <tr>
                                            <th>Plan:</th>
                                            <td>{{ $activeSubscription->plan->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                @if($activeSubscription->end_date >= \Carbon\Carbon::now())
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Expired</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Start Date:</th>
                                            <td>{{ $activeSubscription->start_date->format('M d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>End Date:</th>
                                            <td>{{ $activeSubscription->end_date->format('M d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Days Remaining:</th>
                                            <td>
                                                @if($activeSubscription->end_date >= \Carbon\Carbon::now())
                                                    <span class="text-success">
                                                        {{ \Carbon\Carbon::now()->diffInDays($activeSubscription->end_date) }} days
                                                    </span>
                                                @else
                                                    <span class="text-danger">Expired</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                @else
                                    <p class="text-muted">No active subscription</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscription History -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Subscription History</h5>
                            </div>
                            <div class="card-body">
                                @if($subscriptionHistory->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Plan</th>
                                                    <th>Status</th>
                                                    <th>Start Date</th>
                                                    <th>End Date</th>
                                                    <th>Created</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($subscriptionHistory as $subscription)
                                                <tr>
                                                    <td>{{ $subscription->plan->name }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $subscription->status === 'active' ? 'success' : 'secondary' }}">
                                                            {{ ucfirst($subscription->status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $subscription->start_date->format('M d, Y') }}</td>
                                                    <td>{{ $subscription->end_date->format('M d, Y') }}</td>
                                                    <td>{{ $subscription->created_at->format('M d, Y H:i') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted">No subscription history</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Sales -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Recent Sales (Last 10)</h5>
                            </div>
                            <div class="card-body">
                                @if($duka->sales->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Customer</th>
                                                    <th>Total Amount</th>
                                                    <th>Payment Status</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($duka->sales->take(10) as $sale)
                                                <tr>
                                                    <td>{{ $sale->id }}</td>
                                                    <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                                                    <td>Tsh{{ number_format($sale->total_amount, 2) }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $sale->payment_status === 'paid' ? 'success' : ($sale->payment_status === 'loan' ? 'warning' : 'secondary') }}">
                                                            {{ ucfirst($sale->payment_status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted">No sales recorded</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
