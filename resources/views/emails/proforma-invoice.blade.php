<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proforma Invoice - {{ $proformaInvoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 20px;
        }
        .invoice-details, .company-details {
            flex: 1;
        }
        .invoice-details h3, .company-details h3 {
            margin: 0 0 15px 0;
            color: #007bff;
            font-size: 18px;
        }
        .invoice-details p, .company-details p {
            margin: 5px 0;
            font-size: 14px;
        }
        .customer-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .customer-info h4 {
            margin: 0 0 15px 0;
            color: #495057;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        .table .text-right {
            text-align: right;
        }
        .table .text-center {
            text-align: center;
        }
        .totals {
            float: right;
            width: 300px;
            margin-top: 20px;
        }
        .totals table {
            width: 100%;
        }
        .totals .total-row {
            background-color: #007bff;
            color: white;
            font-weight: 600;
        }
        .notes {
            margin-top: 40px;
            padding: 20px;
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
        }
        .notes h5 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        .footer p {
            margin: 5px 0;
            font-size: 14px;
            color: #6c757d;
        }
        .validity-notice {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        @media (max-width: 600px) {
            .container {
                margin: 10px;
            }
            .header {
                padding: 20px;
            }
            .content {
                padding: 20px;
            }
            .invoice-header {
                flex-direction: column;
            }
            .totals {
                float: none;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📄 Proforma Invoice</h1>
            <p>{{ $proformaInvoice->duka->name }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Invoice Header -->
            <div class="invoice-header">
                <div class="invoice-details">
                    <h3>Invoice Details</h3>
                    <p><strong>Invoice Number:</strong> {{ $proformaInvoice->invoice_number }}</p>
                    <p><strong>Invoice Date:</strong> {{ $proformaInvoice->invoice_date->format('d/m/Y') }}</p>
                    <p><strong>Valid Until:</strong> {{ $proformaInvoice->valid_until->format('d/m/Y') }}</p>
                    <p><strong>Currency:</strong> {{ $proformaInvoice->currency }}</p>
                </div>
                <div class="company-details">
                    <h3>From</h3>
                    <p><strong>{{ $proformaInvoice->duka->name }}</strong></p>
                    <p>{{ $proformaInvoice->duka->location ?? 'Location not specified' }}</p>
                    @if($proformaInvoice->tenant && $proformaInvoice->tenant->tenantAccount)
                        <p>{{ $proformaInvoice->tenant->tenantAccount->phone ?? '' }}</p>
                        <p>{{ $proformaInvoice->tenant->tenantAccount->email ?? '' }}</p>
                    @endif
                </div>
            </div>

            <!-- Customer Info -->
            <div class="customer-info">
                <h4>Bill To</h4>
                <p><strong>{{ $proformaInvoice->customer->name }}</strong></p>
                <p>{{ $proformaInvoice->customer->phone ?? '' }}</p>
                <p>{{ $proformaInvoice->customer->email ?? '' }}</p>
                <p>{{ $proformaInvoice->customer->address ?? '' }}</p>
            </div>

            <!-- Validity Notice -->
            <div class="validity-notice">
                <strong>⚠️ This proforma invoice is valid until {{ $proformaInvoice->valid_until->format('d/m/Y') }}</strong><br>
                <small>Please confirm your order before the validity period expires.</small>
            </div>

            <!-- Items Table -->
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($proformaInvoice->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->product_name }}</strong>
                                @if($item->description)
                                    <br><small class="text-muted">{{ $item->description }}</small>
                                @endif
                                <br><small class="text-muted">SKU: {{ $item->product_sku }}</small>
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">{{ $proformaInvoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-right">{{ $proformaInvoice->currency }} {{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totals -->
            <div class="totals">
                <table>
                    <tr>
                        <td>Subtotal:</td>
                        <td class="text-right">{{ $proformaInvoice->currency }} {{ number_format($proformaInvoice->subtotal, 2) }}</td>
                    </tr>
                    @if($proformaInvoice->tax_amount > 0)
                        <tr>
                            <td>Tax:</td>
                            <td class="text-right">{{ $proformaInvoice->currency }} {{ number_format($proformaInvoice->tax_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if($proformaInvoice->discount_amount > 0)
                        <tr>
                            <td>Discount:</td>
                            <td class="text-right">-{{ $proformaInvoice->currency }} {{ number_format($proformaInvoice->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="total-row">
                        <td><strong>Total Amount:</strong></td>
                        <td class="text-right"><strong>{{ $proformaInvoice->currency }} {{ number_format($proformaInvoice->total_amount, 2) }}</strong></td>
                    </tr>
                </table>
            </div>
            <div style="clear: both;"></div>

            <!-- Notes -->
            @if($proformaInvoice->notes)
                <div class="notes">
                    <h5>📝 Notes</h5>
                    <p>{{ $proformaInvoice->notes }}</p>
                </div>
            @endif

            <!-- Call to Action -->
            <div style="text-align: center; margin: 30px 0;">
                <p style="font-size: 16px; color: #495057;">
                    Thank you for your business! Please contact us if you have any questions about this proforma invoice.
                </p>
                <div style="margin: 20px 0;">
                    <a href="mailto:{{ $proformaInvoice->tenant->email ?? 'contact@smartbiz.com' }}"
                       style="background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: 600; display: inline-block; margin: 5px;">
                        📧 Contact Us
                    </a>
                    <a href="tel:{{ $proformaInvoice->tenant->tenantAccount->phone ?? '+255123456789' }}"
                       style="background-color: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: 600; display: inline-block; margin: 5px;">
                        📞 Call Now
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ $proformaInvoice->duka->name }}</strong></p>
            <p>This is a computer-generated proforma invoice and does not require a signature.</p>
            @if($proformaInvoice->tenant && $proformaInvoice->tenant->tenantAccount)
                <p>{{ $proformaInvoice->tenant->tenantAccount->company_name ?? '' }} • {{ $proformaInvoice->tenant->tenantAccount->phone ?? '' }}</p>
            @endif
            <p>Generated on {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
