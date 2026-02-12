@forelse($cart as $id => $item)
<div class="cart-item">
    <div class="flex-grow-1 overflow-hidden">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark text-truncate mb-1">{{ $item['name'] }}</h6>
            <form action="{{ route('sale.remove_from_cart', [$duka->id, $id]) }}" method="POST" class="cart-remove-form">
                @csrf
                <button type="submit" class="btn btn-link text-danger p-0 ms-2"><i class="fas fa-times"></i></button>
            </form>
        </div>
        <div class="d-flex justify-content-between text-muted small">
            <span>{{ number_format($item['unit_price']) }} x {{ $item['quantity'] }}</span>
            <span class="fw-bold text-indigo">{{ number_format($item['total']) }}</span>
        </div>
    </div>
    <div class="qty-control">
        <span class="fw-bold px-2 text-dark">{{ $item['quantity'] }}</span>
    </div>
</div>
@empty
<div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-5">
    <h6 class="fw-bold">{{ __('sales.cart_empty') }}</h6>
</div>
@endforelse