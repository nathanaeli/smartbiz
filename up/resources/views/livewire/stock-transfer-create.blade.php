<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <div class="header-title">
                <h4 class="card-title">Transfer Stock Between Dukas</h4>
            </div>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="transferStock">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="from_duka_id" class="form-label">From Duka</label>
                            <select wire:model.live="from_duka_id" id="from_duka_id" class="form-select @error('from_duka_id') is-invalid @enderror" required>
                                <option value="">Select From Duka</option>
                                @foreach($dukas as $duka)
                                    <option value="{{ $duka->id }}" {{ $from_duka_id == $duka->id ? 'selected' : '' }}>
                                        {{ $duka->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('from_duka_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="to_duka_id" class="form-label">To Duka</label>
                            <select wire:model.live="to_duka_id" id="to_duka_id" class="form-select @error('to_duka_id') is-invalid @enderror" required>
                                <option value="">Select To Duka</option>
                                @foreach($dukas as $duka)
                                    <option value="{{ $duka->id }}" {{ $to_duka_id == $duka->id ? 'selected' : '' }}>
                                        {{ $duka->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('to_duka_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                @if($from_duka_id || $to_duka_id)
                <div class="row mt-3">
                    <div class="col-12">
                        <h5>Product Selection</h5>

                        <!-- Smart Product Search -->
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="text" wire:model.live.debounce.300ms="productSearch"
                                           class="form-control"
                                           placeholder="Search for products by name or SKU...">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="button" wire:click="openCreateProductModal"
                                        class="btn btn-success w-100">
                                    <i class="fas fa-plus"></i> Create New Product
                                </button>
                            </div>
                        </div>

                        <!-- Search Results -->
                        @if(!empty($searchResults))
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Search Results</h6>
                            </div>
                            <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                @foreach($searchResults as $product)
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                    <div>
                                        <strong>{{ $product['name'] }}</strong> ({{ $product['sku'] }})<br>
                                        <small class="text-muted">
                                            Category: {{ $product['category'] }} |
                                            Price: ${{ number_format($product['selling_price'], 2) }}
                                        </small>
                                    </div>
                                    <button type="button" wire:click="selectProduct({{ $product['id'] }})"
                                            class="btn btn-sm btn-primary">
                                        Select
                                    </button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            @if($fromDukaProducts)
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">Available Products in Source Duka</h6>
                                    </div>
                                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                        @foreach($fromDukaProducts as $product)
                                        <div class="form-check">
                                            <input wire:model.live="product_id" class="form-check-input" type="radio" name="product_id" id="from_product_{{ $product['id'] }}" value="{{ $product['id'] }}">
                                            <label class="form-check-label" for="from_product_{{ $product['id'] }}">
                                                <strong>{{ $product['name'] }}</strong> ({{ $product['sku'] }})<br>
                                                <small class="text-muted">Stock: {{ $product['formatted_quantity'] }}</small>
                                            </label>
                                        </div>
                                        @endforeach
                                        @if(empty($fromDukaProducts))
                                        <p class="text-muted">No products with stock available in this duka.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($toDukaProducts)
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">Products in Destination Duka</h6>
                                    </div>
                                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                        @foreach($toDukaProducts as $product)
                                        <div class="form-check">
                                            <input wire:model.live="product_id" class="form-check-input" type="radio" name="product_id" id="to_product_{{ $product['id'] }}" value="{{ $product['id'] }}">
                                            <label class="form-check-label" for="to_product_{{ $product['id'] }}">
                                                <strong>{{ $product['name'] }}</strong> ({{ $product['sku'] }})<br>
                                                <small class="text-muted">Current Stock: {{ $product['formatted_quantity'] }}</small>
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                @if($product_id && ($selectedFromProductStock !== null || $selectedToProductStock !== null))
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h6>Stock Information</h6>
                            @if($selectedFromProductStock !== null)
                            <p><strong>Source Duka Stock:</strong> {{ number_format($selectedFromProductStock) }}</p>
                            @endif
                            @if($selectedToProductStock !== null)
                            <p><strong>Destination Duka Stock:</strong> {{ number_format($selectedToProductStock) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="quantity" class="form-label">Quantity to Transfer</label>
                            <input type="number" wire:model="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror"
                                   value="{{ $quantity }}" min="1" :max="$selectedFromProductStock ?? ''" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($selectedFromProductStock)
                            <small class="form-text text-muted">Maximum available: {{ number_format($selectedFromProductStock) }}</small>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="reason" class="form-label">Reason</label>
                            <input type="text" wire:model="reason" id="reason" class="form-control @error('reason') is-invalid @enderror"
                                   value="{{ $reason }}" required>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea wire:model="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ $notes }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>Transfer Stock</span>
                            <span wire:loading>Processing...</span>
                        </button>
                        <a href="{{ route('tenant.dashboard') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif

    @error('general')
        <div class="alert alert-danger mt-3">
            {{ $message }}
        </div>
    @enderror

    <!-- Create Product Modal -->
    @if($showCreateProductModal)
    <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Product</h5>
                    <button type="button" class="btn-close" wire:click="closeCreateProductModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="createProduct">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="newProductName" class="form-label">Product Name *</label>
                                    <input type="text" wire:model="newProductName" id="newProductName"
                                           class="form-control @error('newProductName') is-invalid @enderror" required>
                                    @error('newProductName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="newProductSku" class="form-label">SKU *</label>
                                    <input type="text" wire:model="newProductSku" id="newProductSku"
                                           class="form-control @error('newProductSku') is-invalid @enderror" required>
                                    @error('newProductSku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="newProductCategoryId" class="form-label">Category</label>
                                    <select wire:model="newProductCategoryId" id="newProductCategoryId" class="form-select">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="newProductSellingPrice" class="form-label">Selling Price *</label>
                                    <input type="number" wire:model="newProductSellingPrice" id="newProductSellingPrice"
                                           class="form-control @error('newProductSellingPrice') is-invalid @enderror"
                                           step="0.01" min="0" required>
                                    @error('newProductSellingPrice')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="newProductCostPrice" class="form-label">Cost Price</label>
                                    <input type="number" wire:model="newProductCostPrice" id="newProductCostPrice"
                                           class="form-control @error('newProductCostPrice') is-invalid @enderror"
                                           step="0.01" min="0">
                                    @error('newProductCostPrice')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="newProductDescription" class="form-label">Description</label>
                                    <textarea wire:model="newProductDescription" id="newProductDescription"
                                              class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeCreateProductModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="createProduct">Create Product</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
