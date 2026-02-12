@extends(auth()->user()->hasRole('officer') ? 'layouts.officer' : 'layouts.app')

@section('content')
<div class="container-fluid card">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('officer.dashboard') }}">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('cash-flow.index', ['duka_id' => $preselectedDukaId]) }}">Cash Flow</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Add Entry
            </li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-plus me-2"></i>Add Cash Flow Entry
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('cash-flow.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <!-- Type Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="type" id="income" value="income"
                                           {{ old('type', 'income') == 'income' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-success" for="income">
                                        <i class="fas fa-arrow-up me-1"></i>Income
                                    </label>

                                    <input type="radio" class="btn-check" name="type" id="expense" value="expense"
                                           {{ old('type') == 'expense' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-danger" for="expense">
                                        <i class="fas fa-arrow-down me-1"></i>Expense
                                    </label>
                                </div>
                                @error('type')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Duka Selection -->
                            @if($preselectedDukaId)
                                <!-- Hidden input for preselected duka -->
                                <input type="hidden" name="duka_id" value="{{ $preselectedDukaId }}">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Selected Duka</label>
                                    <div class="form-control bg-light p-2 rounded" style="border: 1px solid #dee2e6;">
                                        @php
                                            $selectedDuka = $availableDukas->find($preselectedDukaId);
                                        @endphp
                                        @if($selectedDuka)
                                            <strong>{{ $selectedDuka->name }}</strong> - {{ $selectedDuka->location }}
                                        @else
                                            Invalid Duka Selected
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="col-md-6 mb-3">
                                    <label for="duka_id" class="form-label">Duka <span class="text-danger">*</span></label>
                                    <select name="duka_id" id="duka_id" class="form-select @error('duka_id') is-invalid @enderror" required>
                                        <option value="">Select Duka</option>
                                        @foreach($availableDukas as $duka)
                                            <option value="{{ $duka->id }}" {{ old('duka_id') == $duka->id ? 'selected' : '' }}>
                                                {{ $duka->name }} - {{ $duka->location }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('duka_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                        </div>

                        <div class="row">
                            <!-- Category -->
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                                    <option value="">Select Category</option>
                                    @if(old('type', 'income') == 'income')
                                        @foreach($categories['income'] as $cat)
                                            <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>
                                                {{ $cat }}
                                            </option>
                                        @endforeach
                                    @else
                                        @foreach($categories['expense'] as $cat)
                                            <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>
                                                {{ $cat }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Amount Section -->
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Amount (TSH) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">TSH</span>
                                    <input type="number" step="0.01" min="0.01" name="amount" id="amount"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           value="{{ old('amount') }}" placeholder="0.00" required>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Product Stock Summary (shown for Purchase of Goods expense) -->
                        <div id="product-stock-section" class="card mb-3" style="display: none;">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-box me-2"></i>Product Stock Summary (Purchase Cost)
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    This shows the total purchase value based on product stock and product items.
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped" id="product-stock-table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>SKU</th>
                                                <th class="text-end">Stock Qty</th>
                                                <th class="text-end">Item Stock</th>
                                                <th class="text-end">Total Items</th>
                                                <th class="text-end">Base Price</th>
                                                <th class="text-end">Total Cost</th>
                                            </tr>
                                        </thead>
                                        <tbody id="product-stock-body">
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    <div class="spinner-border spinner-border-sm" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    Loading product stock data...
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary">
                                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                                <td class="text-end"><strong id="total-items">0</strong></td>
                                                <td></td>
                                                <td class="text-end"><strong id="total-cost">0.00 TSH</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="mt-3 text-end">
                                    <button type="button" class="btn btn-info" id="use-total-cost-btn">
                                        <i class="fas fa-mouse-pointer me-1"></i>Use Total Cost as Amount
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Product Sales Summary (shown for Income) -->
                        <div id="product-sales-section" class="card mb-3" style="display: none;">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Sales by Product (Income)
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-success mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    This shows sales revenue for each product. Use this to record income from sales.
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="date_from" class="form-label">From Date</label>
                                        <input type="date" name="date_from" id="date_from" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="date_to" class="form-label">To Date</label>
                                        <input type="date" name="date_to" id="date_to" class="form-control">
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped" id="product-sales-table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>SKU</th>
                                                <th class="text-end">Quantity Sold</th>
                                                <th class="text-end">Total Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody id="product-sales-body">
                                            <tr>
                                                <td colspan="4" class="text-center">
                                                    <div class="spinner-border spinner-border-sm" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    Loading sales data...
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-success">
                                                <td colspan="2" class="text-end"><strong>Total:</strong></td>
                                                <td class="text-end"><strong id="total-sold">0</strong></td>
                                                <td class="text-end"><strong id="total-revenue">0.00 TSH</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="mt-3 text-end">
                                    <button type="button" class="btn btn-success" id="use-total-revenue-btn">
                                        <i class="fas fa-mouse-pointer me-1"></i>Use Total Revenue as Amount
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction Date -->
                        <div class="mb-3">
                            <label for="transaction_date" class="form-label">Transaction Date <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" id="transaction_date"
                                   class="form-control @error('transaction_date') is-invalid @enderror"
                                   value="{{ old('transaction_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                            @error('transaction_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="3"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Enter description (optional)">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Reference Number -->
                        <div class="mb-3">
                            <label for="reference_number" class="form-label">Reference Number</label>
                            <input type="text" name="reference_number" id="reference_number"
                                   class="form-control @error('reference_number') is-invalid @enderror"
                                   value="{{ old('reference_number') }}" placeholder="Receipt/Invoice number (optional)">
                            @error('reference_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between pt-3 border-top">
                            <a href="{{ $preselectedDukaId ? route('cash-flow.index', ['duka_id' => $preselectedDukaId]) : route('cash-flow.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancel
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Entry
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category');
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const productStockSection = document.getElementById('product-stock-section');
    const productSalesSection = document.getElementById('product-sales-section');
    const preselectedDukaId = {{ $preselectedDukaId ? $preselectedDukaId : 'null' }};
    const dukaSelect = document.getElementById('duka_id');
    const amountInput = document.getElementById('amount');
    const useTotalCostBtn = document.getElementById('use-total-cost-btn');
    const useTotalRevenueBtn = document.getElementById('use-total-revenue-btn');
    const productStockBody = document.getElementById('product-stock-body');
    const productSalesBody = document.getElementById('product-sales-body');
    const totalItemsEl = document.getElementById('total-items');
    const totalCostEl = document.getElementById('total-cost');
    const totalSoldEl = document.getElementById('total-sold');
    const totalRevenueEl = document.getElementById('total-revenue');
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');

    let currentDukaId = preselectedDukaId;
    let productStockData = null;
    let productSalesData = null;

    // Update categories when type changes
    function updateCategories(type) {
        const categories = @json($categories);

        // Clear current options
        categorySelect.innerHTML = '<option value="">Select Category</option>';

        // Add new options
        categories[type].forEach(function(category) {
            const option = document.createElement('option');
            option.value = category;
            option.textContent = category;
            categorySelect.appendChild(option);
        });

        // Show/hide product sections based on type
        checkProductSections();
    }

    // Check if product sections should be shown
    function checkProductSections() {
        const selectedType = document.querySelector('input[name="type"]:checked').value;
        const selectedCategory = categorySelect.value;

        // Stock section for expense
        if (selectedType === 'expense' && selectedCategory === 'Purchase of Goods' && currentDukaId) {
            productStockSection.style.display = 'block';
            productSalesSection.style.display = 'none';
            fetchProductStock();
        } else {
            productStockSection.style.display = 'none';
        }

        // Sales section for income
        if (selectedType === 'income' && currentDukaId) {
            productSalesSection.style.display = 'block';
            productStockSection.style.display = 'none';
            fetchProductSales();
        } else {
            productSalesSection.style.display = 'none';
        }
    }

    // Fetch product stock data from API
    function fetchProductStock() {
        if (!currentDukaId) return;

        productStockBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    Loading product stock data...
                </td>
            </tr>
        `;

        fetch(`/api/cash-flow/product-stock?duka_id=${currentDukaId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    productStockData = data.products;
                    renderProductStockTable(data);
                } else {
                    productStockBody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center text-danger">
                                Error loading product data: ${data.error || 'Unknown error'}
                            </td>
                        </tr>
                    `;
                }
            })
            .catch(error => {
                productStockBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-danger">
                            Error loading product data: ${error.message}
                        </td>
                    </tr>
                `;
            });
    }

    // Render product stock table
    function renderProductStockTable(data) {
        let html = '';
        let totalItems = 0;
        let totalCost = 0;

        data.products.forEach(product => {
            html += `
                <tr>
                    <td>${product.name}</td>
                    <td><small class="text-muted">${product.sku}</small></td>
                    <td class="text-end">${product.stock_quantity}</td>
                    <td class="text-end">${product.product_items_stock_amount}</td>
                    <td class="text-end">${product.total_items}</td>
                    <td class="text-end">${parseFloat(product.base_price).toLocaleString()} TSH</td>
                    <td class="text-end"><strong>${parseFloat(product.total_cost).toLocaleString()} TSH</strong></td>
                </tr>
            `;
            totalItems += product.total_items;
            totalCost += product.total_cost;
        });

        if (data.products.length === 0) {
            html = `
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        No products found for this duka.
                    </td>
                </tr>
            `;
        }

        productStockBody.innerHTML = html;
        totalItemsEl.textContent = totalItems.toLocaleString();
        totalCostEl.textContent = totalCost.toLocaleString() + ' TSH';
    }

    // Fetch product sales data from API
    function fetchProductSales() {
        if (!currentDukaId) return;

        productSalesBody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    Loading sales data...
                </td>
            </tr>
        `;

        let url = `/api/cash-flow/product-sales?duka_id=${currentDukaId}`;
        if (dateFromInput.value) {
            url += `&date_from=${dateFromInput.value}`;
        }
        if (dateToInput.value) {
            url += `&date_to=${dateToInput.value}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    productSalesData = data.products;
                    renderProductSalesTable(data);
                } else {
                    productSalesBody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center text-danger">
                                Error loading sales data: ${data.error || 'Unknown error'}
                            </td>
                        </tr>
                    `;
                }
            })
            .catch(error => {
                productSalesBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-danger">
                            Error loading sales data: ${error.message}
                        </td>
                    </tr>
                `;
            });
    }

    // Render product sales table
    function renderProductSalesTable(data) {
        let html = '';
        let totalSold = 0;
        let totalRevenue = 0;

        data.products.forEach(product => {
            html += `
                <tr>
                    <td>${product.name}</td>
                    <td><small class="text-muted">${product.sku}</small></td>
                    <td class="text-end">${product.quantity_sold}</td>
                    <td class="text-end"><strong>${parseFloat(product.total_revenue).toLocaleString()} TSH</strong></td>
                </tr>
            `;
            totalSold += product.quantity_sold;
            totalRevenue += product.total_revenue;
        });

        if (data.products.length === 0) {
            html = `
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        No sales found for this duka.
                    </td>
                </tr>
            `;
        }

        productSalesBody.innerHTML = html;
        totalSoldEl.textContent = totalSold.toLocaleString();
        totalRevenueEl.textContent = totalRevenue.toLocaleString() + ' TSH';
    }

    // Handle type change
    typeRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            updateCategories(this.value);
        });
    });

    // Handle category change
    categorySelect.addEventListener('change', function() {
        checkProductSections();
    });

    // Handle duka selection change
    dukaSelect.addEventListener('change', function() {
        currentDukaId = this.value;
        checkProductSections();
    });

    // Handle date filter change
    dateFromInput.addEventListener('change', fetchProductSales);
    dateToInput.addEventListener('change', fetchProductSales);

    // Use total cost as amount button (expense)
    useTotalCostBtn.addEventListener('click', function() {
        if (productStockData) {
            const totalCost = productStockData.reduce((sum, product) => sum + product.total_cost, 0);
            amountInput.value = totalCost.toFixed(2);
        }
    });

    // Use total revenue as amount button (income)
    useTotalRevenueBtn.addEventListener('click', function() {
        if (productSalesData) {
            const totalRevenue = productSalesData.reduce((sum, product) => sum + product.total_revenue, 0);
            amountInput.value = totalRevenue.toFixed(2);
        }
    });

    // Initialize
    updateCategories(document.querySelector('input[name="type"]:checked').value);
});
</script>
@endsection
