@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4 card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark">{{ __('messages.consolidated_cash_flow') }}</h2>
            <p class="text-muted">{{ __('messages.combined_financial_performance') }}</p>
        </div>
        <form class="d-flex gap-2 bg-white p-2 rounded shadow-sm border">
            <input type="date" name="start_date" value="{{ $start->format('Y-m-d') }}" class="form-control form-control-sm border-0 bg-light">
            <input type="date" name="end_date" value="{{ $end->format('Y-m-d') }}" class="form-control form-control-sm border-0 bg-light">
            <button type="submit" class="btn btn-sm btn-primary px-3">{{ __('messages.filter_all') }}</button>
        </form>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-4 text-center">
                    <h6 class="text-uppercase small opacity-75 fw-bold">{{ __('messages.total_group_income') }}</h6>
                    <h1 class="fw-bold mb-0">{{ number_format($totalIncome) }} TSH</h1>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body p-4 text-center">
                    <h6 class="text-uppercase small opacity-75 fw-bold">{{ __('messages.total_group_expenses') }}</h6>
                    <h1 class="fw-bold mb-0">{{ number_format($totalExpense) }} TSH</h1>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm {{ $netCashFlow >= 0 ? 'bg-primary' : 'bg-warning' }} text-white">
                <div class="card-body p-4 text-center">
                    <h6 class="text-uppercase small opacity-75 fw-bold">{{ __('messages.net_group_cash_position') }}</h6>
                    <h1 class="fw-bold mb-0">{{ number_format($netCashFlow) }} TSH</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">{{ __('messages.branch_performance_comparison') }}</h5>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">{{ __('messages.branch_name') }}</th>
                        <th class="text-end">{{ __('messages.total_inflow') }}</th>
                        <th class="text-end">{{ __('messages.total_outflow') }}</th>
                        <th class="text-end pe-4">{{ __('messages.net_contribution') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dukaSummaries as $summary)
                    <tr>
                        <td class="ps-4 fw-bold text-dark">{{ $summary['name'] }}</td>
                        <td class="text-end text-success">{{ number_format($summary['income']) }}</td>
                        <td class="text-end text-danger">{{ number_format($summary['expense']) }}</td>
                        <td class="text-end fw-bold pe-4 {{ $summary['net'] >= 0 ? 'text-primary' : 'text-warning' }}">
                            {{ number_format($summary['net']) }} TSH
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">{{ __('messages.master_ledger') }}</h5>
            <span class="badge bg-light text-dark border">{{ $transactions->count() }} {{ __('messages.records') }}</span>
        </div>
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-hover mb-0">
                <thead class="bg-light sticky-top">
                    <tr>
                        <th class="ps-4 border-0">{{ __('messages.date') }}</th>
                        <th class="border-0">{{ __('messages.branch') }}</th>
                        <th class="border-0">{{ __('messages.category') }}</th>
                        <th class="text-end pe-4 border-0">{{ __('messages.amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $trx)
                    <tr>
                        <td class="ps-4 small text-muted">{{ $trx->transaction_date->format('d M, Y') }}</td>
                        <td><span class="fw-bold">{{ $trx->duka->name }}</span></td>
                        <td><span class="badge bg-soft-secondary text-secondary">{{ strtoupper($trx->category) }}</span></td>
                        <td class="text-end pe-4 fw-bold {{ $trx->type == 'income' ? 'text-success' : 'text-danger' }}">
                            {{ $trx->type == 'income' ? '+' : '-' }} {{ number_format($trx->amount) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .bg-soft-secondary { background-color: #f1f3f5; }
    .sticky-top { top: 0; z-index: 1020; }
</style>
@endsection
