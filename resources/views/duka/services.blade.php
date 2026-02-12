@extends('layouts.app')

@section('title', $duka->name . ' - Service Management')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 pb-4 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 small">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('duka.show', $duka->id) }}" class="text-decoration-none">{{ $duka->name }}</a></li>
                    <li class="breadcrumb-item active">Service Management</li>
                </ol>
            </nav>
            <h1 class="h2 fw-bold text-dark mb-1">Service Management</h1>
            <p class="text-muted small mb-0">Manage service categories and individual services for {{ $duka->name }}.</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm rounded-2 px-3 d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add Category
        </button>
        <button type="button" class="btn btn-dark btn-sm rounded-2 px-3 d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addServiceModal">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add Service
        </button>
        <a href="{{ route('duka.show', $duka->id) }}" class="btn btn-outline-secondary btn-sm rounded-2 px-3 d-flex align-items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Duka
        </a>
    </div>

    @if($categories->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body p-5 text-center">
            <div class="bg-soft-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="ri-service-line ri-3x text-primary"></i>
            </div>
            <h4 class="fw-bold">No Services Configured</h4>
            <p class="text-muted mx-auto" style="max-width: 400px;">Start by creating a service category and then add individual services to offer to your customers.</p>
            <div class="d-flex justify-content-center gap-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">Create Your First Category</button>
            </div>
        </div>
    </div>
    @else
    <div class="row g-4">
        @foreach($categories as $category)
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold">{{ $category->name }}</h5>
                        @if($category->description)
                        <p class="text-muted small mb-0">{{ $category->description }}</p>
                        @endif
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                            </svg>
                        </button>
                        <form action="{{ route('duka.services.categories.destroy', [$duka->id, $category->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category? All services will remain if any.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Service Name</th>
                                    <th>Description</th>
                                    <th>Billing</th>
                                    <th>Price (TZS)</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($category->services as $service)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">{{ $service->name }}</td>
                                    <td class="small text-muted">{{ Str::limit($service->description, 50) }}</td>
                                    <td><span class="badge bg-soft-info text-info">{{ $service->billing_type }}</span></td>
                                    <td class="fw-bold">{{ number_format($service->price, 2) }}</td>
                                    <td>
                                        @if($service->is_active)
                                        <span class="badge bg-success">Active</span>
                                        @else
                                        <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-soft-primary d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#editServiceModal{{ $service->id }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                            </svg>
                                        </button>
                                        <form action="{{ route('duka.services.destroy', [$duka->id, $service->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-soft-danger d-inline-flex align-items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No services in this category.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('duka.services.categories.update', [$duka->id, $category->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Category Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ $category->description }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Services Modals -->
        @foreach($category->services as $service)
        <div class="modal fade" id="editServiceModal{{ $service->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('duka.services.update', [$duka->id, $service->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Service: {{ $service->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select" required>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $service->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Service Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $service->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2">{{ $service->description }}</textarea>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">TZS</span>
                                        <input type="number" name="price" step="0.01" class="form-control" value="{{ $service->price }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Billing Type</label>
                                    <select name="billing_type" class="form-select" required>
                                        <option value="Per Service" {{ $service->billing_type == 'Per Service' ? 'selected' : '' }}>Per Service</option>
                                        <option value="Hourly" {{ $service->billing_type == 'Hourly' ? 'selected' : '' }}>Hourly</option>
                                        <option value="Daily" {{ $service->billing_type == 'Daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="Monthly" {{ $service->billing_type == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="Fixed" {{ $service->billing_type == 'Fixed' ? 'selected' : '' }}>Fixed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeCheck{{ $service->id }}" {{ $service->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="activeCheck{{ $service->id }}">Active</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update Service</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
        @endforeach
    </div>
    @endif
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('duka.services.categories.store', $duka->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-primary">Add Service Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Consultations, Repairs" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Describe what services fall under this category..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Create Category</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('duka.services.store', $duka->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">Add New Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-dark fw-bold">Select Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="" disabled selected>Choose a category...</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-dark fw-bold">Service Name</label>
                        <input type="text" name="name" class="form-control px-3" placeholder="What are you offering?" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-dark fw-bold">Description (Optional)</label>
                        <textarea name="description" class="form-control px-3" rows="2" placeholder="Brief details about the service..."></textarea>
                    </div>
                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label class="form-label text-dark fw-bold">Standard Price</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">TZS</span>
                                <input type="number" name="price" step="0.01" class="form-control" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-dark fw-bold">Billing Unit</label>
                            <select name="billing_type" class="form-select" required>
                                <option value="Per Service" selected>Per Service</option>
                                <option value="Hourly">Hourly</option>
                                <option value="Daily">Daily</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Fixed">Fixed Package</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" class="btn btn-dark px-4">Save Service</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .bg-soft-primary {
        background-color: rgba(7, 154, 162, 0.1);
    }

    .bg-soft-info {
        background-color: rgba(49, 186, 241, 0.1);
    }

    .btn-soft-primary {
        background-color: rgba(7, 154, 162, 0.1);
        color: #079aa2;
        border: none;
    }

    .btn-soft-primary:hover {
        background-color: #079aa2;
        color: #fff;
    }

    .btn-soft-danger {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border: none;
    }

    .btn-soft-danger:hover {
        background-color: #dc3545;
        color: #fff;
    }
</style>
@endsection