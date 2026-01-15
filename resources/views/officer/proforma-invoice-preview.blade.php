@extends('layouts.officer')

@section('content')
<div class="container-fluid card">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Proforma Invoice Created</h1>
                    <p class="text-muted">Invoice has been saved successfully. You can now print it.</p>
                </div>
                <div>
                    <a href="{{ route('officer.proformainvoice') }}" class="btn btn-secondary me-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                            <path d="M19 12H5M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Back to Form
                    </a>
                    <button onclick="window.print()" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                            <path d="M17 17H7V7H17V17ZM17 3H5C3.89 3 3 3.89 3 5V19C3 20.11 3.89 21 5 21H19C20.11 21 21 20.11 21 19V7L17 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Print Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-5" id="invoice-content">
                    <!-- Invoice Header -->
                    <div class="row mb-4">
                        <div class="col-6">
                            <h2 class="mb-0">PROFORMA INVOICE</h2>
                            <p class="text-muted mb-0">Invoice #: {{ $invoiceData['invoice_number'] }}</p>
                            <p class="text-muted mb-0">Date: {{ \Carbon\Carbon::parse($invoiceData['invoice_date'])->format('d/m/Y') }}</p>
                            <p class="text-muted mb-0">Valid Until: {{ \Carbon\Carbon::parse($invoiceData['valid_until'])->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-6 text-end">
                            @php
                                $tenantUser = \App\Models\User::find($invoiceData['tenant_id']);
                                $tenantAccount = $tenantUser ? $tenantUser->tenantAccount : null;
                            @endphp
                            @if($tenantAccount)
                                <h4 class="mb-1">{{ $tenantAccount->company_name ?? $tenantUser->name }}</h4>
                                @if($tenantAccount->address)
                                    <p class="mb-0">{{ $tenantAccount->address }}</p>
                                @endif
                                @if($tenantAccount->phone)
                                    <p class="mb-0">Phone: {{ $tenantAccount->phone }}</p>
                                @endif
                                @if($tenantAccount->email)
                                    <p class="mb-0">Email: {{ $tenantAccount->email }}</p>
                                @endif
                            @elseif($tenantUser)
                                <h4 class="mb-1">{{ $tenantUser->name }}</h4>
                                @if($tenantUser->email)
                                    <p class="mb-0">Email: {{ $tenantUser->email }}</p>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Customer and Duka Information -->
                    <div class="row mb-4">
                        <div class="col-6">
                            <h5>Bill To:</h5>
                            <strong>{{ $invoiceData['customer']->name }}</strong><br>
                            @if($invoiceData['customer']->phone)
                                Phone: {{ $invoiceData['customer']->phone }}<br>
                            @endif
                            @if($invoiceData['customer']->email)
                                Email: {{ $invoiceData['customer']->email }}<br>
                            @endif
                            @if($invoiceData['customer']->address)
                                {{ $invoiceData['customer']->address }}
                            @endif
                        </div>
                        <div class="col-6">
                            <h5>From Duka:</h5>
                            <strong>{{ $invoiceData['duka']->name }}</strong><br>
                            @if($invoiceData['duka']->location)
                                Location: {{ $invoiceData['duka']->location }}<br>
                            @endif
                            @if($invoiceData['duka']->phone)
                                Phone: {{ $invoiceData['duka']->phone }}
                            @endif
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>Description</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoiceData['items'] as $index => $item)
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
                                            <td class="text-end">{{ $invoiceData['currency'] }} {{ number_format($item->unit_price, 2) }}</td>
                                            <td class="text-end">{{ $invoiceData['currency'] }} {{ number_format($item->total_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Invoice Summary -->
                    <div class="row">
                        <div class="col-8">
                            @if($invoiceData['notes'])
                                <h6>Notes:</h6>
                                <p class="text-muted">{{ $invoiceData['notes'] }}</p>
                            @endif
                        </div>
                        <div class="col-4">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Subtotal:</strong></td>
                                    <td class="text-end">{{ $invoiceData['currency'] }} {{ number_format($invoiceData['subtotal'], 2) }}</td>
                                </tr>
                                @if($invoiceData['tax_amount'] > 0)
                                    <tr>
                                        <td><strong>Tax:</strong></td>
                                        <td class="text-end">{{ $invoiceData['currency'] }} {{ number_format($invoiceData['tax_amount'], 2) }}</td>
                                    </tr>
                                @endif
                                @if($invoiceData['discount_amount'] > 0)
                                    <tr>
                                        <td><strong>Discount:</strong></td>
                                        <td class="text-end">-{{ $invoiceData['currency'] }} {{ number_format($invoiceData['discount_amount'], 2) }}</td>
                                    </tr>
                                @endif
                                <tr class="border-top">
                                    <td><strong class="h5">Total:</strong></td>
                                    <td class="text-end"><strong class="h5">{{ $invoiceData['currency'] }} {{ number_format($invoiceData['total_amount'], 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row mt-5">
                        <div class="col-12 text-center text-muted">
                            <small>This proforma invoice has been saved to the database with Invoice #: {{ $invoiceData['invoice_number'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
    .btn, .d-flex, .justify-content-between {
        display: none !important;
    }
}
</style>
@endsection
