@foreach($products as $product)
<div class="col-xxl-3 col-lg-4 col-md-6">
    <div class="sf-card">
        @if(($stocks[$product->id] ?? 0) <= 5)
            <div class="badge-stock">Low Stock</div>
    @endif

    <div class="card-img-box">
        @if($product->image)
        <img src="{{ $product->image_url }}" alt="img">
        @else
        @if($product->type == 'service')
        <i class="fas fa-tools fa-3x text-muted opacity-25"></i>
        @else
        <i class="fas fa-mobile-alt fa-3x text-muted opacity-25"></i>
        @endif
        @endif
    </div>

    <div class="item-title text-truncate">{{ $product->name }}</div>
    <div class="item-price">Tsh {{ number_format($product->selling_price) }}</div>

    @if($product->type == 'service')
    <form action="{{ route('sale.add_to_cart', $duka->id) }}" method="POST" class="add-to-cart-form">
        @csrf
        <input type="hidden" name="item_id" value="{{ $product->id }}">
        <input type="hidden" name="type" value="service">
        <button type="submit" class="btn-service shadow-sm">
            <i class="fas fa-plus"></i> Add Service
        </button>
    </form>
    @else
    <div class="action-row">
        <form action="{{ route('sale.add_to_cart', $duka->id) }}" method="POST" class="add-to-cart-form">
            @csrf
            <input type="hidden" name="item_id" value="{{ $product->id }}">
            <input type="hidden" name="type" value="product">
            <button class="btn-icon-cart"><i class="fas fa-shopping-cart"></i></button>
        </form>

        <div class="qty-counter flex-fill justify-content-between">
            <button class="qty-btn" onclick="decrement(this)">-</button>
            <span class="qty-val">1</span>
            <button class="qty-btn" onclick="increment(this)">+</button>
        </div>
    </div>
    @endif
</div>
</div>
@endforeach