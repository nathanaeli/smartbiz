<div class="container-fluid mt-4 card">
    <div wire:ignore.self class="modal fade" id="saleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Processing Sale: {{ $selectedDukaName }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <div class="col-lg-7 p-4 bg-light">
                            <input type="text" wire:model.live="productSearch" class="form-control mb-3" placeholder="Search products...">
                            <div class="row g-2 overflow-auto" style="max-height: 500px;">
                                @foreach($products as $product)
                                    <div class="col-md-4">
                                        <div class="card h-100 shadow-sm border-0 p-2 text-center" wire:click="addToCart({{ $product->id }})" style="cursor:pointer;">
                                            <div class="fw-bold">{{ $product->name }}</div>
                                            <div class="text-primary">{{ number_format($product->price) }} {{ $currency }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-lg-5 p-4 bg-white border-start">
                            <h5 class="mb-3">Cart ({{ count($cart) }} items)</h5>
                            <div class="overflow-auto mb-3" style="max-height: 350px;">
                                @foreach($cart as $id => $item)
                                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                        <div>
                                            <div class="small fw-bold">{{ $item['name'] }}</div>
                                            <small>{{ $item['qty'] }} x {{ number_format($item['price']) }}</small>
                                        </div>
                                        <button wire:click="removeFromCart({{ $id }})" class="btn btn-sm text-danger">&times;</button>
                                    </div>
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-between h4 fw-bold mb-4">
                                <span>Total:</span>
                                <span class="text-primary">{{ number_format($totalAmount) }} {{ $currency }}</span>
                            </div>
                            <button wire:click="processCheckout" class="btn btn-primary w-100 py-3 fw-bold" {{ empty($cart) ? 'disabled' : '' }}>
                                Complete Transaction
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
       Livewire.on('open-sale-modal', (event) => {
           var myModal = new bootstrap.Modal(document.getElementById('saleModal'));
           myModal.show();
       });

       Livewire.on('sale-completed', (event) => {
           var modalEl = document.getElementById('saleModal');
           var modal = bootstrap.Modal.getInstance(modalEl);
           modal.hide();
           alert('Sale Completed Successfully!');
       });
    });
</script>
@endpush