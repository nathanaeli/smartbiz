@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 card">
    <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
        <div>
            <h2 class="fw-bold mb-1">{{ __('messages.financial_performance') }}</h2>
            <p class="text-muted">{{ __('messages.detailed_profit_loss_analysis') }}</p>
        </div>

        <div class="bg-white p-1 rounded border shadow-sm">
            <form action="{{ route('tenant.reports.pl') }}" method="GET" class="d-flex align-items-center gap-2">
                @if($dukas->count() > 1)
                <select name="duka_id" class="form-select form-select-sm border-0 bg-light">
                    @foreach($dukas as $duka)
                    <option value="{{ $duka->id }}" {{ $selectedDukaId == $duka->id ? 'selected' : '' }}>
                        {{ $duka->name }}
                    </option>
                    @endforeach
                </select>
                @endif
                <input type="date" name="start_date" value="{{ $start->format('Y-m-d') }}" class="form-control form-control-sm border-0 bg-light">
                <span class="text-muted small">to</span>
                <input type="date" name="end_date" value="{{ $end->format('Y-m-d') }}" class="form-control form-control-sm border-0 bg-light">
                <button type="submit" class="btn btn-sm btn-primary px-3">{{ __('messages.filter') }}</button>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-body p-4 border-start border-4 border-info">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-muted small fw-bold mb-2">{{ __('messages.inventory_asset_value') }}</h6>
                            <h2 class="fw-bold mb-1 text-dark">{{ number_format($totalStockBuyingValue) }} TSH</h2>
                            <p class="small text-info mb-0">{{ __('messages.total_cost_of_stock') }}</p>
                        </div>
                        <div class="bg-soft-info p-3 rounded-circle h-100">
                            <i class="ri-archive-line fs-3 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-body p-4 border-start border-4 border-primary">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-muted small fw-bold mb-2">{{ __('messages.potential_revenue') }}</h6>
                            <h2 class="fw-bold mb-1 text-dark">{{ number_format($totalStockSellingValue) }} TSH</h2>
                            <p class="small text-primary mb-0">{{ __('messages.expected_cash_if_all_items_sold') }}</p>
                        </div>
                        <div class="bg-soft-primary p-3 rounded-circle h-100">
                            <i class="ri-funds-box-line fs-3 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">{{ __('messages.profit_loss_statement') }}</h5>
                </div>
                <div class="col-auto">
                    <span class="badge bg-light text-muted border px-3 py-2">
                        {{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body p-md-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">

                    <div class="mb-5">
                        <div class="d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted fw-bold">{{ __('messages.operating_revenue') }}</span>
                            <span class="fw-bold text-dark">{{ number_format($totalRevenue) }} TSH</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom border-dashed">
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-2">1.1</span>
                                <span>{{ __('messages.total_sales_income') }}</span>
                            </div>
                            <span class="text-success fw-semibold">+ {{ number_format($totalRevenue) }}</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom border-dashed text-danger">
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-2">1.2</span>
                                <span>{{ __('messages.cost_of_goods_sold') }}</span>
                            </div>
                            <span>- {{ number_format($cogs) }}</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center py-3 bg-light rounded px-3 mt-3">
                            <span class="fw-bold text-dark">{{ __('messages.gross_margin_profit') }}</span>
                            <span class="h4 mb-0 fw-bold text-success">{{ number_format($grossProfit) }} TSH</span>
                        </div>
                    </div>

                    <div class="mb-5">
                        <div class="d-flex justify-content-between align-items-center py-2 mb-3">
                            <span class="text-muted fw-bold text-uppercase small">{{ __('messages.operating_expenses') }}</span>
                        </div>

                        @forelse($expenses as $index => $expense)
                        <div class="d-flex justify-content-between align-items-center py-2 small border-bottom border-light">
                            <div class="text-capitalize">
                                <span class="text-muted me-2">2.{{ $index + 1 }}</span>
                                {{ str_replace('_', ' ', $expense->category) }}
                            </div>
                            <span class="text-dark">{{ number_format($expense->total) }}</span>
                        </div>
                        @empty
                        <p class="text-muted small text-center italic">{{ __('messages.no_expenses_recorded') }}</p>
                        @endforelse

                        <div class="d-flex justify-content-between align-items-center py-3 text-danger mt-2">
                            <span class="fw-bold">{{ __('messages.total_operating_costs') }}</span>
                            <span class="fw-bold">- {{ number_format($totalExpenses) }} TSH</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-4 {{ $netProfit >= 0 ? 'bg-success' : 'bg-danger' }} text-white shadow-lg">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="fw-bold mb-0">{{ __('messages.net_profit') }}</h3>
                                <small class="opacity-75">{{ __('messages.final_earnings_after_all_costs') }}</small>
                            </div>
                            <h2 class="fw-bold mb-0">{{ number_format($netProfit) }} TSH</h2>
                        </div>
                    </div>

                    <div class="text-center mt-4 pt-3">
                        <button class="btn btn-link text-muted btn-sm text-decoration-none" onclick="window.print()">
                            <i class="ri-printer-line me-1"></i> {{ __('messages.print_statement') }}
                        </button>
                        <a href="{{ route('tenant.reports.pl', ['duka_id' => $selectedDukaId, 'start_date' => $start->toDateString(), 'end_date' => $end->toDateString(), 'export' => 'pdf']) }}" class="btn btn-link text-primary btn-sm text-decoration-none ms-3">
                            <i class="ri-file-pdf-line me-1"></i> Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-info {
        background-color: rgba(13, 202, 240, 0.12);
    }

    .bg-soft-primary {
        background-color: rgba(13, 110, 253, 0.12);
    }

    .border-dashed {
        border-style: dashed !important;
    }

    .rounded-4 {
        border-radius: 1rem !important;
    }

    @media print {

        .btn,
        form,
        .card-header-actions {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .container-fluid {
            padding: 0 !important;
        }
    }
</style>
@endsection