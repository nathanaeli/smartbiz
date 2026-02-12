@extends('layouts.app')

@section('content')
<div class="pos-container">
    <style>
        :root {
            --pos-primary: #6366f1;
            --pos-primary-dark: #4f46e5;
            --pos-bg: #f3f4f6;
            --pos-card-bg: #ffffff;
            --pos-text: #1f2937;
            --pos-text-muted: #6b7280;
            --pos-border: #e5e7eb;
            --pos-accent: #10b981;
            --pos-danger: #ef4444;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .iq-navbar-header,
        .footer {
            display: none !important;
        }

        .main-content .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
        }

        .pos-container {
            display: flex;
            height: calc(100vh - 80px);
            background-color: var(--pos-bg);
            font-family: 'Inter', sans-serif;
            color: var(--pos-text);
            width: 100%;
        }

        .product-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border-right: 1px solid var(--pos-border);
        }

        .product-header {
            background: var(--pos-card-bg);
            padding: 1.5rem;
            border-bottom: 1px solid var(--pos-border);
            box-shadow: var(--shadow-sm);
            z-index: 10;
        }

        .search-wrapper {
            position: relative;
            margin-bottom: 1rem;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid var(--pos-border);
            border-radius: 0.75rem;
            outline: none;
        }

        .search-input:focus {
            border-color: var(--pos-primary);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--pos-text-muted);
        }

        .category-scroll {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
            scrollbar-width: none;
        }

        .category-btn {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            border: 1px solid var(--pos-border);
            background: var(--pos-card-bg);
            cursor: pointer;
            text-decoration: none;
            color: var(--pos-text-muted);
        }

        .category-btn.active {
            background: var(--pos-primary);
            color: white;
            border-color: var(--pos-primary);
        }

        .product-grid {
            padding: 1.5rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 1rem;
            overflow-y: auto;
        }

        .product-card {
            background: var(--pos-card-bg);
            border-radius: 1rem;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            cursor: pointer;
            border: 1px solid transparent;
            box-shadow: var(--shadow-sm);
            transition: all 0.2s;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--pos-primary);
        }

        .stock-badge {
            align-self: flex-end;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-weight: 600;
        }

        .stock-in {
            background: #d1fae5;
            color: #065f46;
        }

        .stock-low {
            background: #fee2e2;
            color: #991b1b;
        }

        .product-icon {
            width: 48px;
            height: 48px;
            background: var(--pos-bg);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--pos-text-muted);
            margin: 0 auto;
        }

        .price-tag {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--pos-primary);
            text-align: center;
        }

        .cart-area {
            width: 400px;
            background: var(--pos-card-bg);
            display: flex;
            flex-direction: column;
            box-shadow: -4px 0 15px rgba(0, 0, 0, 0.05);
            z-index: 20;
        }

        .cart-header {
            padding: 1.5rem;
            background: var(--pos-primary);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-items {
            flex: 1;
            overflow-y: auto;
            background: #f9fafb;
        }

        .cart-item {
            padding: 1rem;
            background: white;
            border-bottom: 1px solid var(--pos-border);
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .qty-control {
            display: flex;
            align-items: center;
            background: var(--pos-bg);
            border-radius: 0.5rem;
            padding: 0.25rem;
        }

        .qty-btn {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--pos-text);
            border: none;
            background: none;
        }

        .qty-btn:hover {
            background: #e5e7eb;
        }

        .checkout-panel {
            padding: 1.5rem;
            background: white;
            border-top: 1px solid var(--pos-border);
        }

        .btn-checkout {
            width: 100%;
            background: var(--pos-primary);
            color: white;
            padding: 1rem;
            border-radius: 0.75rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-checkout:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>

    <!-- Product Area -->
    <div class="product-area">
        <div class="product-header">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold m-0 text-dark">{{ __('sales.pos_title') }}</h4>
                    <small class="text-muted">{{ __('sales.command_center') }}</small>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill border border-success">
                        <i class="fas fa-wifi me-2"></i>{{ __('sales.online') }}
                    </span>
                    <button class="btn btn-light btn-sm rounded-pill px-3 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#importSalesModal">
                        <i class="fas fa-file-import me-2"></i> Import
                    </button>
                    <a href="{{ route('tenant.dashboard') }}" class="btn btn-light btn-sm rounded-pill px-3 d-flex align-items-center">
                        <i class="fas fa-arrow-left me-2"></i> {{ __('sales.exit') }}
                    </a>
                </div>
            </div>

            <form action="{{ route('sale.process', $duka->id) }}" method="GET" class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" class="search-input" value="{{ request('search') }}" placeholder="{{ __('sales.cart_help') }}" autofocus>
                @if(request('category_id'))
                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                @endif
            </form>

            <div class="category-scroll">
                <a href="{{ route('sale.process', ['dukaId' => $duka->id]) }}" class="category-btn {{ !request('category_id') ? 'active' : '' }}">
                    {{ __('sales.all_items') }}
                </a>
                @foreach($categories as $category)
                <a href="{{ route('sale.process', ['dukaId' => $duka->id, 'category_id' => $category->id]) }}"
                    class="category-btn {{ request('category_id') == $category->id ? 'active' : '' }}">
                    {{ $category->name }}
                </a>
                @endforeach
            </div>
        </div>

        <div class="product-grid">
            @if(session('success'))
            <div class="alert alert-success col-12">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger col-12">{{ session('error') }}</div>
            @endif

            @forelse($products as $product)
            @php
            $initialQty = $stocks[$product->id] ?? 0;
            $cartQty = isset($cart[$product->id]) ? $cart[$product->id]['quantity'] : 0;
            $remainingQty = max(0, $initialQty - $cartQty);
            @endphp
            <form action="{{ route('sale.add_to_cart', $duka->id) }}" method="POST" class="product-card-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="submit" class="product-card w-100 text-start" style="appearance: none; background: var(--pos-card-bg);">
                    <div class="stock-badge {{ $remainingQty > 5 ? 'stock-in' : 'stock-low' }}">
                        {{ $remainingQty }} {{ __('sales.stock_left') }}
                    </div>
                    <div class="product-icon"><i class="fas fa-box-open fa-2x"></i></div>
                    <div class="text-center flex-grow-1">
                        <h6 class="fw-bold text-dark mb-1 text-truncate">{{ $product->name }}</h6>
                        <small class="text-muted">{{ $product->sku ?? 'No SKU' }}</small>
                    </div>
                    <div class="price-tag">{{ number_format($product->selling_price) }}</div>
                </button>
            </form>
            @empty
            <div class="col-12 text-center p-5">
                <h5>No products found</h5>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Cart Area -->
    <div class="cart-area">
        <div class="cart-header">
            <h5 class="m-0 fw-bold"><i class="fas fa-shopping-cart me-2"></i>{{ __('sales.current_order') }}</h5>
            <form action="{{ route('sale.clear_cart', $duka->id) }}" method="POST" class="clear-cart-form">
                @csrf
                <button type="submit" class="btn btn-sm btn-light text-danger fw-bold rounded-pill px-3">{{ __('sales.clear_cart') }}</button>
            </form>
        </div>

        <div class="cart-items" id="cart-items-container">
            @include('sale.partials.cart-items')
        </div>

        <div class="checkout-panel">
            <div class="d-flex justify-content-between align-items-end mb-3">
                <div class="text-muted">{{ __('sales.total_payable') }}</div>
                <div class="h2 mb-0 fw-bolder text-dark"><span id="cart-total">{{ number_format($total) }}</span> <small class="fs-6 text-muted">TSH</small></div>
            </div>
            <button type="button" id="checkout-btn" class="btn-checkout shadow-lg" {{ empty($cart) ? 'disabled' : '' }} data-bs-toggle="modal" data-bs-target="#checkoutModal">
                <span>{{ __('sales.checkout') }}</span>
                <span><i class="fas fa-arrow-right"></i></span>
            </button>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('sale.checkout', $duka->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold">{{ __('sales.complete_sale') }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="text-center mb-4">
                            <h1 class="display-4 fw-bold text-primary mb-0"><span id="modal-total">{{ number_format($total) }}</span> <small class="fs-6 text-muted">TSH</small></h1>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('sales.customer_details') }}</label>
                            <select name="customer_id" class="form-select">
                                <option value="">{{ __('sales.walk_in_customer') }}</option>
                                @foreach(\App\Models\Customer::where('tenant_id', auth()->user()->tenant->id)->get() as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>



                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="isLoan" name="is_loan">
                            <label class="form-check-label" for="isLoan">{{ __('sales.is_loan') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold">{{ __('sales.confirm_payment') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importSalesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Import Sales</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="card border mb-3 shadow-none">
                        <div class="card-header bg-light py-2">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i> Instructions (Read First)</h6>
                        </div>
                        <div class="card-body bg-light-subtle small p-3">
                            <p class="mb-2">
                                <strong>How to Import Sales:</strong>
                                <a href="{{ route('sale.import_instructions') }}" class="float-end badge bg-info text-white text-decoration-none p-2">
                                    <i class="fas fa-file-pdf me-1"></i> Download PDF Guide
                                </a>
                            </p>
                            <ol class="ps-3 mb-0">
                                <li>Download the template below. It contains your current products.</li>
                                <li><strong>Quantity Sold:</strong> Enter the quantity for items you sold. Leave empty for others.</li>
                                <li><strong>Buying Price:</strong> Enter the COST price at that time. This creates an audit trail for accurate profit calculation.</li>
                                <li><strong>Sale Date:</strong> Format <code>YYYY-MM-DD</code>. Default is today.</li>
                                <li><strong>Customer:</strong> Fill Name/Phone to link sales to customers.</li>
                                <li>Save and Upload the file.</li>
                            </ol>
                        </div>
                    </div>
                    <p><a href="{{ route('sale.download_template', $duka->id) }}" class="btn btn-outline-primary w-100">Download Template</a></p>
                    <form action="{{ route('sale.import', $duka->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label>Upload Excel</label>
                            <input type="file" name="import_file" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Import</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cartContainer = document.getElementById('cart-items-container');
        const totalSpan = document.getElementById('cart-total');
        const checkoutBtn = document.getElementById('checkout-btn');

        function updateCartUI(data) {
            if (data.status === 'success') {
                cartContainer.innerHTML = data.html;
                totalSpan.innerText = data.total;
                const modalTotalSpan = document.getElementById('modal-total');
                if (modalTotalSpan) modalTotalSpan.innerText = data.total;

                if (checkoutBtn) checkoutBtn.disabled = data.cart_empty;

                // Simple Toast
                const toast = document.createElement('div');
                toast.className = 'position-fixed bottom-0 end-0 p-3';
                toast.style.zIndex = '1050';
                toast.innerHTML = '<div class="toast show bg-success text-white align-items-center"><div class="d-flex"><div class="toast-body">Cart Updated</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 2000);
            }
        }

        // Add to Cart
        document.querySelectorAll('.product-card-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                fetch(this.action, {
                        method: 'POST',
                        body: new FormData(this),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(updateCartUI)
                    .catch(err => console.error(err));
            });
        });

        // Delegate Remove/Clear
        document.body.addEventListener('submit', function(e) {
            if (e.target.matches('.cart-remove-form') || e.target.matches('.clear-cart-form')) {
                e.preventDefault();
                fetch(e.target.action, {
                        method: 'POST',
                        body: new FormData(e.target),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(updateCartUI)
                    .catch(err => console.error(err));
            }
        });
    });
</script>
@endsection
