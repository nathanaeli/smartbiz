@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ __('messages.manage_product') }}: {{ $product->name }}</h4>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="row">
                            <!-- Product Image -->
                            <div class="col-md-4">
                                <div class="text-center">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                        class="img-fluid rounded" style="max-height: 200px;">
                                </div>
                            </div>

                            <!-- Product Stats -->
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h5>{{ __('messages.current_stock') }}</h5>
                                                <h3 class="text-primary">{{ $product->current_stock }} {{ $product->unit }}
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h5>{{ __('messages.realized_profit') }}</h5>
                                                <h3 class="text-info">{{ number_format($actualProfit) }} TSH</h3>
                                                <small class="text-muted">{{ __('messages.money_earned_from_sales') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <strong>{{ __('messages.buying_price') }}:</strong> {{ $product->formatted_base_price }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>{{ __('messages.selling_price') }}:</strong> {{ $product->formatted_selling_price }}
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <strong>{{ __('messages.profit_margin') }}:</strong> {{ $product->profit_margin }}%
                                    </div>
                                    <div class="col-md-6">
                                        <strong>{{ __('messages.status') }}:</strong>
                                        <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $product->is_active ? __('messages.active') : __('messages.inactive') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Form -->
                        <form action="{{ route('tenant.product.update', encrypt($product->id)) }}" method="POST"
                            enctype="multipart/form-data" class="mt-4">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.product_name') }} *</label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $product->name) }}" required>
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.sku') }} *</label>
                                    <input type="text" name="sku" class="form-control"
                                        value="{{ old('sku', $product->sku) }}" required>
                                    @error('sku')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.category') }}</label>
                                    <select name="category_id" class="form-control">
                                        <option value="">-- {{ __('messages.select_category') }} --</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.unit') }} *</label>
                                    <select name="unit" class="form-control" required>
                                        <option value="">-- {{ __('messages.select_unit') }} --</option>
                                        @foreach (['pcs', 'kg', 'g', 'ltr', 'ml', 'box', 'bag', 'pack', 'set', 'pair', 'dozen', 'carton'] as $unit)
                                            <option value="{{ $unit }}"
                                                {{ old('unit', $product->unit) == $unit ? 'selected' : '' }}>
                                                {{ $unit }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('unit')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.buying_price') }} *</label>
                                    <input type="number" step="0.01" name="base_price" class="form-control"
                                        value="{{ old('base_price', $product->base_price) }}" required>
                                    @error('base_price')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.selling_price') }} *</label>
                                    <input type="number" step="0.01" name="selling_price" class="form-control"
                                        value="{{ old('selling_price', $product->selling_price) }}" required>
                                    @error('selling_price')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">{{ __('messages.description') }}</label>
                                    <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
                                    @error('description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">{{ __('messages.product_image') }}</label>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                    @error('image')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active"
                                            value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            {{ __('messages.active') }}
                                        </label>
                                    </div>
                                    @error('is_active')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">{{ __('messages.update_product') }}</button>
                                <a href="{{ route('duka.show', encrypt($product->duka_id)) }}"
                                    class="btn btn-secondary">{{ __('messages.back_to_duka') }}</a>
                            </div>
                        </form>

                        <!-- Stock Management Section -->
                        <div class="mt-5">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5>{{ __('messages.stock_details') }}</h5>
                                <button class="btn btn-success btn-sm" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#addStockForm" aria-expanded="false">
                                    <i class="ri-add-circle-line"></i> {{ __('messages.add_stock') }}
                                </button>
                            </div>

                            <!-- Add Stock Form -->
                            <div class="collapse mt-3" id="addStockForm">
                                <div class="card card-body">
                                    <form action="{{ route('stocks.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="duka_id" value="{{ $product->duka_id }}">
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">{{ __('messages.quantity_to_add') }} *</label>
                                                <input type="number" name="quantity" class="form-control"
                                                    min="1" required>
                                                @error('quantity')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Batch Number</label>
                                                <label class="form-label">{{ __('messages.batch_number') }}</label>
                                                <input type="text" name="batch_number" class="form-control" placeholder="{{ __('messages.batch_number') }}">
                                                @error('batch_number')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Expiry Date</label>
                                                <input type="date" name="expiry_date" class="form-control">
                                                @error('expiry_date')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">{{ __('messages.reason') }}</label>
                                                <input type="text" name="reason" class="form-control"
                                                    placeholder="e.g., Purchase, Restock">
                                                @error('reason')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">{{ __('messages.buying_price_unit_cost') }} *</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" name="unit_cost"
                                                        class="form-control" value="{{ $product->base_price }}" required>
                                                    <span class="input-group-text">TZS</span>
                                                </div>
                                                <small class="text-muted">{{ __('messages.defaults_to_base_price') }}</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">{{ __('messages.notes') }}</label>
                                                <textarea name="notes" class="form-control" rows="2"></textarea>
                                                @error('notes')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-primary">{{ __('messages.add_stock') }}</button>
                                            <button type="button" class="btn btn-secondary" data-bs-toggle="collapse"
                                                data-bs-target="#addStockForm">{{ __('messages.cancel') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.batch_number') }}</th>
                                            <th>{{ __('messages.quantity') }}</th>
                                            <th>{{ __('messages.expiry_date') }}</th>
                                            <th>{{ __('messages.status') }}</th>
                                            <th>{{ __('messages.value') }}</th>
                                            <th>{{ __('messages.updated_by') }}</th>
                                            <th>{{ __('messages.updated_at') }}</th>
                                            <th>{{ __('messages.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($product->stocks as $stock)
                                            <tr>
                                                <td>{{ $stock->batch_number ?? 'N/A' }}</td>
                                                <td>{{ $stock->quantity }} {{ $product->unit }}</td>
                                                <td>{{ $stock->expiry_date ? $stock->expiry_date->format('d/m/Y') : 'N/A' }}
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge {{ $stock->status_badge }}">{{ $stock->status }}</span>
                                                </td>
                                                <td>{{ $stock->formatted_value }}</td>
                                                <td>{{ $stock->lastUpdatedBy?->name ?? 'System' }}</td>
                                                <td>{{ $stock->updated_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editStockModal"
                                                        data-action="{{ route('stocks.update', $stock->id) }}"
                                                        data-quantity="{{ $stock->quantity }}"
                                                        data-batch="{{ $stock->batch_number }}"
                                                        data-expiry="{{ $stock->expiry_date ? $stock->expiry_date->format('Y-m-d') : '' }}"
                                                        data-notes="{{ $stock->notes }}">
                                                        {{ __('messages.edit') }}
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">{{ __('messages.no_stock_entries') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Edit Stock Modals -->

                            <!-- Stock History Section -->
                            <div class="mt-5">
                                <h5>{{ __('messages.stock_history') }}</h5>
                                <div class="table-responsive mt-3">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>{{ __('messages.date') }}</th>
                                                <th>{{ __('messages.type') }}</th>
                                                <th>{{ __('messages.quantity_change') }}</th>
                                                <th>{{ __('messages.previous_qty') }}</th>
                                                <th>{{ __('messages.new_qty') }}</th>
                                                <th>{{ __('messages.batch') }}</th>
                                                <th>{{ __('messages.reason') }}</th>
                                                <th>{{ __('messages.user') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($movements as $movement)
                                                <tr>
                                                    <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                                    <td>
                                                        <span
                                                            class="badge bg-{{ $movement->type === 'in' ? 'success' : 'danger' }}">
                                                            {{ strtoupper($movement->type) }}
                                                        </span>
                                                    </td>
                                                    <td
                                                        class="fw-bold {{ $movement->type === 'in' ? 'text-success' : 'text-danger' }}">
                                                        {{ $movement->type === 'in' ? '+' : '-' }}{{ abs($movement->quantity_change) }}
                                                    </td>
                                                    <td>{{ $movement->previous_quantity }}</td>
                                                    <td>{{ $movement->new_quantity }}</td>
                                                    <td><small
                                                            class="text-muted">{{ $movement->batch_number ?? 'N/A' }}</small>
                                                    </td>
                                                    <td>{{ ucfirst($movement->reason) }}</td>
                                                    <td>{{ $movement->user->name ?? 'System' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted">{{ __('messages.no_stock_movements') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Single Edit Stock Modal -->
    <div class="modal fade" id="editStockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.edit_stock') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editStockForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('messages.quantity') }} *</label>
                                <input type="number" name="quantity" id="edit_quantity" class="form-control" min="0" required>
                                @error('quantity')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('messages.batch_number') }}</label>
                                <input type="text" name="batch_number" id="edit_batch_number" class="form-control">
                                @error('batch_number')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('messages.expiry_date') }}</label>
                                <input type="date" name="expiry_date" id="edit_expiry_date" class="form-control">
                                @error('expiry_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('messages.reason') }}</label>
                                <input type="text" name="reason" class="form-control" placeholder="e.g., Adjustment, Correction">
                                @error('reason')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">{{ __('messages.notes') }}</label>
                                <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                                @error('notes')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('messages.update_stock') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var editStockModal = document.getElementById('editStockModal');
            if (editStockModal) {
                editStockModal.addEventListener('show.bs.modal', function (event) {
                    // Button that triggered the modal
                    var button = event.relatedTarget;

                    // Extract info from data-* attributes
                    var action = button.getAttribute('data-action');
                    var quantity = button.getAttribute('data-quantity');
                    var batch = button.getAttribute('data-batch');
                    var expiry = button.getAttribute('data-expiry');
                    var notes = button.getAttribute('data-notes');

                    // Update the modal's content.
                    var form = document.getElementById('editStockForm');
                    form.action = action;

                    document.getElementById('edit_quantity').value = quantity;
                    document.getElementById('edit_batch_number').value = batch;
                    document.getElementById('edit_expiry_date').value = expiry;
                    document.getElementById('edit_notes').value = notes;
                });
            }
        });
    </script>
@endsection
