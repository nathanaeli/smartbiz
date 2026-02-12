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
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-4">
                        <div class="toggle-switch">
                            @if($duka->supportsProducts())
                            <button class="toggle-btn active" id="btn-products">Products</button>
                            @endif
                            @if($duka->supportsServices())
                            <button class="toggle-btn" id="btn-services">Services</button>
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold small">Exit</a>
                </div>

                <div class="category-scroll" id="product-categories">
                    <a href="#" class="cat-pill active" data-category-id="">All Products</a>
                    @foreach($categories as $category)
                    <a href="#" class="cat-pill" data-category-id="{{ $category->id }}">{{ $category->name }}</a>
                    @endforeach
                </div>

                <div class="category-scroll d-none" id="service-categories">
                    <a href="#" class="cat-pill active service-cat" data-category-id="">All Services</a>
                    @foreach($serviceCategories as $cat)
                    <a href="#" class="cat-pill service-cat" data-category-id="{{ $cat->id }}">{{ $cat->name }}</a>
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

            <div class="row g-3 overflow-auto pb-4 d-none" style="flex: 1;" id="service-grid">
                @include('sale.partials.service-list', ['services' => $services, 'duka' => $duka])
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
                            <!-- Empty Cart SVG -->
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="opacity-25">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
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
<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <form action="{{ route('sale.checkout', $duka->id) }}" method="POST" id="checkout-form">
                @csrf
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold">Complete Sale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Total Display -->
                    <div class="bg-light p-4 rounded-4 text-center mb-4">
                        <p class="text-muted small fw-bold mb-1">TOTAL PAYABLE</p>
                        <h2 class="fw-bold text-primary mb-0" id="modal-total-display">Tsh {{ number_format($total) }}</h2>
                    </div>

                    <!-- Customer Selection -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Customer Details</label>
                        <select name="customer_id" class="form-select border-0 bg-light rounded-3 p-3">
                            <option value="">Walk-in Customer</option>
                            @foreach(\App\Models\Customer::where('tenant_id', auth()->user()->tenant->id)->get() as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Discount & Amount Tendered Row -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Discount Check (Tsh)</label>
                            <input type="number" name="discount_amount" id="discount-input" class="form-control border-0 bg-light rounded-3 p-3" placeholder="0" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Amount Tendered</label>
                            <input type="number" name="amount_tendered" id="tendered-input" class="form-control border-0 bg-light rounded-3 p-3" placeholder="0" min="0">
                        </div>
                    </div>

                    <!-- Live Calc Display -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold">Grand Total:</span>
                        <span class="fw-bold text-dark" id="grand-total-display">Tsh {{ number_format($total) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted small fw-bold">Change:</span>
                        <span class="fw-bold text-success" id="change-display">Tsh 0</span>
                    </div>

                    <!-- Loan Switch -->
                    <div class="form-check form-switch p-0 m-0 d-flex align-items-center justify-content-between">
                        <label class="form-check-label fw-bold text-muted small" for="isLoan">Mark as Outstanding (Loan)</label>
                        <input class="form-check-input ms-0" type="checkbox" id="isLoan" name="is_loan" style="width: 40px; height: 20px;">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="sf-checkout-btn sf-pay-btn mb-0 shadow-none border-0 w-100 py-3 rounded-3 fw-bold text-white" style="background: #3a57e8;">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cartContainer = document.getElementById('cart-container');
        const searchInput = document.getElementById('product-search');
        const checkoutBtn = document.querySelector('.btn-pay');

        // Products / Services Toggle Elements
        const btnProducts = document.getElementById('btn-products');
        const btnServices = document.getElementById('btn-services');
        const productCategories = document.getElementById('product-categories');
        const serviceCategories = document.getElementById('service-categories');
        const productGrid = document.getElementById('product-grid');
        const serviceGrid = document.getElementById('service-grid');

        // State
        let activeMode = 'product';

        function updateCartUI(data) {
            cartContainer.innerHTML = data.html;
            document.querySelectorAll('.total-row span:last-child').forEach(el => el.innerText = 'Tsh ' + data.total);
            document.querySelectorAll('.summary-row:first-child span:last-child').forEach(el => el.innerText = 'Tsh ' + data.total);

            if (checkoutBtn) checkoutBtn.disabled = data.cart_empty;
        }

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

                            // Update Stock Badge if productId is returned
                            if (data.productId !== null && data.newStock !== null) {
                                const stockBadge = document.querySelector(`.stock-badge-${data.productId}`);
                                const addBtn = document.querySelector(`.btn-add-${data.productId}`);

                                if (stockBadge) {
                                    stockBadge.innerText = (data.newStock <= 5 ? 'Low Stock: ' : 'In Stock: ') + data.newStock;

                                    // Update classes
                                    if (data.newStock <= 5) {
                                        stockBadge.classList.remove('bg-success');
                                        stockBadge.classList.add('bg-danger');
                                        stockBadge.closest('.sf-card').classList.add('low-stock-warning');
                                    } else {
                                        stockBadge.classList.remove('bg-danger');
                                        stockBadge.classList.add('bg-success');
                                        stockBadge.closest('.sf-card').classList.remove('low-stock-warning');
                                    }
                                }

                                if (addBtn) {
                                    if (data.newStock <= 0) {
                                        addBtn.disabled = true;
                                        addBtn.innerHTML = '<i class="fas fa-ban"></i> Out';
                                    } else {
                                        addBtn.disabled = false;
                                        addBtn.innerHTML = '<i class="fas fa-cart-plus"></i> Add';
                                    }
                                }
                            }

                        } else if (data.error) {
                            alert(data.error);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        });

        // Toggle Logic
        if (btnProducts) btnProducts.addEventListener('click', () => switchMode('product'));
        if (btnServices) btnServices.addEventListener('click', () => switchMode('service'));

        @if($duka -> supportsServices() && !$duka -> supportsProducts())
        switchMode('service');
        @else
        switchMode('product');
        @endif

        function switchMode(mode) {
            activeMode = mode;
            if (mode === 'product') {
                if (btnProducts) btnProducts.classList.add('active');
                if (btnServices) btnServices.classList.remove('active');

                if (productGrid) productGrid.classList.remove('d-none');
                if (serviceGrid) serviceGrid.classList.add('d-none');

                if (productCategories) productCategories.classList.remove('d-none');
                if (serviceCategories) serviceCategories.classList.add('d-none');

                if (searchInput) searchInput.placeholder = "Search products...";
            } else {
                if (btnServices) btnServices.classList.add('active');
                if (btnProducts) btnProducts.classList.remove('active');

                if (serviceGrid) serviceGrid.classList.remove('d-none');
                if (productGrid) productGrid.classList.add('d-none');

                if (serviceCategories) serviceCategories.classList.remove('d-none');
                if (productCategories) productCategories.classList.add('d-none');

                if (searchInput) searchInput.placeholder = "Search services...";
            }
            filterItems();
        }

        function filterItems() {
            const container = activeMode === 'product' ? productCategories : serviceCategories;
            const activePill = container.querySelector('.cat-pill.active');
            const categoryId = activePill ? activePill.dataset.categoryId : '';
            const search = searchInput.value;

            const url = new URL(window.location.href);
            url.searchParams.set('category_id', categoryId);
            url.searchParams.set('search', search);
            url.searchParams.set('type', activeMode);

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    if (activeMode === 'product') {
                        productGrid.innerHTML = html;
                    } else {
                        serviceGrid.innerHTML = html;
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Delegated Event for Category Pills
        document.addEventListener('click', function(e) {
            if (e.target.matches('.cat-pill')) {
                e.preventDefault();
                const container = e.target.closest('.category-scroll');
                if (container) {
                    container.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
                    e.target.classList.add('active');
                    filterItems();
                }
            }
        });

        // Search Input Listener
        let searchTimeout;
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(filterItems, 500);
            });
        }

        // Checkout Modal Calculations
        const checkoutModal = document.getElementById('checkoutModal');
        if (checkoutModal) {
            const modalTotalDisplay = document.getElementById('modal-total-display');
            const grandTotalDisplay = document.getElementById('grand-total-display');
            const changeDisplay = document.getElementById('change-display');
            const discountInput = document.getElementById('discount-input');
            const tenderedInput = document.getElementById('tendered-input');

            function updateCalculations() {
                // Get numeric value from text (remove 'Tsh ' and commas)
                let baseTotalText = modalTotalDisplay.innerText.replace(/[^0-9.-]+/g, "");
                let baseTotal = parseFloat(baseTotalText) || 0;

                const discount = parseFloat(discountInput.value) || 0;
                const tendered = parseFloat(tenderedInput.value) || 0;

                // Calc Grand Total
                let grandTotal = Math.max(0, baseTotal - discount);
                grandTotalDisplay.innerText = 'Tsh ' + grandTotal.toLocaleString();

                // Calc Change
                let change = Math.max(0, tendered - grandTotal);
                changeDisplay.innerText = 'Tsh ' + change.toLocaleString();

                // Visual feedback
                if (tendered > 0 && tendered < grandTotal) {
                    changeDisplay.classList.remove('text-success');
                    changeDisplay.classList.add('text-danger');
                } else {
                    changeDisplay.classList.remove('text-danger');
                    changeDisplay.classList.add('text-success');
                }
            }

            discountInput.addEventListener('input', updateCalculations);
            tenderedInput.addEventListener('input', updateCalculations);

            // Observer for when the modal total updates via AJAX
            const observer = new MutationObserver(updateCalculations);
            observer.observe(modalTotalDisplay, {
                childList: true,
                characterData: true,
                subtree: true
            });
        }
    });



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
