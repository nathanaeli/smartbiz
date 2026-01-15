@extends('layouts.officer')

@section('content')
<div class="container-fluid card">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        Manage Stock - {{ $product->name }}
                    </h4>
                    <a href="{{ route('manageproduct') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i>Back to Products
                    </a>
                </div>
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 0 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                            </svg>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                            </svg>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5>Product Information</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <th>SKU:</th>
                                            <td>{{ $product->sku }}</td>
                                        </tr>
                                        <tr>
                                            <th>Name:</th>
                                            <td>{{ $product->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Category:</th>
                                            <td>{{ $product->category->name ?? 'No Category' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Unit:</th>
                                            <td>{{ $product->unit }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5>Quick Actions</h5>
                                    <p class="text-muted">Use the buttons below for each duka to quickly add or reduce stock.</p>
                                    <div class="alert alert-info">
                                        <strong>Quick Amounts:</strong> +1, +5, +10 for adding; -1, -5, -10 for reducing
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @foreach($dukas as $duka)
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0">{{ $duka->name }}</h6>
                                        <div class="d-flex align-items-center">
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-1 text-info">
                                                <path d="M7.752.066a.5.5 0 0 1 .496 0l3.75 2.143a.5.5 0 0 1 .252.434v3.995l3.498 2A.5.5 0 0 1 16 9.07v4.286a.5.5 0 0 1-.252.434l-3.75 2.143a.5.5 0 0 1-.496 0l-3.502-2-3.502 2.001a.5.5 0 0 1-.496 0l-3.75-2.143A.5.5 0 0 1 0 13.357V9.071a.5.5 0 0 1 .252-.434L3.75 6.638V2.643a.5.5 0 0 1 .252-.434L7.752.066zM4.25 7.504 1.508 9.071l2.742 1.567 2.742-1.567L4.25 7.504zM7.5 9.933l-2.75 1.571v3.134l2.75-1.571V9.933zm1 3.134 2.75 1.571v-3.134L8.5 9.933v3.134zm.508-3.996 2.742 1.567 2.742-1.567-2.742-1.567-2.742 1.567zm2.242-2.433V3.504L8.5 5.076V8.21l2.75-1.571zM7.5 5.076V1.943l-2.75 1.571V6.21l2.75-1.571zM4.75 3.504v3.134L2 7.21V5.076L4.75 3.504z"/>
                                            </svg>
                                            <strong>{{ $stocks[$duka->id] ?? 0 }}</strong> {{ $product->unit }}
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @if(auth()->user()->hasPermission('adding_stock'))
                                            <div class="mb-3">
                                                <label class="form-label">Add Stock</label>
                                                <div class="btn-group d-flex" role="group">
                                                    <button type="submit" form="add-form-{{ $duka->id }}" class="btn btn-sm text-primary btn-success flex-fill d-flex align-items-center justify-content-center"
                                                            title="Add 1 {{ $product->unit }}">
                                                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                                                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                                        </svg>
                                                        1
                                                    </button>
                                                    <button type="submit" form="add5-form-{{ $duka->id }}" class="btn btn-sm btn-success flex-fill d-flex align-items-center text-primary justify-content-center"
                                                            title="Add 5 {{ $product->unit }}">
                                                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                                                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                                        </svg>
                                                        5
                                                    </button>
                                                    <button type="submit" form="add10-form-{{ $duka->id }}" class="btn btn-sm btn-success flex-fill d-flex text-primary align-items-center justify-content-center"
                                                            title="Add 10 {{ $product->unit }}">
                                                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                                                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                                        </svg>
                                                        10
                                                    </button>
                                                </div>
                                                <form id="add-form-{{ $duka->id }}" action="{{ route('officer.stocks.add') }}" method="POST" style="display: none;">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                    <input type="hidden" name="duka_id" value="{{ $duka->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <input type="hidden" name="reason" value="Quick add">
                                                </form>
                                                <form id="add5-form-{{ $duka->id }}" action="{{ route('officer.stocks.add') }}" method="POST" style="display: none;">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                    <input type="hidden" name="duka_id" value="{{ $duka->id }}">
                                                    <input type="hidden" name="quantity" value="5">
                                                    <input type="hidden" name="reason" value="Quick add">
                                                </form>
                                                <form id="add10-form-{{ $duka->id }}" action="{{ route('officer.stocks.add') }}" method="POST" style="display: none;">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                    <input type="hidden" name="duka_id" value="{{ $duka->id }}">
                                                    <input type="hidden" name="quantity" value="10">
                                                    <input type="hidden" name="reason" value="Quick add">
                                                </form>
                                            </div>
                                        @endif

                                        @if(auth()->user()->hasPermission('reduce_stock') && ($stocks[$duka->id] ?? 0) > 0)
                                            <div class="mb-3">
                                                <label class="form-label">Reduce Stock</label>
                                                <div class="btn-group d-flex" role="group">
                                                    <button type="submit" form="reduce-form-{{ $duka->id }}" class="btn btn-sm text-secondary btn-danger flex-fill d-flex align-items-center justify-content-center"
                                                            title="Reduce 1 {{ $product->unit }}">
                                                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                                                            <path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8z"/>
                                                        </svg>
                                                        1
                                                    </button>
                                                    <button type="submit" form="reduce5-form-{{ $duka->id }}" class="btn btn-sm btn-danger flex-fill d-flex align-items-center text-secondary justify-content-center"
                                                            title="Reduce 5 {{ $product->unit }}">
                                                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                                                            <path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8z"/>
                                                        </svg>
                                                        5
                                                    </button>
                                                    <button type="submit" form="reduce10-form-{{ $duka->id }}" class="btn btn-sm btn-danger text-secondary flex-fill d-flex align-items-center justify-content-center"
                                                            title="Reduce 10 {{ $product->unit }}">
                                                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                                                            <path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8z"/>
                                                        </svg>
                                                        10
                                                    </button>
                                                </div>
                                                <form id="reduce-form-{{ $duka->id }}" action="{{ route('officer.stocks.reduce') }}" method="POST" style="display: none;">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                    <input type="hidden" name="duka_id" value="{{ $duka->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <input type="hidden" name="reason" value="Quick reduce">
                                                </form>
                                                <form id="reduce5-form-{{ $duka->id }}" action="{{ route('officer.stocks.reduce') }}" method="POST" style="display: none;">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                    <input type="hidden" name="duka_id" value="{{ $duka->id }}">
                                                    <input type="hidden" name="quantity" value="5">
                                                    <input type="hidden" name="reason" value="Quick reduce">
                                                </form>
                                                <form id="reduce10-form-{{ $duka->id }}" action="{{ route('officer.stocks.reduce') }}" method="POST" style="display: none;">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                    <input type="hidden" name="duka_id" value="{{ $duka->id }}">
                                                    <input type="hidden" name="quantity" value="10">
                                                    <input type="hidden" name="reason" value="Quick reduce">
                                                </form>
                                            </div>
                                        @endif

                                        <div class="mb-3">
                                            <label class="form-label">Custom Amount</label>
                                            <form action="{{ route('officer.stocks.add') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <input type="hidden" name="duka_id" value="{{ $duka->id }}">
                                                <div class="input-group input-group-sm">
                                                    <input type="number" class="form-control" name="quantity" placeholder="Qty" min="1" required>
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <input type="hidden" name="reason" value="Custom add">
                                            </form>
                                            @if(($stocks[$duka->id] ?? 0) > 0)
                                                <form action="{{ route('officer.stocks.reduce') }}" method="POST" class="d-inline ms-2">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                    <input type="hidden" name="duka_id" value="{{ $duka->id }}">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control" name="quantity" placeholder="Qty" min="1" max="{{ $stocks[$duka->id] }}" required>
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                                                                <path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8z"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <input type="hidden" name="reason" value="Custom reduce">
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
