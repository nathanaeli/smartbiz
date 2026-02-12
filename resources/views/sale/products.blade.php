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
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .pos-container {
            display: flex;
            height: calc(100vh - 80px);
            background-color: var(--pos-bg);
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
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--pos-border);
        }

        .search-input {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 2.5rem;
            border: 2px solid var(--pos-border);
            border-radius: 0.5rem;
            outline: none;
        }

        .search-input:focus {
            border-color: var(--pos-primary);
        }

        .category-scroll {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding: 0.5rem 0;
            scrollbar-width: none;
        }

        .category-btn {
            padding: 0.4rem 1rem;
            border-radius: 999px;
            border: 1px solid var(--pos-border);
            background: var(--pos-card-bg);
            cursor: pointer;
            text-decoration: none;
            color: var(--pos-text-muted);
            white-space: nowrap;
            font-size: 0.9rem;
        }

        .category-btn.active {
            background: var(--pos-primary);
            color: white;
            border-color: var(--pos-primary);
        }

        .product-grid {
            padding: 1.5rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            overflow-y: auto;
        }

        .product-card {
            background: var(--pos-card-bg);
            border-radius: 0.75rem;
            padding: 1rem;
            border: 1px solid transparent;
            box-shadow: var(--shadow-sm);
            transition: all 0.2s;
            cursor: pointer;
            text-align: center;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: var(--pos-primary);
        }

        .stock-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 0.3rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 0.5rem;
        }

        .stock-in {
            background: #d1fae5;
            color: #065f46;
        }

        .stock-low {
            background: #fee2e2;
            color: #991b1b;
        }

        .cart-area {
            width: 380px;
            background: var(--pos-card-bg);
            display: flex;
            flex-direction: column;
            box-shadow: -4px 0 15px rgba(0, 0, 0, 0.05);
        }

        .cart-header {
            padding: 1rem 1.5rem;
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

        .checkout-panel {
            padding: 1.5rem;
            background: white;
            border-top: 1px solid var(--pos-border);
        }

        .btn-checkout {
            width: 100%;
            background: var(--pos-primary);
            color: white;
            padding: 0.8rem;
            border-radius: 0.5rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-checkout:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .nav-link-custom {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            color: var(--pos-text-muted);
            font-weight: 500;
        }

        .nav-link-custom.active {
            background: var(--pos-primary);
            color: white;
        }
    </style>

    <div class="product-area">
        <div class="product-header">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="fw-bold m-0 text-dark">Product Sales</h5>
                    <div class="bg-light p-1 rounded-3 d-flex gap-1">
                        <a href="{{ route('sale.products', $duka->id) }}" class="nav-link-custom active">Products</a>
                        <a href="{{ route('sale.services', $duka->id) }}" class="nav-link-custom">Services</a>
                    </div>
                </div>
                <div class="d-flex gap-2 text-end">
                    <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="fas fa-arrow-left"></i> Exit
                    </a>
                </div>
            </div>

            <form action="{{ route('sale.products', $duka->id) }}" method="GET" class="position-relative mb-2">
                <i class="fas fa-search position-absolute" style="left: 1rem; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
                <input type="text" name="search" class="search-input" value="{{ request('search') }}" placeholder="Search products by name or SKU..." autofocus>
                @if(request('category_id')) <input type="hidden" name="category_id" value="{{ request('category_id') }}"> @endif
            </form>

            <div class="category-scroll">
                <a href="{{ route('sale.products', $duka->id) }}" class="category-btn {{ !request('category_id') ? 'active' : '' }}">All Categories</a>
                @foreach($categories as $cat)
                <a href="{{ route('sale.products', ['dukaId' => $duka->id, 'category_id' => $cat->id]) }}" class="category-btn {{ request('category_id') == $cat->id ? 'active' : '' }}">{{ $cat->name }}</a>
                @endforeach
            </div>
        </div>

        <div class="product-grid">
            @forelse($products as $product)
            @php
            $initialQty = $stocks[$product->id] ?? 0;
            $cartQty = ($cart['p_'.$product->id]['quantity'] ?? 0);
            $rem = max(0, $initialQty - $cartQty);
            @endphp
            <form action="{{ route('sale.add_to_cart', $duka->id) }}" method="POST" class="add-to-cart-form">
                @csrf
                <input type="hidden" name="item_id" value="{{ $product->id }}">
                <input type="hidden" name="type" value="product">
                <button type="submit" class="product-card w-100" style="background: white; border: none; outline: none;">
                    <div class="stock-badge {{ $rem > 5 ? 'stock-in' : 'stock-low' }}">{{ $rem }} Left</div>
                    <div class="mb-2 text-muted"><i class="fas fa-box fa-2x"></i></div>
                    <h6 class="fw-bold text-dark mb-1 text-truncate">{{ $product->name }}</h6>
                    <div class="text-primary fw-bold">{{ number_format($product->selling_price) }}</div>
                </button>
            </form>
            @empty
            <div class="col-12 text-center py-5 text-muted">No products found in this category.</div>
            @endforelse
        </div>
    </div>

    <div class="cart-area">
        <div class="cart-header">
            <h6 class="m-0 fw-bold"><i class="fas fa-shopping-cart me-2"></i> Current Cart</h6>
            <form action="{{ route('sale.clear_cart', $duka->id) }}" method="POST" class="clear-cart-form">
                @csrf
                <button type="submit" class="btn btn-sm btn-light text-danger fw-bold rounded-pill">Clear</button>
            </form>
        </div>
        <div class="cart-items" id="cart-items-container">
            @include('sale.partials.cart-items')
        </div>
        <div class="checkout-panel">
            <div class="d-flex justify-content-between align-items-end mb-3">
                <div class="text-muted small">Total Payable</div>
                <div class="h4 mb-0 fw-bold text-dark"><span id="cart-total">{{ number_format($total) }}</span> <small class="fs-6 text-muted">TSH</small></div>
            </div>
            <button type="button" class="btn-checkout" id="checkout-btn" {{ $total > 0 ? '' : 'disabled' }} data-bs-toggle="modal" data-bs-target="#checkoutModal">
                <span>Checkout</span>
                <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>
</div>

<!-- Simple Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <form action="{{ route('sale.checkout', $duka->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white border-0">
                    <h6 class="modal-title fw-bold">Complete Transaction</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="text-muted small mb-1">Final Amount</div>
                    <h2 class="fw-bold text-primary mb-4">{{ number_format($total) }} TSH</h2>

                    <div class="text-start">
                        <label class="form-label small fw-bold">Select Customer</label>
                        <select name="customer_id" class="form-select mb-3">
                            <option value="">Walk-in Customer</option>
                            @foreach(\App\Models\Customer::where('tenant_id', auth()->user()->tenant->id)->get() as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="isLoan" name="is_loan">
                            <label class="form-check-label small" for="isLoan">Mark as Loan (Deferred Payment)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-sm btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Confirm Sale</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const updateUI = (data) => {
            if (data.status === 'success') {
                document.getElementById('cart-items-container').innerHTML = data.html;
                document.getElementById('cart-total').innerText = data.total;
                document.getElementById('checkout-btn').disabled = data.cart_empty;
                const mTotal = document.querySelector('#checkoutModal h2');
                if (mTotal) mTotal.innerText = data.total + ' TSH';
            }
        };

        // Add to cart hijack
        document.body.addEventListener('submit', function(e) {
            if (e.target.matches('.add-to-cart-form') || e.target.matches('.cart-remove-form') || e.target.matches('.clear-cart-form')) {
                e.preventDefault();
                fetch(e.target.action, {
                        method: 'POST',
                        body: new FormData(e.target),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(updateUI)
                    .catch(console.error);
            }
        });
    });
</script>
@endsection