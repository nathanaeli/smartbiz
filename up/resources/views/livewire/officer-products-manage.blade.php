<div>
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 bg-gradient-primary text-white overflow-hidden position-relative">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <div class="mb-3 mb-md-0">
                            <h2 class="fw-bold mb-1">{{ __('products.title') }}</h2>
                            <p class="mb-0 opacity-90">{{ __('products.subtitle') }}</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if($this->hasPermission('adding_product'))
                                <a href="{{ route('officer.product.create') }}" class="btn btn-light btn-sm rounded-pill">
                                    <i class="fas fa-plus me-2"></i>{{ __('products.add_product') }}
                                </a>
                                <a href="{{ route('officer.products.import') }}" class="btn btn-success btn-sm rounded-pill">
                                    <i class="fas fa-upload me-2"></i>{{ __('products.import_excel') }}
                                </a>
                            @endif
                            <button type="button" class="btn btn-info btn-sm rounded-pill" wire:click="downloadTemplate">
                                <i class="fas fa-file-excel me-2"></i>{{ __('products.download_template') }}
                            </button>
                            <a href="{{ route('officer.products.export', [
                                'search' => $search,
                                'filterCategory' => $filterCategory,
                                'filterDuka' => $filterDuka,
                                'filterStockStatus' => $filterStockStatus
                            ]) }}" class="btn btn-outline-light btn-sm rounded-pill" target="_blank">
                                <i class="fas fa-download me-2"></i>{{ __('products.export') }}
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Decorative elements -->
                <div class="position-absolute top-0 end-0 opacity-10 p-3">
                    <i class="fas fa-boxes fa-5x"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label small text-muted fw-bold text-uppercase">Search Products</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-primary"></i></span>
                        <input type="text" class="form-control border-0 bg-light" placeholder="{{ __('products.search_placeholder') }}"
                               wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small text-muted fw-bold text-uppercase">Category</label>
                    <select class="form-select border-0 bg-light" wire:model.live="filterCategory">
                        <option value="">{{ __('products.filter_all_categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small text-muted fw-bold text-uppercase">Duka</label>
                    <select class="form-select border-0 bg-light" wire:model.live="filterDuka">
                        <option value="">{{ __('products.filter_all_dukas') }}</option>
                        @foreach($dukas as $duka)
                            <option value="{{ $duka->id }}">{{ $duka->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small text-muted fw-bold text-uppercase">Stock Status</label>
                    <select class="form-select border-0 bg-light" wire:model.live="filterStockStatus">
                        <option value="">{{ __('products.filter_all_stock_status') }}</option>
                        <option value="in_stock">{{ __('products.in_stock') }}</option>
                        <option value="low_stock">{{ __('products.low_stock') }}</option>
                        <option value="out_of_stock">{{ __('products.out_of_stock') }}</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small text-muted fw-bold text-uppercase opacity-0">Clear</label>
                    <button type="button" class="btn btn-outline-secondary w-100 rounded-pill" wire:click="clearFilters">
                        <i class="fas fa-times me-1"></i>{{ __('products.clear_filters') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if(count($selectedProducts) > 0)
        <div class="alert alert-primary border-0 shadow-sm rounded-4 d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                    <i class="fas fa-check-circle text-primary fs-4"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">{{ count($selectedProducts) }} {{ __('products.selected_products') }}</h6>
                    <small class="text-muted">Perform bulk actions on selected items</small>
                </div>
            </div>
            <div class="d-flex gap-2">
                @if($this->hasPermission('delete_product'))
                    <button type="button" class="btn btn-danger btn-sm rounded-pill" wire:click="bulkDelete" wire:confirm="{{ __('products.delete_confirm_bulk', ['count' => count($selectedProducts)]) }}">
                        <i class="fas fa-trash me-1"></i>{{ __('products.delete_selected') }}
                    </button>
                @endif
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" wire:click="$set('selectedProducts', [])">
                    <i class="fas fa-times me-1"></i>{{ __('products.clear_selection') }}
                </button>
            </div>
        </div>
    @endif

    <!-- Products Grid/Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-borderless">
                    <thead>
                        <tr class="border-bottom bg-light">
                            <th class="ps-4 py-3" width="40">
                                <input type="checkbox" class="form-check-input" wire:model.live="selectAll">
                            </th>
                            <th class="py-3">
                                <span class="text-muted text-uppercase small fw-bold">{{ __('products.table_product') }}</span>
                            </th>
                            <th class="py-3">
                                <span class="text-muted text-uppercase small fw-bold">{{ __('products.table_category') }}</span>
                            </th>
                            <th class="py-3">
                                <span class="text-muted text-uppercase small fw-bold">{{ __('products.table_status') }}</span>
                            </th>
                            <th class="py-3">
                                <span class="text-muted text-uppercase small fw-bold">{{ __('products.table_prices') }}</span>
                            </th>
                            <th class="py-3 pe-4 text-end">
                                <span class="text-muted text-uppercase small fw-bold">{{ __('products.table_actions') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr class="border-bottom border-light">
                                <td class="ps-4">
                                    <input type="checkbox" class="form-check-input" wire:model.live="selectedProducts" value="{{ $product->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center py-2">
                                        @if($product->image)
                                            <div class="avatar avatar-60 rounded-3 me-3 overflow-hidden" style="width: 60px; height: 60px;">
                                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" 
                                                     class="w-100 h-100" style="object-fit: cover;">
                                            </div>
                                        @else
                                            <div class="avatar avatar-60 rounded-3 me-3 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-box text-primary fs-4"></i>
                                            </div>
                                        @endif
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold text-dark">{{ $product->name }}</h6>
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <span class="badge bg-light text-dark border small">SKU: {{ $product->sku }}</span>
                                                @if($product->description)
                                                    <small class="text-muted">{{ Str::limit($product->description, 40) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">
                                        {{ $product->category->name ?? 'No Category' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $stockQuantity = $product->stocks->sum('quantity');
                                        $availableItems = $product->items->where('status', 'available')->count();
                                        $totalStock = $stockQuantity + $availableItems;
                                        $status = __('products.out_of_stock');
                                        $badgeClass = 'bg-danger';
                                        $iconClass = 'fa-times-circle';
                                        if ($totalStock > 0) {
                                            if ($totalStock <= 10) {
                                                $status = __('products.low_stock');
                                                $badgeClass = 'bg-warning';
                                                $iconClass = 'fa-exclamation-triangle';
                                            } else {
                                                $status = __('products.in_stock');
                                                $badgeClass = 'bg-success';
                                                $iconClass = 'fa-check-circle';
                                            }
                                        }
                                    @endphp
                                    <div class="d-flex flex-column">
                                        <span class="badge {{ $badgeClass }} rounded-pill px-3 mb-1">
                                            <i class="fas {{ $iconClass }} me-1"></i>{{ $status }}
                                        </span>
                                        <small class="text-muted fw-bold">{{ $totalStock }} {{ $product->unit }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-danger-subtle text-danger small">{{ __('products.buy') }}</span>
                                            <span class="fw-bold">{{ number_format($product->base_price, 0) }} TZS</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success-subtle text-success small">{{ __('products.sell') }}</span>
                                            <span class="fw-bold">{{ number_format($product->selling_price, 0) }} TZS</span>
                                        </div>
                                        @if($product->base_price > 0)
                                            <small class="text-primary fw-bold">
                                                <i class="fas fa-chart-line me-1"></i>{{ __('products.profit') }}: {{ number_format((($product->selling_price - $product->base_price) / $product->base_price) * 100, 1) }}%
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td class="pe-4">
                                    <div class="d-flex gap-1 justify-content-end">
                                        @if($this->hasPermission('edit_product'))
                                            <button type="button" class="btn btn-sm btn-primary btn-icon rounded-circle" 
                                                    wire:click="editProduct({{ $product->id }})" 
                                                    title="{{ __('products.edit_product') }}">
                                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 2.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5H9v-.5a.5.5 0 0 1 .5-.5h.5V9a.5.5 0 0 1 .5-.5h.5v-.793l.793-.793zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                                                </svg>
                                            </button>
                                        @endif

                                        @if($this->hasPermission('adding_stock') || $this->hasPermission('reduce_stock'))
                                            <button type="button" class="btn btn-sm btn-success btn-icon rounded-circle" 
                                                    wire:click="navigateToStockComponent({{ $product->id }})" 
                                                    title="{{ __('products.manage_stock') }}">
                                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path d="M7.752.066a.5.5 0 0 1 .496 0l3.75 2.143a.5.5 0 0 1 .252.434v3.995l3.498 2A.5.5 0 0 1 16 9.07v4.286a.5.5 0 0 1-.252.434l-3.75 2.143a.5.5 0 0 1-.496 0l-3.502-2-3.502 2.001a.5.5 0 0 1-.496 0l-3.75-2.143A.5.5 0 0 1 0 13.357V9.071a.5.5 0 0 1 .252-.434L3.75 6.638V2.643a.5.5 0 0 1 .252-.434L7.752.066zM4.25 7.504 1.508 9.071l2.742 1.567 2.742-1.567L4.25 7.504zM7.5 9.933l-2.75 1.571v3.134l2.75-1.571V9.933zm1 3.134 2.75 1.571v-3.134L8.5 9.933v3.134zm.508-3.996 2.742 1.567 2.742-1.567-2.742-1.567-2.742 1.567zm2.242-2.433V3.504L8.5 5.076V8.21l2.75-1.571zM7.5 5.076V1.943l-2.75 1.571V6.21l2.75-1.571zM4.75 3.504v3.134L2 7.21V5.076L4.75 3.504z"/>
                                                </svg>
                                            </button>
                                        @endif

                                        <a href="{{ route('officer.product.items', $product->id) }}" 
                                           class="btn btn-sm btn-info btn-icon rounded-circle" 
                                           title="{{ __('products.view_items') }}">
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M1.5 1a.5.5 0 0 0-.5.5v4a.5.5 0 0 1-1 0v-4A1.5 1.5 0 0 1 1.5 0h4a.5.5 0 0 1 0 1h-4zM10 .5a.5.5 0 0 1 .5-.5h4A1.5 1.5 0 0 1 16 1.5v4a.5.5 0 0 1-1 0v-4a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 1-.5-.5zM.5 10a.5.5 0 0 1 .5.5v4a.5.5 0 0 0 .5.5h4a.5.5 0 0 1 0 1h-4A1.5 1.5 0 0 1 0 14.5v-4a.5.5 0 0 1 .5-.5zm15 0a.5.5 0 0 1 .5.5v4a1.5 1.5 0 0 1-1.5 1.5h-4a.5.5 0 0 1 0-1h4a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 1 .5-.5z"/>
                                                <path d="M3 5a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5z"/>
                                            </svg>
                                        </a>

                                        @if($this->hasPermission('delete_product'))
                                            <button type="button" class="btn btn-sm btn-danger btn-icon rounded-circle" 
                                                    wire:click="deleteProduct({{ $product->id }})" 
                                                    wire:confirm="{{ __('products.delete_confirm') }}" 
                                                    title="Delete Product">
                                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-5">
                                        <div class="mb-4 opacity-50">
                                            <i class="fas fa-box-open fa-4x text-muted"></i>
                                        </div>
                                        <h5 class="text-muted mb-2">{{ __('products.no_products') }}</h5>
                                        @if($this->search || $this->filterCategory || $this->filterStockStatus || $this->filterDuka)
                                            <p class="text-muted mb-3">Try adjusting your filters</p>
                                            <button type="button" class="btn btn-primary btn-sm rounded-pill" wire:click="clearFilters">
                                                <i class="fas fa-times me-1"></i>{{ __('products.clear_filters') }}
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
            <div class="card-footer border-0 bg-light">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- Smart Success/Error Messages -->
    @if (session()->has('success'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
            <div class="toast show animate__animated animate__slideInRight shadow-lg" role="alert" style="min-width: 350px;">
                <div class="toast-header bg-success text-white border-0">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong class="me-auto">{{ __('products.success_message') }}!</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    <i class="fas fa-check text-success me-2"></i>{{ session('success') }}
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
            <div class="toast show animate__animated animate__slideInRight shadow-lg" role="alert" style="min-width: 350px;">
                <div class="toast-header bg-danger text-white border-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong class="me-auto">Error!</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    <i class="fas fa-times text-danger me-2"></i>{{ session('error') }}
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, var(--bs-primary) 0%, #0056b3 100%);
}

.table th, .table td {
    vertical-align: middle;
}

.btn-icon {
    width: 36px;
    height: 36px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.avatar-60 {
    flex-shrink: 0;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
    transform: translateX(2px);
}

.card {
    transition: all 0.3s ease;
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-icon:hover {
    transform: scale(1.1);
}

.form-control:focus,
.form-select:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

.badge {
    font-weight: 600;
    letter-spacing: 0.5px;
}

/* Rounded pill styles */
.rounded-pill {
    border-radius: 50rem !important;
}

.rounded-4 {
    border-radius: 1rem !important;
}

/* Toast animation */
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.animate__slideInRight {
    animation: slideInRight 0.3s ease-out;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .d-flex.gap-2 {
        flex-wrap: wrap;
    }
    
    .avatar-60 {
        width: 40px !important;
        height: 40px !important;
    }
}

/* Subtle hover effect on cards */
.card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
}
</style>
@endpush

