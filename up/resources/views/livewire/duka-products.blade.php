<div class="duka-products-container">
    <style>
        .product-thumbnail {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #f1f5f9;
            transition: all 0.3s ease;
        }

        .product-thumbnail:hover {
            transform: scale(1.1);
            border-color: #3b82f6;
        }

        .badge-stock {
            padding: 6px 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 30px;
        }

        .filter-bar {
            background: #f8fafc;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }

        .search-input-group .input-group-text {
            background: white;
            border-right: none;
            color: #64748b;
        }

        .search-input-group .form-control {
            border-left: none;
        }

        .search-input-group .form-control:focus {
            box-shadow: none;
            border-color: #e2e8f0;
        }

        .table-premium thead th {
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 1px;
            background: #f8fafc;
            border-top: none;
        }

        .table-premium tbody td {
            vertical-align: middle;
            color: #334155;
            font-size: 0.9rem;
        }

        .btn-action {
            width: 34px;
            height: 34px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        .loader-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(2px);
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }
    </style>

    {{-- Top Controls --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h4 class="fw-bold mb-0 text-slate-800">
            <span class="text-primary">{{ $products->total() }}</span> Products Catalog
        </h4>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success shadow-sm rounded-pill px-4" wire:click="$set('showExcelModal', true)">
                <i class="ri-file-excel-line me-1"></i> Bulk Import
            </button>
            <button class="btn btn-primary shadow-sm rounded-pill px-4" wire:click="$set('showAddModal', true)">
                <i class="ri-add-line me-1"></i> New Product
            </button>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="filter-bar shadow-sm">
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="input-group search-input-group">
                    <span class="input-group-text"><i class="ri-search-line"></i></span>
                    <input type="text" class="form-control" placeholder="Search by name, SKU or barcode..." wire:model.live.debounce.300ms="search">
                </div>
            </div>
            <div class="col-lg-3 col-md-4">
                <select class="form-select shadow-none" wire:model.live="filterCategory">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-4">
                <select class="form-select shadow-none" wire:model.live="filterStock">
                    <option value="">All Stock Levels</option>
                    <option value="in_stock">In Stock</option>
                    <option value="low_stock">Low Stock (≤10)</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-4 text-end">
                <button class="btn btn-light w-100 border text-muted" wire:click="$set('search', ''); $set('filterCategory', ''); $set('filterStock', '')">
                    <i class="ri-refresh-line"></i> Reset
                </button>
            </div>
        </div>
    </div>

    {{-- Product Table Card --}}
    <div class="card border-0 shadow-sm position-relative overflow-hidden" style="border-radius: 16px;">
        <div wire:loading.flex class="loader-overlay">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-premium mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Product Info</th>
                        <th>Identifier</th>
                        <th>Category</th>
                        <th>Price (TSH)</th>
                        <th>Current Stock</th>
                        <th>Total Profit</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-thumbnail">
                                </div>
                                <div>
                                    <div class="fw-bold fs-6 text-dark">{{ $product->name }}</div>
                                    <div class="text-muted small">Unit: {{ $product->unit ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <code class="text-primary small fw-bold">{{ $product->sku }}</code>
                            @if($product->barcode)
                            <div class="text-muted extra-small mt-1"><i class="ri-barcode-line me-1"></i>{{ $product->barcode }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-soft-info text-info rounded-pill px-3 py-2 border-0">
                                {{ $product->category->name ?? 'Uncategorized' }}
                            </span>
                        </td>
                        <td>
                            <div class="small text-muted mb-1">Buy: {{ number_format($product->base_price) }}</div>
                            <div class="fw-bold text-success">Sell: {{ number_format($product->selling_price) }}</div>
                        </td>
                        <td>
                            @php
                            $stock = $product->current_stock;
                            $badgeClass = 'bg-soft-success text-success';
                            $statusText = 'In Stock';
                            if ($stock <= 0) {
                                $badgeClass='bg-soft-danger text-danger' ;
                                $statusText='Out of Stock' ;
                                } elseif ($stock <=10) {
                                $badgeClass='bg-soft-warning text-warning' ;
                                $statusText='Low Stock' ;
                                }
                                @endphp
                                <div class="fw-bold fs-5 mb-1">{{ $stock }}
        </div>
        <span class="badge badge-stock {{ $badgeClass }} border-0">{{ $statusText }}</span>
        </td>
        <td>
            <div class="fw-bold text-primary">{{ $product->formatted_profit }}</div>
            <div class="text-muted small">Margin: {{ $product->profit_margin }}%</div>
        </td>
        <td class="text-end pe-4">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('tenant.product.manage', encrypt($product->id)) }}"
                    class="btn btn-action btn-soft-primary" title="Manage Inventory">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3h9.05zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8h2.05zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1h9.05z" />
                    </svg>
                </a>
                <button type="button" class="btn btn-action btn-soft-danger"
                    wire:click="deleteProduct({{ $product->id }})"
                    wire:confirm="Are you sure you want to delete this product? This action cannot be undone."
                    title="Delete Product">
                    <i class="ri-delete-bin-7-line"></i>
                </button>
            </div>
        </td>
        </tr>
        @empty
        <tr>
            <td colspan="7">
                <div class="empty-state">
                    <div class="bg-soft-secondary rounded-circle d-inline-flex p-4 mb-3">
                        <i class="ri-archive-2-line ri-4x text-muted"></i>
                    </div>
                    <h4 class="fw-bold">No Products Found</h4>
                    <p class="text-muted mx-auto" style="max-width: 400px;">
                        @if($search || $filterCategory || $filterStock)
                        No products match your current filters. Try resetting them or looking for something else.
                        @else
                        Your product catalog is empty. Start adding items manually or import them using an Excel file.
                        @endif
                    </p>
                    @if($search || $filterCategory || $filterStock)
                    <button class="btn btn-primary rounded-pill px-4" wire:click="$set('search', ''); $set('filterCategory', ''); $set('filterStock', '')">
                        Clear All Filters
                    </button>
                    @endif
                </div>
            </td>
        </tr>
        @endforelse
        </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if ($products->hasPages())
    <div class="card-footer bg-transparent border-0 p-4">
        {{ $products->links() }}
    </div>
    @endif
</div>


{{-- ---------------- MODALS REDESIGN ---------------- --}}

{{-- ADD PRODUCT MODAL --}}
@if ($showAddModal)
<div class="modal fade show d-block" style="background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px);" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pt-4 px-4">
                <div class="bg-soft-primary rounded-circle p-2 me-3">
                    <i class="ri-add-line ri-xl text-primary"></i>
                </div>
                <h4 class="modal-title fw-bold">Register New Product</h4>
                <button type="button" wire:click="$set('showAddModal', false)" class="btn-close shadow-none"></button>
            </div>

            <form wire:submit.prevent="saveProduct">
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-7">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-600">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control rounded-pill px-3 shadow-none border-light-subtle" wire:model="name" placeholder="E.g. Apple iPhone 15">
                                    @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-600">Category</label>
                                    <select class="form-select rounded-pill shadow-none border-light-subtle" wire:model="category_id">
                                        <option value="">-- Select --</option>
                                        @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-600">Unit</label>
                                    <input type="text" class="form-control rounded-pill shadow-none border-light-subtle" wire:model="unit" placeholder="Pcs, Kg, Ltr">
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-4 border border-light-subtle">
                                        <label class="form-label fw-600 text-muted small mb-1">BUYING PRICE (COST)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-0 pe-1">TZS</span>
                                            <input type="number" step="0.01" class="form-control bg-transparent border-0 fw-bold shadow-none p-0" wire:model="base_price">
                                        </div>
                                    </div>
                                    @error('base_price') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-soft-success rounded-4 border border-success border-opacity-10">
                                        <label class="form-label fw-600 text-success small mb-1">SELLING PRICE</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-0 pe-1 text-success">TZS</span>
                                            <input type="number" step="0.01" class="form-control bg-transparent border-0 fw-bold text-success shadow-none p-0" wire:model="selling_price">
                                        </div>
                                    </div>
                                    @error('selling_price') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-600">Initial Stock Level <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control rounded-pill shadow-none border-light-subtle" wire:model="initial_stock">
                                    @error('initial_stock') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="card h-100 border-0 bg-light rounded-4 overflow-hidden">
                                <div class="card-body text-center d-flex flex-column align-items-center justify-content-center">
                                    <label class="form-label fw-bold mb-3">Product Image</label>
                                    <div class="position-relative mb-3">
                                        <div class="bg-white rounded-4 shadow-sm p-2" style="width: 180px; height: 180px; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                                            @if ($image)
                                            <img src="{{ $image->temporaryUrl() }}" class="img-fluid rounded-3 h-100 object-fit-cover w-100">
                                            @elseif ($productImage)
                                            <img src="{{ asset('storage/products/' . $productImage) }}" class="img-fluid rounded-3 h-100 object-fit-cover w-100">
                                            @else
                                            <i class="ri-image-add-line ri-4x text-muted opacity-25"></i>
                                            @endif
                                        </div>
                                        <input type="file" id="productImageInput" class="d-none" wire:model="image" accept="image/*">
                                        <button type="button" class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 shadow" onclick="document.getElementById('productImageInput').click()">
                                            <i class="ri-camera-fill"></i>
                                        </button>
                                    </div>
                                    <p class="text-muted extra-small mb-0">Max size: 2MB. Recommended: 800x800px</p>
                                    @error('image') <span class="text-danger small mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-600">Product Description</label>
                            <textarea class="form-control rounded-4 shadow-none border-light-subtle" wire:model="description" rows="3" placeholder="Tell more about this product..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" wire:click="$set('showAddModal', false)" class="btn btn-light rounded-pill px-4 me-2">Discard</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                        <span wire:loading.remove wire:target="saveProduct">Complete Registration</span>
                        <span wire:loading wire:target="saveProduct">Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- EXCEL IMPORT MODAL --}}
@if ($showExcelModal)
<div class="modal fade show d-block" style="background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px);" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pt-4 px-4">
                <div class="bg-soft-success rounded-circle p-2 me-3">
                    <i class="ri-file-excel-line ri-xl text-success"></i>
                </div>
                <h4 class="modal-title fw-bold">Smart Excel Importer</h4>
                <button type="button" wire:click="$set('showExcelModal', false)" class="btn-close shadow-none"></button>
            </div>

            <form wire:submit.prevent="importFromExcel">
                <div class="modal-body p-4">
                    <div class="row mb-4 text-center">
                        <div class="col-md-6">
                            <label class="cursor-pointer w-100">
                                <input type="radio" class="btn-check" wire:model="importType" value="products" id="importProducts">
                                <div class="p-3 rounded-4 border-2 {{ $importType === 'products' ? 'border-primary bg-soft-primary' : 'border-light bg-light' }} transition-all">
                                    <i class="ri-box-3-fill ri-2x {{ $importType === 'products' ? 'text-primary' : 'text-muted' }} d-block mb-1"></i>
                                    <span class="fw-bold d-block">Products</span>
                                    <small class="text-muted">Import full catalog</small>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="cursor-pointer w-100">
                                <input type="radio" class="btn-check" wire:model="importType" value="categories" id="importCategories">
                                <div class="p-3 rounded-4 border-2 {{ $importType === 'categories' ? 'border-warning bg-soft-warning' : 'border-light bg-light' }} transition-all">
                                    <i class="ri-folder-6-fill ri-2x {{ $importType === 'categories' ? 'text-warning' : 'text-muted' }} d-block mb-1"></i>
                                    <span class="fw-bold d-block">Categories</span>
                                    <small class="text-muted">Organize catalog</small>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="bg-light p-4 rounded-4 mb-4 border border-dashed border-2 border-primary border-opacity-25 text-center">
                        @if(!$excelFile)
                        <i class="ri-upload-cloud-2-line ri-3x text-primary opacity-25 d-block mb-2"></i>
                        <h5 class="fw-bold">Upload spreadsheet</h5>
                        <p class="text-muted small mb-3">Drop your .xlsx or .csv file here or click to browse</p>
                        <input type="file" id="excelFileInput" class="d-none" wire:model="excelFile" accept=".xlsx,.xls,.csv">
                        <button type="button" class="btn btn-primary rounded-pill px-4" onclick="document.getElementById('excelFileInput').click()">Choose File</button>
                        @else
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="ri-file-chart-line ri-2x text-success me-3"></i>
                            <div class="text-start">
                                <div class="fw-bold">{{ $excelFile->getClientOriginalName() }}</div>
                                <div class="text-muted small">{{ round($excelFile->getSize() / 1024, 2) }} KB</div>
                            </div>
                            <button type="button" class="btn btn-link text-danger ms-4" wire:click="$set('excelFile', null)"><i class="ri-close-circle-line h4"></i></button>
                        </div>
                        @endif
                        @error('excelFile') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                    </div>

                    <div class="card border-0 bg-soft-info p-3 mb-3 rounded-4">
                        <div class="d-flex">
                            <i class="ri-lightbulb-line ri-xl text-info me-3"></i>
                            <div>
                                <h6 class="fw-bold text-info mb-1">Quick Tip</h6>
                                <p class="text-muted small mb-0">Use our template to ensure all columns match perfectly with the system requirements.</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light rounded-pill btn-sm px-3" wire:click="downloadSampleExcel('products')">
                            <i class="ri-download-cloud-line me-1"></i> Product Template
                        </button>
                        <button type="button" class="btn btn-light rounded-pill btn-sm px-3" wire:click="downloadSampleExcel('categories')">
                            <i class="ri-download-cloud-line me-1"></i> Category Template
                        </button>
                    </div>

                    @if(!empty($importResults))
                    <div class="alert mt-4 rounded-4 {{ !empty($importResults['errors']) ? 'alert-warning' : 'alert-success border-0 shadow-sm' }}">
                        <div class="d-flex align-items-center mb-2">
                            <i class="ri-checkbox-circle-fill ri-xl me-2"></i>
                            <h6 class="fw-bold mb-0">Import Summary</h6>
                        </div>
                        <div class="ps-4">
                            <span class="badge bg-success rounded-pill px-3">{{ $importResults['success'] }} Success</span>
                            @if($importResults['skipped'] > 0)
                            <span class="badge bg-secondary rounded-pill px-3">{{ $importResults['skipped'] }} Skipped</span>
                            @endif
                        </div>

                        @if(!empty($importResults['errors']))
                        <div class="mt-3 p-3 bg-white rounded-3 border border-warning border-opacity-25 overflow-auto" style="max-height: 150px;">
                            <div class="text-danger fw-bold small mb-1">Correction Required:</div>
                            @foreach($importResults['errors'] as $error)
                            <div class="text-danger extra-small">• {{ $error }}</div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" wire:click="$set('showExcelModal', false)" class="btn btn-light rounded-pill px-4">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-5 shadow-sm" {{ !$excelFile ? 'disabled' : '' }}>
                        <i class="ri-upload-2-fill me-1"></i> Start Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

</div>