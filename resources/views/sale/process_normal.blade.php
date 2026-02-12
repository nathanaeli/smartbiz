@extends('layouts.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    :root {
        --sf-primary: #4361ee;
        /* The vibrant blue from the image */
        --sf-primary-soft: #eaecf5;
        --sf-bg: #f8f9fc;
        --sf-text-main: #1e293b;
        --sf-text-muted: #94a3b8;
        --sf-border: #e2e8f0;
        --sf-radius: 20px;
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: var(--sf-bg);
        color: var(--sf-text-main);
        overflow-x: hidden;
    }

    /* --- Custom Scrollbar for inner areas --- */
    ::-webkit-scrollbar {
        width: 6px;
    }

    ::-webkit-scrollbar-track {
        background: transparent;
    }

    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    /* --- 1. Top Header Section --- */
    .top-toolbar {
        background: white;
        border-radius: var(--sf-radius);
        padding: 1rem 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        margin-bottom: 2rem;
    }

    .toggle-switch {
        background: #f1f5f9;
        border-radius: 12px;
        padding: 4px;
        display: inline-flex;
        gap: 4px;
    }

    .toggle-btn {
        border: none;
        background: transparent;
        padding: 8px 24px;
        border-radius: 10px;
        font-weight: 600;
        color: var(--sf-text-muted);
        font-size: 0.9rem;
        transition: all 0.2s;
        text-decoration: none;
    }

    .toggle-btn.active {
        background: var(--sf-primary);
        color: white;
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
    }

    .category-scroll {
        display: flex;
        gap: 10px;
        margin-top: 1.5rem;
        overflow-x: auto;
        padding-bottom: 5px;
    }

    .cat-pill {
        padding: 8px 20px;
        border-radius: 100px;
        background: white;
        border: 1px solid var(--sf-border);
        color: var(--sf-text-muted);
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.2s;
    }

    .cat-pill:hover {
        background: #f8fafc;
        color: var(--sf-primary);
    }

    .cat-pill.active {
        background: var(--sf-primary);
        border-color: var(--sf-primary);
        color: white;
    }

    /* --- 2. Product Cards --- */
    .sf-card {
        background: white;
        border: 1px solid white;
        border-radius: var(--sf-radius);
        padding: 1.25rem;
        transition: all 0.2s ease;
        height: 100%;
        position: relative;
    }

    .sf-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
        border-color: #e2e8f0;
    }

    .badge-stock {
        position: absolute;
        top: 15px;
        left: 15px;
        background: #dcfce7;
        color: #166534;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 8px;
        z-index: 2;
    }

    .card-img-box {
        height: 160px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        /* Placeholder styling to match image */
        background: radial-gradient(circle, #f8fafc 0%, #ffffff 70%);
    }

    .card-img-box img {
        max-height: 140px;
        object-fit: contain;
        mix-blend-mode: multiply;
    }

    .item-title {
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 4px;
        color: var(--sf-text-main);
    }

    .item-price {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        font-weight: 500;
    }

    /* Action Areas */
    .action-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    /* The +/- Counter */
    .qty-counter {
        display: flex;
        align-items: center;
        background: white;
        border: 1px solid var(--sf-border);
        border-radius: 10px;
        padding: 4px;
    }

    .qty-btn {
        width: 32px;
        height: 32px;
        border: none;
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--sf-text-muted);
        font-size: 1.1rem;
        cursor: pointer;
    }

    .qty-btn:hover {
        color: var(--sf-primary);
        background: #f1f5f9;
        border-radius: 6px;
    }

    .qty-val {
        width: 30px;
        text-align: center;
        font-weight: 700;
        font-size: 0.9rem;
    }

    /* The Cart Icon Button */
    .btn-icon-cart {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        border: 1px solid var(--sf-border);
        background: white;
        color: var(--sf-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
    }

    .btn-icon-cart:hover {
        background: var(--sf-primary);
        color: white;
        border-color: var(--sf-primary);
    }

    /* The "Add Service" Button */
    .btn-service {
        width: 100%;
        background: #5c7cfa;
        /* Slightly lighter blue for services */
        color: white;
        border: none;
        padding: 10px;
        border-radius: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-service:hover {
        background: #4263eb;
    }

    /* --- 3. Right Cart Panel --- */
    .cart-panel {
        background: white;
        border-radius: var(--sf-radius);
        height: calc(100vh - 40px);
        display: flex;
        flex-direction: column;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    /* Distinct Blue Header */
    .cart-header {
        background: var(--sf-primary);
        padding: 1.5rem;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn-clear {
        border: 1px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 0.75rem;
    }

    .cart-body {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        background: #f8fafc;
    }

    /* Cart Items */
    .cart-item {
        background: white;
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        border: 1px solid #f1f5f9;
        position: relative;
    }

    .cart-item-title {
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 2px;
    }

    .cart-item-price {
        color: #64748b;
        font-size: 0.85rem;
    }

    /* Cart Footer */
    .cart-footer {
        background: white;
        padding: 1.5rem;
        border-top: 1px solid var(--sf-border);
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 0.9rem;
        color: #64748b;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        margin: 15px 0;
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--sf-text-main);
    }

    .btn-pay {
        background: var(--sf-primary);
        color: white;
        width: 100%;
        padding: 14px;
        border-radius: 12px;
        font-weight: 700;
        border: none;
        margin-bottom: 10px;
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
    }

    .btn-loan {
        background: white;
        border: 1px solid var(--sf-border);
        color: #64748b;
        width: 100%;
        padding: 14px;
        border-radius: 12px;
        font-weight: 600;
    }
</style>

<div class="container-fluid p-4">
    <div class="row g-4 h-100">
        <div class="col-xl-9 col-lg-8 d-flex flex-column h-100">

            <div class="top-toolbar">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-4">
                        <h4 class="fw-bold mb-0 text-dark"><i class="fas fa-layer-group text-primary me-2"></i> Products</h4>

                        <div class="toggle-switch">
                            <a href="#" class="toggle-btn active">Products</a>
                            <a href="#" class="toggle-btn">Services</a>
                        </div>
                    </div>

                    <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold small">Exit</a>
                </div>

                <div class="category-scroll">
                    <a href="#" class="cat-pill active" data-category-id="">All</a>
                    @foreach($categories as $category)
                    <a href="#" class="cat-pill" data-category-id="{{ $category->id }}">{{ $category->name }}</a>
                    @endforeach
                </div>

                <div class="mt-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="product-search" class="form-control border-start-0" placeholder="Search products by name, SKU or barcode...">
                    </div>
                </div>
            </div>

            <div class="row g-3 overflow-auto pb-4" style="flex: 1;" id="product-grid">
                @include('sale.partials.product-list', ['products' => $products, 'stocks' => $stocks, 'duka' => $duka])
            </div>
        </div>

        <div class="col-xl-3 col-lg-4">
            <div class="cart-panel">
                <div class="cart-header">
                    <h5 class="mb-0 fw-bold">Current Cart</h5>
                    <form action="{{ route('sale.clear_cart', $duka->id) }}" method="POST" id="clear-cart-form">
                        @csrf
                        <button type="submit" class="btn-clear text-white border-0 bg-transparent" style="cursor: pointer;"><i class="fas fa-trash-alt me-1"></i> Clear</button>
                    </form>
                </div>

                <div class="cart-body" id="cart-container">
                    @if(count($cart) > 0)
                    @include('sale.partials.cart-items', ['cart' => $cart, 'duka' => $duka])
                    @else
                    <div class="text-center py-5 mt-4">
                        <div class="mb-3 p-4 bg-white rounded-circle d-inline-flex shadow-sm text-primary-soft">
                            <i class="fas fa-shopping-cart fa-3x text-secondary opacity-25"></i>
                        </div>
                        <h6 class="fw-bold text-dark">Cart is empty</h6>
                        <p class="text-muted small">Select products or services to add</p>
                    </div>
                    @endif
                </div>

                <div class="cart-footer">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span class="fw-bold text-dark">Tsh {{ number_format($total) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Discount:</span>
                        <span class="fw-bold text-dark">Tsh 0</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax:</span>
                        <span class="fw-bold text-dark">Tsh 0</span>
                    </div>
                    <div class="total-row">
                        <span>TOTAL:</span>
                        <span>Tsh {{ number_format($total) }}</span>
                    </div>

                    <button class="btn-pay" data-bs-toggle="modal" data-bs-target="#checkoutModal" {{ $total > 0 ? '' : 'disabled' }}>
                        Pay Now
                    </button>
                    <button class="btn-loan">
                        Save as Loan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productGrid = document.getElementById('product-grid');
        const cartContainer = document.getElementById('cart-container');
        const searchInput = document.getElementById('product-search');
        const totalDisplays = document.querySelectorAll('.total-row span:last-child, .summary-row span:last-child.fw-bold');
        const checkoutBtn = document.querySelector('.btn-pay');

        // Function to update cart UI
        function updateCartUI(data) {
            cartContainer.innerHTML = data.html;
            totalDisplays.forEach(el => el.innerText = 'Tsh ' + data.total);
            if (checkoutBtn) checkoutBtn.disabled = data.cart_empty;
        }

        // Add to Cart / Update Qty (Event Delegation)
        document.addEventListener('submit', function(e) {
            if (e.target.classList.contains('add-to-cart-form') || e.target.id === 'clear-cart-form' || e.target.classList.contains('cart-remove-form')) {
                e.preventDefault();
                const form = e.target;
                const url = form.action;
                const formData = new FormData(form);

                fetch(url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            updateCartUI(data);
                        } else if (data.error) {
                            alert(data.error);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        });

        // Filtering & Search
        function filterProducts() {
            const categoryId = document.querySelector('.cat-pill.active').dataset.categoryId || '';
            const search = searchInput.value;
            const url = new URL(window.location.href);
            url.searchParams.set('category_id', categoryId);
            url.searchParams.set('search', search);

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    productGrid.innerHTML = html;
                })
                .catch(error => console.error('Error:', error));
        }

        // Category clicks
        document.querySelectorAll('.cat-pill').forEach(pill => {
            pill.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                filterProducts();
            });
        });

        // Search input
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(filterProducts, 500);
        });
    });

    // Qty counters helper (remain mostly for visual display in grid before add)
    function increment(btn) {
        let span = btn.parentElement.querySelector('.qty-val');
        let val = parseInt(span.innerText);
        span.innerText = val + 1;
    }

    function decrement(btn) {
        let span = btn.parentElement.querySelector('.qty-val');
        let val = parseInt(span.innerText);
        if (val > 1) span.innerText = val - 1;
    }
</script>
@endsection