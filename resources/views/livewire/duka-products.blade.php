<div class="p-3">

    {{-- ---------------- PRODUCT LIST ---------------- --}}
    <div class="card shadow-sm">
        <div class="card-header mb-10 d-flex justify-content-between">
            <h5 class="mb-0">Products ({{ $products->total() }})</h5>

            <div class="d-flex gap-2">
                <button class="btn btn-success btn-sm mb-10" wire:click="$set('showExcelModal', true)">
                    <i class="ri-file-excel-line"></i> Import Excel
                </button>
                <button class="btn btn-primary btn-sm mb-10" wire:click="$set('showAddModal', true)">
                    <i class="ri-add-circle-line"></i> Add Product
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Buying</th>
                        <th>Selling</th>
                        <th>Stock</th>
                        <th>Profit</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <img src="{{ $product->image_url }}" width="40" height="40" class="rounded">
                            </td>

                            <td class="fw-bold">{{ $product->name }}</td>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->category->name ?? 'Uncategorized' }}</td>

                            <td>{{ $product->formatted_base_price }}</td>
                            <td>{{ $product->formatted_selling_price }}</td>

                            <td>{{ $product->current_stock }} {{ $product->unit ?? '' }}</td>

                            <td class="text-success fw-bold">{{ $product->formatted_profit }}</td>

                            <td>
                                <a href="{{ route('tenant.product.manage', encrypt($product->id)) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    Manage
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-1"
                                    wire:click="deleteProduct({{ $product->id }})"
                                    onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">
                                    <i class="ri-delete-bin-line"></i> Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">
                                No products found for this Duka.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION LINKS --}}
        @if ($products->hasPages())
            <div class="border-top p-2">
                {{ $products->links() }}
            </div>
        @endif

    </div>


    {{-- ---------------- ADD PRODUCT MODAL ---------------- --}}
    @if ($showAddModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.45);" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title ">Add Product</h5>
                        <button type="button" wire:click="$set('showAddModal', false)" class="btn-close"></button>
                    </div>

                    <form wire:submit.prevent="saveProduct">
                        <div class="modal-body">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Product Name *</label>
                                    <input type="text" class="form-control" wire:model="name">
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Category</label>
                                    <select class="form-control" wire:model="category_id">
                                        <option value="">-- Select Category --</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Buying Price *</label>
                                    <input type="number" step="0.01" class="form-control" wire:model="base_price">
                                    @error('base_price')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Selling Price *</label>
                                    <input type="number" step="0.01" class="form-control"
                                        wire:model="selling_price">
                                    @error('selling_price')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Unit (Kg, Pcs, Box)</label>
                                    <input type="text" class="form-control" wire:model="unit">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Initial Stock Quantity *</label>
                                    <input type="number" class="form-control" wire:model="initial_stock">
                                    @error('initial_stock')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- SMART IMAGE UPLOAD & ALWAYS SHOW PREVIEW --}}
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Product Image</label>
                                    <input type="file" class="form-control" wire:model="image" accept="image/*">

                                    <div class="mt-3 d-flex justify-content-center">
                                        <div class="border rounded p-2 bg-light"
                                            style="width: 140px; height: 140px; display:flex; align-items:center; justify-content:center; overflow:hidden;">

                                            @if ($image)
                                                <img src="{{ $image->temporaryUrl() }}" class="img-fluid rounded"
                                                    style="object-fit: cover; width: 100%; height: 100%;">
                                            @elseif ($productImage)
                                                <img src="{{ asset('storage/products/' . $productImage) }}"
                                                    class="img-fluid rounded"
                                                    style="object-fit: cover; width: 100%; height: 100%;">
                                            @else
                                                <img src="{{ asset('images/no-product.png') }}"
                                                    class="img-fluid opacity-50"
                                                    style="object-fit: contain; width: 70%; height: 70%;">
                                            @endif

                                        </div>
                                    </div>

                                    @error('image')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" wire:model="description" rows="2"></textarea>
                                </div>

                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" wire:click="$set('showAddModal', false)"
                                class="btn btn-outline-secondary">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Save Product
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    @endif

    {{-- ---------------- EXCEL IMPORT MODAL ---------------- --}}
    @if ($showExcelModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.45);" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ri-file-excel-line text-success"></i> Import from Excel
                        </h5>
                        <button type="button" wire:click="$set('showExcelModal', false)" class="btn-close"></button>
                    </div>

                    <form wire:submit.prevent="importFromExcel">
                        <div class="modal-body">

                            {{-- IMPORT TYPE SELECTION --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label class="form-label fw-bold">What would you like to import?</label>
                                    <div class="d-flex gap-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" wire:model="importType" value="products" id="importProducts">
                                            <label class="form-check-label" for="importProducts">
                                                <i class="ri-box-3-line text-primary"></i> Products
                                                <small class="d-block text-muted">Import products with stock information</small>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" wire:model="importType" value="categories" id="importCategories">
                                            <label class="form-check-label" for="importCategories">
                                                <i class="ri-folder-line text-warning"></i> Categories
                                                <small class="d-block text-muted">Import product categories only</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- FILE UPLOAD --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Excel File *</label>
                                    <input type="file" class="form-control" wire:model="excelFile" accept=".xlsx,.xls,.csv">
                                    <small class="text-muted">Supported formats: .xlsx, .xls, .csv (Max: 10MB)</small>
                                    @error('excelFile')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- IMPORT INSTRUCTIONS --}}
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6><i class="ri-information-line"></i> Import Instructions:</h6>
                                        <ul class="mb-0">
                                            @if($importType === 'products')
                                                <li>Excel file must have columns: <strong>name, category, buying_price, selling_price, unit, initial_stock, description, barcode</strong></li>
                                                <li>Category names will be automatically created if they don't exist</li>
                                                <li>Products with duplicate names or SKUs will be skipped</li>
                                                <li>Buying price must be less than selling price</li>
                                            @else
                                                <li>Excel file must have columns: <strong>name, description, parent_category, status</strong></li>
                                                <li>Parent categories will be automatically created if they don't exist</li>
                                                <li>Status should be 'active' or 'inactive'</li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            {{-- SAMPLE DOWNLOAD --}}
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            wire:click="downloadSampleExcel('products')">
                                            <i class="ri-download-line"></i> Download Products Sample
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            wire:click="downloadSampleExcel('categories')">
                                            <i class="ri-download-line"></i> Download Categories Sample
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- IMPORT RESULTS --}}
                            @if(!empty($importResults))
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="alert alert-{{ !empty($importResults['errors']) ? 'warning' : 'success' }}">
                                            <h6><i class="ri-check-double-line"></i> Import Results:</h6>
                                            <p class="mb-2">
                                                <strong>{{ $importResults['success'] }}</strong> {{ ucfirst($importResults['type']) }} imported successfully
                                                @if($importResults['skipped'] > 0)
                                                    | <strong>{{ $importResults['skipped'] }}</strong> skipped
                                                @endif
                                            </p>

                                            @if(!empty($importResults['errors']))
                                                <details class="mt-2">
                                                    <summary class="cursor-pointer text-danger">
                                                        <i class="ri-error-warning-line"></i> View Errors ({{ count($importResults['errors']) }})
                                                    </summary>
                                                    <ul class="mt-2 mb-0 small">
                                                        @foreach($importResults['errors'] as $error)
                                                            <li class="text-danger">{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </details>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>

                        <div class="modal-footer">
                            <button type="button" wire:click="$set('showExcelModal', false)"
                                class="btn btn-outline-secondary">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-success"
                                {{ !$excelFile ? 'disabled' : '' }}>
                                <i class="ri-upload-2-line"></i> Import {{ ucfirst($importType) }}
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    @endif

</div>
