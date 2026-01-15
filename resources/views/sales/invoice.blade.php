<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $sale->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
        .invoice-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .invoice-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
        }
        .company-logo {
            max-width: 120px;
            max-height: 80px;
        }
        .invoice-details {
            border-bottom: 2px solid #dee2e6;
            padding: 20px 0;
        }
        .table th {
            background-color: #f8f9fa;
            border-top: none;
        }
        .total-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }
        .footer-text {
            text-align: center;
            color: #6c757d;
            font-size: 12px;
            margin-top: 30px;
        }
        @media print {
            body { background: white; }
            .invoice-container { box-shadow: none; margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="invoice-container">
            <!-- Header -->
            <div class="invoice-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        @if($tenantAccount && $tenantAccount->logo)
                            <img src="{{ $tenantAccount->logo_url }}" alt="Company Logo" class="company-logo mb-3">
                        @endif
                        <h2 class="mb-1">{{ $tenantAccount->company_name ?? 'SMARTBIZ' }}</h2>
                        @if($tenantAccount)
                            <p class="mb-0">{{ $tenantAccount->address ?? '' }}</p>
                            <p class="mb-0">{{ $tenantAccount->phone ?? '' }} | {{ $tenantAccount->email ?? '' }}</p>
                        @endif
                    </div>
                    <div class="col-md-6 text-end">
                        <h1 class="mb-1">INVOICE</h1>
                        <h4 class="mb-0">#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</h4>
                        <p class="mb-0">Date: {{ $sale->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Invoice Details -->
            <div class="p-4">
                <div class="invoice-details">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Bill To:</h5>
                            <strong>{{ $sale->customer ? $sale->customer->name : 'Walk-in Customer' }}</strong><br>
                            @if($sale->customer && $sale->customer->phone)
                                Phone: {{ $sale->customer->phone }}<br>
                            @endif
                            @if($sale->customer && $sale->customer->email)
                                Email: {{ $sale->customer->email }}<br>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h5>Sale Information:</h5>
                            <strong>Duka:</strong> {{ $sale->duka->name }}<br>
                            <strong>Type:</strong> {{ $sale->is_loan ? 'Loan Sale' : 'Cash Sale' }}<br>
                            <strong>Status:</strong>
                            @if($sale->is_loan)
                                <span class="badge bg-warning">{{ $sale->payment_status }}</span>
                            @else
                                <span class="badge bg-success">Paid</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="mt-4">
                    <h5>Items Purchased:</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Discount</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->saleItems as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->product->name }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ number_format($item->unit_price, 2) }} TSH</td>
                                        <td>{{ number_format($item->discount_amount, 2) }} TSH</td>
                                        <td><strong>{{ number_format($item->total, 2) }} TSH</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Totals -->
                <div class="row mt-4">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <div class="total-section">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong>{{ number_format($sale->total_amount + $sale->discount_amount, 2) }} TSH</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Discount:</span>
                                <strong>{{ number_format($sale->discount_amount, 2) }} TSH</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span><strong>Total Amount:</strong></span>
                                <strong class="text-primary">{{ number_format($sale->total_amount, 2) }} TSH</strong>
                            </div>
                            @if($sale->is_loan)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Paid:</span>
                                    <strong>{{ number_format($sale->total_payments, 2) }} TSH</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span><strong>Remaining Balance:</strong></span>
                                    <strong class="{{ $sale->remaining_balance > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($sale->remaining_balance, 2) }} TSH
                                    </strong>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payment Information for Loans -->
                @if($sale->is_loan && $sale->loanPayments->isNotEmpty())
                    <div class="mt-4">
                        <h6>Payment History:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->loanPayments->sortBy('payment_date') as $payment)
                                        <tr>
                                            <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                            <td>{{ number_format($payment->amount, 2) }} TSH</td>
                                            <td>{{ $payment->notes ?: 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Terms and Conditions -->
                @if($tenantAccount && $tenantAccount->description)
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6>Terms & Conditions:</h6>
                        <p class="mb-0">{{ $tenantAccount->description }}</p>
                    </div>
                @endif

                <!-- Footer -->
                <div class="footer-text">
                    <p>Thank you for your business!</p>
                    @if($tenantAccount && $tenantAccount->website)
                        <p>Visit us at: {{ $tenantAccount->website }}</p>
                    @endif
                    <p>Generated on {{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Print Button -->
        <div class="text-center mt-3 no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Invoice
            </button>
            <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Sale Details
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
