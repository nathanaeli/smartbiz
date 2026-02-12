<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Proforma Invoice {{ $invoice->invoice_number }} - Professional invoice template">
    <title>Proforma Invoice #{{ $invoice->invoice_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page { size: A4; margin: 8mm 10mm; }
        body {
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            line-height: 1.4;
            color: #1e293b;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 210mm;
            min-height: 297mm;
            max-height: 297mm;
            margin: 0 auto;
            background: white;
            overflow: hidden;
            box-shadow: 0 0 30px rgba(0,0,0,0.08);
        }

        header {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            padding: 20px 30px;
            text-align: center;
        }
        .title {
            font-size: 30px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.8px;
        }
        .meta {
            font-size: 14px;
            margin-top: 8px;
            font-weight: 500;
        }

        main { padding: 25px 30px; }

        /* Company + Invoice Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
            background: #f8fafc;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        }
        .info-table td {
            padding: 16px 20px;
            vertical-align: top;
        }
        .logo {
            width: 82px;
            height: 82px;
            border-radius: 18px;
            object-fit: cover;
            border: 5px solid #3b82f6;
            box-shadow: 0 6px 16px rgba(59,130,246,0.3);
        }
        .company-info h1 {
            font-size: 24px;
            margin: 0 0 8px;
            font-weight: 800;
            color: #1e293b;
        }
        .company-info p {
            margin: 5px 0;
            color: #475569;
            font-size: 13.5px;
        }
        .invoice-details {
            background: #1e40af;
            color: white;
            text-align: right;
            border-radius: 12px;
        }
        .invoice-details h2 {
            font-size: 22px;
            margin: 0 0 10px;
            font-weight: 700;
        }

        /* Bill To & Duka */
        .bill-duka {
            width: 100%;
            border-collapse: collapse;
            margin: 22px 0;
            font-size: 13.5px;
        }
        .bill-duka td {
            padding: 16px 20px;
            background: #f1f5f9;
            border-radius: 12px;
            width: 50%;
        }
        .bill-duka h3 {
            margin: 0 0 12px;
            color: #1e40af;
            font-size: 16px;
            font-weight: 700;
        }

        /* Items Table – Clean & Smart */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 13.5px;
            box-shadow: 0 8px 28px rgba(0,0,0,0.09);
            border-radius: 14px;
            overflow: hidden;
        }
        .items-table thead {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
        }
        .items-table th {
            padding: 16px 14px;
            text-align: left;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .items-table th:nth-child(2), .items-table th:nth-child(3), .items-table th:nth-child(4) {
            text-align: center;
        }
        .items-table td {
            padding: 15px 14px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
        }
        .items-table tbody tr:hover {
            background: #f0f7ff;
        }
        .items-table tbody tr:last-child td { border-bottom: none; }
        .product-name { font-weight: 600; color: #1e293b; }
        .qty {
            text-align: center;
            font-weight: 700;
            color: #1e40af;
            background: #dbeafe;
            padding: 6px 16px;
            border-radius: 20px;
            display: inline-block;
            min-width: 50px;
        }
        .amount {
            text-align: right;
            font-weight: 600;
            white-space: nowrap;
        }

        /* Totals – Stunning Highlight */
        .totals-box {
            width: 400px;
            margin: 15px 0 0 auto;
            border: 3px solid #10b981;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 12px 35px rgba(16,185,129,0.2);
        }
        .totals-box table {
            width: 100%;
            background: #f0fdf4;
        }
        .totals-box td {
            padding: 14px 22px;
            font-size: 15px;
        }
        .totals-box .final {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            font-size: 20px !important;
            font-weight: 800;
        }

        /* Signature */
        .signature {
            display: flex;
            justify-content: space-between;
            margin: 35px 0 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .sig {
            text-align: center;
            flex: 1;
        }
        .line {
            width: 200px;
            height: 4px;
            background: #3b82f6;
            margin: 0 auto 12px;
            border-radius: 3px;
        }

        footer {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            text-align: center;
            padding: 18px;
            font-size: 13px;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            font-weight: 500;
        }

        /* Accessibility & Print Optimization */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        @media print {
            body, .container { margin: 0 !important; padding: 0 !important; box-shadow: none !important; }
            .no-print { display: none !important; }
            .action-buttons { display: none !important; }
            @page { margin: 6mm; }

            /* Ensure good contrast for printing */
            .items-table thead { background: #333 !important; color: white !important; }
            .totals-box { border: 2px solid #333 !important; }
            .totals-box .final { background: #333 !important; }
        }
    </style>
</head>
<body>

<div class="container">

    <!-- Document Header -->
    <header class="document-header" role="banner">
        <h1 class="title" aria-label="Document Type">PROFORMA INVOICE</h1>
        <div class="meta">
            Invoice #<strong>{{ $invoice->invoice_number }}</strong> •
            {{ $invoice->invoice_date->format('d F Y') }} •
            Valid Until: <strong>{{ $invoice->valid_until?->format('d F Y') ?? '30 Days' }}</strong>
        </div>
    </header>

    <!-- Main Content -->
    <main class="invoice-main" role="main">

        <!-- Company & Invoice Information Section -->
        <section class="company-invoice-section" aria-labelledby="company-info-heading">
            <table class="info-table">
                <tr>
                    <td style="width:90px; text-align:center;">
                        @if($account?->logo_url)
                            <img src="{{ $account->logo_url }}"
                                 alt="Company Logo"
                                 class="logo"
                                 loading="lazy">
                        @endif
                    </td>
                    <td class="company-info">
                        <h1 id="company-info-heading">{{ $account->company_name ?? 'STOCKFLOWKP SOLUTIONS LTD' }}</h1>
                        <address>
                            <p><strong>{{ $account->address ?? 'Dar es Salaam, Tanzania' }}</strong></p>
                            <p>Phone: {{ $account->phone ?? '+255 789 000 111' }} • {{ $account->email ?? 'sales@stockflowkp.co.tz' }}</p>
                        </address>
                    </td>
                    <td class="invoice-details">
                        <h2>INVOICE NO.</h2>
                        <h1 style="font-size:32px;margin:0;color:#fbbf24;" aria-label="Invoice Number">{{ $invoice->invoice_number }}</h1>
                        <p>Issue Date: {{ $invoice->invoice_date->format('d M Y') }}</p>
                    </td>
                </tr>
            </table>
        </section>

        <!-- Bill To & Duka Section -->
        <section class="parties-section" aria-labelledby="parties-heading">
            <h2 id="parties-heading" class="sr-only">Parties Involved</h2>
            <table class="bill-duka">
                <tr>
                    <td>
                        <h3>Bill To</h3>
                        <address>
                            <p><strong>{{ $invoice->customer ? $invoice->customer->name : 'N/A' }}</strong></p>
                            <p>Phone: {{ $invoice->customer ? $invoice->customer->phone : 'N/A' }}</p>
                            <p>Email: {{ $invoice->customer ? ($invoice->customer->email ?? 'N/A') : 'N/A' }}</p>
                        </address>
                    </td>
                    <td>
                        <h3>Duka / Shop</h3>
                        <address>
                            <p><strong>{{ $invoice->duka ? $invoice->duka->name : 'N/A' }}</strong></p>
                            <p>{{ $invoice->duka ? ($invoice->duka->location ?? 'N/A') : 'N/A' }}</p>
                        </address>
                    </td>
                </tr>
            </table>
        </section>

        <!-- Items Table Section -->
        <section class="items-section" aria-labelledby="items-heading">
            <h2 id="items-heading" class="sr-only">Invoice Items</h2>
            <table class="items-table">
                <caption>Invoice Items and Pricing Details</caption>
                <thead>
                    <tr>
                        <th scope="col">Description</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Unit Price</th>
                        <th scope="col">Total ({{ $invoice->currency }})</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->items ?? [] as $item)
                    <tr>
                        <td><span class="product-name">{{ $item->product_name ?? 'N/A' }}</span></td>
                        <td><span class="qty" aria-label="Quantity">{{ $item->quantity ?? 0 }}</span></td>
                        <td class="amount">{{ number_format($item->unit_price ?? 0, 2) }}</td>
                        <td class="amount">{{ number_format($item->total_price ?? 0, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted" aria-live="polite">No items found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <!-- Totals Section -->
        <section class="totals-section" aria-labelledby="totals-heading">
            <h2 id="totals-heading" class="sr-only">Invoice Totals</h2>
            <div class="totals-box">
                <table>
                    <tbody>
                        <tr><td>Subtotal</td><td style="text-align:right">{{ number_format($invoice->subtotal, 2) }}</td></tr>
                        <tr><td>Tax</td><td style="text-align:right">{{ number_format($invoice->tax_amount, 2) }}</td></tr>
                        <tr><td>Discount</td><td style="text-align:right">{{ number_format($invoice->discount_amount, 2) }}</td></tr>
                        <tr class="final">
                            <td><strong>TOTAL AMOUNT</strong></td>
                            <td style="text-align:right"><strong>{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Notes Section (if available) -->
        @if($invoice->notes)
        <section class="notes-section" aria-labelledby="notes-heading">
            <h2 id="notes-heading">Additional Notes</h2>
            <div class="notes-content">
                <p>{{ $invoice->notes }}</p>
            </div>
        </section>
        @endif

        <!-- Signature Section -->
        <section class="signature-section" aria-labelledby="signatures-heading">
            <h2 id="signatures-heading" class="sr-only">Signatures</h2>
            <div class="signature">
                <div class="sig">
                    <div class="line" aria-hidden="true"></div>
                    <div>Authorized Signatory</div>
                </div>
                <div class="sig">
                    <div class="line" aria-hidden="true"></div>
                    <div>Customer Acceptance</div>
                </div>
            </div>
        </section>

        <!-- Print Button (Screen Only) -->
        <div class="no-print action-buttons" style="text-align:center;margin:20px 0;">
            <button onclick="window.print()"
                    style="padding:12px 30px;background:#1e40af;color:white;border:none;border-radius:10px;font-weight:700;font-size:15px;cursor:pointer;"
                    aria-label="Print invoice or save as PDF">
                Print / Save as PDF
            </button>
        </div>

    </main>

    <footer>
        <strong>{{ $account->company_name ?? 'stockflowkp Solutions Ltd' }}</strong> • Thank you for your business •
        Generated on {{ now()->format('d F Y \a\t H:i') }}
    </footer>

</div>

</body>
</html>
