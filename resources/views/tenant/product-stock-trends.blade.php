@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="ri-line-chart-line me-2"></i>
                            Stock Trends - {{ $product->name }}
                        </h4>
                        <div>
                            <a href="{{ route('tenant.stock-trends.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="ri-arrow-left-line me-1"></i> Back to Trends
                            </a>
                            <a href="{{ route('tenant.product.manage', encrypt($product->id)) }}" class="btn btn-primary btn-sm">
                                <i class="ri-edit-line me-1"></i> Edit Product
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

                    <!-- Product Info -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>{{ $product->name }}</h5>
                                            <p class="text-muted mb-1">SKU: {{ $product->sku }}</p>
                                            <p class="text-muted mb-1">Duka: {{ $product->duka->name ?? 'N/A' }}</p>
                                            <p class="text-muted mb-0">Category: {{ $product->category->name ?? 'Uncategorized' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <h6 class="text-primary">{{ number_format($trends['current_stock']) }}</h6>
                                                    <small class="text-muted">Current Stock</small>
                                                </div>
                                                <div class="col-4">
                                                    <h6 class="text-info">{{ number_format($trends['total_movements']) }}</h6>
                                                    <small class="text-muted">Total Movements</small>
                                                </div>
                                                <div class="col-4">
                                                    <h6 class="text-warning">{{ number_format($trends['movement_frequency'], 1) }}</h6>
                                                    <small class="text-muted">Avg Daily</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="ri-money-dollar-circle-line text-primary" style="font-size: 2rem;"></i>
                                    <h6 class="mt-2">Stock Value</h6>
                                    <h4 class="text-primary">TZS {{ number_format($trends['current_stock'] * $product->base_price) }}</h4>
                                    <small class="text-muted">Buying Price: TZS {{ number_format($product->base_price) }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Level Chart -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-line-chart-line me-2"></i>
                                        Stock Level Over Time
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container" style="height: 350px;">
                                        <canvas id="stockLevelChart"></canvas>
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
                                        $additions = collect($trends['daily_data'])->where('movement_type', 'add')->sum('movement');
                                        $reductions = abs(collect($trends['daily_data'])->where('movement_type', 'reduce')->sum('movement'));
                                        $updates = collect($trends['daily_data'])->where('movement_type', 'update')->sum('movement');
                                    @endphp

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Additions:</span>
                                            <span class="badge bg-success">+{{ number_format($additions) }}</span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Reductions:</span>
                                            <span class="badge bg-danger">-{{ number_format($reductions) }}</span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Updates:</span>
                                            <span class="badge bg-info">{{ number_format(abs($updates)) }}</span>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <strong>Net Change:</strong>
                                        <strong class="{{ ($additions - $reductions) >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ ($additions - $reductions) >= 0 ? '+' : '' }}{{ number_format($additions - $reductions) }}
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Movements Table -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-history-line me-2"></i>
                                        Stock Movement History
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Type</th>
                                                    <th>Quantity Change</th>
                                                    <th>Stock Level</th>
                                                    <th>Batch Number</th>
                                                    <th>Reason</th>
                                                    <th>User</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($movements as $movement)
                                                    <tr>
                                                        <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
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
                                                        <td>{{ $movement->batch_number ?? 'N/A' }}</td>
                                                        <td>{{ $movement->reason ?? 'N/A' }}</td>
                                                        <td>{{ $movement->user->name ?? 'System' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted">
                                                            No stock movements found for this product.
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
    // Stock Level Chart
    const stockData = @json($trends['daily_data'] ?? []);
    const ctx = document.getElementById('stockLevelChart').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: stockData.map(d => d.date),
            datasets: [{
                label: 'Stock Level',
                data: stockData.map(d => d.stock_level),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Movement Amount',
                data: stockData.map(d => d.movement),
                type: 'bar',
                backgroundColor: stockData.map(d => d.movement > 0 ? 'rgba(34, 197, 94, 0.7)' : 'rgba(239, 68, 68, 0.7)'),
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
                    text: 'Stock Level and Movement Trends'
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Stock Level'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Movement Amount'
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
