<div class="pos-container">
    <style>
        :root {
            --pos-primary: #6366f1;
            /* Indigo 500 */
            --pos-primary-dark: #4f46e5;
            /* Indigo 600 */
            --pos-bg: #f3f4f6;
            /* Gray 100 */
            --pos-card-bg: #ffffff;
            --pos-text: #1f2937;
            /* Gray 800 */
            --pos-text-muted: #6b7280;
            /* Gray 500 */
            --pos-border: #e5e7eb;
            /* Gray 200 */
            --pos-accent: #10b981;
            /* Emerald 500 */
            --pos-danger: #ef4444;
            /* Red 500 */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
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
            /* Adjust for standard navbar height */
            background-color: var(--pos-bg);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--pos-text);
            /* Removed fixed positioning to show app layout */
            width: 100%;
        }

        /* Product Section */
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
            font-size: 1rem;
            transition: all 0.2s;
            outline: none;
        }

        .search-input:focus {
            border-color: var(--pos-primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
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

        .category-scroll::-webkit-scrollbar {
            display: none;
        }

        .category-btn {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
            border: 1px solid var(--pos-border);
            background: var(--pos-card-bg);
            color: var(--pos-text-muted);
            cursor: pointer;
            transition: all 0.2s;
        }

        .category-btn.active {
            background: var(--pos-primary);
            color: white;
            border-color: var(--pos-primary);
        }

        .category-btn:hover:not(.active) {
            background: var(--pos-bg);
            color: var(--pos-text);
        }

        /* Product Grid */
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
            transition: all 0.2s;
            border: 1px solid transparent;
            box-shadow: var(--shadow-sm);
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

        /* Cart Section */
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
            border-radius: 0.25rem;
        }

        .qty-btn:hover {
            background: #e5e7eb;
        }

        .checkout-panel {
            padding: 1.5rem;
            background: white;
            border-top: 1px solid var(--pos-border);
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .btn-checkout {
            width: 100%;
            background: var(--pos-primary);
            color: white;
            padding: 1rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1.125rem;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-checkout:hover {
            background: var(--pos-primary-dark);
        }

        .btn-checkout:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
                visibility: visible;
            }

            90% {
                opacity: 1;
                visibility: visible;
            }

            100% {
                opacity: 0;
                visibility: hidden;
            }
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
                    <a href="http://192.168.156.223:8000/sales/history" target="_blank" class="btn btn-light btn-sm rounded-pill px-3 d-flex align-items-center" title="Sales History / Invoice">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        Invoice
                    </a>
                    <button class="btn btn-light btn-sm rounded-pill px-3 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#importSalesModal">
                        <i class="fas fa-file-import me-2"></i> Import
                    </button>
                    <a href="{{ route('tenant.dashboard') }}" class="btn btn-light btn-sm rounded-pill px-3 d-flex align-items-center">
                        <i class="fas fa-arrow-left me-2"></i> {{ __('sales.exit') }}
                    </a>
                </div>
            </div>

            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text"
                    class="search-input"
                    wire:model.live.debounce.300ms="searchProduct"
                    placeholder="{{ __('sales.cart_help') }} (F2)"
                    autofocus>
            </div>

            <div class="category-scroll">
                <button wire:click="setCategory(null)"
                    class="category-btn {{ is_null($selectedCategoryId) ? 'active' : '' }}">
                    {{ __('sales.all_items') }}
                </button>
                @foreach($categories as $category)
                <button wire:click="setCategory({{ $category->id }})"
                    class="category-btn {{ $selectedCategoryId == $category->id ? 'active' : '' }}">
                    {{ $category->name }}
                </button>
                @endforeach
            </div>
        </div>

        <div class="product-grid">
            @forelse($products as $product)
            @php
            $stock = $product->stocks->first();
            $initialQty = $stock ? $stock->quantity : 0;
            $cartQty = isset($cart[$product->id]) ? $cart[$product->id]['quantity'] : 0;
            $remainingQty = max(0, $initialQty - $cartQty);
            @endphp
            <div class="product-card" wire:click="addToCart({{ $product->id }})">
                <div class="stock-badge {{ $remainingQty > 5 ? 'stock-in' : 'stock-low' }}">
                    {{ $remainingQty }} {{ __('sales.stock_left') }}
                </div>

                <div class="product-icon">
                    <i class="fas fa-box-open fa-2x"></i>
                </div>

                <div class="text-center flex-grow-1">
                    <h6 class="fw-bold text-dark mb-1 text-truncate" title="{{ $product->name }}">{{ $product->name }}</h6>
                    <small class="text-muted">{{ $product->sku ?? 'No SKU' }}</small>
                </div>

                <div class="price-tag">
                    {{ number_format($product->selling_price) }}
                </div>
            </div>
            @empty
            <div class="col-12 h-100 d-flex flex-column align-items-center justify-content-center text-muted" style="grid-column: 1/-1; min-height: 300px;">
                <i class="fas fa-search fa-4x mb-3 opacity-25"></i>
                <h5>No products found</h5>
                <p class="small">Try adjusting filters or search term</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Cart Area -->
    <div class="cart-area">
        <div class="cart-header">
            <h5 class="m-0 fw-bold"><i class="fas fa-shopping-cart me-2"></i>{{ __('sales.current_order') }}</h5>
            <button wire:click="clearCart" class="btn btn-sm btn-light text-danger fw-bold rounded-pill px-3">
                {{ __('sales.clear_cart') }}
            </button>
        </div>

        <div class="cart-items">
            @forelse($cart as $id => $item)
            <div class="cart-item">
                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark text-truncate mb-1">{{ $item['product']->name }}</h6>
                        <button class="btn btn-link text-danger p-0 ms-2 opacity-50 hover-opacity-100"
                            wire:click.stop="updateQuantity({{ $id }}, 0)"
                            title="Remove Item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>{{ number_format($item['unit_price']) }} x {{ $item['quantity'] }}</span>
                        <span class="fw-bold text-indigo">{{ number_format($item['quantity'] * $item['unit_price']) }}</span>
                    </div>
                </div>

                <div class="qty-control">
                    <div class="qty-btn" wire:click.stop="updateQuantity({{ $id }}, {{ $item['quantity'] - 1 }})">
                        <!-- SVG Minus -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </div>
                    <span class="fw-bold px-2 text-dark">{{ $item['quantity'] }}</span>
                    <div class="qty-btn" wire:click.stop="addToCart({{ $id }})">
                        <!-- SVG Plus -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </div>
                </div>
            </div>
            @empty
            <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-5">
                <div class="bg-gray-100 p-4 rounded-circle mb-3">
                    <i class="fas fa-basket-shopping fa-3x opacity-50"></i>
                </div>
                <h6 class="fw-bold">{{ __('sales.cart_empty') }}</h6>
                <p class="small text-center opacity-75">{{ __('sales.cart_help') }}</p>
            </div>
            @endforelse
        </div>

        <div class="checkout-panel">
            <!-- Options -->
            <div class="mb-3">
                <div class="d-flex gap-2 mb-2">
                    <div class="flex-grow-1">
                        <label class="small fw-bold text-muted text-uppercase mb-1">{{ __('sales.backdate') }}</label>
                        <input type="datetime-local" wire:model="backDate" class="form-control form-control-sm bg-light border-0">
                    </div>
                    <div class="flex-grow-1">
                        <label class="small fw-bold text-muted text-uppercase mb-1">{{ __('sales.discount') }}</label>
                        <input type="number" wire:model.live.debounce.500ms="discountAmount" class="form-control form-control-sm bg-light border-0" placeholder="0">
                    </div>
                </div>

                <div class="form-check form-switch bg-light p-2 rounded ps-5 border border-light">
                    <input class="form-check-input ms-n4" type="checkbox" id="loanToggle" wire:model.live="isLoan">
                    <label class="form-check-label fw-bold small text-uppercase cursor-pointer" for="loanToggle">{{ __('sales.is_loan') }}</label>
                </div>

                @if($isLoan)
                <!-- Loan indicator - customer selection moved to modal -->
                <div class="mt-2 p-2 bg-warning-subtle rounded border border-warning-subtle">
                    <div class="fw-bold small text-uppercase text-warning-emphasis mb-1">
                        <i class="fas fa-exclamation-triangle me-1"></i> {{ __('sales.loan_warning') }}
                    </div>
                    <small class="text-muted d-block mb-2">{{ __('sales.select_customer_checkout') }}</small>
                    <input type="date" wire:model="dueDate" class="form-control form-control-sm border-0 shadow-none">
                </div>
                @endif
            </div>

            <!-- Total & Pay -->
            <div class="d-flex justify-content-between align-items-end mb-3">
                <div class="text-muted">{{ __('sales.total_payable') }}</div>
                <div class="h2 mb-0 fw-bolder text-dark">{{ number_format($this->total) }} <small class="fs-6 text-muted">TSH</small></div>
            </div>

            <button type="button"
                class="btn-checkout shadow-lg"
                {{ empty($cart) ? 'disabled' : '' }}
                data-bs-toggle="modal"
                data-bs-target="#checkoutModal">
                <span>{{ __('sales.checkout') }} <span class="opacity-75 fs-6 fw-normal">(F9)</span></span>
                <span><i class="fas fa-arrow-right"></i></span>
            </button>
        </div>
    </div>
    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true" wire:ignore.self
        x-data="{
            total: @entangle('total').live
        }"
        x-on:sale-completed.window="$('#checkoutModal').modal('hide');">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="checkoutModalLabel">
                        <i class="fas fa-cash-register me-2"></i>{{ __('sales.complete_sale') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Total Display -->
                    <div class="text-center mb-4">
                        <p class="text-muted text-uppercase small fw-bold mb-1">{{ __('sales.total_amount_due') }}</p>
                        <h1 class="display-4 fw-bold text-primary mb-0">
                            <span x-text="new Intl.NumberFormat().format(total)"></span> <small class="fs-6 text-muted">TSH</small>
                        </h1>
                    </div>

                    <div class="row g-3">
                        <!-- Customer Details Section -->
                        <div class="col-12">
                            <div class="card bg-light border-0">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-user me-2"></i>{{ __('sales.customer_details') }}</h6>

                                    <div class="mb-3">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="newCustomerToggle" wire:model.live="createNewCustomer">
                                            <label class="form-check-label small fw-bold" for="newCustomerToggle">{{ __('sales.new_customer') }}</label>
                                        </div>

                                        @if($createNewCustomer)
                                        <div class="row g-2">
                                            <div class="col-7">
                                                <input type="text" wire:model="customerName" class="form-control form-control-sm" placeholder="{{ __('sales.customer_name') }}">
                                                @error('customerName') <span class="text-danger small">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-5">
                                                <input type="text" wire:model="customerPhone" class="form-control form-control-sm" placeholder="{{ __('sales.phone_optional') }}">
                                            </div>
                                        </div>
                                        @else
                                        <select wire:model="customerId" class="form-select form-select-sm">
                                            <option value="">{{ __('sales.walk_in_customer') }}</option>
                                            @foreach(\App\Models\Customer::where('tenant_id', auth()->user()->tenant->id)->get() as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                                            @endforeach
                                        </select>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>

                    <hr class="my-4">

                    <!-- Processing State (Livewire) -->
                    <div wire:loading wire:target="completeSale" class="w-100 text-center py-2">
                        <span class="spinner-border text-primary spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        {{ __('sales.processing_sale') }}
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                    <button type="button"
                        class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm"
                        wire:click="completeSale"
                        wire:loading.attr="disabled">
                        <i class="fas fa-check me-2"></i> {{ __('sales.confirm_payment') }}
                    </button>
                </div>
            </div>
            <!-- Sale Success Modal -->
            <div class="modal fade @if(session()->has('sale_status')) show @endif"
                id="saleSuccessModal"
                tabindex="-1"
                data-bs-backdrop="static"
                data-bs-keyboard="false"
                aria-hidden="{{ session()->has('sale_status') ? 'false' : 'true' }}"
                style="@if(session()->has('sale_status')) display: block; background-color: rgba(0,0,0,0.5); pointer-events: auto; animation: fadeOut 0.5s ease-out 3s forwards; @endif">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title fw-bold">
                                <i class="fas fa-check-circle me-2"></i>{{ __('sales.sale_success_title') }}
                            </h5>
                        </div>
                        <div class="modal-body p-4">
                            <div class="text-center mb-4">
                                <div class="avatar avatar-xl bg-success-subtle text-success rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                    <i class="fas fa-receipt fa-2x"></i>
                                </div>
                                <h4 class="fw-bold text-dark">{{ __('sales.transaction_complete') }}</h4>
                                <p class="text-muted small">{{ __('sales.receipt_no') }}{{ $lastSale?->id }} • {{ now()->format('d M, h:i A') }}</p>
                            </div>

                            @if($lastSale)
                            <div class="table-responsive bg-light rounded-3 p-3">
                                <table class="table table-sm table-borderless mb-0">
                                    <thead>
                                        <tr class="text-muted text-uppercase small">
                                            <th>{{ __('sales.item') }}</th>
                                            <th class="text-center">{{ __('sales.sold_qty') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($lastSale->saleItems as $item)
                                        <tr class="border-bottom border-light">
                                            <td class="py-2">
                                                <span class="fw-bold text-dark d-block">{{ $item->product->name }}</span>
                                                <span class="small text-muted">{{ number_format($item->unit_price) }} TSH</span>
                                            </td>
                                            <td class="py-2 text-center text-dark fw-bold">{{ $item->quantity }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="border-top border-2">
                                        <tr>
                                            <td class="pt-3 fw-bold text-dark">{{ __('sales.total_amount') }}</td>
                                            <td class="pt-3 text-end fw-bolder text-primary">{{ number_format($lastSale->total_amount) }} TSH</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            @endif
                        </div>
                        <div class="modal-footer border-0 bg-light">
                            <button type="button" class="btn btn-secondary px-4 rounded-pill" wire:click="closeSuccessModal" data-bs-dismiss="modal">
                                {{ __('sales.close_new_sale') }}
                            </button>
                            <!-- Small hack to ensure backdrop is gone if bootstrap messes up -->
                            <script>
                                // Helper to force cleanup if needed
                                function clearBackdrops() {
                                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                                    document.body.classList.remove('modal-open');
                                    document.body.style = '';
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="product-grid">
            @if (session()->has('sale_status'))
            <div class="col-12 mb-3">
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 d-flex align-items-center" role="alert">
                    <div class="bg-success text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <strong class="d-block">{{ __('sales.transaction_successful') }}</strong>
                        <span class="small">{{ session('sale_status') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            @endif

            @forelse($products as $product)
            @empty
            @endforelse
        </div>

        <!-- Import Sales Modal -->
        <div class="modal fade" id="importSalesModal" tabindex="-1" wire:ignore.self>
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold">
                            <i class="fas fa-file-excel me-2"></i>Import Sales (Excel)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <!-- Step 1: Download Template -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark mb-2">Step 1: Download Template</h6>
                            <p class="text-muted small mb-2">Download the Excel template to ensure your file has the correct columns (SKU, Quantity).</p>
                            <button type="button" wire:click="downloadTemplate" class="btn btn-outline-primary btn-sm rounded-pill">
                                <i class="fas fa-download me-2"></i> Download Template
                            </button>
                        </div>

                        <hr>

                        <!-- Step 2: Upload File -->
                        <div class="mb-2">
                            <h6 class="fw-bold text-dark mb-2">Step 2: Upload Excel File</h6>
                            <input type="file" wire:model="importFile" class="form-control" accept=".xlsx,.xls,.csv">
                            @error('importFile') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div wire:loading wire:target="importFile" class="text-info small mt-2">
                            <i class="fas fa-spinner fa-spin me-1"></i> Uploading file...
                        </div>

                        <div wire:loading wire:target="importSales" class="text-primary small mt-2 d-block">
                            <i class="fas fa-spinner fa-spin me-1"></i> Processing sales... Don't close window.
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                        <button type="button"
                            class="btn btn-primary px-4 rounded-pill fw-bold"
                            wire:click="importSales"
                            wire:loading.attr="disabled"
                            {{ !$importFile ? 'disabled' : '' }}>
                            <i class="fas fa-upload me-2"></i> Process Import
                        </button>
                    </div>
                </div>
            </div>
        </div>
