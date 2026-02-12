<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $duka->name }} - Inventory List</title>
    <style>
        @page {
            margin: 20px 30px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }

        /* Layout */
        .header-bg {
            position: absolute;
            top: -50px;
            left: -50px;
            right: -50px;
            height: 120px;
            background-color: #4e73df;
            color: white;
            z-index: -1;
        }

        .header {
            margin-bottom: 30px;
            padding-top: 10px;
            color: #fff;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header p {
            margin: 2px 0;
            opacity: 0.9;
            font-size: 12px;
        }

        .sub-header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .meta-info {
            display: table-cell;
            vertical-align: bottom;
        }

        .meta-date {
            text-align: right;
            font-size: 11px;
            color: #666;
            font-weight: bold;
        }

        /* Summary Cards */
        .summary {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: separate;
            border-spacing: 10px 0;
        }

        .card {
            background: #f8f9fc;
            border-left: 4px solid #4e73df;
            padding: 10px 15px;
            border-radius: 4px;
            width: 32%;
            display: inline-block;
            vertical-align: top;
            box-sizing: border-box;
        }

        .card.risk {
            border-left-color: #e74a3b;
            background: #fff5f5;
        }

        .card.success {
            border-left-color: #1cc88a;
            background: #f0fdf4;
        }

        .card-title {
            font-size: 9px;
            text-transform: uppercase;
            color: #888;
            letter-spacing: 0.5px;
            font-weight: bold;
            margin-bottom: 4px;
            display: block;
        }

        .card-value {
            font-size: 16px;
            font-weight: 800;
            color: #333;
            display: block;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background-color: #f1f3f9;
            color: #4e73df;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            padding: 10px 8px;
            border-bottom: 2px solid #e3e6f0;
            text-align: left;
        }

        td {
            border-bottom: 1px solid #eee;
            padding: 8px;
            vertical-align: middle;
            color: #555;
        }

        tr:nth-child(even) {
            background-color: #fbfbfc;
        }

        .product-name {
            color: #2e3546;
            font-weight: 600;
            font-size: 12px;
        }

        .sku {
            font-size: 9px;
            color: #858796;
            display: block;
            margin-top: 2px;
        }

        .text-right {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        /* Badges */
        .badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
        }

        .bg-success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .bg-warning {
            background: #fff3cd;
            color: #856404;
        }

        .bg-danger {
            background: #f8d7da;
            color: #721c24;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: -10px;
            left: -30px;
            right: -30px;
            height: 40px;
            background-color: #f8f9fc;
            border-top: 2px solid #4e73df;
            padding: 10px 30px;
            font-size: 9px;
            color: #666;
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            text-align: left;
            vertical-align: middle;
            width: 40%;
        }

        .footer-center {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
            width: 20%;
            font-weight: bold;
            color: #4e73df;
        }

        .footer-right {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
            width: 40%;
        }
    </style>
</head>

<body>
    <!-- Prepare Data -->
    @php
    $totalItems = $products->count();
    $totalValue = 0;
    $lowStockCount = 0;
    $outOfStockCount = 0;

    // First pass to calculate totals
    foreach($products as $p) {
    $s = $p->stocks->first();
    $q = $s ? $s->quantity : 0;
    $totalValue += ($q * $p->selling_price);
    if($q <= 0) $outOfStockCount++;
        elseif($q <=10) $lowStockCount++;
        }
        @endphp

        <div class="header-bg">
        </div>

        <div class="header">
            <h1>{{ $duka->name }}</h1>
            <p>Managed Inventory Report</p>
        </div>

        <div class="sub-header">
            <div class="meta-info">
                <p style="font-size: 10px; color: #666;">
                    <strong>Tenant ID:</strong> {{ $duka->tenant_id ?? 'N/A' }}<br>
                    <strong>Location:</strong> {{ $duka->location ?? 'Headquarters' }}
                </p>
            </div>
            <div class="meta-date">
                GENERATED: {{ now()->format('d M Y, h:i A') }}
            </div>
        </div>

        <!-- Executive Summary -->
        <div style="width: 100%; margin-bottom: 20px;">
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="width: 32%; padding: 0; border: none;">
                        <div class="card">
                            <span class="card-title">Total Products</span>
                            <span class="card-value">{{ number_format($totalItems) }}</span>
                        </div>
                    </td>
                    <td style="width: 2%; border: none;"></td>
                    <td style="width: 32%; padding: 0; border: none;">
                        <div class="card success">
                            <span class="card-title">Inventory Value</span>
                            <span class="card-value">{{ number_format($totalValue, 2) }}</span>
                        </div>
                    </td>
                    <td style="width: 2%; border: none;"></td>
                    <td style="width: 32%; padding: 0; border: none;">
                        <div class="card {{ ($lowStockCount + $outOfStockCount) > 0 ? 'risk' : 'success' }}">
                            <span class="card-title">Stock Alerts</span>
                            <span class="card-value">{{ $lowStockCount + $outOfStockCount }} <span style="font-size: 10px; font-weight: normal; color: #666;">(Low/Out)</span></span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="40%">Item Profile</th>
                    <th width="15%">Category</th>
                    <th width="10%" class="text-right">Price</th>
                    <th width="10%" class="text-right">Qty</th>
                    <th width="10%" class="text-right">Total</th>
                    <th width="10%" class="text-right">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $index => $product)
                @php
                $stock = $product->stocks->first();
                $qty = $stock ? $stock->quantity : 0;
                $rowVal = $qty * $product->selling_price;

                $badgeClass = 'bg-success';
                $badgeText = 'IN STOCK';
                if ($qty <= 0) {
                    $badgeClass='bg-danger' ;
                    $badgeText='NO STOCK' ;
                    } elseif ($qty <=10) {
                    $badgeClass='bg-warning' ;
                    $badgeText='LOW' ;
                    }
                    @endphp
                    <tr>
                    <td style="color: #aaa;">{{ $index + 1 }}</td>
                    <td>
                        <span class="product-name">{{ $product->name }}</span>
                        <span class="sku">{{ $product->sku }}</span>
                    </td>
                    <td>{{ $product->category->name ?? 'General' }}</td>
                    <td class="text-right">{{ number_format($product->selling_price, 2) }}</td>
                    <td class="text-right" style="font-weight: bold; color: #333;">{{ $qty }}</td>
                    <td class="text-right">{{ number_format($rowVal, 2) }}</td>
                    <td class="text-right">
                        <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                    </td>
                    </tr>
                    @endforeach
            </tbody>
        </table>

        <div class="footer">
            <div class="footer-left">
                SmartBiz System &bull; {{ date('Y') }}<br>
                <span style="color: #999">Confidential Internal Document</span>
            </div>
            <div class="footer-center">
                SMARTBIZ
            </div>
            <div class="footer-right">
                Page <script type="text/php">if (isset($pdf)) { echo $pdf->get_page_number() . " of " . $pdf->get_page_count(); }</script>
            </div>
        </div>
</body>

</html>