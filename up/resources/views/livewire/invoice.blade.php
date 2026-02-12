<div class="card shadow-sm">

    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Create Proforma Invoice</h5>
    </div>

    <div class="card-body">

        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Step 1: Select Duka --}}
        @if(!$selectedDuka)
            <h6 class="text-muted">Choose Duka</h6>
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Select</th>
                        <th>Name</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dukaList as $d)
                    <tr>
                        <td><button wire:click="selectDuka({{ $d->id }})" class="btn btn-sm btn-primary"><svg width="16" height="16" fill="currentColor" class="me-1" viewBox="0 0 16 16"><path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/></svg>Select</button></td>
                        <td>{{ $d->name }}</td>
                        <td>{{ $d->location ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else

        {{-- Step 2: Customer & Items --}}
        <div class="mb-3">
            <label>Customer</label>
            <select wire:model="customer_id" class="form-select">
                <option value="">-- Select Customer --</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                @endforeach
            </select>
        </div>

        <h6 class="fw-bold mt-4">Invoice Items</h6>
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 200px;">Product</th>
                    <th style="width:150px;">Qty</th>
                    <th style="width:150px;">Price</th>
                    <th style="width:150px;">Total</th>
                    <th style="width:80px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                    <tr>
                        <td>
                            <select wire:model="items.{{ $index }}.product_id" wire:change="updateItemPrice({{ $index }})" class="form-select">
                                <option value="">Select</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} ({{ number_format($p->selling_price,2) }} {{ $currency }})</option>
                                @endforeach
                            </select>
                        </td>

                        <td><input type="number" min="1" wire:model="items.{{ $index }}.quantity" wire:change="updateItemTotal({{ $index }})" class="form-control" style="width: 100%;"></td>
                        <td><input type="number" step="0.01" wire:model="items.{{ $index }}.unit_price" class="form-control" readonly style="width: 100%;"></td>
                        <td><input type="number" step="0.01" wire:model="items.{{ $index }}.total_price" class="form-control" readonly style="width: 100%;"></td>
                        <td><button wire:click="removeItem({{ $index }})" class="btn btn-sm btn-danger"><svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5.5.5 0 0 1-.5-.5V6a.5.5 0 0 0-1 0v6.5A1.5 1.5 0 0 0 9.5 14h1a1.5 1.5 0 0 0 1.5-1.5V6a.5.5 0 0 0-.5-.5zM4.118 4L4 4.059V13.5A2.5 2.5 0 0 0 6.5 16h3a2.5 2.5 0 0 0 2.5-2.5V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/></svg></button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button class="btn btn-secondary btn-sm" wire:click="addItem"><svg width="16" height="16" fill="currentColor" class="me-1" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 0 0 1h3v-3A.5.5 0 0 1 8 4z"/></svg>Add Item</button>

        {{-- Totals --}}
        <div class="row mt-4">
            <div class="col-md-4 ms-auto">
                <table class="table table-sm">
                    <tr><th>Subtotal:</th><td>{{ number_format($subtotal,2) }} {{ $currency }}</td></tr>
                    <tr><th>Tax:</th><td><input type="number" wire:model="tax_amount" wire:change="calculateTotals" class="form-control"></td></tr>
                    <tr><th>Discount:</th><td><input type="number" wire:model="discount_amount" wire:change="calculateTotals" class="form-control"></td></tr>
                    <tr class="table-light"><th>Total:</th><td class="fw-bold">{{ number_format($total_amount,2) }} {{ $currency }}</td></tr>
                </table>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <label class="form-label">Notes</label>
                <textarea wire:model="notes" class="form-control" rows="3" placeholder="Optional notes"></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Valid For (Days)</label>
                <input type="number" wire:model="valid_days" class="form-control" min="1" max="365" value="30">
            </div>
        </div>

        <button wire:click="preview" class="btn btn-primary mt-3">
            <svg width="16" height="16" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
            </svg>
            Preview Proforma Invoice
        </button>

        @endif
    </div>
</div>
