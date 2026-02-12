<div>
<div class="container-fluid py-4 min-vh-100" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-lg rounded-4 p-4 bg-gradient-primary text-white overflow-hidden position-relative">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-white bg-opacity-25 p-3 me-3">
                        <i class="fas fa-shopping-bag fs-3"></i>
                    </div>
                    <div>
                        <small class="d-block opacity-90 text-uppercase fw-bold mb-1">{{ __('sales.todays_sales') }}</small>
                        <span class="h2 fw-bold mb-0">{{ $sales->count() }}</span>
                    </div>
                </div>
                <div class="position-absolute bottom-0 end-0 opacity-10 pe-3 pb-2">
                    <i class="fas fa-chart-line fa-4x"></i>
                </div>
            </div>
        </div>
        <div class="col-md-9 d-flex align-items-center justify-content-end">
             <button wire:click="openSellModal" class="btn btn-dark btn-lg rounded-pill shadow-lg px-5 py-3">
                <i class="fas fa-plus-circle me-2 text-warning"></i>{{ __('sales.new_transaction') }}
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 py-4">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h4 class="fw-bold mb-0"><i class="fas fa-receipt me-2 text-primary"></i>{{ __('sales.registry') }}</h4>
                </div>
                <div class="col-md-8 d-flex gap-2 justify-content-end">
                    <div class="input-group input-group-lg" style="max-width: 400px;">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-primary"></i></span>
                        <input type="text" wire:model.live="search" class="form-control bg-light border-0 fw-medium" placeholder="{{ __('sales.search_placeholder') }}">
                    </div>
                    <select wire:model.live="selectedDukaId" class="form-select form-select-lg border-0 bg-light w-auto rounded-3">
                        @foreach($this->assignedDukas as $ad)
                            <option value="{{ $ad->duka_id }}">{{ $ad->duka->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0 table-borderless">
                <thead>
                    <tr class="bg-light border-bottom">
                        <th class="ps-4 py-3 text-muted text-uppercase small fw-bold">{{ __('sales.reference') }}</th>
                        <th class="py-3 text-muted text-uppercase small fw-bold">{{ __('sales.customer') }}</th>
                        <th class="py-3 text-muted text-uppercase small fw-bold">{{ __('sales.terminal') }}</th>
                        <th class="py-3 text-muted text-uppercase small fw-bold">{{ __('sales.status') }}</th>
                        <th class="text-end pe-4 py-3 text-muted text-uppercase small fw-bold">{{ __('sales.amount') }}</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($sales as $sale)
                    <tr class="border-bottom border-light">
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                    <i class="fas fa-hashtag text-primary small"></i>
                                </div>
                                <span class="fw-bold text-primary">ORD-{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</span>
                            </div>
                        </td>
                        <td class="py-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-40 rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center me-3 fw-bold">
                                    {{ substr($sale->customer->name ?? 'D', 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $sale->customer->name ?? __('sales.direct_sale') }}</div>
                                    @if($sale->customer)
                                        <small class="text-muted">{{ $sale->customer->phone ?? '' }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-3"><span class="badge bg-light text-dark border fw-normal px-3 py-2 rounded-pill">{{ $sale->duka->name }}</span></td>
                        <td class="py-3">
                            @if($sale->is_loan)
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill">
                                    <i class="fas fa-credit-card me-1"></i>{{ __('sales.loan') }}
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                                    <i class="fas fa-check-circle me-1"></i>{{ __('sales.completed') }}
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-4 py-3">
                            <div class="fw-bold text-dark fs-6">{{ $currency }} {{ number_format($sale->total_amount, 2) }}</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="opacity-50">
                                <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                                <p class="text-muted">No sales records found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showSellModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(8px);">
        <div class="modal-dialog modal-fullscreen p-0 p-md-4">
            <div class="modal-content shadow-2xl border-0 rounded-4 overflow-hidden animate__animated animate__fadeIn">
                <div class="modal-body p-0 bg-white">
                    <div class="row g-0">
                        <!-- Left Panel - Products -->
                        <div class="col-lg-8 bg-light d-flex flex-column" style="height: 100vh;">
                            <div class="p-4 bg-gradient-primary text-white shadow-sm d-flex justify-content-between align-items-center">
                                <div class="w-50 position-relative">
                                    <i class="fas fa-barcode position-absolute top-50 start-0 translate-middle-y ms-3 opacity-75"></i>
                                    <input type="text" wire:model.live.debounce.300ms="productSearch"
                                           class="form-control form-control-lg ps-5 border-0 bg-white bg-opacity-25 text-white placeholder-white rounded-pill"
                                           placeholder="{{ __('sales.scan_placeholder') }}"
                                           style="backdrop-filter: blur(10px);">
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="text-white small opacity-90"><i class="fas fa-store me-2"></i>{{ $this->assignedDukas->where('duka_id', $selectedDukaId)->first()->duka->name }}</span>
                                    <button wire:click="closeSellModal" class="btn btn-light btn-sm rounded-circle p-2" style="width: 36px; height: 36px;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="p-4 flex-grow-1 overflow-auto">
                                @if($filteredProducts)
                                <div class="row row-cols-1 row-cols-md-3 row-cols-xl-4 g-3">
                                    @foreach($filteredProducts as $p)
                                    <div class="col">
                                        <div class="card h-100 border-0 shadow-sm rounded-4 position-relative overflow-hidden product-card"
                                             wire:click="addToCart({{ $p->id }})"
                                             style="cursor:pointer; transition: all 0.3s ease;">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <span class="badge bg-primary-subtle text-primary rounded-pill small">{{ $p->sku }}</span>
                                                    <span class="badge rounded-pill {{ $p->stocks->first()->quantity > 5 ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $p->stocks->first()->quantity }} Units
                                                    </span>
                                                </div>
                                                <h6 class="fw-bold text-dark mb-2" style="min-height: 40px;">{{ Str::limit($p->name, 35) }}</h6>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="text-primary fw-bold mb-0">{{ number_format($p->selling_price) }}</h5>
                                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                                                        <i class="fas fa-plus text-primary"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="text-center py-5">
                                    <div class="opacity-50">
                                        <i class="fas fa-search fa-4x text-muted mb-3"></i>
                                        <p class="mt-3 text-muted fs-5">{{ __('sales.search_prompt') }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Right Panel - Cart -->
                        <div class="col-lg-4 bg-white d-flex flex-column shadow-2xl" style="height: 100vh;">
                            <div class="p-4 bg-dark text-white">
                                <label class="small text-uppercase opacity-75 fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-user-circle me-2"></i>{{ __('sales.client_details') }}
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white bg-opacity-10 border-0 text-white"><i class="fas fa-user"></i></span>
                                    <input type="text" wire:model.live.debounce.300ms="customerSearch"
                                           class="form-control bg-white bg-opacity-10 border-0 text-white placeholder-white"
                                           placeholder="{{ __('sales.select_customer') }}">
                                </div>
                                @if($filteredCustomers)
                                <div class="list-group position-absolute shadow-lg w-100 mt-1 z-3 border-0 rounded-3 overflow-hidden" style="left: 1rem; right: 1rem; width: auto;">
                                    @foreach($filteredCustomers as $c)
                                    <button wire:click="selectCustomer({{ $c->id }}, '{{ $c->name }}')"
                                            class="list-group-item list-group-item-action py-3 border-0">
                                        <div class="fw-bold">{{ $c->name }}</div>
                                        <small class="text-muted">{{ $c->phone }}</small>
                                    </button>
                                    @endforeach
                                </div>
                                @endif
                                @if($selectedCustomerId)
                                    <div class="mt-2 text-success small fw-bold animate__animated animate__bounceIn">
                                        <i class="fas fa-check-circle me-1"></i>{{ $customerSearch }} {{ __('sales.selected') }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex-grow-1 overflow-auto p-0 bg-light">
                                <div class="list-group list-group-flush">
                                    @forelse($cart as $idx => $item)
                                    <div class="list-group-item border-bottom bg-white px-4 py-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold text-dark mb-1">{{ $item['name'] }}</div>
                                                <small class="text-muted">{{ number_format($item['price']) }} × {{ $item['quantity'] }}</small>
                                            </div>
                                            <div class="text-end ms-3">
                                                <div class="fw-bold text-primary mb-1">{{ number_format($item['total']) }}</div>
                                                <button wire:click="removeFromCart({{ $idx }})" class="btn btn-sm btn-danger btn-icon rounded-circle p-0" style="width: 24px; height: 24px;">
                                                    <i class="fas fa-trash-alt small"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center py-5 opacity-25">
                                        <i class="fas fa-shopping-cart fa-4x mb-3"></i>
                                        <p class="mt-2">{{ __('sales.cart_empty') }}</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="p-4 bg-white border-top shadow-lg">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <span class="text-muted">{{ __('sales.subtotal') }}</span>
                                    <span class="fw-bold fs-5">{{ number_format(collect($cart)->sum('total')) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">{{ __('sales.discount') }}</span>
                                    <div class="input-group" style="width: 140px;">
                                        <span class="input-group-text bg-light border-0 small">{{ $currency }}</span>
                                        <input type="number" wire:model.live="discount" class="form-control bg-light border-0 text-end fw-bold" placeholder="0">
                                    </div>
                                </div>
                                
                                <div class="bg-primary bg-opacity-10 rounded-3 p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-end">
                                        <h6 class="mb-0 fw-bold text-muted">{{ __('sales.grand_total') }}</h6>
                                        <h3 class="mb-0 fw-bold text-primary">{{ $currency }} {{ number_format($this->total, 2) }}</h3>
                                    </div>
                                </div>

                                <div class="btn-group w-100 mb-3 p-1 bg-light rounded-pill shadow-sm">
                                    <input type="radio" class="btn-check" name="type" id="cash" checked wire:click="$set('isLoan', false)">
                                    <label class="btn btn-outline-secondary border-0 rounded-pill py-2 fw-bold" for="cash">
                                        <i class="fas fa-money-bill-wave me-1"></i>{{ __('sales.cash_pay') }}
                                    </label>

                                    <input type="radio" class="btn-check" name="type" id="loan" wire:click="$set('isLoan', true)">
                                    <label class="btn btn-outline-secondary border-0 rounded-pill py-2 fw-bold" for="loan">
                                        <i class="fas fa-credit-card me-1"></i>{{ __('sales.loan_credit') }}
                                    </label>
                                </div>

                                @if($isLoan)
                                <div class="mb-3 animate__animated animate__fadeInUp">
                                    <label class="small fw-bold text-warning d-flex align-items-center mb-2">
                                        <i class="fas fa-calendar-alt me-2"></i>{{ __('sales.due_date') }}
                                    </label>
                                    <input type="date" wire:model="dueDate" class="form-control border-warning bg-warning-subtle fw-bold rounded-3">
                                </div>
                                @endif

                                <button wire:click="createSale"
                                        class="btn btn-primary btn-lg w-100 py-3 rounded-pill shadow-lg fw-bold"
                                        @if(!$selectedCustomerId || empty($cart)) disabled @endif>
                                    <i class="fas fa-print me-2"></i>{{ __('sales.process_transaction') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, var(--bs-primary) 0%, #0056b3 100%);
}

.shadow-2xl {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.product-card {
    transition: all 0.3s ease;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
}

.avatar-40 {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
}

.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
    transform: scale(1.01);
}

.placeholder-white::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.animate__fadeIn {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.animate__bounceIn {
    animation: bounceIn 0.5s ease-out;
}

@keyframes bounceIn {
    0% { transform: scale(0.3); opacity: 0; }
    50% { transform: scale(1.05); }
    70% { transform: scale(0.9); }
    100% { transform: scale(1); opacity: 1; }
}

.btn-check:checked + .btn-outline-secondary {
    background-color: var(--bs-primary) !important;
    color: white !important;
    border-color: var(--bs-primary) !important;
}
</style>
</div>
