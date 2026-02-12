@extends('layouts.officer')

@section('content')
<div class="container-fluid card">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Product Items: {{ $product->name }}</h1>
            <p class="text-muted mb-0">Manage individual items for this product</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('manageproduct') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Products
            </a>
        </div>
    </div>

    <!-- Product Info Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    @if($product->image)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="img-fluid rounded">
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                            <i class="fas fa-box fa-3x text-muted"></i>
                        </div>
                    @endif
                </div>
                <div class="col-md-9">
                    <h5>{{ $product->name }}</h5>
                    <p class="text-muted">{{ $product->description }}</p>
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>SKU:</strong> {{ $product->sku }}<br>
                            <strong>Category:</strong> {{ $product->category->name ?? 'No Category' }}<br>
                            <strong>Unit:</strong> {{ $product->unit }}
                        </div>
                        <div class="col-sm-6">
                            <strong>Buying Price:</strong> {{ number_format($product->base_price, 2) }} TZS<br>
                            <strong>Selling Price:</strong> {{ number_format($product->selling_price, 2) }} TZS<br>
                            <strong>Total Items:</strong> {{ $productItems->total() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>QR Code</th>
                            <th>Status</th>
                            <th>Stock Amount</th>
                            <th>Sold At</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productItems as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->qr_code }}</strong>
                                    <br><small class="text-muted">ID: {{ $item->id }}</small>
                                </td>
                                <td>
                                    @if($item->status === 'available')
                                        <span class="badge bg-success">Available</span>
                                    @elseif($item->status === 'sold')
                                        <span class="badge bg-primary">Sold</span>
                                    @elseif($item->status === 'damaged')
                                        <span class="badge bg-danger">Damaged</span>
                                    @endif
                                </td>
                                <td>{{ $item->stock_amount ?? 'N/A' }}</td>
                                <td>
                                    @if($item->sold_at)
                                        {{ $item->sold_at->format('M d, Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $item->created_by ? \App\Models\User::find($item->created_by)->name ?? 'Unknown' : 'System' }}</td>
                                <td>{{ $item->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @if($item->status === 'available')
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="markAsSold({{ $item->id }})">
                                                <i class="fas fa-shopping-cart"></i> Mark Sold
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning"
                                                    onclick="markAsDamaged({{ $item->id }})">
                                                <i class="fas fa-exclamation-triangle"></i> Mark Damaged
                                            </button>
                                        @elseif($item->status === 'sold')
                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                    onclick="markAsAvailable({{ $item->id }})">
                                                <i class="fas fa-undo"></i> Mark Available
                                            </button>
                                        @elseif($item->status === 'damaged')
                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                    onclick="markAsAvailable({{ $item->id }})">
                                                <i class="fas fa-wrench"></i> Mark Repaired
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-box-open fa-3x mb-3"></i>
                                        <p>No product items found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($productItems->hasPages())
            <div class="card-footer">
                {{ $productItems->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Item Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm">
                <div class="modal-body">
                    <input type="hidden" id="itemId" name="item_id">
                    <input type="hidden" id="newStatus" name="status">
                    <p id="statusMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.table th, .table td {
    vertical-align: middle;
}

.btn-group .btn {
    border-radius: 0.375rem !important;
}

.modal-dialog {
    margin: 1rem;
}

@media (max-width: 576px) {
    .modal-dialog {
        margin: 0.5rem;
        max-width: calc(100vw - 1rem);
    }

    .modal-content {
        border-radius: 0.5rem;
    }

    .modal-header, .modal-footer {
        padding: 0.75rem;
    }

    .modal-body {
        padding: 1rem 0.75rem;
    }
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }

    .table-responsive {
        font-size: 0.875rem;
    }

    .btn-group {
        flex-direction: column;
    }

    .btn-group .btn {
        margin-bottom: 0.25rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
function markAsSold(itemId) {
    document.getElementById('itemId').value = itemId;
    document.getElementById('newStatus').value = 'sold';
    document.getElementById('statusMessage').textContent = 'Are you sure you want to mark this item as sold?';
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}

function markAsDamaged(itemId) {
    document.getElementById('itemId').value = itemId;
    document.getElementById('newStatus').value = 'damaged';
    document.getElementById('statusMessage').textContent = 'Are you sure you want to mark this item as damaged?';
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}

function markAsAvailable(itemId) {
    document.getElementById('itemId').value = itemId;
    document.getElementById('newStatus').value = 'available';
    document.getElementById('statusMessage').textContent = 'Are you sure you want to mark this item as available?';
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}

document.getElementById('statusForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/officer/product-items/update-status', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the item status.');
    });

    bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
});
</script>
@endpush
@endsection
