@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="ri-line-chart-line me-2"></i>
                        Stock Movement Trends
                    </h4>
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

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <i class="ri-arrow-up-circle-line" style="font-size: 2rem;"></i>
                                    <h3 class="mt-2">{{ number_format($trends['type_trends']['add'] ?? 0) }}</h3>
                                    <p class="mb-0">Total Additions</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <i class="ri-arrow-down-circle-line" style="font-size: 2rem;"></i>
                                    <h3 class="mt-2">{{ number_format($trends['type_trends']['reduce'] ?? 0) }}</h3>
                                    <p class="mb-0">Total Reductions</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <i class="ri-refresh-line" style="font-size: 2rem;"></i>
                                    <h3 class="mt-2">{{ number_format($trends['total_movements'] ?? 0) }}</h3>
                                    <p class="mb-0">Total Movements</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="ri-calendar-line" style="font-size: 2rem;"></i>
                                    <h3 class="mt-2">{{ $trends['period_days'] ?? 30 }}</h3>
                                    <p class="mb-0">Days Tracked</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Movement Chart -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-line-chart-line me-2"></i>
                                        Daily Stock Movement Trends
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container" style="height: 300px;">
                                        <canvas id="movementChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-pie-chart-line me-2"></i>
                                        Movement Types
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="typeChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Most Active Products -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-star-line me-2"></i>
                                        Most Active Products (Last 30 Days)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Product Name</th>
                                                    <th>Total Movement</th>
                                                    <th>Current Stock</th>
                                                    <th>Activity Level</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($trends['product_trends'] as $product)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $product['name'] }}</strong>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-info">{{ number_format($product['total_movement']) }} units</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ $product['current_stock'] > 10 ? 'bg-success' : 'bg-warning' }}">
                                                                {{ number_format($product['current_stock']) }} units
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @if($product['total_movement'] > 100)
                                                                <span class="badge bg-danger">Very High</span>
                                                            @elseif($product['total_movement'] > 50)
                                                                <span class="badge bg-warning">High</span>
                                                            @elseif($product['total_movement'] > 20)
                                                                <span class="badge bg-info">Medium</span>
                                                            @else
                                                                <span class="badge bg-secondary">Low</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('tenant.stock-trends.product', encrypt($product['product_id'] ?? 0)) }}"
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i class="ri-eye-line"></i> View Details
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">
                                                            No stock movement data available for the selected period.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Duka Performance -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-store-2-line me-2"></i>
                                        Duka Performance Overview
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @forelse($dukas as $duka)
                                            @php
                                                $dukaMovements = $movements->filter(function($movement) use ($duka) {
                                                    return $movement->stock->product->duka->id === $duka->id;
                                                });
                                                $totalMovements = $dukaMovements->count();
                                                $netChange = $dukaMovements->sum('quantity_change');
                                            @endphp

                                            <div class="col-md-4 mb-3">
                                                <div class="card border-primary">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <h6 class="card-title">{{ $duka->name }}</h6>
                                                                <p class="card-text text-muted small">{{ $duka->location ?? 'No location' }}</p>
                                                            </div>
                                                            <div class="text-end">
                                                                <span class="badge bg-primary">{{ $totalMovements }}</span>
                                                                <div class="small text-muted">movements</div>
                                                            </div>
                                                        </div>
                                                        <div class="mt-2">
                                                            <small class="text-muted">Net Change: </small>
                                                            <span class="badge {{ $netChange >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $netChange >= 0 ? '+' : '' }}{{ number_format($netChange) }}
                                                            </span>
                                                        </div>
                                                        <div class="mt-2">
                                                            <a href="{{ route('tenant.stock-trends.duka', encrypt($duka->id)) }}"
                                                               class="btn btn-sm btn-outline-primary w-100">
                                                                <i class="ri-line-chart-line me-1"></i> View Trends
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12">
                                                <div class="text-center py-4">
                                                    <i class="ri-store-2-line text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                                                    <h5 class="mt-3 text-muted">No dukas found</h5>
                                                    <p class="text-muted">Create dukas to see stock movement trends</p>
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Movement Trends Chart
    const movementData = @json($trends['daily_trends'] ?? []);
    const ctx1 = document.getElementById('movementChart').getContext('2d');

    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: movementData.map(d => d.date),
            datasets: [{
                label: 'Additions',
                data: movementData.map(d => d.additions),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4
            }, {
                label: 'Reductions',
                data: movementData.map(d => d.reductions),
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4
            }, {
                label: 'Net Change',
                data: movementData.map(d => d.net_change),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Daily Stock Movement Trends'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Movement Types Pie Chart
    const typeData = @json($trends['type_trends'] ?? []);
    const ctx2 = document.getElementById('typeChart').getContext('2d');

    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Additions', 'Reductions', 'Updates'],
            datasets: [{
                data: [
                    typeData['add'] || 0,
                    typeData['reduce'] || 0,
                    typeData['update'] || 0
                ],
                backgroundColor: [
                    'rgb(34, 197, 94)',
                    'rgb(239, 68, 68)',
                    'rgb(59, 130, 246)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
});
</script>
@endsection
