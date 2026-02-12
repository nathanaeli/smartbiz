@extends('layouts.app')

@section('title', 'Proforma Invoice - ' . $invoice->invoice_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Proforma Invoice</h4>
                        <div>
                            <a href="{{ route('proforma.index') }}" class="btn btn-secondary btn-sm me-2">
                                <i class="fas fa-arrow-left me-1"></i>Back to Invoices
                            </a>
                            <button onclick="window.print()" class="btn btn-primary btn-sm">
                                <i class="fas fa-print me-1"></i>Print
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div id="invoice-content">
                        <!-- Company Header -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                @if($invoice->tenant->tenantAccount && $invoice->tenant->tenantAccount->logo)
                                    <img src="{{ $invoice->tenant->tenantAccount->logo_url }}" alt="Company Logo" class="mb-3" style="max-height: 80px;">
                                @endif
                                <h3 class="mb-1">{{ $invoice->tenant->tenantAccount->company_name ?? $invoice->tenant->name }}</h3>
                                @if($invoice->tenant->tenantAccount)
                                    <p class="mb-0">{{ $invoice->tenant->tenantAccount->address }}</p>
                                    @if($invoice->tenant->tenantAccount->phone)
                                        <p class="mb-0">Phone: {{ $invoice->tenant->tenantAccount->phone }}</p>
                                    @endif
                                    @if($invoice->tenant->tenantAccount->email)
                                        <p class="mb-0">Email: {{ $invoice->tenant->tenantAccount->email }}</p>
                                    @endif
                                @endif
                            </div>
                            <div class="col-md-6 text-end">
                                <h2 class="mb-1">PROFORMA INVOICE</h2>
                                <p class="mb-0"><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                                <p class="mb-0"><strong>Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</p>
                                @if($invoice->valid_until)
                                    <p class="mb-0"><strong>Valid Until:</strong> {{ $invoice->valid_until->format('M d, Y') }}</p>
                                @endif
                                <p class="mb-0"><strong>Status:</strong>
                                    <span class="badge bg-{{ $invoice->status === 'draft' ? 'secondary' : ($invoice->status === 'sent' ? 'primary' : ($invoice->status === 'approved' ? 'success' : 'danger')) }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Bill To / Ship To -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="border p-3 rounded">
                                    <h5 class="mb-3">Bill To:</h5>
                                    <p class="mb-1"><strong>{{ $invoice->customer->name }}</strong></p>
                                    @if($invoice->customer->email)
                                        <p class="mb-1">{{ $invoice->customer->email }}</p>
                                    @endif
                                    @if($invoice->customer->phone)
                                        <p class="mb-1">{{ $invoice->customer->phone }}</p>
                                    @endif
                                    @if($invoice->customer->address)
                                        <p class="mb-0">{{ $invoice->customer->address }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border p-3 rounded">
                                    <h5 class="mb-3">Duka Information:</h5>
                                    <p class="mb-1"><strong>{{ $invoice->duka->name }}</strong></p>
                                    <p class="mb-1">{{ $invoice->duka->location }}</p>
                                    <p class="mb-0">Manager: {{ $invoice->duka->manager_name }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Items -->
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>Description</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $item->product_name }}</strong>
                                            @if($item->product_sku)
                                                <br><small class="text-muted">SKU: {{ $item->product_sku }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $item->description ?? '-' }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end">${{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Totals -->
                        <div class="row">
                            <div class="col-md-8">
                                @if($invoice->notes)
                                    <div class="border p-3 rounded">
                                        <h6>Notes:</h6>
                                        <p class="mb-0">{{ $invoice->notes }}</p>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <div class="border p-3 rounded">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <strong>${{ number_format($invoice->subtotal, 2) }}</strong>
                                    </div>
                                    @if($invoice->tax_amount > 0)
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Tax:</span>
                                            <strong>${{ number_format($invoice->tax_amount, 2) }}</strong>
                                        </div>
                                    @endif
                                    @if($invoice->discount_amount > 0)
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Discount:</span>
                                            <strong>-${{ number_format($invoice->discount_amount, 2) }}</strong>
                                        </div>
                                    @endif
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <span class="h5 mb-0">Total:</span>
                                        <strong class="h5 mb-0 text-primary">${{ number_format($invoice->total_amount, 2) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="row mt-4">
                            <div class="col-12 text-center text-muted">
                                <small>
                                    This is a proforma invoice and not a final bill. Prices and availability are subject to change.
                                    @if($invoice->valid_until)
                                        Valid until {{ $invoice->valid_until->format('M d, Y') }}.
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #invoice-content, #invoice-content * {
        visibility: visible;
    }
    #invoice-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .card-header, .card-footer, .btn {
        display: none !important;
    }
    .card-body {
        border: none !important;
    }
}
</style>
@endpush
