@extends('layouts.app')

@section('content')
<div class="sf-wrapper">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        :root {
            --sf-primary: #10b981;
            /* Green for Services */
            --sf-primary-soft: #ecfdf5;
            --sf-bg: #f8f9fa;
            --sf-sidebar-bg: #ffffff;
            --sf-text: #232d42;
            --sf-text-muted: #8a92a6;
            --sf-border: #eeeeee;
            --sf-radius: 16px;
            --sf-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            --sf-font: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            font-family: var(--sf-font);
            background-color: var(--sf-bg);
            overflow: hidden;
        }

        .sf-wrapper {
            display: flex;
            height: 100vh;
            width: 100%;
            background: var(--sf-bg);
        }

        /* Sidebar same as products */
        .sf-sidebar {
            width: 260px;
            background: var(--sf-sidebar-bg);
            border-right: 1px solid var(--sf-border);
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
            transition: all 0.3s;
        }

        .sf-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 2.5rem;
            font-weight: 800;
            font-size: 1.25rem;
            color: #3a57e8;
            text-transform: uppercase;
        }

        .sf-nav-group {
            margin-bottom: 2rem;
        }

        .sf-nav-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--sf-text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1rem;
            padding-left: 0.5rem;
        }

        .sf-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.8rem 1rem;
            border-radius: 12px;
            text-decoration: none;
            color: var(--sf-text-muted);
            font-weight: 600;
            transition: all 0.2s;
            margin-bottom: 4px;
        }

        .sf-nav-link:hover {
            background: #ebf0fe;
            color: #3a57e8;
        }

        .sf-nav-link.active {
            background: #ebf0fe;
            color: #3a57e8;
        }

        .sf-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sf-header {
            height: 80px;
            background: white;
            border-bottom: 1px solid var(--sf-border);
            display: flex;
            align-items: center;
            padding: 0 2rem;
            justify-content: space-between;
        }

        .sf-search-bar {
            background: #f1f4f9;
            border-radius: 12px;
            display: flex;
            align-items: center;
            padding: 0 1rem;
            width: 400px;
        }

        .sf-search-bar input {
            border: none;
            background: transparent;
            padding: 0.8rem 0.5rem;
            width: 100%;
            outline: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .sf-process-header {
            background: white;
            border-bottom: 1px solid var(--sf-border);
            padding: 1.5rem 2rem;
        }

        .sf-mode-switch {
            background: #f1f4f9;
            padding: 4px;
            border-radius: 12px;
            display: inline-flex;
        }

        .sf-mode-btn {
            padding: 8px 16px;
            border-radius: 10px;
            border: none;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            text-decoration: none;
        }

        .sf-mode-btn.active {
            background: var(--sf-primary);
            color: white;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
        }

        .sf-mode-btn.inactive {
            background: transparent;
            color: var(--sf-text-muted);
        }

        .sf-categories {
            display: flex;
            gap: 12px;
            margin-top: 1.5rem;
            overflow-x: auto;
            padding-bottom: 5px;
            scrollbar-width: none;
        }

        .sf-chip {
            padding: 8px 16px;
            border-radius: 12px;
            background: #f1f4f9;
            border: 1px solid transparent;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--sf-text-muted);
            text-decoration: none;
            white-space: nowrap;
        }

        .sf-chip.active {
            background: var(--sf-primary-soft);
            color: var(--sf-primary);
            border-color: var(--sf-primary);
        }

        .sf-grid-container {
            flex: 1;
            display: flex;
            overflow: hidden;
        }

        .sf-grid {
            flex: 1;
            padding: 1.5rem 2rem;
            overflow-y: auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.5rem;
            align-content: flex-start;
        }

        /* Service Card */
        .sf-card {
            background: white;
            border-radius: var(--sf-radius);
            padding: 1.5rem;
            box-shadow: var(--sf-shadow);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .sf-card:hover {
            transform: translateY(-5px);
            border-color: var(--sf-primary);
        }

        .sf-card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: var(--sf-primary-soft);
            color: var(--sf-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 5px;
        }

        .sf-card-info h6 {
            font-weight: 700;
            color: var(--sf-text);
            margin-bottom: 2px;
        }

        .sf-card-info p {
            font-size: 0.75rem;
            color: var(--sf-text-muted);
            margin: 0;
        }

        .sf-card-price {
            font-weight: 800;
            color: var(--sf-primary);
            font-size: 1.1rem;
        }

        .sf-cart {
            width: 400px;
            background: white;
            border-left: 1px solid var(--sf-border);
            display: flex;
            flex-direction: column;
        }

        .sf-cart-header {
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--sf-border);
        }

        .sf-cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        .sf-cart-summary {
            padding: 1.5rem;
            background: #f8f9fa;
            border-top: 1px solid var(--sf-border);
            margin: 1rem;
            border-radius: var(--sf-radius);
        }

        .sf-cart-total {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--sf-text);
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px dashed var(--sf-border);
        }

        .sf-checkout-btn {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            font-weight: 800;
            border: none;
            margin-bottom: 10px;
        }

        .sf-pay-btn {
            background: var(--sf-primary);
            color: white;
        }

        .sf-loan-btn {
            background: white;
            border: 2px solid var(--sf-border);
            color: var(--sf-text-muted);
        }

        .sf-qty-control {
            display: flex;
            align-items: center;
            background: #f1f4f9;
            border-radius: 10px;
            padding: 4px;
            gap: 10px;
        }

        .sf-qty-btn {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            border: none;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            cursor: pointer;
        }

        #iq-sidebar-main,
        .iq-navbar-header,
        .footer {
            display: none !important;
        }

        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        .main-content .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
            max-width: 100% !important;
        }
    </style>

    <!-- Sidebar Left -->
    <aside class="sf-sidebar">
        <div class="sf-logo"><i class="fas fa-layer-group"></i> STOCKFLOWKP</div>
        <div class="sf-nav-group">
            <div class="sf-nav-title">Home</div>
            <a href="#" class="sf-nav-link"><i class="fas fa-home"></i> Landing Page</a>
            <a href="{{ route('tenant.dashboard') }}" class="sf-nav-link"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="#" class="sf-nav-link"><i class="fas fa-envelope"></i> Messages</a>
            <a href="#" class="sf-nav-link"><i class="fas fa-store"></i> Manage Duka</a>
        </div>
        <div class="sf-nav-group">
            <div class="sf-nav-title">Pages</div>
            <a href="#" class="sf-nav-link"><i class="fas fa-cog"></i> Setup</a>
            <a href="#" class="sf-nav-link active"><i class="fas fa-shopping-bag"></i> Sales History</a>
            <a href="#" class="sf-nav-link"><i class="fas fa-chart-line"></i> Aging Analysis</a>
            <a href="#" class="sf-nav-link"><i class="fas fa-wallet"></i> Cashflow</a>
            <a href="#" class="sf-nav-link active"><i class="fas fa-power-off"></i> Sales</a>
        </div>
    </aside>

    <!-- Main Section -->
    <main class="sf-main">
        <header class="sf-header">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('tenant.dashboard') }}" class="btn btn-light rounded-circle"><i class="fas fa-arrow-left text-primary"></i></a>
                <div class="sf-search-bar"><i class="fas fa-search text-muted"></i><input type="text" placeholder="Search services..."></div>
            </div>
            <div class="sf-header-actions">
                <button class="btn btn-outline-primary border-2 fw-bold px-4 rounded-3 d-none d-md-block">Quick Sale</button>
                <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill fw-bold">Free plan <span class="ms-2 opacity-75">11 Days Left</span></div>
                <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}&background=10b981&color=fff" class="rounded-circle shadow-sm" width="40" height="40">
            </div>
        </header>

        <div class="sf-process-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="fw-bold m-0"><i class="fas fa-tools text-primary me-2"></i> Services</h5>
                    <div class="sf-mode-switch">
                        <a href="{{ route('sale.process', $duka->id) }}" class="sf-mode-btn inactive">Products</a>
                        <a href="{{ route('sale.services', $duka->id) }}" class="sf-mode-btn active">Services</a>
                    </div>
                </div>
                <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary btn-sm px-4 rounded-pill fw-bold">Exit</a>
            </div>
            <div class="sf-categories">
                <a href="{{ route('sale.services', $duka->id) }}" class="sf-chip {{ !request('service_category_id') ? 'active' : '' }}">All Services</a>
                @foreach($serviceCategories as $cat)
                <a href="{{ route('sale.services', ['dukaId' => $duka->id, 'service_category_id' => $cat->id]) }}" class="sf-chip {{ request('service_category_id') == $cat->id ? 'active' : '' }}">{{ $cat->name }}</a>
                @endforeach
            </div>
        </div>

        <div class="sf-grid-container">
            <div class="sf-grid">
                @forelse($services as $service)
                <div class="sf-card">
                    <div class="sf-card-icon"><i class="fas fa-tools fa-lg"></i></div>
                    <div class="sf-card-info">
                        <h6>{{ $service->name }}</h6>
                        <p>{{ $service->category->name ?? 'General' }}</p>
                    </div>
                    <div class="sf-card-price">{{ number_format($service->price) }} TSH</div>
                    <form action="{{ route('sale.add_to_cart', $duka->id) }}" method="POST" class="add-to-cart-form">
                        @csrf
                        <input type="hidden" name="item_id" value="{{ $service->id }}">
                        <input type="hidden" name="type" value="service">
                        <button type="submit" class="sf-btn-add w-100 justify-content-center" style="background: var(--sf-primary);">
                            <i class="fas fa-plus"></i> Add Service
                        </button>
                    </form>
                </div>
                @empty
                <div class="col-12 text-center py-5 text-muted">No services found.</div>
                @endforelse
            </div>

            <aside class="sf-cart">
                <div class="sf-cart-header">
                    <h6 class="fw-bold mb-0">Current Cart</h6>
                    <form action="{{ route('sale.clear_cart', $duka->id) }}" method="POST" class="clear-cart-form">@csrf<button type="submit" class="btn btn-sm btn-link text-muted fw-bold">Clear</button></form>
                </div>
                <div class="sf-cart-items" id="cart-items-container">@include('sale.partials.cart-items')</div>
                <div class="p-3">
                    <div class="sf-cart-summary">
                        <div class="d-flex justify-content-between mb-2 small fw-bold"><span class="text-muted">Subtotal:</span><span>Tsh {{ number_format($total) }}</span></div>
                        <div class="sf-cart-total"><span>TOTAL:</span><span id="cart-total">Tsh {{ number_format($total) }}</span></div>
                    </div>
                    <button class="sf-checkout-btn sf-pay-btn" data-bs-toggle="modal" data-bs-target="#checkoutModal" {{ $total > 0 ? '' : 'disabled' }} id="checkout-btn">Pay Now</button>
                    <button class="sf-checkout-btn sf-loan-btn" {{ $total > 0 ? '' : 'disabled' }}>Save as Loan</button>
                </div>
            </aside>
        </div>
    </main>

    <!-- Checkout Modal same as products -->
    <div class="modal fade" id="checkoutModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <form action="{{ route('sale.checkout', $duka->id) }}" method="POST">
                    @csrf
                    <div class="modal-header border-0 p-4 pb-0">
                        <h5 class="modal-title fw-bold">Complete Sale</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="bg-light p-4 rounded-4 text-center mb-4">
                            <p class="text-muted small fw-bold mb-1">TOTAL PAYABLE</p>
                            <h2 class="fw-bold text-primary mb-0">Tsh {{ number_format($total) }}</h2>
                        </div>
                        <div class="mb-3"><label class="form-label small fw-bold text-muted">Customer Details</label><select name="customer_id" class="form-select border-0 bg-light rounded-3 p-3">
                                <option value="">Walk-in Customer</option>@foreach(\App\Models\Customer::where('tenant_id', auth()->user()->tenant->id)->get() as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                            </select></div>
                        <div class="form-check form-switch p-0 m-0 d-flex align-items-center justify-content-between"><label class="form-check-label fw-bold text-muted small" for="isLoan">Mark as Outstanding (Loan)</label><input class="form-check-input ms-0" type="checkbox" id="isLoan" name="is_loan" style="width: 40px; height: 20px;"></div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0"><button type="submit" class="sf-checkout-btn sf-pay-btn mb-0 shadow-none" style="background: #3a57e8;">Confirm Payment</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const updateUI = (data) => {
            if (data.status === 'success') {
                document.getElementById('cart-items-container').innerHTML = data.html;
                document.querySelectorAll('#cart-total').forEach(el => el.innerText = 'Tsh ' + data.total);
                const btn = document.getElementById('checkout-btn');
                if (btn) btn.disabled = data.cart_empty;
                window.location.reload();
            }
        };
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
                    .then(r => r.json()).then(updateUI).catch(console.error);
            }
        });
    });
</script>
@endsection