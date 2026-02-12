@extends('layouts.officer')
@section('content')
<div class="row">
    <!-- Today's Sales Card -->
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-50 bg-soft-primary rounded">
                        <svg class="icon-32" width="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.4" d="M16.191 2H7.81C4.77 2 3 3.78 3 6.83V17.16C3 20.26 4.77 22 7.81 22H16.191C19.28 22 21 20.26 21 17.16V6.83C21 3.78 19.28 2 16.191 2Z" fill="currentColor"></path>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M8.07996 6.6499V6.6599C7.64896 6.6599 7.29996 7.0099 7.29996 7.4399C7.29996 7.8699 7.64896 8.2199 8.07996 8.2199H11.069C11.5 8.2199 11.85 7.8699 11.85 7.4289C11.85 6.9999 11.5 6.6499 11.069 6.6499H8.07996ZM15.92 12.7399H8.07996C7.64896 12.7399 7.29996 12.3899 7.29996 11.9599C7.29996 11.5299 7.64896 11.1789 8.07996 11.1789H15.92C16.35 11.1789 16.7 11.5299 16.7 11.9599C16.7 12.3899 16.35 12.7399 15.92 12.7399ZM15.92 17.3099H8.07996C7.77996 17.3099 7.48996 17.1999 7.28996 16.9999C7.08996 16.7999 6.99996 16.5099 6.99996 16.2099C6.99996 15.7799 7.34996 15.4299 7.77996 15.4299H15.92C16.35 15.4299 16.7 15.7799 16.7 16.2099C16.7 16.6399 16.35 16.9899 15.92 16.9899V17.3099Z" fill="currentColor"></path>
                        </svg>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-1">{{ __('dashboard.todays_sales') }}</h6>
                        <h4 class="mb-0">{{ number_format($todayRevenue, 2) }}</h4>
                        <small class="text-muted">{{ $todaySalesCount }} {{ __('dashboard.transactions') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Stock Card -->
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-50 bg-soft-success rounded">
                        <svg class="icon-32" width="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.4" d="M9.07861 16.1355H14.8936C15.4766 16.1355 15.9406 15.6645 15.9406 15.0815V12.3105C16.6176 12.0135 17.0656 11.3525 17.0656 10.5885V8.57251C17.0656 7.28551 15.8786 6.21251 14.5586 6.29551C14.2956 4.82251 13.0586 3.63651 11.5756 3.63651C10.0926 3.63651 8.85561 4.82251 8.59261 6.29551C7.27261 6.21251 6.08561 7.28551 6.08561 8.57251V10.5885C6.08561 11.3525 6.53361 12.0135 7.21061 12.3105V15.0815C7.21061 15.6645 7.67461 16.1355 8.25761 16.1355H9.07861Z" fill="currentColor"></path>
                            <path d="M20.0691 18.2185H3.89691C2.96591 18.2185 2.21094 17.4635 2.21094 16.5325V14.6155C2.21094 13.6845 2.96591 12.9295 3.89691 12.9295H20.0691C21.0001 12.9295 21.7551 13.6845 21.7551 14.6155V16.5325C21.7551 17.4635 21.0001 18.2185 20.0691 18.2185Z" fill="currentColor"></path>
                        </svg>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-1">{{ __('dashboard.total_stock') }}</h6>
                        <h4 class="mb-0">{{ number_format($totalStock) }}</h4>
                        <small class="text-muted">{{ __('dashboard.units_inventory') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Customers Card -->
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-50 bg-soft-info rounded">
                        <svg class="icon-32" width="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.9488 14.54C8.49884 14.54 5.58789 15.1038 5.58789 17.2795C5.58789 19.4562 8.51765 20.0001 11.9488 20.0001C15.3988 20.0001 18.3098 19.4364 18.3098 17.2606C18.3098 15.084 15.38 14.54 11.9488 14.54Z" fill="currentColor"></path>
                            <path opacity="0.4" d="M11.949 12.467C14.2851 12.467 16.1583 10.5831 16.1583 8.23351C16.1583 5.88306 14.2851 4 11.949 4C9.61293 4 7.73975 5.88306 7.73975 8.23351C7.73975 10.5831 9.61293 12.467 11.949 12.467Z" fill="currentColor"></path>
                        </svg>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-1">{{ __('dashboard.total_customers') }}</h6>
                        <h4 class="mb-0">{{ number_format($totalCustomers) }}</h4>
                        <small class="text-muted">{{ __('dashboard.registered_customers') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gross Volume Card -->
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-50 bg-soft-warning rounded">
                        <svg class="icon-32" width="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.4" d="M15.94 2H8.06C5.81 2 4 3.81 4 6.06V17.94C4 20.19 5.81 22 8.06 22H15.94C18.19 22 20 20.19 20 17.94V6.06C20 3.81 18.19 2 15.94 2Z" fill="currentColor"></path>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M9.5 8.5C9.5 7.67 10.17 7 11 7H13C13.83 7 14.5 7.67 14.5 8.5C14.5 9.33 13.83 10 13 10H11C10.17 10 9.5 9.33 9.5 8.5ZM9.5 13.5C9.5 12.67 10.17 12 11 12H13C13.83 12 14.5 12.67 14.5 13.5C14.5 14.33 13.83 15 13 15H11C10.17 15 9.5 14.33 9.5 13.5Z" fill="currentColor"></path>
                        </svg>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-1">{{ __('dashboard.gross_volume') }}</h6>
                        <h4 class="mb-0">{{ number_format($grossVolume, 2) }}</h4>
                        <small class="text-muted">{{ __('dashboard.total_sales_value') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Last Stock Transfer Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{ __('dashboard.last_stock_transfer') }}</h5>
            </div>
            <div class="card-body">
                @if($lastStockTransfer)
                    <div class="row">
                        <div class="col-md-6">
                            <h6>{{ __('dashboard.transfer_details') }}</h6>
                            <p class="mb-1"><strong>{{ __('dashboard.from') }}:</strong> {{ $lastStockTransfer->fromDuka->name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>{{ __('dashboard.to') }}:</strong> {{ $lastStockTransfer->toDuka->name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>{{ __('dashboard.transferred_by') }}:</strong> {{ $lastStockTransfer->transferredBy->name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>{{ __('dashboard.date') }}:</strong> {{ $lastStockTransfer->created_at->format('M d, Y H:i') }}</p>
                            <p class="mb-0"><strong>{{ __('dashboard.reason') }}:</strong> {{ $lastStockTransfer->reason ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>{{ __('dashboard.products_transferred') }}</h6>
                            @if($lastStockTransfer->items->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>{{ __('dashboard.product') }}</th>
                                                <th>{{ __('dashboard.quantity') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($lastStockTransfer->items as $item)
                                                <tr>
                                                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                                                    <td>{{ $item->quantity }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">{{ __('dashboard.no_product_details') }}</p>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="text-muted text-center">No stock transfers found.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Sales and Low Stock Section -->
<div class="row mt-4">
</div>

<!-- Recent Sales and Low Stock Section -->
<div class="row mt-4">
    <!-- Recent Sales -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{ __('dashboard.recent_sales') }}</h5>
            </div>
            <div class="card-body">
                @if($recentSales->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('dashboard.customer') }}</th>
                                    <th>{{ __('dashboard.amount') }}</th>
                                    <th>{{ __('dashboard.date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentSales as $sale)
                                    <tr>
                                        <td>{{ $sale->customer->name ?? __('dashboard.walk_in_customer') }}</td>
                                        <td>{{ number_format($sale->total_amount, 2) }}</td>
                                        <td>{{ $sale->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center">{{ __('dashboard.no_recent_sales') }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Low Stock Products -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{ __('dashboard.low_stock_alert') }}</h5>
            </div>
            <div class="card-body">
                @if($lowStockProducts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('dashboard.product') }}</th>
                                    <th>{{ __('dashboard.stock_level') }}</th>
                                    <th>{{ __('dashboard.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowStockProducts as $product)
                                    @php
                                        $totalStock = $product->stocks->sum('quantity');
                                        $status = $totalStock == 0 ? __('dashboard.out_of_stock') : __('dashboard.low_stock');
                                        $statusClass = $totalStock == 0 ? 'danger' : 'warning';
                                    @endphp
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $totalStock }}</td>
                                        <td><span class="badge bg-{{ $statusClass }}">{{ $status }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center">{{ __('dashboard.well_stocked') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

