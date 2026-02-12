@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4 card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">{{ __('messages.group_profit_loss') }}</h2>
            <p class="text-muted">{{ __('messages.consolidated_financial_overview') }}</p>
        </div>
        <div class="bg-white p-2 rounded shadow-sm border">
            <form action="{{ route('tenant.reports.consolidated_pl') }}" method="GET" class="d-flex align-items-center gap-2">
                <input type="date" name="start_date" value="{{ $start->format('Y-m-d') }}" class="form-control form-control-sm border-0 bg-light">
                <input type="date" name="end_date" value="{{ $end->format('Y-m-d') }}" class="form-control form-control-sm border-0 bg-light">
                <button type="submit" class="btn btn-sm btn-primary px-3">{{ __('messages.update_report') }}</button>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-soft-primary border-start border-primary border-4">
                <h6 class="text-muted small fw-bold">{{ __('messages.total_group_revenue') }}</h6>
                <h3 class="fw-bold text-primary mb-0">{{ number_format($totalRevenue) }} TSH</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-soft-info border-start border-info border-4">
                <h6 class="text-muted small fw-bold">{{ __('messages.total_stock_assets') }}</h6>
                <h3 class="fw-bold text-info mb-0">{{ number_format($totalStockBuyingValue) }} TSH</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 {{ $netProfit >= 0 ? 'bg-soft-success' : 'bg-soft-danger' }} border-start {{ $netProfit >= 0 ? 'border-success' : 'border-danger' }} border-4">
                <h6 class="text-muted small fw-bold">{{ __('messages.group_net_profit') }}</h6>
                <h3 class="fw-bold {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }} mb-0">{{ number_format($netProfit) }} TSH</h3>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-md-5">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div class="text-center mb-4 border-bottom pb-3">
                        <h4 class="fw-bold">{{ __('messages.group_income_statement') }}</h4>
                        <small class="text-muted">{{ __('messages.period') }}: {{ $start->format('d M Y') }} - {{ $end->format('d M Y') }}</small>
                    </div>

                    <div class="py-3 border-bottom d-flex justify-content-between">
                        <span class="fw-bold">{{ __('messages.total_sales_income') }}</span>
                        <span class="text-dark">{{ number_format($totalRevenue) }}</span>
                    </div>
                    <div class="py-3 border-bottom d-flex justify-content-between text-danger italic">
                        <span>{{ __('messages.cost_of_goods_sold_cogs') }}</span>
                        <span>- {{ number_format($cogs) }}</span>
                    </div>
                    <div class="py-3 mb-4 bg-light px-3 rounded d-flex justify-content-between align-items-center">
                        <span class="fw-bold h5 mb-0">{{ __('messages.total_gross_profit') }}</span>
                        <span class="fw-bold h5 mb-0 text-success">{{ number_format($grossProfit) }} TSH</span>
                    </div>

                    <h6 class="text-muted small fw-bold text-uppercase mb-3">{{ __('messages.operating_expenses_combined') }}</h6>
                    @foreach($expenses as $expense)
                        <div class="py-2 border-bottom border-light d-flex justify-content-between small">
                            <span class="text-capitalize">{{ str_replace('_', ' ', $expense->category) }}</span>
                            <span>{{ number_format($expense->total) }}</span>
                        </div>
                    @endforeach
                    <div class="py-3 d-flex justify-content-between text-danger">
                        <span class="fw-bold">{{ __('messages.total_operational_costs') }}</span>
                        <span class="fw-bold">- {{ number_format($totalExpenses) }} TSH</span>
                    </div>

                    <div class="mt-4 p-4 rounded {{ $netProfit >= 0 ? 'bg-success text-white' : 'bg-danger text-white' }} shadow">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="fw-bold mb-0">{{ __('messages.group_net_profit') }}</h2>
                            <h1 class="fw-bold mb-0">{{ number_format($netProfit) }} TSH</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.08); }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.08); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.08); }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.08); }
</style>
@endsection
