<div>
    <!-- Appbar Trigger Button -->
    <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2 position-relative" data-bs-toggle="modal" data-bs-target="#unifiedCartModal">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="9" cy="21" r="1" />
            <circle cx="20" cy="21" r="1" />
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
        </svg>
        <span class="d-none d-md-inline">Quick Sale</span>
        @if(count($cart) > 0)
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            {{ count($cart) }}
        </span>
        @endif
    </button>

    <!-- Modal -->
    <div wire:ignore.self class="modal fade" id="unifiedCartModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        Unified Quick Sale
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-0">
                    <div class="row g-0">
                        <!-- Left Panel: Selection & Search -->
                        <div class="col-lg-7 p-4 bg-light border-end">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">SHOP / DUKA</label>
                                    <select wire:model.live="duka_id" class="form-select border-0 shadow-sm">
                                        <option value="">Select Duka</option>
                                        @foreach($dukas as $duka)
                                        <option value="{{ $duka->id }}">{{ $duka->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">CUSTOMER</label>
                                    <select wire:model.live="customer_id" class="form-select border-0 shadow-sm">
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="position-relative">
                                <div class="input-group input-group-lg shadow-sm border-0 mb-2">
                                    <span class="input-group-text bg-white border-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted">
                                            <circle cx="11" cy="11" r="8" />
                                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                        </svg>
                                    </span>
                                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-0 px-3" placeholder="Search Product or Service...">
                                </div>

                                @if(!empty($searchResults))
                                <div class="position-absolute w-100 mt-1 shadow-lg bg-white rounded-3 overflow-hidden z-index-100" style="z-index: 1050">
                                    @foreach($searchResults as $result)
                                    <div wire:click="addToCart('{{ $result['id'] }}', '{{ $result['type'] }}')" class="d-flex justify-content-between align-items-center p-3 border-bottom search-item cursor-pointer hover-bg-light" style="cursor: pointer">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle p-2 {{ $result['type'] === 'product' ? 'bg-soft-primary text-primary' : 'bg-soft-warning text-warning' }}">
                                                @if($result['type'] === 'product')
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                                                </svg>
                                                @else
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                                                </svg>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $result['name'] }}</div>
                                                <small class="text-muted text-uppercase">{{ $result['type'] }} @if($result['stock']) | Stock: {{ $result['stock'] }} @endif</small>
                                            </div>
                                        </div>
                                        <div class="fw-bold text-primary">{{ number_format($result['price']) }}</div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Right Panel: Cart Summary -->
                        <div class="col-lg-5 p-4 bg-white">
                            <h5 class="mb-4 d-flex justify-content-between align-items-center">
                                Cart
                                <span class="badge bg-soft-primary text-primary rounded-pill">{{ count($cart) }} Items</span>
                            </h5>

                            <div class="cart-items mb-4 overflow-auto" style="max-height: 400px;">
                                @forelse($cart as $key => $item)
                                <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="flex-grow-1">
                                            <div class="small fw-bold">{{ $item['name'] }}</div>
                                            <div class="d-flex align-items-center gap-2 mt-1">
                                                <button wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})" class="btn btn-xs btn-outline-secondary p-1">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <line x1="5" y1="12" x2="19" y2="12" />
                                                    </svg>
                                                </button>
                                                <span class="small mx-1">{{ $item['quantity'] }}</span>
                                                <button wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})" class="btn btn-xs btn-outline-secondary p-1">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <line x1="12" y1="5" x2="12" y2="19" />
                                                        <line x1="5" y1="12" x2="19" y2="12" />
                                                    </svg>
                                                </button>
                                                <small class="text-muted ms-2">x {{ number_format($item['price']) }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">{{ number_format($item['price'] * $item['quantity']) }}</div>
                                        <button wire:click="removeFromCart('{{ $key }}')" class="btn btn-link link-danger p-0 mt-1 small text-decoration-none">Remove</button>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-5 text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mb-3 opacity-25">
                                        <circle cx="9" cy="21" r="1" />
                                        <circle cx="20" cy="21" r="1" />
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                                    </svg>
                                    <p>Your cart is empty</p>
                                </div>
                                @endforelse
                            </div>

                            <div class="p-3 bg-soft-primary rounded-3 mb-4">
                                <div class="d-flex justify-content-between h4 fw-bold mb-0">
                                    <span>Total Amount</span>
                                    <span>{{ number_format($totalAmount) }}</span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">PAYMENT METHOD</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="radio" class="btn-check" wire:model="payment_method" name="payment_method" id="pay_cash" value="cash">
                                        <label class="btn btn-outline-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2" for="pay_cash">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="2" y="5" width="20" height="14" rx="2" />
                                                <line x1="2" y1="10" x2="22" y2="10" />
                                            </svg>
                                            Cash
                                        </label>
                                    </div>
                                    <div class="col-6">
                                        <input type="radio" class="btn-check" wire:model="payment_method" name="payment_method" id="pay_mobile" value="mobile">
                                        <label class="btn btn-outline-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2" for="pay_mobile">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M5 17h14v2H5zM5 13h14v2H5zM5 9h14v2H5zM5 5h14v2H5z" />
                                            </svg>
                                            Mobile
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">NOTES</label>
                                <textarea wire:model="notes" class="form-control border-0 shadow-sm" rows="2" placeholder="Internal notes..."></textarea>
                            </div>

                            <button wire:click="checkout" class="btn btn-primary w-100 py-3 fw-bold shadow-lg" {{ empty($cart) || !$customer_id ? 'disabled' : '' }}>
                                <span wire:loading.remove wire:target="checkout">COMPLETE TRANSACTION</span>
                                <span wire:loading wire:target="checkout">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    PROCESSING...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('sale-completed', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('unifiedCartModal'));
                if (modal) modal.hide();
            });
        });
    </script>
    <style>
        .hover-bg-light:hover {
            background-color: #f8f9fa;
            transition: background 0.2s;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .z-index-100 {
            z-index: 100;
        }

        .btn-xs {
            padding: 0.1rem 0.3rem;
            font-size: 0.75rem;
        }
    </style>
    @endpush
</div>