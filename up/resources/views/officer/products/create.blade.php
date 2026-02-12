@extends('layouts.officer')

@section('content')
<div class="container-fluid card">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-plus-circle text-success me-2"></i>{{ __('products.create_title') }}
                    </h2>
                    <p class="text-muted mb-0">{{ __('products.create_subtitle') }}</p>
                </div>
                <a href="{{ route('manageproduct') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('products.back_to_products') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>{{ __('products.error_fix') }}</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Main Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-box text-primary me-2"></i>{{ __('products.product_details') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('officer.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
                        @csrf

                        <!-- Product Name & Unit -->
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="name" class="form-label fw-semibold">
                                    {{ __('products.label_name') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}"
                                       placeholder="{{ __('products.label_name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="unit" class="form-label fw-semibold">
                                    {{ __('products.label_unit') }} <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg @error('unit') is-invalid @enderror" id="unit" name="unit" required>
                                    <option value="">{{ __('products.select_unit') }}</option>
                                    <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                                    <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilograms (kg)</option>
                                    <option value="g" {{ old('unit') == 'g' ? 'selected' : '' }}>Grams (g)</option>
                                    <option value="ltr" {{ old('unit') == 'ltr' ? 'selected' : '' }}>Liters (ltr)</option>
                                    <option value="ml" {{ old('unit') == 'ml' ? 'selected' : '' }}>Milliliters (ml)</option>
                                    <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>Boxes</option>
                                    <option value="bag" {{ old('unit') == 'bag' ? 'selected' : '' }}>Bags</option>
                                    <option value="pack" {{ old('unit') == 'pack' ? 'selected' : '' }}>Packs</option>
                                    <option value="set" {{ old('unit') == 'set' ? 'selected' : '' }}>Sets</option>
                                    <option value="pair" {{ old('unit') == 'pair' ? 'selected' : '' }}>Pairs</option>
                                    <option value="dozen" {{ old('unit') == 'dozen' ? 'selected' : '' }}>Dozens</option>
                                    <option value="carton" {{ old('unit') == 'carton' ? 'selected' : '' }}>Cartons</option>
                                </select>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">{{ __('products.label_description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3"
                                      placeholder="{{ __('products.label_description') }}">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Pricing -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="buying_price" class="form-label fw-semibold">
                                    {{ __('products.label_buying_price') }} ({{ $currency }}) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $currency }}</span>
                                    <input type="number" class="form-control @error('buying_price') is-invalid @enderror"
                                           id="buying_price" name="buying_price" value="{{ old('buying_price') }}"
                                           placeholder="0.00" step="0.01" min="0" required>
                                </div>
                                @error('buying_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="selling_price" class="form-label fw-semibold">
                                    {{ __('products.label_selling_price') }} ({{ $currency }}) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $currency }}</span>
                                    <input type="number" class="form-control @error('selling_price') is-invalid @enderror"
                                           id="selling_price" name="selling_price" value="{{ old('selling_price') }}"
                                           placeholder="0.00" step="0.01" min="0" required>
                                </div>
                                @error('selling_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Profit Calculator -->
                        <div class="card bg-light mb-3">
                            <div class="card-body py-3">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="text-muted small">{{ __('products.profit_per_unit') }}</div>
                                        <div class="h5 text-success mb-0" id="profitPerUnit">{{ $currency }} 0.00</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small">{{ __('products.profit_margin') }}</div>
                                        <div class="h5 text-primary mb-0" id="profitMargin">0.00%</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small">{{ __('products.sku_preview') }}</div>
                                        <div class="h5 text-info mb-0" id="skuPreview">{{ __('products.auto_generated') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Category & Store -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-tags text-warning me-2"></i>{{ __('products.category_store') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="category_id" class="form-label fw-semibold">
                            {{ __('products.label_category') }} <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" form="productForm" required>
                            <option value="">{{ __('products.select_category') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="duka_id" class="form-label fw-semibold">
                            {{ __('products.label_duka') }} <span class="text-danger">*</span>
                        </label>
                        @if($dukas->count() === 1)
                            <input type="hidden" name="duka_id" value="{{ $dukas->first()->id }}" form="productForm">
                            <div class="form-control-plaintext bg-light p-2 rounded">{{ $dukas->first()->name }}</div>
                        @else
                            <select class="form-select @error('duka_id') is-invalid @enderror" id="duka_id" name="duka_id" form="productForm" required>
                                <option value="">{{ __('products.select_store') }}</option>
                                @foreach($dukas as $duka)
                                    <option value="{{ $duka->id }}" {{ old('duka_id') == $duka->id ? 'selected' : '' }}>
                                        {{ $duka->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('duka_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stock & Additional -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-cubes text-success me-2"></i>{{ __('products.stock_additional') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="initial_stock" class="form-label fw-semibold">
                            {{ __('products.label_initial_stock') }} <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control @error('initial_stock') is-invalid @enderror"
                               id="initial_stock" name="initial_stock" value="{{ old('initial_stock', 0) }}"
                               placeholder="0" min="0" form="productForm" required>
                        @error('initial_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="barcode" class="form-label fw-semibold">{{ __('products.label_barcode') }}</label>
                        <input type="text" class="form-control @error('barcode') is-invalid @enderror"
                               id="barcode" name="barcode" value="{{ old('barcode') }}"
                               placeholder="{{ __('products.label_barcode') }}" form="productForm">
                        @error('barcode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label fw-semibold">{{ __('products.label_image') }}</label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror"
                               id="image" name="image" accept="image/*" form="productForm">
                        <div class="form-text">{{ __('products.supported_formats') }}</div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <button type="submit" class="btn btn-success btn-lg w-100" form="productForm">
                        <i class="fas fa-save me-2"></i>{{ __('products.btn_create') }}
                    </button>
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            All fields marked with * are required
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.card-header {
    border-bottom: 1px solid #f1f3f4;
    border-radius: 12px 12px 0 0 !important;
}

.form-control, .form-select {
    border: 1px solid #e1e5e9;
    border-radius: 8px;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    border-radius: 8px;
    padding: 12px 24px;
    font-weight: 600;
}

.btn-success:hover {
    background: linear-gradient(135deg, #218838 0%, #1aa085 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.input-group-text {
    background-color: #f8f9fa;
    border: 1px solid #e1e5e9;
    color: #6c757d;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const buyingPriceInput = document.getElementById('buying_price');
    const sellingPriceInput = document.getElementById('selling_price');
    const profitPerUnitDiv = document.getElementById('profitPerUnit');
    const profitMarginDiv = document.getElementById('profitMargin');
    const skuPreviewDiv = document.getElementById('skuPreview');
    const nameInput = document.getElementById('name');
    const initialStockInput = document.getElementById('initial_stock');

    // Calculate profit and margin
    function calculateProfit() {
        const buyingPrice = parseFloat(buyingPriceInput.value) || 0;
        const sellingPrice = parseFloat(sellingPriceInput.value) || 0;

        const profitPerUnit = sellingPrice - buyingPrice;
        const profitMargin = buyingPrice > 0 ? ((profitPerUnit / buyingPrice) * 100) : 0;

        profitPerUnitDiv.textContent = '{{ $currency }} ' + profitPerUnit.toFixed(2);
        profitMarginDiv.textContent = profitMargin.toFixed(2) + '%';

        // Color coding
        if (profitPerUnit > 0) {
            profitPerUnitDiv.className = 'h5 text-success mb-0';
            profitMarginDiv.className = 'h5 text-success mb-0';
        } else if (profitPerUnit < 0) {
            profitPerUnitDiv.className = 'h5 text-danger mb-0';
            profitMarginDiv.className = 'h5 text-danger mb-0';
        } else {
            profitPerUnitDiv.className = 'h5 text-warning mb-0';
            profitMarginDiv.className = 'h5 text-warning mb-0';
        }
    }

    // Generate SKU preview
    function generateSkuPreview() {
        const name = nameInput.value.trim();
        const stock = parseInt(initialStockInput.value) || 0;

        if (name) {
            const cleanName = name.replace(/[^A-Za-z0-9]/g, '').toUpperCase().substring(0, 4);
            const stockPart = stock.toString().padStart(3, '0');
            const randomPart = Math.floor(Math.random() * 90 + 10);
            const sku = `${cleanName}-${stockPart}-${randomPart}`;
            skuPreviewDiv.textContent = sku;
        } else {
            skuPreviewDiv.textContent = '{{ __('products.auto_generated') }}';
        }
    }

    // Event listeners
    buyingPriceInput.addEventListener('input', calculateProfit);
    sellingPriceInput.addEventListener('input', calculateProfit);
    nameInput.addEventListener('input', generateSkuPreview);
    initialStockInput.addEventListener('input', generateSkuPreview);

    // Initial calculation
    calculateProfit();
    generateSkuPreview();

    // Form validation
    const form = document.getElementById('productForm');
    form.addEventListener('submit', function(e) {
        const buyingPrice = parseFloat(buyingPriceInput.value) || 0;
        const sellingPrice = parseFloat(sellingPriceInput.value) || 0;

        if (sellingPrice <= buyingPrice) {
            e.preventDefault();
            alert('{{ __('products.selling_price_error') }}');
            sellingPriceInput.focus();
            return false;
        }

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ __('products.creating') }}';
        submitBtn.disabled = true;

        // Re-enable after 10 seconds as fallback
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 10000);
    });
});
</script>
@endpush
