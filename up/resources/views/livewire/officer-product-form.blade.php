<div>
    <style>
        .btn-loading {
            opacity: 0.7;
            pointer-events: none;
        }
        .card {
            position: relative;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .invalid-feedback {
            display: block;
        }
    </style>

    <form wire:submit.prevent="save" enctype="multipart/form-data">
        <div class="row">
            <!-- Basic Information -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Basic Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       wire:model="name" id="name" placeholder="Enter product name">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                                <select class="form-select @error('unit') is-invalid @enderror"
                                        wire:model="unit" id="unit">
                                    <option value="">Select Unit</option>
                                    @foreach($availableUnits as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      wire:model="description" id="description" rows="3"
                                      placeholder="Enter product description"></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="barcode" class="form-label">Barcode</label>
                                <input type="text" class="form-control @error('barcode') is-invalid @enderror"
                                       wire:model="barcode" id="barcode" placeholder="Enter barcode">
                                @error('barcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control @error('category_name') is-invalid @enderror"
                                       wire:model="category_name" id="category_name"
                                       list="categories" placeholder="Type or select category"
                                       wire:change="updatedCategoryName">
                                <datalist id="categories">
                                    @foreach($availableCategories as $category)
                                        <option value="{{ $category['name'] }}">
                                    @endforeach
                                </datalist>
                                @error('category_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing & Status -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-dollar-sign me-2"></i>Pricing & Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="buying_price" class="form-label">Buying Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currency }}</span>
                                <input type="number" step="0.01" class="form-control @error('buying_price') is-invalid @enderror"
                                       wire:model="buying_price"
                                       id="buying_price" placeholder="0.00">
                            </div>
                            @error('buying_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="selling_price" class="form-label">Selling Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currency }}</span>
                                <input type="number" step="0.01" class="form-control @error('selling_price') is-invalid @enderror"
                                       wire:model="selling_price" id="selling_price" placeholder="0.00">
                            </div>
                            @error('selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if($profitMargin > 0)
                            <div class="mb-3">
                                <div class="alert alert-info py-2">
                                    <small>
                                        <i class="fas fa-chart-line me-1"></i>
                                        Profit Margin: <strong>{{ $profitMargin }}%</strong>
                                    </small>
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:model="is_active"
                                       id="is_active" value="1">
                                <label class="form-check-label" for="is_active">
                                    Active Product
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock & Image -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-warehouse me-2"></i>Stock Information
                        </h5>
                        @if(count($availableDukas) > 1)
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    wire:click="toggleAdvancedStock">
                                <i class="fas fa-{{ $showAdvancedStock ? 'minus' : 'plus' }} me-1"></i>
                                {{ $showAdvancedStock ? 'Simple' : 'Advanced' }}
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($showAdvancedStock && count($availableDukas) > 1)
                            <!-- Advanced Stock Management -->
                            <div class="mb-3">
                                <h6>Stock by Duka</h6>
                                <div class="row">
                                    @foreach($stocks as $index => $stock)
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">{{ $stock['duka_name'] }}</label>
                                            <div class="input-group">
                                                <input type="number"
                                                       class="form-control @error("stocks.{$index}.quantity") is-invalid @enderror"
                                                       wire:model="stocks.{{ $index }}.quantity"
                                                       placeholder="0" min="0">
                                                <span class="input-group-text">{{ $unit ?? 'pcs' }}</span>
                                            </div>
                                            <small class="text-muted">Current: {{ $stock['current_quantity'] }}</small>
                                            @error("stocks.{$index}.quantity")
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <!-- Simple Stock Management -->
                            @if(count($availableDukas) == 1)
                                <div class="mb-3">
                                    <label for="single_stock" class="form-label">
                                        Stock Quantity ({{ $availableDukas[0]['name'] }})
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('stocks.0.quantity') is-invalid @enderror"
                                               wire:model="stocks.0.quantity" id="single_stock"
                                               placeholder="0" min="0">
                                        <span class="input-group-text">{{ $unit ?? 'pcs' }}</span>
                                    </div>
                                    <small class="text-muted">Current: {{ $stocks[0]['current_quantity'] ?? 0 }}</small>
                                    @error('stocks.0.quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Use Advanced mode to manage stock across multiple dukas.
                                </div>
                            @endif
                        @endif

                        @if($totalStock > 0)
                            <div class="alert alert-success py-2">
                                <small>
                                    <i class="fas fa-chart-bar me-1"></i>
                                    Total Stock: <strong>{{ $totalStock }} {{ $unit ?? 'pcs' }}</strong>
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-image me-2"></i>Product Image
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($existingImage)
                            <div class="mb-3 text-center">
                                <img src="{{ filter_var($existingImage, FILTER_VALIDATE_URL) ? $existingImage : asset('storage/products/' . $existingImage) }}"
                                     alt="Current Image" class="img-fluid rounded" style="max-height: 150px;">
                                <p class="text-muted mt-2">Current Image</p>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="image" class="form-label">Upload New Image</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror"
                                   wire:model="image" id="image" accept="image/*">
                            <div class="form-text">Supported formats: JPEG, PNG, JPG, GIF. Max size: 2MB</div>
                            @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- General Error Display -->
                        @if($errors->has('general'))
                            <div class="alert alert-danger mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ $errors->first('general') }}
                            </div>
                        @endif

                        <!-- Success Message (if any) -->
                        @if(session()->has('success'))
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('manageproduct') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Cancel
                            </a>

                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="fas fa-save me-2"></i>Update Product
                                </span>
                                <span wire:loading>
                                    <i class="fas fa-spinner fa-spin me-2"></i>Updating...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('console-log', (message) => {
                console.log('Livewire Debug:', message);
            });
        });
    </script>
</div>
