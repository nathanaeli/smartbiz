@forelse($cart as $key => $item)
<div class="cart-item">
    <div class="d-flex justify-content-between">
        <div>
            <div class="cart-item-title">
                @if(($item['type'] ?? 'product') === 'service')
                <!-- Service SVG -->
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg>
                @else
                <!-- Product SVG -->
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3a57e8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
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