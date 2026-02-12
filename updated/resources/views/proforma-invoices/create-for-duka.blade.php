@extends('layouts.app')

@section('title', 'Create Proforma Invoice for ' . $duka->name)

@section('content')
<div class="container-fluid card">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-primary">Create Proforma Invoice for {{ $duka->name }}</h4>
        <a href="{{ route('proforma.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Invoices
        </a>
    </div>

    <div class="alert alert-info">
        <strong>Duka:</strong> {{ $duka->name }} <br>
        <strong>Location:</strong> {{ $duka->location ?? 'N/A' }} <br>
        <strong>Manager:</strong> {{ $duka->manager_name ?? 'N/A' }}
    </div>

    <form action="{{ route('proforma.store') }}" method="POST">
        @csrf
        <input type="hidden" name="duka_id" value="{{ $duka->id }}">

        {{-- STEP 1: SELECT CUSTOMER --}}
        <h5 class="mb-2"><i class="fas fa-user text-success me-1"></i> Step 1: Select Customer</h5>

        <div class="mb-4">
            <select name="customer_id" id="customer_id" class="form-select" required>
                <option value="">-- Choose Customer --</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">
                        {{ $c->name }} - {{ $c->phone }} ({{ $c->email }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- STEP 2: SELECT PRODUCTS --}}
        <h5 class="mb-2"><i class="fas fa-box text-warning me-1"></i> Step 2: Select Products & Quantities</h5>

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th width="120">Price</th>
                    <th width="120">Qty</th>
                    <th width="150">Subtotal</th>
                </tr>
            </thead>
            <tbody id="productsTable">
                @foreach($products as $p)
                <tr>
                    <td>
                        {{ $p['name'] }} <br>
                        <small class="text-muted">SKU: {{ $p['sku'] }} | Unit: {{ $p['unit'] }}</small>
                        <input type="hidden" name="products[{{ $p['id'] }}][id]" value="{{ $p['id'] }}">
                    </td>
                    <td>
                        <input type="number" class="form-control price-input"
                               name="products[{{ $p['id'] }}][price]"
                               value="{{ $p['selling_price'] }}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control qty-input"
                               name="products[{{ $p['id'] }}][qty]" min="0" value="0">
                    </td>
                    <td>
                        <input type="text" class="form-control subtotal-input"
                               name="products[{{ $p['id'] }}][subtotal]" value="0" readonly>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- TOTAL --}}
        <div class="text-end">
            <h4>Total: <span id="grandTotal" class="text-success">0.00</span> TZS</h4>
        </div>

        {{-- NOTES --}}
        <div class="mb-3">
            <label class="form-label">Notes (Optional)</label>
            <textarea name="notes" class="form-control" rows="3"></textarea>
        </div>

        {{-- VALIDITY --}}
        <div class="mb-3">
            <label class="form-label">Valid For (Days)</label>
            <input type="number" name="valid_days" class="form-control" value="30" min="1" max="365">
        </div>

        <div class="text-end mt-3">
            <button class="btn btn-success btn-lg">
                <i class="fas fa-file-invoice me-1"></i> Generate Proforma Invoice
            </button>
        </div>
    </form>

</div>
@endsection


@push('scripts')
<script>
document.addEventListener('input', function () {
    let grand = 0;
    document.querySelectorAll('#productsTable tr').forEach(row => {
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const qty   = parseFloat(row.querySelector('.qty-input').value) || 0;
        const sub   = price * qty;
        row.querySelector('.subtotal-input').value = sub.toFixed(2);

        grand += sub;
    });
    document.getElementById('grandTotal').innerText = grand.toFixed(2);
});
</script>
@endpush
