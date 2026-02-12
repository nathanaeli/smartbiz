@forelse($services as $service)
<div class="sf-card service-card">
    <div class="sf-card-icon"><i class="fas fa-tools fa-lg"></i></div>
    <div class="sf-card-info">
        <h6>{{ $service->name }}</h6>
        <p>{{ $service->category->name ?? 'General' }}</p>
    </div>
    <div class="sf-card-price">{{ number_format($service->price) }} TSH</div>
    <form action="{{ route('sale.add_to_cart', $duka->id ?? $service->duka_id) }}" method="POST" class="add-to-cart-form">
        @csrf
        <input type="hidden" name="item_id" value="{{ $service->id }}">
        <input type="hidden" name="type" value="service">
        <button type="submit" class="sf-btn-add w-100 justify-content-center" style="background: var(--sf-primary);">
            <i class="fas fa-plus"></i> Add
        </button>
    </form>
</div>
@empty
<div class="col-12 text-center py-5 text-muted">No services found.</div>
@endforelse