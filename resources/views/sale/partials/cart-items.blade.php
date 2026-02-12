@forelse($cart as $key => $item)
<div class="cart-item">
    <div class="d-flex justify-content-between">
        <div>
            <div class="cart-item-title">
                @if(($item['type'] ?? 'product') === 'service')
                <i class="fas fa-tools text-muted small me-1"></i>
                @endif
                {{ $item['name'] }}
                <span class="text-muted small ms-1" style="font-size: 0.7rem;">({{ ucfirst($item['type'] ?? 'product') }})</span>
            </div>
            <div class="cart-item-price">Tsh {{ number_format($item['unit_price']) }}</div>
        </div>
        <form action="{{ route('sale.remove_from_cart', [$duka->id, $key]) }}" method="POST" class="cart-remove-form">
            @csrf
            <button type="submit" class="btn btn-link text-danger p-0" style="font-size: 1.1rem; text-decoration: none;">
                <i class="fas fa-times-circle"></i>
            </button>
        </form>
    </div>
    <div class="d-flex justify-content-end mt-2">
        <div class="qty-counter p-0" style="height: 28px;">
            <form action="{{ route('sale.remove_from_cart', [$duka->id, $key]) }}" method="POST" class="cart-remove-form">
                @csrf
                <button type="submit" class="qty-btn" style="width: 24px;">-</button>
            </form>
            <span class="qty-val" style="font-size: 0.8rem; width: 20px;">{{ $item['quantity'] }}</span>
            <form action="{{ route('sale.add_to_cart', $duka->id) }}" method="POST" class="add-to-cart-form">
                @csrf
                <input type="hidden" name="item_id" value="{{ $item['id'] }}">
                <input type="hidden" name="type" value="{{ $item['type'] ?? 'product' }}">
                <button type="submit" class="qty-btn" style="width: 24px;">+</button>
            </form>
        </div>
    </div>
</div>
@empty
<div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-5 opacity-50">
    <img src="https://img.icons8.com/isometric/100/shopping-cart.png" width="80" class="mb-3">
    <h6 class="fw-bold">No items found</h6>
    <p class="small text-center">Try changing category or search</p>
</div>
@endforelse