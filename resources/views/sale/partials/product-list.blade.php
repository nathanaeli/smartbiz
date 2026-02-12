@foreach($products as $product)
@php
$stockQty = $stocks[$product->id] ?? 0;
$isLowStock = $stockQty <= 5;
    $hasImage=!empty($product->image);
    @endphp

    <div class="col-xxl-3 col-lg-4 col-md-6">
        <div class="sf-card {{ $isLowStock ? 'low-stock-warning' : '' }}" style="position: relative; overflow: hidden;">

            @if($isLowStock)
            <div class="badge-stock bg-danger text-white position-absolute top-0 end-0 m-2 px-2 py-1 rounded small fw-bold stock-badge-{{ $product->id }}" style="z-index: 10;">
                Low Stock: {{ $stockQty }}
            </div>
            @else
            <div class="badge-stock bg-success text-white position-absolute top-0 end-0 m-2 px-2 py-1 rounded small fw-bold stock-badge-{{ $product->id }}" style="z-index: 10;">
                In Stock: {{ $stockQty }}
            </div>
            @endif

            <div class="card-img-box d-flex align-items-center justify-content-center bg-light" style="height: 180px;">
                @if($hasImage)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="img-fluid" style="max-height: 100%; object-fit: contain;">
                @else
                <!-- Default Placeholder with Initials or Icon -->
                <div class="text-center text-muted">
                    <i class="fas fa-box-open fa-3x mb-2 opacity-50"></i>
                    <div class="small fw-bold">{{ Str::limit($product->name, 20) }}</div>
                </div>
                @endif
            </div>

            <div class="p-3">
                <div class="item-title text-truncate fw-bold text-dark mb-1" title="{{ $product->name }}">{{ $product->name }}</div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="item-price text-primary fw-bold">Tsh {{ number_format($product->selling_price) }}</div>
                    <small class="text-muted">{{ $product->unit ?? 'Unit' }}</small>
                </div>

                <div class="action-row d-flex align-items-center gap-2">
                    <form action="{{ route('sale.add_to_cart', $duka->id) }}" method="POST" class="add-to-cart-form flex-grow-1">
                        @csrf
                        <input type="hidden" name="item_id" value="{{ $product->id }}">
                        <input type="hidden" name="type" value="product">
                        <button type="submit" class="btn btn-primary w-100 btn-sm fw-bold d-flex align-items-center justify-content-center gap-2 btn-add-{{ $product->id }}" {{ $stockQty <= 0 ? 'disabled' : '' }}>
                            <i class="fas fa-cart-plus"></i> Add
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach