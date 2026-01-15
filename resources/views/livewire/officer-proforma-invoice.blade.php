<div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                            <path d="M9 7H15M9 11H15M9 15H15M5 7V19L7 21L9 19L11 21L13 19L15 21L17 19V7H5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ __('proforma.create_title') }}
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Smart Features Summary -->
                    <div class="alert alert-info border-0 mb-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <i class="fas fa-brain fa-2x text-info"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading mb-1">{{ __('proforma.smart_features_title') }}</h6>
                                <p class="mb-0 small">{{ __('proforma.smart_features_desc') }}</p>
                                <div class="mt-2">
                                    <span class="smart-badge smart me-1">
                                        <i class="fas fa-user me-1"></i>{{ __('proforma.badge_smart_customers') }}
                                    </span>
                                    <span class="smart-badge ai me-1">
                                        <i class="fas fa-cubes me-1"></i>{{ __('proforma.badge_stock_intelligence') }}
                                    </span>
                                    <span class="smart-badge auto me-1">
                                        <i class="fas fa-calculator me-1"></i>{{ __('proforma.badge_auto_calculations') }}
                                    </span>
                                    <span class="smart-badge smart me-1">
                                        <i class="fas fa-lightbulb me-1"></i>{{ __('proforma.badge_recommendations') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Proforma Invoice Form -->
                        <div class="col-lg-8">
                            <!-- Duka Selection -->
                            <div class="mb-3">
                                <label class="form-label">{{ __('proforma.select_duka') }}</label>
                                <select class="form-select" wire:model.live="selectedDukaId">
                                    @if(is_object($assignedDukas) && $assignedDukas->count() > 0)
                                        @foreach($assignedDukas as $assignment)
                                            <option value="{{ $assignment->duka_id }}">{{ $assignment->duka->name }}</option>
                                        @endforeach
                                    @else
                                        <option disabled>{{ __('proforma.no_dukas') }}</option>
                                    @endif
                                </select>
                            </div>

                            <!-- Smart Customer Selection -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user me-1 text-primary"></i>{{ __('proforma.select_customer') }}
                                    <small class="text-muted">(Smart suggestions with history)</small>
                                </label>
                                <input type="text" class="form-control" wire:model.live.debounce.300ms="customerSearch"
                                        placeholder="{{ __('proforma.customer_search_placeholder') }}" autocomplete="off">
                                @if($filteredCustomers && count($filteredCustomers) > 0)
                                    <div class="list-group mt-1 smart-suggestions" style="max-height: 250px; overflow-y: auto;">
                                        @foreach($filteredCustomers as $customer)
                                            <button type="button" class="list-group-item list-group-item-action customer-suggestion"
                                                    wire:click="selectCustomer({{ $customer->id }})">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <strong>{{ $customer->name }}</strong>
                                                        <br><small class="text-muted">{{ $customer->phone }} • {{ $customer->email }}</small>
                                                        @php
                                                            $customerInvoices = \App\Models\ProformaInvoice::where('customer_id', $customer->id)->count();
                                                            $lastPurchase = \App\Models\ProformaInvoice::where('customer_id', $customer->id)->latest()->first();
                                                        @endphp
                                                        @if($customerInvoices > 0)
                                                            <div class="mt-1">
                                                                <small class="text-info">
                                                                    <i class="fas fa-history me-1"></i>{{ $customerInvoices }} {{ __('proforma.previous_invoices') }}
                                                                    @if($lastPurchase)
                                                                        • {{ __('proforma.last') }}: {{ $lastPurchase->created_at->diffForHumans() }}
                                                                    @endif
                                                                </small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="ms-2">
                                                        @if($customerInvoices > 0)
                                                            <span class="badge bg-success">{{ __('proforma.returning') }}</span>
                                                        @else
                                                            <span class="badge bg-primary">{{ __('proforma.new') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                                @if($selectedCustomerId)
                                    @php
                                        $selectedCustomer = \App\Models\Customer::find($selectedCustomerId);
                                        $customerStats = \App\Models\ProformaInvoice::where('customer_id', $selectedCustomerId)
                                            ->selectRaw('COUNT(*) as total_invoices, SUM(total_amount) as total_spent, MAX(created_at) as last_purchase')
                                            ->first();
                                    @endphp
                                    <div class="mt-2">
                                        <div class="card bg-light">
                                            <div class="card-body py-2">
                                                <div class="row text-center">
                                                    <div class="col-4">
                                                        <small class="text-muted">{{ __('proforma.customer') }}</small>
                                                        <div class="h6 text-success mb-0">{{ $selectedCustomer->name }}</div>
                                                    </div>
                                                    <div class="col-4">
                                                        <small class="text-muted">{{ __('proforma.total_invoices') }}</small>
                                                        <div class="h6 text-primary mb-0">{{ $customerStats->total_invoices ?? 0 }}</div>
                                                    </div>
                                                    <div class="col-4">
                                                        <small class="text-muted">{{ __('proforma.total_spent') }}</small>
                                                        <div class="h6 text-info mb-0">{{ $currency }} {{ number_format($customerStats->total_spent ?? 0, 2) }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Valid Until Date -->
                            <div class="mb-3">
                                <label class="form-label">{{ __('proforma.valid_until') }}</label>
                                <input type="date" class="form-control" wire:model="validUntil"
                                        min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                <div class="form-text">{{ __('proforma.valid_until_help') }}</div>
                            </div>

                            <!-- Smart Product Search and Add -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-search me-1 text-success"></i>{{ __('proforma.add_products') }}
                                    <small class="text-muted">(Smart search with stock levels)</small>
                                </label>
                                <input type="text" class="form-control" wire:model.live.debounce.300ms="productSearch"
                                        placeholder="{{ __('proforma.products_search_placeholder') }}" autocomplete="off">
                                @if($filteredProducts && count($filteredProducts) > 0)
                                    <div class="list-group mt-1 smart-suggestions" style="max-height: 300px; overflow-y: auto;">
                                        @foreach($filteredProducts as $product)
                                            @php
                                                $totalStock = $product->stocks->sum('quantity');
                                                $stockStatus = $totalStock > 50 ? 'high' : ($totalStock > 10 ? 'medium' : ($totalStock > 0 ? 'low' : 'out'));
                                                $stockBadgeClass = $stockStatus === 'out' ? 'danger' : ($stockStatus === 'low' ? 'warning' : 'success');
                                                $isInCart = collect($cart)->contains('product_id', $product->id);
                                            @endphp
                                            <div class="list-group-item d-flex justify-content-between align-items-center product-suggestion {{ $isInCart ? 'bg-light' : '' }}">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <strong class="me-2">{{ $product->name }}</strong>
                                                                @if($isInCart)
                                                                    <span class="badge bg-info">{{ __('proforma.in_cart') }}</span>
                                                                @endif
                                                            </div>
                                                            <div class="text-muted small mb-1">
                                                                <span class="me-3">{{ $product->sku }}</span>
                                                                <span class="me-3">{{ $currency }} {{ number_format($product->selling_price, 2) }}</span>
                                                                @if($product->category)
                                                                    <span class="badge bg-light text-dark">{{ $product->category->name }}</span>
                                                                @endif
                                                            </div>
                                                            <div class="d-flex align-items-center">
                                                                <small class="text-{{ $stockBadgeClass }} me-2">
                                                                    <i class="fas fa-cubes me-1"></i>{{ __('proforma.stock') }}: {{ $totalStock }}
                                                                </small>
                                                                @if($totalStock <= 10 && $totalStock > 0)
                                                                    <small class="text-warning">
                                                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ __('proforma.low_stock') }}
                                                                    </small>
                                                                @elseif($totalStock == 0)
                                                                    <small class="text-danger">
                                                                        <i class="fas fa-times-circle me-1"></i>{{ __('proforma.out_of_stock') }}
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="text-end ms-3">
                                                            @if($totalStock > 0)
                                                                <button type="button" class="btn btn-sm btn-primary"
                                                                        wire:click="addToCart({{ $product->id }})"
                                                                        @if($isInCart) disabled @endif>
                                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-1">
                                                                        <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    </svg>
                                                                    {{ $isInCart ? __('proforma.added') : __('proforma.add_to_cart') }}
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-sm btn-secondary" disabled>
                                                                    <i class="fas fa-ban me-1"></i>{{ __('proforma.out_of_stock') }}
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Smart Product Recommendations -->
                                @if(empty($productSearch) && count($cart) > 0)
                                    <div class="mt-3">
                                        <h6 class="text-muted mb-2">
                                            <i class="fas fa-lightbulb me-1 text-warning"></i>{{ __('proforma.recommended_products') }}
                                        </h6>
                                        <div class="row">
                                            @php
                                                // Get products frequently bought together
                                                $cartProductIds = collect($cart)->pluck('product_id');
                                                $recommendedProducts = \App\Models\Product::where('tenant_id', auth()->user()->tenant_id)
                                                    ->whereNotIn('id', $cartProductIds)
                                                    ->with(['stocks' => function($q) use ($selectedDukaId) {
                                                        $q->where('duka_id', $selectedDukaId);
                                                    }])
                                                    ->take(3)
                                                    ->get();
                                            @endphp
                                            @foreach($recommendedProducts as $recProduct)
                                                @php $recStock = $recProduct->stocks->sum('quantity'); @endphp
                                                @if($recStock > 0)
                                                    <div class="col-md-4 mb-2">
                                                        <div class="card h-100 border-primary">
                                                            <div class="card-body p-2">
                                                                <small class="fw-bold">{{ $recProduct->name }}</small>
                                                                <br><small class="text-muted">{{ $currency }} {{ number_format($recProduct->selling_price, 2) }}</small>
                                                                <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-1"
                                                                        wire:click="addToCart({{ $recProduct->id }})">
                                                                    {{ __('proforma.add') }}
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Cart -->
                            @if(count($cart) > 0)
                                <div class="card border-warning">
                                    <div class="card-header bg-warning">
                                        <h6 class="mb-0">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                                                <path d="M3 3H5L5.4 5M7 13H17L21 5H5.4M7 13L5.4 5M7 13L4.707 15.293C4.077 15.923 4.523 17 5.414 17H17M17 17C15.895 17 15 17.895 15 19C15 20.105 15.895 21 17 21C18.105 21 19 20.105 19 19C19 17.895 18.105 17 17 17ZM9 19C9 20.105 8.105 21 7 21C5.895 21 5 20.105 5 19C5 17.895 5.895 17 7 17C8.105 17 9 17.895 9 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            {{ __('proforma.products_in_invoice') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('proforma.product') }}</th>
                                                        <th>{{ __('proforma.price') }}</th>
                                                        <th>{{ __('proforma.qty') }}</th>
                                                        <th>{{ __('proforma.total') }}</th>
                                                        <th>{{ __('proforma.action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($cart as $index => $item)
                                                        <tr>
                                                            <td>
                                                                <strong>{{ $item['name'] }}</strong>
                                                                <br><small class="text-muted">{{ $item['sku'] }}</small>
                                                            </td>
                                                            <td>{{ $currency }} {{ number_format($item['price'], 2) }}</td>
                                                            <td>
                                                                <input type="number" class="form-control form-control-sm"
                                                                        wire:model.live="cart.{{ $index }}.quantity"
                                                                        min="1"
                                                                        wire:change="updateCartQuantity({{ $index }}, $event.target.value)">
                                                            </td>
                                                            <td>{{ $currency }} {{ number_format($item['total'], 2) }}</td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-danger"
                                                                        wire:click="removeFromCart({{ $index }})">
                                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M3 6H5M5 6H21M5 6V20C5 20.5304 5.89543 21.0391 5.21071 21.4142C5.58579 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V6M8 6V4C8 3.46957 8.21071 3 8.58579 3C8.96086 2.78929 9.46957 2.5 10 2.5H14C14.5304 2.5 15.0391 2.78929 15.4142 3C15.7893 3.21071 16 3.46957 16 4V6M10 11V17M14 11V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    </svg>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Smart Discount and Notes -->
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <label class="form-label">
                                                    <i class="fas fa-percent me-1 text-warning"></i>{{ __('proforma.discount') }} ({{ $currency }})
                                                    <small class="text-muted">(Smart recommendations)</small>
                                                </label>
                                                <input type="number" class="form-control" wire:model.live="discount" min="0" step="0.01">
                                                @if($subtotal > 0)
                                                    <div class="mt-2">
                                                        <small class="text-muted">{{ __('proforma.suggested_discounts') }}</small>
                                                        <div class="btn-group btn-group-sm d-block mt-1" role="group">
                                                            @php
                                                                $discount5 = $subtotal * 0.05;
                                                                $discount10 = $subtotal * 0.10;
                                                                $discount15 = $subtotal * 0.15;
                                                            @endphp
                                                            <button type="button" class="btn btn-outline-primary btn-sm"
                                                                    onclick="setDiscount({{ $discount5 }})">5% ({{ $currency }} {{ number_format($discount5, 2) }})</button>
                                                            <button type="button" class="btn btn-outline-success btn-sm"
                                                                    onclick="setDiscount({{ $discount10 }})">10% ({{ $currency }} {{ number_format($discount10, 2) }})</button>
                                                            <button type="button" class="btn btn-outline-warning btn-sm"
                                                                    onclick="setDiscount({{ $discount15 }})">15% ({{ $currency }} {{ number_format($discount15, 2) }})</button>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">
                                                    <i class="fas fa-sticky-note me-1 text-info"></i>{{ __('proforma.notes') }}
                                                    <small class="text-muted">(Auto-generated suggestions)</small>
                                                </label>
                                                <textarea class="form-control" wire:model="notes" rows="2" placeholder="{{ __('proforma.notes_placeholder') }}"></textarea>
                                                @if($selectedCustomerId && count($cart) > 0)
                                                    <div class="mt-2">
                                                        <small class="text-muted">{{ __('proforma.quick_notes') }}</small>
                                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                                    onclick="setNote('{{ __('proforma.payment_30_days') }}')">{{ __('proforma.payment_30_days') }}</button>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                                    onclick="setNote('{{ __('proforma.valid_15_days') }}')">{{ __('proforma.valid_15_days') }}</button>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                                    onclick="setNote('{{ __('proforma.subject_to_stock') }}')">{{ __('proforma.subject_to_stock') }}</button>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Invoice Summary -->
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="card-title mb-0">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                                            <path d="M9 7H15M9 11H15M9 15H15M5 7V19L7 21L9 19L11 21L13 19L15 21L17 19V7H5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        {{ __('proforma.invoice_summary') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>{{ __('proforma.subtotal') }}:</span>
                                        <strong>{{ $currency }} {{ number_format($subtotal, 2) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>{{ __('proforma.tax') }}:</span>
                                        <strong>{{ $currency }} {{ number_format($taxAmount, 2) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>{{ __('proforma.discount') }}:</span>
                                        <strong>{{ $currency }} {{ number_format($discount, 2) }}</strong>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="h5">{{ __('proforma.total') }}:</span>
                                        <strong class="h5 text-success">{{ $currency }} {{ number_format($total, 2) }}</strong>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between text-muted">
                                            <small>{{ __('proforma.items_in_invoice') }}</small>
                                            <small>{{ count($cart) }}</small>
                                        </div>
                                        @if($selectedCustomerId)
                                            <div class="d-flex justify-content-between text-muted">
                                                <small>{{ __('proforma.customer') }}:</small>
                                                <small>{{ \App\Models\Customer::find($selectedCustomerId)->name }}</small>
                                            </div>
                                        @endif
                                        <div class="d-flex justify-content-between text-muted">
                                            <small>{{ __('proforma.valid_until') }}:</small>
                                            <small>{{ \Carbon\Carbon::parse($validUntil)->format('d/m/Y') }}</small>
                                        </div>

                                        <!-- Smart Invoice Insights -->
                                        @if(count($cart) > 0)
                                            <hr class="my-2">
                                            <div class="smart-insights">
                                                @php
                                                    $avgItemPrice = collect($cart)->avg('price');
                                                    $totalItems = collect($cart)->sum('quantity');
                                                    $uniqueProducts = count($cart);
                                                @endphp
                                                <div class="d-flex justify-content-between text-info small mb-1">
                                                    <span>{{ __('proforma.average_item_price') }}</span>
                                                    <span>{{ $currency }} {{ number_format($avgItemPrice, 2) }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between text-info small mb-1">
                                                    <span>{{ __('proforma.total_items') }}</span>
                                                    <span>{{ $totalItems }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between text-info small">
                                                    <span>{{ __('proforma.unique_products') }}</span>
                                                    <span>{{ $uniqueProducts }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="d-flex gap-2 flex-column">
                                        <button type="button" class="btn btn-primary"
                                                wire:click="generateProformaInvoicePreview"
                                                @if(count($cart) == 0 || !$selectedCustomerId || $generatingPreview) disabled @endif>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                                                <path d="M17 17H7V7H17V17ZM17 3H5C3.89 3 3 3.89 3 5V19C3 20.11 3.89 21 5 21H19C20.11 21 21 20.11 21 19V7L17 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            {{ __('proforma.create_print') }}
                                        </button>

                                        <button type="button" class="btn btn-info"
                                                wire:click="sendEmail"
                                                @if(count($cart) == 0 || !$selectedCustomerId || $sendingEmail) disabled @endif>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                                                <path d="M3 8L10.89 4.26C11.2187 4.09253 11.5858 4.00783 11.9554 4.01423C12.325 4.02063 12.6884 4.11799 13.01 4.296L21 8M3 8L3 16C3 16.5304 3.21071 17.0391 3.58579 17.4142C3.96086 17.7893 4.46957 18 5 18H19C19.5304 18 20.0391 17.7893 20.4142 17.4142C20.7893 17.0391 21 16.5304 21 16V8M3 8L10.5 12.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            {{ __('proforma.send_email') }}
                                        </button>

                                        <button type="button" class="btn btn-success"
                                                wire:click="createProformaInvoice"
                                                @if(count($cart) == 0 || !$selectedCustomerId || $creatingInvoice) disabled @endif>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                                                <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            {{ __('proforma.create_save') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success mt-3">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                <path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.7089 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger mt-3">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                <path d="M10.29 3.86L1.82 18C1.64662 18.3024 1.55299 18.6453 1.54776 18.9942C1.54253 19.3431 1.62596 19.6878 1.79004 20.0048C1.95412 20.3218 2.19375 20.6004 2.4864 20.816C2.77905 21.0316 3.11512 21.177 3.467 21.24H20.533C20.8849 21.177 21.221 21.0316 21.5136 20.816C21.8063 20.6004 22.0459 20.3218 22.21 20.0048C22.374 19.6878 22.4575 19.3431 22.4522 18.9942C22.447 18.6453 22.3534 18.3024 22.18 18L13.71 3.86C13.5317 3.56611 13.2807 3.32312 12.9812 3.15447C12.6817 2.98583 12.3438 2.89725 12 2.89725C11.6562 2.89725 11.3183 2.98583 11.0188 3.15447C10.7193 3.32312 10.4683 3.56611 10.29 3.86Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 9V13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 17H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif
</div>

@push('styles')
<style>
.smart-suggestions {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.customer-suggestion:hover,
.product-suggestion:hover {
    background-color: #f8f9fa !important;
    cursor: pointer;
}

.smart-insights {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 0.5rem;
    border-radius: 0.25rem;
    border: 1px solid #dee2e6;
}

.smart-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
}

.smart-badge.smart {
    background-color: #d1ecf1;
    color: #0c5460;
}

.smart-badge.ai {
    background-color: #d4edda;
    color: #155724;
}

.smart-badge.auto {
    background-color: #fff3cd;
    color: #856404;
}
</style>
@endpush

@push('scripts')
<script>
// Smart Proforma Invoice Functions
function setDiscount(amount) {
    const discountInput = document.querySelector('input[name="discount"]');
    if (discountInput) {
        discountInput.value = amount.toFixed(2);
        discountInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
}

function setNote(note) {
    const notesTextarea = document.querySelector('textarea[name="notes"]');
    if (notesTextarea) {
        notesTextarea.value = note;
        notesTextarea.dispatchEvent(new Event('input', { bubbles: true }));
    }
}

// Smart validation feedback
document.addEventListener('livewire:updated', function () {
    // Highlight products that are running low in stock
    const productSuggestions = document.querySelectorAll('.product-suggestion');
    productSuggestions.forEach(suggestion => {
        const stockText = suggestion.querySelector('.text-danger, .text-warning');
        if (stockText) {
            suggestion.style.borderLeft = '3px solid #ffc107';
        }
    });

    // Auto-scroll to newly added cart items
    const cartSection = document.querySelector('.card.border-warning');
    if (cartSection) {
        cartSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});

// Smart keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + Enter to create invoice
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        const createBtn = document.querySelector('button[wire\\:click="createProformaInvoice"]');
        if (createBtn && !createBtn.disabled) {
            createBtn.click();
        }
    }

    // Ctrl/Cmd + P to preview
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        const previewBtn = document.querySelector('button[wire\\:click="generateProformaInvoicePreview"]');
        if (previewBtn && !previewBtn.disabled) {
            previewBtn.click();
        }
    }
});
</script>
@endpush
