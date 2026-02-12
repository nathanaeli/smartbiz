@extends(auth()->user()->hasRole('officer') ? 'layouts.officer' : 'layouts.app')

@section('content')
<div class="container-fluid card">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('tenant.dashboard') }}">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ auth()->user()->hasRole('officer') ? route('officer.cashflow.index') : route('cash-flow.index') }}">Cash Flow Management</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                {{ $selectedDuka->name }} - {{ $selectedDuka->location }}
            </li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <!-- Selected Duka Header -->
            <div class="card mb-4 border-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">
                                <svg class="me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #0d6efd;">
                                    <path d="M3 7V5C3 3.89543 3.89543 3 5 3H7M17 3H19C20.1046 3 21 3.89543 21 5V7M21 17V19C21 20.1046 20.1046 21 19 21H17M7 21H5C3.89543 21 3 20.1046 3 19V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="9" cy="9" r="1" fill="currentColor"/>
                                    <circle cx="15" cy="15" r="1" fill="currentColor"/>
                                </svg>{{ $selectedDuka->name }}
                            </h4>
                            <p class="text-muted mb-0">{{ $selectedDuka->location }}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ auth()->user()->hasRole('officer') ? route('officer.cashflow.index') : route('cash-flow.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-exchange-alt me-1"></i>Change Duka
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="card-title text-success mb-1">Total Income</h6>
                                    <h4 class="mb-0">{{ number_format($totalIncome, 2) }} TSH</h4>
                                </div>
                                <div class="ms-3">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #198754;">
                                        <path d="M12 19V5m0 0l-7 7m7-7l7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-danger">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="card-title text-danger mb-1">Total Expenses</h6>
                                    <h4 class="mb-0">{{ number_format($totalExpense, 2) }} TSH</h4>
                                </div>
                                <div class="ms-3">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #dc3545;">
                                        <path d="M12 5v14m0 0l7-7m-7 7l-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-{{ $netCashFlow >= 0 ? 'success' : 'danger' }}">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="card-title text-{{ $netCashFlow >= 0 ? 'success' : 'danger' }} mb-1">Net Cash Flow</h6>
                                    <h4 class="mb-0">{{ number_format(abs($netCashFlow), 2) }} TSH</h4>
                                </div>
                                <div class="ms-3">
                                    @if($netCashFlow >= 0)
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #198754;">
                                            <path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    @else
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #dc3545;">
                                            <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Revenue Summary -->
            <div class="card mb-4 border-primary">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <svg class="me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #0d6efd;">
                            <path d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13v8a2 2 0 002 2h10a2 2 0 002-2v-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="9" cy="21" r="1" fill="currentColor"/>
                            <circle cx="20" cy="21" r="1" fill="currentColor"/>
                        </svg>Sales Revenue Summary ({{ $selectedDuka->name }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Sales Revenue (from Sales Table)</h6>
                                    <h4 class="text-success mb-0">{{ number_format($totalSalesRevenue, 2) }} TSH</h4>
                                    <small class="text-muted">Automatically calculated from all sales</small>
                                </div>
                                <div class="ms-3">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #198754;">
                                        <path d="M3 3v18h18M7 14l4-4 4 4 4-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Other Income (Manual Entries)</h6>
                                    <h4 class="text-info mb-0">{{ number_format($totalOtherIncome, 2) }} TSH</h4>
                                    <small class="text-muted">From cash flow entries (excluding sales)</small>
                                </div>
                                <div class="ms-3">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #0dcaf0;">
                                        <path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Sales revenue is automatically calculated from your sales transactions.
                        You can add additional income sources (like investments, services) using the "Add Entry" button above.
                    </div>
                </div>
            </div>

            <!-- Product Stock Summary -->
            <div class="card mb-4 border-info">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <svg class="me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #0dcaf0;">
                            <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>Product Stock Overview ({{ $selectedDuka->name }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Total Products</h6>
                                    <h4 class="text-info mb-0">{{ count($productStockData) }}</h4>
                                </div>
                                <div class="ms-3">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #0dcaf0;">
                                        <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Total Stock (Combined)</h6>
                                    <h4 class="text-success mb-0">{{ number_format($totalProductItems) }}</h4>
                                </div>
                                <div class="ms-3">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #198754;">
                                        <path d="M3 3v18h18M7 14l4-4 4 4 4-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Total Stock Value</h6>
                                    <h4 class="text-warning mb-0">{{ number_format($totalStockValue, 2) }} TSH</h4>
                                </div>
                                <div class="ms-3">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #ffc107;">
                                        <path d="M12 1v23M17 5H9.5C8.57174 5 7.6815 5.36875 7.02513 6.02513C6.36875 6.6815 6 7.57174 6 8.5C6 9.42826 6.36875 10.3185 7.02513 10.9749C7.6815 11.6313 8.57174 12 9.5 12H14.5C15.4283 12 16.3185 12.3687 16.9749 13.0251C17.6313 13.6815 18 14.5717 18 15.5C18 16.4283 17.6313 17.3185 16.9749 17.9749C16.3185 18.6313 15.4283 19 14.5 19H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Stock Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th>SKU</th>
                                    <th class="text-end">Stock</th>
                                    <th class="text-end">Base Price</th>
                                    <th class="text-end">Selling Price</th>
                                    <th class="text-end">Stock Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productStockData as $product)
                                    <tr>
                                        <td>{{ $product['name'] }}</td>
                                        <td>{{ $product['sku'] }}</td>
                                        <td class="text-end">
                                            <span class="badge bg-info">{{ number_format($product['combined_stock']) }}</span>
                                        </td>
                                        <td class="text-end">{{ number_format($product['base_price'], 2) }} TSH</td>
                                        <td class="text-end">{{ number_format($product['selling_price'], 2) }} TSH</td>
                                        <td class="text-end">
                                            <span class="text-success fw-bold">{{ number_format($product['total_cost'], 2) }} TSH</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-box-open fa-3x mb-3"></i>
                                                <p>No products found for this duka.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <svg class="me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 1v23M17 5H9.5C8.57174 5 7.6815 5.36875 7.02513 6.02513C6.36875 6.6815 6 7.57174 6 8.5C6 9.42826 6.36875 10.3185 7.02513 10.9749C7.6815 11.6313 8.57174 12 9.5 12H14.5C15.4283 12 16.3185 12.3687 16.9749 13.0251C17.6313 13.6815 18 14.5717 18 15.5C18 16.4283 17.6313 17.3185 16.9749 17.9749C16.3185 18.6313 15.4283 19 14.5 19H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>Cash Flow Entries
                    </h4>
                    <div class="d-flex gap-2">
                        <form action="{{ auth()->user()->hasRole('officer') ? route('officer.cashflow.generate-product-expenses') : route('cash-flow.generate-product-expenses') }}"
                              method="POST"
                              onsubmit="return confirm('This will create cash flow expense entries for all products based on their base prices. Continue?')">
                            @csrf
                            <input type="hidden" name="duka_id" value="{{ $selectedDuka->id }}">
                            <button type="submit" class="btn btn-warning btn-sm">
                                <svg class="me-1" width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Generate Product Expenses
                            </button>
                        </form>
                        <a href="{{ auth()->user()->hasRole('officer') ? route('officer.cashflow.create', ['duka_id' => $selectedDuka->id]) : route('cash-flow.create', ['duka_id' => $selectedDuka->id]) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Add Entry
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="row g-3 mb-4">
                        <input type="hidden" name="duka_id" value="{{ $selectedDuka->id }}">
                        <div class="col-md-3">
                            <label for="type" class="form-label">Type</label>
                            <select name="type" id="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Income</option>
                                <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <input type="text" name="category" id="category" class="form-control"
                                    value="{{ request('category') }}" placeholder="Search category">
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" name="date_from" id="date_from" class="form-control"
                                    value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" name="date_to" id="date_to" class="form-control"
                                    value="{{ request('date_to') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <svg class="me-1" width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>Filter
                            </button>
                            <a href="{{ auth()->user()->hasRole('officer') ? route('officer.cashflow.index', ['duka_id' => $selectedDuka->id]) : route('cash-flow.index', ['duka_id' => $selectedDuka->id]) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear Filters
                            </a>
                        </div>
                    </form>

                    <!-- Cash Flow Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Duka</th>
                                    <th class="text-end">Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cashFlows as $cashFlow)
                                    <tr>
                                        <td>{{ $cashFlow->transaction_date->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $cashFlow->type === 'income' ? 'success' : 'danger' }}">
                                                {{ ucfirst($cashFlow->type) }}
                                            </span>
                                        </td>
                                        <td>{{ $cashFlow->category }}</td>
                                        <td>{{ Str::limit($cashFlow->description, 50) }}</td>
                                        <td>{{ $cashFlow->duka->name }}</td>
                                        <td class="text-end">
                                            <span class="text-{{ $cashFlow->type === 'income' ? 'success' : 'danger' }}">
                                                {{ $cashFlow->type === 'income' ? '+' : '-' }}{{ number_format($cashFlow->amount, 2) }} TSH
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('cash-flow.show', $cashFlow) }}"
                                                   class="btn btn-sm btn-outline-info">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                                    </svg>
                                                </a>
                                                <a href="{{ route('cash-flow.edit', $cashFlow) }}"
                                                   class="btn btn-sm btn-outline-warning">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </a>
                                                <form action="{{ route('cash-flow.destroy', $cashFlow) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this entry?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                                <p>No cash flow entries found.</p>
                                                <a href="{{ auth()->user()->hasRole('officer') ? route('officer.cashflow.create', ['duka_id' => $selectedDuka->id]) : route('cash-flow.create', ['duka_id' => $selectedDuka->id]) }}" class="btn btn-primary">
                                                    <i class="fas fa-plus me-1"></i>Add First Entry
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($cashFlows->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $cashFlows->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
