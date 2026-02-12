<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            background: #fff;
            margin: 0;
            padding: 0;
            font-size: 13px;
            line-height: 1.5;
        }

        /* Modern Header with Accent */
        .header-bg {
            background-color: #f8f9fa;
            border-bottom: 4px solid #2c3e50;
            padding: 40px 50px;
            display: table;
            width: 100%;
        }

        .logo-area {
            display: table-cell;
            vertical-align: middle;
            width: 50%;
        }

        .logo-img {
            max-height: 80px;
            max-width: 200px;
        }

        .invoice-title {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
            width: 50%;
        }

        .invoice-title h1 {
            color: #2c3e50;
            font-size: 32px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .invoice-title h3 {
            color: #7f8c8d;
            font-size: 16px;
            margin: 5px 0 0;
            font-weight: normal;
        }

        /* Container for content */
        .content {
            padding: 40px 50px;
        }

        /* Info Grid */
        .info-table {
            width: 100%;
            margin-bottom: 40px;
            border-collapse: collapse;
        }

        .info-table td {
            vertical-align: top;
            width: 33.33%;
        }

        .info-label {
            color: #95a5a6;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 1px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .info-value {
            font-size: 14px;
            color: #2c3e50;
            line-height: 1.4;
        }

        .info-value strong {
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            background-color: #2c3e50;
            color: #fff;
            text-align: left;
            padding: 12px 15px;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        .items-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ecf0f1;
            color: #555;
        }

        .items-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .items-table .text-right {
            text-align: right;
        }

        .items-table .text-center {
            text-align: center;
        }

        /* Totals Section */
        .totals-table {
            width: 40%;
            float: right;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px 0;
            text-align: right;
        }

        .totals-table .label {
            color: #7f8c8d;
            padding-right: 20px;
        }

        .totals-table .value {
            color: #2c3e50;
            font-weight: bold;
            font-size: 14px;
        }

        .totals-table .grand-total {
            border-top: 2px solid #2c3e50;
            border-bottom: 2px double #2c3e50;
            padding: 15px 0;
            margin-top: 10px;
        }

        .totals-table .grand-total .value {
            font-size: 20px;
            color: #27ae60;
        }

        /* Footer */
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
            text-align: center;
            color: #95a5a6;
            font-size: 11px;
            clear: both;
        }

        .footer p {
            margin: 3px 0;
        }

        /* Badges */
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }

        .badge-success {
            background: #e8f8f5;
            color: #27ae60;
        }

        .badge-warning {
            background: #fef9e7;
            color: #f1c40f;
        }

        .badge-danger {
            background: #fdedec;
            color: #c0392b;
        }

        /* Payment History (if needed) */
        .payments-section {
            margin-top: 40px;
            clear: both;
            padding-top: 20px;
        }

        .payments-section h4 {
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    @php
    $currency = $tenantAccount?->currency ?? 'TSH';
    $brandName = $tenantAccount->company_name ?? 'SmartBiz Solution';
    @endphp

    <!-- Header -->
    <div class="header-bg">
        <div class="logo-area">
            @if($tenantAccount && $tenantAccount->logo)
            <img src="{{ public_path('storage/account/'.$tenantAccount->logo) }}" class="logo-img" alt="Logo">
            @else
            <!-- Text Logo Fallback if needed or just empty -->
            <h2 style="margin:0; color:#2c3e50; letter-spacing:1px;">{{ $brandName }}</h2>
            @endif
        </div>
        <div class="invoice-title">
            <h1>Invoice</h1>
            <h3>#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</h3>
            <div style="margin-top: 5px;">
                @if($sale->is_loan)
                <span class="badge badge-warning">Loan Sale - {{ $sale->payment_status }}</span>
                @else
                <span class="badge badge-success">Paid Fully</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="content">

        <!-- Info Grid -->
        <table class="info-table">
            <tr>
                <td>
                    <div class="info-label">From</div>
                    <div class="info-value">
                        <strong>{{ $brandName }}</strong>
                        @if($tenantAccount)
                        {{ $tenantAccount->address }}<br>
                        {{ $tenantAccount->phone }}<br>
                        {{ $tenantAccount->email }}
                        @endif
                    </div>
                </td>
                <td>
                    <div class="info-label">Bill To</div>
                    <div class="info-value">
                        <strong>{{ $sale->customer ? $sale->customer->name : 'Walk-in Customer' }}</strong>
                        @if($sale->customer)
                        @if($sale->customer->phone)<br>{{ $sale->customer->phone }}@endif
                        @if($sale->customer->email)<br>{{ $sale->customer->email }}@endif
                        @if($sale->customer->address)<br>{{ $sale->customer->address }}@endif
                        @endif
                    </div>
                </td>
                <td>
                    <div class="info-label">Details</div>
                    <div class="info-value">
                        <strong>Date:</strong> {{ $sale->created_at->format('M d, Y') }}<br>
                        <strong>Time:</strong> {{ $sale->created_at->format('h:i A') }}<br>
                        <strong>Duka:</strong> {{ $sale->duka->name }}<br>
                        <strong>Served By:</strong> {{ $sale->creator ? $sale->creator->name : 'System' }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 40%;">Item Description</th>
                    <th class="text-center" style="width: 15%;">Qty</th>
                    <th class="text-right" style="width: 20%;">Price</th>
                    <th class="text-right" style="width: 20%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $item->product->name }}
                        @if($item->discount_amount > 0)
                        <br><small style="color: #c0392b; font-size: 10px;">(Disc: {{ number_format($item->discount_amount) }})</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div style="overflow: hidden;">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal</td>
                    <td class="value">{{ number_format($sale->total_amount + $sale->discount_amount, 2) }} {{ $currency }}</td>
                </tr>
                @if($sale->discount_amount > 0)
                <tr>
                    <td class="label">Discount</td>
                    <td class="value" style="color: #c0392b;">- {{ number_format($sale->discount_amount, 2) }} {{ $currency }}</td>
                </tr>
                @endif
                <tr class="grand-total">
                    <td class="label" style="color: #2c3e50; font-weight: bold;">TOTAL DUE</td>
                    <td class="value">{{ number_format($sale->total_amount, 2) }} <small style="font-size: 12px;">{{ $currency }}</small></td>
                </tr>

                @if($sale->is_loan)
                <tr>
                    <td class="label">Amount Paid</td>
                    <td class="value">{{ number_format($sale->total_payments, 2) }} {{ $currency }}</td>
                </tr>
                <tr>
                    <td class="label">Balance</td>
                    <td class="value" style="color: #c0392b;">{{ number_format($sale->remaining_balance, 2) }} {{ $currency }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Loan Payments History -->
        @if($sale->is_loan && $sale->loanPayments->isNotEmpty())
        <div class="payments-section">
            <h4>Payment History</h4>
            <table class="items-table" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th style="background: #ecf0f1; color: #2c3e50;">Date</th>
                        <th style="background: #ecf0f1; color: #2c3e50;">Amount</th>
                        <th style="background: #ecf0f1; color: #2c3e50;">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->loanPayments->sortBy('payment_date') as $payment)
                    <tr>
                        <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                        <td>{{ number_format($payment->amount, 2) }} {{ $currency }}</td>
                        <td>{{ $payment->notes ?: '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            @if($tenantAccount && $tenantAccount->description)
            <p style="font-style: italic; margin-bottom: 20px;">"{{ $tenantAccount->description }}"</p>
            @endif
            <p>Thank you for your business!</p>
            @if($tenantAccount && $tenantAccount->website)
            <p>{{ $tenantAccount->website }}</p>
            @endif
            <p style="font-size: 9px; margin-top: 10px;">Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>
</body>

</html>