<div class="container-fluid">
    <!-- Product Selection -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Select Products</h5>
                <span class="badge bg-light text-dark">{{ $this->filteredProducts->total() }} Products Available</span>
            </div>
        </div>
        <div class="card-body">
            <!-- Search Bar -->
            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        </svg>
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="searchProduct" class="form-control border-start-0 ps-0"
                           placeholder="Search products by name or SKU..." style="border-radius: 0 8px 8px 0;">
                    @if(!empty($searchProduct))
                        <button wire:click="$set('searchProduct', '')" class="btn btn-outline-secondary">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Products Table -->
            @if($this->filteredProducts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-primary">
                            <tr>
                                <th style="width: 80px;">Image</th>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Stock</th>
                                <th>Price</th>
                                <th style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($this->filteredProducts as $product)
                                <tr>
                                    <!-- Product Image -->
                                    <td class="text-center">
                                        @if($product->image)
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;"
                                                 onerror="this.src='{{ asset('images/no-product.png') }}'">
                                        @else
                                            <div style="width: 50px; height: 50px; background: linear-gradient(45deg, #f8f9fa 25%, transparent 25%), linear-gradient(-45deg, #f8f9fa 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f8f9fa 75%), linear-gradient(-45deg, transparent 75%, #f8f9fa 75%); background-size: 10px 10px; border-radius: 6px; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center;">
                                                <svg width="24" height="24" fill="currentColor" class="text-muted" viewBox="0 0 24 24">
                                                    <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Product Name -->
                                    <td>
                                        <div class="fw-semibold">{{ $product->name }}</div>
                                        @if($product->description)
                                            <small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                        @endif
                                    </td>

                                    <!-- SKU -->
                                    <td><code class="small">{{ $product->sku }}</code></td>

                                    <!-- Category -->
                                    <td>
                                        @if($product->category)
                                            <span class="badge bg-light text-dark">{{ $product->category->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <!-- Stock -->
                                    <td>
                                        @php
                                            $stockQty = $product->stocks->first()->quantity ?? 0;
                                            $stockClass = $stockQty > 10 ? 'text-success' : ($stockQty > 0 ? 'text-warning' : 'text-danger');
                                        @endphp
                                        <span class="{{ $stockClass }} fw-semibold">{{ $stockQty }}</span>
                                        <small class="text-muted d-block">{{ $stockQty > 10 ? 'In Stock' : ($stockQty > 0 ? 'Low Stock' : 'Out of Stock') }}</small>
                                    </td>

                                    <!-- Price -->
                                    <td>
                                        <div class="fw-bold text-primary">{{ number_format($product->selling_price, 0) }} TSH</div>
                                        @if($product->base_price)
                                            <small class="text-muted">Cost: {{ number_format($product->base_price, 0) }} TSH</small>
                                        @endif
                                    </td>

                                    <!-- Action -->
                                    <td>
                                        @if($stockQty > 0)
                                            <button wire:click="addToCart({{ $product->id }})"
                                                    class="btn btn-sm btn-primary w-100">
                                                <svg class="me-1" width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M19 7h-3V6a3 3 0 0 0-3-3H5a3 3 0 0 0-3 3v14a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3V9a3 3 0 0 0-3-3zM5 6h9v1H5V6zm14 12H5v-1h14v1z"/>
                                                </svg>
                                                Add
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-secondary w-100" disabled>
                                                <svg class="me-1" width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                                </svg>
                                                Out
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $this->filteredProducts->links() }}
                </div>
            @else
                <!-- No Products Found -->
                <div class="text-center py-5">
                    <div style="width: 80px; height: 80px; background: linear-gradient(45deg, #4f46e5, #7c3aed); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);">
                        <svg width="40" height="40" fill="white" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <h5 class="text-muted mb-3">No Products Found</h5>
                    <p class="text-muted mb-4">
                        @if(!empty($searchProduct))
                            No products match your search "{{ $searchProduct }}". Try different keywords.
                        @else
                            No products are currently available in this store.
                        @endif
                    </p>
                    @if(!empty($searchProduct))
                        <button wire:click="$set('searchProduct', '')" class="btn btn-primary">
                            Clear Search
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Cart -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Cart</h5>
                </div>
                <div class="card-body">
                    @if(count($cart) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Unit Price</th>
                                        <th>Discount</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cart as $productId => $item)
                                        <tr>
                                            <td>{{ $item['product']->name }}</td>
                                            <td>
                                                <input type="number" wire:model.live="cart.{{ $productId }}.quantity" wire:change="updateQuantity({{ $productId }}, $event.target.value)" class="form-control form-control-sm" min="1" style="width: 80px;">
                                            </td>
                                            <td>{{ number_format($item['unit_price'], 2) }}</td>
                                            <td>
                                                <input type="number" wire:model.live="cart.{{ $productId }}.discount" wire:change="applyDiscount({{ $productId }}, $event.target.value)" class="form-control form-control-sm" min="0" step="0.01" style="width: 100px;">
                                            </td>
                                            <td>{{ number_format($item['total'], 2) }}</td>
                                            <td>
                                                <button wire:click="removeFromCart({{ $productId }})" class="btn btn-sm btn-danger">Remove</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 row">
                            <div class="col-md-6">
                                <strong>Total: {{ number_format($total, 2) }}</strong>
                            </div>
                            <div class="col-md-6">
                                <strong>Profit/Loss: {{ number_format($profitLoss, 2) }}</strong>
                            </div>
                        </div>
                    @else
                        <p class="text-muted">Cart is empty. Add products from above.</p>
                    @endif
                </div>
            </div>
        </div>

        @push('styles')
        <style>
        /* Custom scrollbar for better UX */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Table styling improvements */
        .table th {
            border-top: none;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .table td {
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(79, 70, 229, 0.05);
        }

        /* Image styling */
        .table img {
            transition: transform 0.2s ease;
        }

        .table img:hover {
            transform: scale(1.1);
        }
        </style>
        @endpush

        <!-- Customer and Sale Options -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Sale Details</h5>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label>Customer</label>
                        <input type="text" wire:model.live="customerInput" class="form-control" placeholder="Select customer..." list="customers">
                        <datalist id="customers">
                            @foreach($customers as $customer)
                                <option value="{{ $customer->name }} ({{ $customer->phone }})">
                            @endforeach
                        </datalist>
                    </div>
                    <div class="form-group mb-3">
                        <label>Discount Reason</label>
                        <textarea wire:model="discountReason" class="form-control" rows="3" placeholder="Reason for discount"></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input wire:model="isLoan" type="checkbox" class="form-check-input" id="isLoan">
                        <label class="form-check-label" for="isLoan">Is this a loan?</label>
                    </div>
                    @if($isLoan)
                    <div class="form-group mb-3">
                        <label for="dueDate">Due Date</label>
                        <input wire:model="dueDate" type="date" class="form-control" id="dueDate" min="{{ date('Y-m-d') }}">
                        <small class="text-muted">Set the repayment due date for this loan</small>
                    </div>
                    @endif
                    <button wire:click="completeSale" class="btn btn-success btn-lg w-100" :disabled="count($cart) === 0">Complete Sale</button>
                </div>
            </div>
        </div>
    </div>
</div>
