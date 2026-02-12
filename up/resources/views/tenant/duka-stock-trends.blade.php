@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="ri-store-2-line me-2"></i>
                            Stock Trends - {{ $duka->name }}
                        </h4>
                        <div>
                            <a href="{{ route('tenant.stock-trends.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="ri-arrow-left-line me-1"></i> Back to Trends
                            </a>
                            <a href="{{ route('duka.show', encrypt($duka->id)) }}" class="btn btn-primary btn-sm">
                                <i class="ri-eye-line me-1"></i> View Duka
                            </a>
                        </div>
                    </div>
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

                    <!-- Duka Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <h5>{{ $duka->name }}</h5>
                                            <p class="mb-1 opacity-75">{{ $duka->location ?? 'No location' }}</p>
                                            <p class="mb-0 opacity-75">Manager: {{ $duka->manager_name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="col-4 text-end">
                                            <i class="ri-store-2-line" style="font-size: 3rem; opacity: 0.5;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-4">
                                    <div class="card bg-info text-white text-center">
                                        <div class="card-body py-3">
                                            <h4>{{ number_format($trends['total_movements']) }}</h4>
                                            <small>Total Movements</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card bg-success text-white text-center">
                                        <div class="card-body py-3">
                                            <h4>{{ number_format($trends['active_products']) }}</h4>
                                            <small>Active Products</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card bg-warning text-white text-center">
                                        <div class="card-body py-3">
                                            <h4>{{ $duka->products->count() }}</h4>
                                            <small>Total Products</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daily Movement Trends -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-line-chart-line me-2"></i>
                                        Daily Movement Trends
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container" style="height: 300px;">
                                        <canvas id="dailyTrendsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-bar-chart-line me-2"></i>
                                        Movement Summary
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $totalAdditions = collect($trends['product_movements'])->sum('total_additions');
                                        $totalReductions = collect($trends['product_movements'])->sum('total_reductions');
                                        $netChange = $totalAdditions - $totalReductions;
                                    @endphp

                                    <div class="text-center mb-3">
                                        <div class="h3 {{ $netChange >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $netChange >= 0 ? '+' : '' }}{{ number_format($netChange) }}
                                        </div>
                                        <div class="text-muted">Net Stock Change</div>
                                    </div>

                                    <hr>

                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span>Total Additions:</span>
                                            <span class="badge bg-success">+{{ number_format($totalAdditions) }}</span>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span>Total Reductions:</span>
                                            <span class="badge bg-danger">-{{ number_format($totalReductions) }}</span>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span>Movement Frequency:</span>
                                            <span class="badge bg-info">{{ number_format($trends['total_movements']) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Performance -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-star-line me-2"></i>
                                        Product Performance
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Product Name</th>
                                                    <th>Total Additions</th>
                                                    <th>Total Reductions</th>
                                                    <th>Net Change</th>
                                                    <th>Movement Count</th>
                                                    <th>Activity Level</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($trends['product_movements'] as $product)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $product['name'] }}</strong>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success">
                                                                +{{ number_format($product['total_additions']) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-danger">
                                                                -{{ number_format($product['total_reductions']) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ $product['net_change'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $product['net_change'] >= 0 ? '+' : '' }}{{ number_format($product['net_change']) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-info">
                                                                {{ number_format($product['movement_count']) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @php
                                                                $totalActivity = $product['total_additions'] + $product['total_reductions'];
                                                            @endphp
                                                            @if($totalActivity > 100)
                                                                <span class="badge bg-danger">Very High</span>
                                                            @elseif($totalActivity > 50)
                                                                <span class="badge bg-warning">High</span>
                                                            @elseif($totalActivity > 20)
                                                                <span class="badge bg-info">Medium</span>
                                                            @else
                                                                <span class="badge bg-secondary">Low</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @php
                                                                // Find the product to get its ID
                                                                $productModel = $duka->products->where('name', $product['name'])->first();
                                                            @endphp
                                                            @if($productModel)
                                                                <a href="{{ route('tenant.stock-trends.product', encrypt($productModel->id)) }}"
                                                                   class="btn btn-sm btn-outline-primary">
                                                                    <i class="ri-line-chart-line"></i> Trends
                                                                </a>
                                                            @else
                                                                <button class="btn btn-sm btn-outline-secondary" disabled>
                                                                    N/A
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted">
                                                            No stock movement data available for this duka.
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

                    <!-- Recent Movements -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-history-line me-2"></i>
                                        Recent Stock Movements
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Date & Time</th>
                                                    <th>Product</th>
                                                    <th>Movement Type</th>
                                                    <th>Quantity Change</th>
                                                    <th>New Stock Level</th>
                                                    <th>Reason</th>
                                                    <th>User</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($movements->take(20) as $movement)
                                                    <tr>
                                                        <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                                        <td>
                                                            <strong>{{ $movement->stock->product->name ?? 'Unknown' }}</strong>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-{{ $movement->type === 'add' ? 'success' : ($movement->type === 'reduce' ? 'danger' : 'info') }}">
                                                                {{ ucfirst($movement->type) }}
                                                            </span>
                                                        </td>
                                                        <td class="{{ $movement->quantity_change > 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ $movement->quantity_change > 0 ? '+' : '' }}{{ number_format($movement->quantity_change) }}
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                {{ number_format($movement->new_quantity) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $movement->reason ?? 'N/A' }}</td>
                                                        <td>{{ $movement->user->name ?? 'System' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted">
                                                            No recent stock movements found for this duka.
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
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Daily Trends Chart
    const dailyData = @json($trends['daily_totals'] ?? []);
    const ctx = document.getElementById('dailyTrendsChart').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dailyData.map(d => d.date),
            datasets: [{
                label: 'Daily Movements',
                data: dailyData.map(d => d.total_movements),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                yAxisID: 'y'
            }, {
                label: 'Net Change',
                data: dailyData.map(d => d.net_change),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Daily Movement Activity'
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Number of Movements'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Net Stock Change'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
});
</script>
@endsection
