@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Subscription Analytics</h4>
                <a href="{{ route('super-admin.subscriptions.index') }}" class="btn btn-secondary">View All Subscriptions</a>
            </div>
            <div class="card-body">
                <!-- Key Metrics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3>Tsh {{ number_format($totalRevenue, 0) }}</h3>
                                <p class="mb-0">Total Revenue</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3>{{ $activeSubscriptionsByPlan->sum('count') }}</h3>
                                <p class="mb-0">Active Subscriptions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3>{{ $expiringSubscriptions->count() }}</h3>
                                <p class="mb-0">Expiring Soon (30 days)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h3>{{ $expiredSubscriptions->count() }}</h3>
                                <p class="mb-0">Expired Subscriptions</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Revenue Chart -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Monthly Revenue (Last 12 Months)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Subscriptions by Plan -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Active Subscriptions by Plan</h5>
                            </div>
                            <div class="card-body">
                                @if($activeSubscriptionsByPlan->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Plan</th>
                                                    <th>Active Subscriptions</th>
                                                    <th>Revenue</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($activeSubscriptionsByPlan as $planData)
                                                    @php
                                                        $plan = \App\Models\Plan::find($planData->plan_id);
                                                        $monthlyRevenue = $plan ? $planData->count * $plan->price : 0;
                                                    @endphp
                                                <tr>
                                                    <td>{{ $plan ? $plan->name : 'Unknown Plan' }}</td>
                                                    <td>{{ $planData->count }}</td>
                                                    <td>Tsh {{ number_format($monthlyRevenue, 0) }}/month</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted">No active subscriptions</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Expiring Subscriptions -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Expiring Soon (Next 30 Days)</h5>
                            </div>
                            <div class="card-body">
                                @if($expiringSubscriptions->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Duka</th>
                                                    <th>Plan</th>
                                                    <th>Expires</th>
                                                    <th>Days Left</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($expiringSubscriptions as $subscription)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('super-admin.dukas.show', $subscription->duka->id) }}">
                                                            {{ $subscription->duka->name }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $subscription->plan->name }}</td>
                                                    <td>{{ $subscription->end_date->format('M d, Y') }}</td>
                                                    <td>
                                                        <span class="badge bg-warning">
                                                            {{ \Carbon\Carbon::now()->diffInDays($subscription->end_date) }} days
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted">No subscriptions expiring soon</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expired Subscriptions -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Recently Expired Subscriptions</h5>
                            </div>
                            <div class="card-body">
                                @if($expiredSubscriptions->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Duka</th>
                                                    <th>Tenant</th>
                                                    <th>Plan</th>
                                                    <th>Expired</th>
                                                    <th>Days Since Expiry</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($expiredSubscriptions as $subscription)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('super-admin.dukas.show', $subscription->duka->id) }}">
                                                            {{ $subscription->duka->name }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $subscription->duka->tenant->user->name ?? 'N/A' }}</td>
                                                    <td>{{ $subscription->plan->name }}</td>
                                                    <td>{{ $subscription->end_date->format('M d, Y') }}</td>
                                                    <td>
                                                        <span class="badge bg-danger">
                                                            {{ $subscription->end_date->diffInDays(\Carbon\Carbon::now()) }} days ago
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted">No recently expired subscriptions</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');

    // Prepare data for chart
    const monthlyData = @json($monthlyRevenue);
    const labels = [];
    const data = [];

    // Generate last 12 months labels
    for (let i = 11; i >= 0; i--) {
        const date = new Date();
        date.setMonth(date.getMonth() - i);
        labels.push(date.toLocaleDateString('en-US', { year: 'numeric', month: 'short' }));
    }

    // Fill data array (defaulting to 0 if no data)
    labels.forEach(label => {
        const monthData = monthlyData.find(item => {
            const itemDate = new Date(item.year, item.month - 1);
            return itemDate.toLocaleDateString('en-US', { year: 'numeric', month: 'short' }) === label;
        });
        data.push(monthData ? parseFloat(monthData.total) : 0);
    });

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Monthly Revenue (Tsh)',
                data: data,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Tsh ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
