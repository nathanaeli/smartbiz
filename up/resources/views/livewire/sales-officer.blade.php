<div class="container">
    <h4>Welcome {{ auth()->user()->name }}</h4>

    @if (session()->has('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Make Sale</h5>
                </div>
                <div class="card-body">
                    <!-- Customer Selection -->
                    <div class="mb-3">
                        <label for="customer" class="form-label">Select Customer</label>
                        <select wire:model.live="selectedCustomer" class="form-select" id="customer">
                            <option value="">Choose customer...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone }}</option>
                            @endforeach
                        </select>
                        @error('selectedCustomer') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <!-- Products -->
                    <div class="mb-3">
                        <label class="form-label">Add Products to Cart</label>
                        <div class="row">
                            @foreach($products as $product)
                                <div class="col-md-6 mb-2">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>{{ $product->name }}</h6>
                                            <p class="text-muted">{{ $product->formatted_selling_price }}</p>
                                            <p class="text-muted">Stock: {{ $product->current_stock }}</p>
                                            <button wire:click="addToCart({{ $product->id }}, 1)" class="btn btn-primary btn-sm" {{ $product->current_stock <= 0 ? 'disabled' : '' }}>
                                                Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Cart -->
                    @if(count($cart) > 0)
                        <div class="mb-3">
                            <h6>Cart</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cart as $index => $item)
                                            <tr>
                                                <td>{{ $item['name'] }}</td>
                                                <td>
                                                    <input type="number" value="{{ $item['quantity'] }}" wire:change="updateCartQuantity({{ $index }}, $event.target.value)" class="form-control form-control-sm" min="1" style="width: 80px;">
                                                </td>
                                                <td>{{ number_format($item['unit_price'], 2) }} TZS</td>
                                                <td>{{ number_format($item['total'], 2) }} TZS</td>
                                                <td>
                                                    <button wire:click="removeFromCart({{ $index }})" class="btn btn-danger btn-sm">Remove</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3">Total Amount</th>
                                            <th>{{ number_format($total_amount, 2) }} TZS</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Sale Options -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model.live="isLoan" id="isLoan">
                                    <label class="form-check-label" for="isLoan">
                                        Is this a loan?
                                    </label>
                                </div>
                            </div>
                            @if($isLoan)
                                <div class="col-md-6">
                                    <label for="dueDate" class="form-label">Due Date</label>
                                    <input type="date" wire:model="dueDate" class="form-control" id="dueDate">
                                    @error('dueDate') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            @endif
                        </div>

                        <!-- Discount -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="discountAmount" class="form-label">Discount Amount (TZS)</label>
                                <input type="number" wire:model.live="discountAmount" class="form-control" id="discountAmount" min="0" step="0.01">
                                @error('discountAmount') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            @if($discountAmount > 0)
                                <div class="col-md-6">
                                    <label for="discountReason" class="form-label">Discount Reason</label>
                                    <input type="text" wire:model="discountReason" class="form-control" id="discountReason">
                                    @error('discountReason') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            @endif
                        </div>

                        <div class="mt-3">
                            <strong>Final Amount: {{ number_format($final_amount, 2) }} TZS</strong>
                        </div>

                        <div class="mt-3">
                            <button wire:click="createSale" class="btn btn-success">Complete Sale</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6>Quick Info</h6>
                </div>
                <div class="card-body">
                    <h6>Products in Stock</h6>
                    <ul class="list-group list-group-flush">
                        @foreach($products as $product)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $product->name }}
                                <span class="badge bg-primary rounded-pill">{{ $product->current_stock }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
