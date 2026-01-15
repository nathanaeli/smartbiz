@extends('layouts.officer')

@section('content')
<div class="container-fluid content-inner mt-n5 py-0 card">
    <div class="row">
        <div class="col-lg-12">
            <div class="card rounded-0 bg-transparent border-0 mb-4">
                <div class="card-body p-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div class="mb-3 mb-md-0">
                            <h4 class="mb-1 fw-bold">{{ __('categories.manage_categories') }}</h4>
                            <p class="mb-0 text-secondary">
                                <span class="text-muted">Organize and manage your product catalog efficiently.</span>
                            </p>
                        </div>
                        <div class="d-flex gap-3">
                            <a href="{{ route('officer.categories.import') }}" class="btn btn-soft-success rounded-pill btn-sm d-flex align-items-center gap-2">
                                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 18V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9 15L12 12L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                {{ __('categories.import_excel') }}
                            </a>
                            <button type="button" class="btn btn-primary rounded-pill btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                {{ __('categories.add_new_category') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="col-lg-12">
            <div class="row g-3 mb-4">
                <!-- Total Categories -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="p-2 rounded-3 bg-primary-subtle text-primary">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="2" cy="6" r="1" fill="currentColor"/>
                                        <circle cx="2" cy="12" r="1" fill="currentColor"/>
                                        <circle cx="2" cy="18" r="1" fill="currentColor"/>
                                    </svg>
                                </div>
                                <span class="badge bg-primary text-white rounded-pill">Total</span>
                            </div>
                            <h2 class="mb-1 counter fw-bold text-dark">{{ $stats['total'] }}</h2>
                            <p class="mb-0 text-muted small text-uppercase fw-bold ls-1">All Categories</p>
                        </div>
                    </div>
                </div>

                <!-- Active Categories -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="p-2 rounded-3 bg-success-subtle text-success">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <polyline points="22 4 12 14.01 9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <span class="badge bg-success text-white rounded-pill">Active</span>
                            </div>
                            <h2 class="mb-1 counter fw-bold text-dark">{{ $stats['active'] }}</h2>
                            <p class="mb-0 text-muted small text-uppercase fw-bold ls-1">Active Categories</p>
                        </div>
                    </div>
                </div>

                <!-- Inactive Categories -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="p-2 rounded-3 bg-secondary-subtle text-secondary">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                        <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                </div>
                                <span class="badge bg-secondary text-white rounded-pill">Inactive</span>
                            </div>
                            <h2 class="mb-1 counter fw-bold text-dark">{{ $stats['inactive'] }}</h2>
                            <p class="mb-0 text-muted small text-uppercase fw-bold ls-1">Archived / Hidden</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header border-0 bg-transparent py-3">
                   <div class="row align-items-center">
                       <div class="col-md-6">
                           <h5 class="fw-bold mb-0">Category List</h5>
                       </div>
                       <div class="col-md-6">
                           <form action="{{ route('officer.categories.manage') }}" method="GET" class="d-flex gap-2 justify-content-md-end">
                                <div class="input-group">
                                    <span class="input-group-text border-0 bg-light">
                                        <svg width="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                                            <line x1="21" y1="21" x2="16.65" y2="16.65" stroke="currentColor" stroke-width="2"/>
                                        </svg>
                                    </span>
                                    <input type="text" name="search" class="form-control border-0 bg-light" placeholder="Search categories..." value="{{ request('search') }}">
                                </div>
                                <select name="status" class="form-select border-0 bg-light w-auto" onchange="this.form.submit()">
                                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                           </form>
                       </div>
                   </div>
                </div>
                <div class="card-body p-0">

                    @if (session('success'))
                        <div class="alert alert-success mx-4 mt-3 rounded-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger mx-4 mt-3 rounded-3">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-borderless mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted text-uppercase small">
                                    <th class="ps-4">{{ __('categories.name') }}</th>
                                    <th>{{ __('categories.parent_category') }}</th>
                                    <th>{{ __('categories.description') }}</th>
                                    <th>{{ __('categories.products') }}</th>
                                    <th>{{ __('categories.status') }}</th>
                                    <th>{{ __('categories.created_at') }}</th>
                                    <th class="text-end pe-4">{{ __('categories.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr class="border-bottom border-light category-row" data-category-id="{{ $category->id }}">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <button class="btn btn-sm btn-icon btn-light rounded-circle me-3 expand-btn shadow-sm" data-category-id="{{ $category->id }}" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                     <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="expand-icon transition">
                                                        <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </button>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark">{{ $category->name }}</h6>
                                                    @if($category->children->count() > 0)
                                                        <small class="text-muted">{{ __('categories.subcategories', ['count' => $category->children->count()]) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($category->parent)
                                                <span class="badge bg-info-subtle text-info rounded-pill px-3">{{ $category->parent->name }}</span>
                                            @else
                                                <span class="text-muted fst-italic">{{ __('categories.root_category') }}</span>
                                            @endif
                                        </td>
                                        <td style="max-width: 250px;">
                                            <span class="text-muted text-truncate d-block">{{ $category->description ?? 'No description' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $category->products->count() }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $category->status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-pill px-3">
                                                {{ ucfirst($category->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $category->created_at->format('M d, Y') }}</small>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button class="btn btn-sm btn-icon btn-soft-primary rounded-circle" data-bs-toggle="modal"
                                                        data-bs-target="#editCategoryModal{{ $category->id }}" title="Edit">
                                                    <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </button>
                                                <button class="btn btn-sm btn-icon btn-soft-danger rounded-circle" data-bs-toggle="modal"
                                                        data-bs-target="#deleteCategoryModal{{ $category->id }}" title="Delete">
                                                    <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Expandable Products Row -->
                                    <tr class="products-row d-none" data-category-id="{{ $category->id }}">
                                        <td colspan="7" class="p-0">
                                            <div class="bg-light-subtle p-3 ps-5 border-bottom border-light">
                                                <div class="card border-0 shadow-none bg-white rounded-3">
                                                    <div class="card-header bg-transparent border-bottom border-light py-2">
                                                        <h6 class="mb-0 small fw-bold text-uppercase text-muted">{{ __('categories.products_in_category', ['category' => $category->name]) }}</h6>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        @if($category->products->count() > 0)
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-hover mb-0 mb-2">
                                                                    <thead class="text-muted small">
                                                                        <tr>
                                                                            <th class="ps-3">{{ __('categories.product_name') }}</th>
                                                                            <th>{{ __('categories.sku') }}</th>
                                                                            <th>{{ __('categories.stock_status') }}</th>
                                                                            <th class="text-end">{{ __('categories.base_price') }}</th>
                                                                            <th class="text-end">{{ __('categories.selling_price') }}</th>
                                                                            <th class="text-end pe-3">{{ __('categories.unit') }}</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($category->products as $product)
                                                                            <tr>
                                                                                <td class="ps-3">
                                                                                    <div class="d-flex align-items-center">
                                                                                         @if($product->image)
                                                                                            <img src="{{ $product->image_url }}" alt="" class="rounded me-2 bg-light" width="24" height="24" style="object-fit: cover;">
                                                                                        @else
                                                                                            <div class="avatar avatar-24 bg-light rounded me-2 d-flex align-items-center justify-content-center text-muted">
                                                                                                <small>{{ substr($product->name, 0, 1) }}</small>
                                                                                            </div>
                                                                                        @endif
                                                                                        <span class="text-dark">{{ $product->name }}</span>
                                                                                    </div>
                                                                                </td>
                                                                                <td class="text-muted">{{ $product->sku }}</td>
                                                                                <td>
                                                                                    @php
                                                                                        $totalStock = $product->stocks->sum('quantity');
                                                                                        $status = __('categories.out_of_stock');
                                                                                        $badgeClass = 'bg-danger-subtle text-danger';
                                                                                        if ($totalStock > 0) {
                                                                                            if ($totalStock <= 10) {
                                                                                                $status = __('categories.low_stock');
                                                                                                $badgeClass = 'bg-warning-subtle text-warning';
                                                                                            } else {
                                                                                                $status = __('categories.in_stock');
                                                                                                $badgeClass = 'bg-success-subtle text-success';
                                                                                            }
                                                                                        }
                                                                                    @endphp
                                                                                    <span class="badge {{ $badgeClass }} rounded-pill">{{ $status }}</span>
                                                                                </td>
                                                                                <td class="text-end">{{ number_format($product->base_price, 2) }}</td>
                                                                                <td class="text-end fw-bold text-dark">{{ number_format($product->selling_price, 2) }} TZS</td>
                                                                                <td class="text-end pe-3">{{ $product->unit }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @else
                                                            <div class="text-center py-4">
                                                                <div class="text-muted opacity-50 mb-2">
                                                                    <svg width="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M21 8V21H3V8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        <path d="M23 3H1V8H23V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        <path d="M10 12H14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    </svg>
                                                                </div>
                                                                <small class="text-muted">{{ __('categories.no_products') }}</small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="opacity-25 mb-3">
                                                 <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </div>
                                            <h5 class="text-muted">{{ __('categories.no_categories_found') }}</h5>
                                            <p class="text-muted small mb-0">{{ __('categories.no_categories_desc') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($categories->hasPages())
                        <div class="d-flex justify-content-center p-3 border-top border-light">
                            {{ $categories->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold">{{ __('categories.add_new_category') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('officer.categories.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-uppercase">{{ __('categories.name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Beverages">
                            @error('name')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase">Parent Category</label>
                            <select name="parent_id" class="form-select">
                                <option value="">-- No Parent (Root) --</option>
                                @foreach($categories as $cat)
                                     @if($cat->parent_id == null)
                                         <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                     @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase">{{ __('categories.status') }} <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="active">{{ __('categories.active') }}</option>
                                <option value="inactive">{{ __('categories.inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-uppercase">{{ __('categories.description') }}</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter category description..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">{{ __('categories.cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">{{ __('categories.add_category') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit & Delete Modals -->
@foreach($categories as $category)
<div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold">{{ __('categories.edit_category') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('officer.categories.update', $category->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-uppercase">{{ __('categories.name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase">Parent Category</label>
                            <select name="parent_id" class="form-select">
                                <option value="">-- No Parent (Root) --</option>
                                @foreach($categories as $cat)
                                    @if($cat->id !== $category->id && $cat->parent_id == null)
                                        <option value="{{ $cat->id }}" {{ $category->parent_id == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase">{{ __('categories.status') }} <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ $category->status == 'active' ? 'selected' : '' }}>{{ __('categories.active') }}</option>
                                <option value="inactive" {{ $category->status == 'inactive' ? 'selected' : '' }}>{{ __('categories.inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-uppercase">{{ __('categories.description') }}</label>
                            <textarea name="description" class="form-control" rows="3">{{ $category->description }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">{{ __('categories.cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">{{ __('categories.update_category') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteCategoryModal{{ $category->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 bg-danger-subtle text-danger rounded-top-4">
                <h5 class="modal-title fw-bold">
                    <svg width="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                        <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ __('categories.delete_category') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-3 text-center fs-5">Are you sure you want to delete <br><strong>{{ $category->name }}</strong>?</p>
                <div class="alert alert-warning border-0 rounded-3 mb-0">
                    <div class="d-flex">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-3 flex-shrink-0 text-warning">
                            <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div>
                             <strong>Warning:</strong> This action cannot be undone. Products in this category may be orphaned or need reassignment.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">{{ __('categories.cancel') }}</button>
                <form action="{{ route('officer.categories.destroy', $category->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger rounded-pill px-4">{{ __('categories.delete_category') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('scripts')
<style>
    .transition { transition: all 0.2s ease-in-out; }
    .category-row:hover { background-color: var(--bs-light); }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle expand/collapse buttons
    document.querySelectorAll('.expand-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const categoryId = this.getAttribute('data-category-id');
            const productsRow = document.querySelector(`.products-row[data-category-id="${categoryId}"]`);
            const icon = this.querySelector('.expand-icon');

            if (productsRow.classList.contains('d-none')) {
                // Expand
                productsRow.classList.remove('d-none');
                icon.style.transform = 'rotate(90deg)';
                this.classList.add('btn-light-primary');
                this.classList.remove('btn-light');
            } else {
                // Collapse
                productsRow.classList.add('d-none');
                icon.style.transform = 'rotate(0deg)';
                this.classList.remove('btn-light-primary');
                this.classList.add('btn-light');
            }
        });
    });
});
</script>
@endpush
