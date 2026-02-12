<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Proforma Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 15mm;
            size: A4;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
        }
        .container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
        }
        .company-info {
            flex: 1;
        }
        .company-info h2 {
            margin: 0 0 5px 0;
            font-size: 24px;
            font-weight: 600;
        }
        .company-info p {
            margin: 2px 0;
            font-size: 11px;
        }
        .logo {
            max-width: 80px;
            max-height: 80px;
            border-radius: 50%;
            border: 3px solid white;
            margin-bottom: 10px;
        }
        .invoice-info {
            text-align: right;
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
        }
        .invoice-info h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: 700;
        }
        .invoice-info p {
            margin: 3px 0;
            font-size: 11px;
        }
        .info-section {
            display: table;
            width: 100%;
            margin: 20px 0;
        }
        .info-box {
            display: table-cell;
            width: 50%;
            padding: 15px;
            vertical-align: top;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            color: #495057;
            font-size: 14px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 5px;
        }
        .info-box p {
            margin: 3px 0;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 6px;
            overflow: hidden;
        }
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e9ecef;
            font-size: 11px;
        }
        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .totals {
            width: 300px;
            margin-left: auto;
            margin-top: 20px;
        }
        .totals table {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 8px;
            overflow: hidden;
        }
        .totals th {
            background: #28a745;
            color: white;
            padding: 12px;
            font-size: 12px;
        }
        .totals td {
            padding: 10px 12px;
            text-align: right;
            font-weight: 600;
        }
        .totals tr:last-child {
            background: #28a745;
            color: white;
        }
        .totals tr:last-child td {
            font-size: 14px;
            font-weight: 700;
        }
        .notes {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .notes h3 {
            margin: 0 0 8px 0;
            color: #856404;
            font-size: 14px;
        }
        .notes p {
            margin: 0;
            color: #856404;
            font-size: 11px;
        }
        .footer {
            margin-top: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            border-radius: 0 0 10px 10px;
        }
        .footer p {
            margin: 5px 0;
            font-size: 11px;
        }
        .terms {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .terms h4 {
            margin: 0 0 10px 0;
            color: #495057;
            font-size: 13px;
        }
        .terms ul {
            margin: 0;
            padding-left: 20px;
        }
        .terms li {
            font-size: 10px;
            margin: 3px 0;
            color: #6c757d;
        }
        .signature-area {
            display: table;
            width: 100%;
            margin: 40px 0;
        }
        .signature {
            display: table-cell;
            text-align: center;
            width: 50%;
            padding: 20px;
        }
        .signature-line {
            border-bottom: 2px solid #667eea;
            width: 200px;
            margin: 0 auto 10px;
        }
        .signature-label {
            font-size: 11px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-info">
                @if($account && $account->logo)
                    <img src="{{ $account->logo_url }}" alt="Logo" class="logo">
                @endif
                <h2>{{ $account->company_name ?? 'stockflowkp Solutions' }}</h2>
                <p>{{ $account->address ?? '123 Business Street, City, Country' }}</p>
                <p>Phone: {{ $account->phone ?? '+255 123 456 789' }}</p>
                <p>Email: {{ $account->email ?? 'info@stockflowkp.com' }}</p>
                <p>Website: {{ $account->website ?? 'www.stockflowkp.com' }}</p>
            </div>
            <div class="invoice-info">
                <h1>PROFORMA INVOICE</h1>
                <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                <p><strong>Date:</strong> {{ $invoice->invoice_date->format('d/m/Y') }}</p>
                <p><strong>Valid Until:</strong> {{ $invoice->valid_until ? $invoice->valid_until->format('d/m/Y') : 'N/A' }}</p>
            </div>
        </div>

        <div class="info-section">
            <div class="info-box">
                <h3>🏪 Duka Information</h3>
                <p><strong>Name:</strong> {{ $invoice->duka->name }}</p>
                <p><strong>Location:</strong> {{ $invoice->duka->location ?? 'N/A' }}</p>
            </div>
            <div class="info-box">
                <h3>👤 Bill To</h3>
                <p><strong>Customer:</strong> {{ $invoice->customer->name }}</p>
                <p><strong>Phone:</strong> {{ $invoice->customer->phone }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th style="width: 80px; text-align: center;">Qty</th>
                    <th style="width: 100px; text-align: right;">Price</th>
                    <th style="width: 100px; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td><strong>{{ $item->product_name }}</strong></td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: right;">{{ number_format($item->unit_price, 2) }} {{ $invoice->currency }}</td>
                    <td style="text-align: right;"><strong>{{ number_format($item->total_price, 2) }} {{ $invoice->currency }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td><strong>Subtotal:</strong></td>
                    <td>{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</td>
                </tr>
                <tr>
                    <td><strong>Tax:</strong></td>
                    <td>{{ number_format($invoice->tax_amount, 2) }} {{ $invoice->currency }}</td>
                </tr>
                <tr>
                    <td><strong>Discount:</strong></td>
                    <td>{{ number_format($invoice->discount_amount, 2) }} {{ $invoice->currency }}</td>
                </tr>
                <tr>
                    <td><strong>Total:</strong></td>
                    <td>{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</td>
                </tr>
            </table>
        </div>

        @if($invoice->notes)
        <div class="notes">
            <h3>📝 Notes</h3>
            <p>{{ $invoice->notes }}</p>
        </div>
        @endif

        <div class="terms">
            <h4>📋 Terms & Conditions</h4>
            <ul>
                <li>Payment is due within 30 days of invoice date</li>
                <li>Late payments may incur additional charges</li>
                <li>All goods remain property of seller until paid in full</li>
                <li>This proforma invoice is valid for {{ $invoice->valid_days ?? 30 }} days</li>
            </ul>
        </div>

        <div class="signature-area">
            <div class="signature">
                <div class="signature-line"></div>
                <div class="signature-label">Authorized Signature</div>
            </div>
            <div class="signature">
                <div class="signature-line"></div>
                <div class="signature-label">Customer Signature</div>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Generated on {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
