<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Sales Summary Report</title>
    <style>
        body {
            font-family: sans-serif;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 15px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
        }

        .meta {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #1f2937;
            border-left: 4px solid #4f46e5;
            padding-left: 10px;
        }

        .cards {
            width: 100%;
            display: table;
            margin-bottom: 20px;
        }

        .card {
            display: table-cell;
            width: 25%;
            padding: 10px;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            text-align: center;
        }

        .card-val {
            font-size: 16px;
            font-weight: bold;
            color: #111;
            margin-top: 5px;
        }

        .card-lbl {
            font-size: 11px;
            text-transform: uppercase;
            color: #6b7280;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 10px;
            text-align: center;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo">Sales Summary Report</div>
        <div class="meta">Generated on: {{ now()->format('M d, Y H:i') }} | Tenant: {{ auth()->user()->tenant->name }}</div>
    </div>

    <div class="cards">
        <div class="card">
            <div class="card-lbl">Total Sales</div>
            <div class="card-val">{{ $sales->count() }}</div>
        </div>
        <div class="card">
            <div class="card-lbl">Total Revenue</div>
            <div class="card-val">{{ number_format($sales->sum('total_amount')) }}</div>
        </div>
        <div class="card">
            <div class="card-lbl">Total Profit</div>
            <div class="card-val">{{ number_format($sales->sum('profit_loss')) }}</div>
        </div>
        <div class="card">
            <div class="card-lbl">Avg Sale</div>
            <div class="card-val">{{ number_format($sales->avg('total_amount')) }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Breakdown by Duka (Shop)</div>
        <table>
            <thead>
                <tr>
                    <th>Duka Name</th>
                    <th class="text-right">Sales Count</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales->groupBy('duka_id') as $dukaId => $group)
                <tr>
                    <td>{{ $group->first()->duka->name ?? 'Unknown' }}</td>
                    <td class="text-right">{{ $group->count() }}</td>
                    <td class="text-right">{{ number_format($group->sum('total_amount'), 2) }}</td>
                    <td class="text-right">{{ number_format($group->sum('profit_loss'), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Breakdown by Payment Type</div>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th class="text-right">Count</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                $cashSales = $sales->where('is_loan', false);
                $loanSales = $sales->where('is_loan', true);
                @endphp
                <tr>
                    <td>Cash / Instant</td>
                    <td class="text-right">{{ $cashSales->count() }}</td>
                    <td class="text-right">{{ number_format($cashSales->sum('total_amount'), 2) }}</td>
                </tr>
                <tr>
                    <td>Loan / Credit</td>
                    <td class="text-right">{{ $loanSales->count() }}</td>
                    <td class="text-right">{{ number_format($loanSales->sum('total_amount'), 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        Confidential Report - Generated by SmartBiz System
    </div>
</body>

</html>