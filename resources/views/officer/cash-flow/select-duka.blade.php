@extends(auth()->user()->hasRole('officer') ? 'layouts.officer' : 'layouts.app')

@section('content')
<div class="container-fluid card">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('tenant.dashboard') }}">
                    <svg class="me-1" width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Cash Flow Overview
            </li>
        </ol>
    </nav>

    <!-- Overall Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title text-success mb-1">Total Income</h6>
                            <h4 class="mb-0">{{ number_format($overallSummary['total_income'], 2) }} TSH</h4>
                        </div>
                        <div class="ms-3">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #198754;">
                                <path d="M12 19V5m0 0l-7 7m7-7l7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title text-danger mb-1">Total Expenses</h6>
                            <h4 class="mb-0">{{ number_format($overallSummary['total_expense'], 2) }} TSH</h4>
                        </div>
                        <div class="ms-3">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #dc3545;">
                                <path d="M12 5v14m0 0l7-7m-7 7l-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-{{ $overallSummary['net_cash_flow'] >= 0 ? 'success' : 'danger' }}">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title text-{{ $overallSummary['net_cash_flow'] >= 0 ? 'success' : 'danger' }} mb-1">Net Cash Flow</h6>
                            <h4 class="mb-0">{{ number_format(abs($overallSummary['net_cash_flow']), 2) }} TSH</h4>
                        </div>
                        <div class="ms-3">
                            @if($overallSummary['net_cash_flow'] >= 0)
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #198754;">
                                    <path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @else
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #dc3545;">
                                    <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title text-primary mb-1">Total Sales Revenue</h6>
                            <h4 class="mb-0">{{ number_format($overallSummary['total_sales_revenue'], 2) }} TSH</h4>
                        </div>
                        <div class="ms-3">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #0d6efd;">
                                <path d="M3 3v18h18M7 14l4-4 4 4 4-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Duka Cash Flow Overview Table -->
    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">
                <svg class="me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 1v23M17 5H9.5C8.57174 5 7.6815 5.36875 7.02513 6.02513C6.36875 6.6815 6 7.57174 6 8.5C6 9.42826 6.36875 10.3185 7.02513 10.9749C7.6815 11.6313 8.57174 12 9.5 12H14.5C15.4283 12 16.3185 12.3687 16.9749 13.0251C17.6313 13.6815 18 14.5717 18 15.5C18 16.4283 17.6313 17.3185 16.9749 17.9749C16.3185 18.6313 15.4283 19 14.5 19H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>Duka Cash Flow Overview
            </h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Duka Name</th>
                            <th>Location</th>
                            <th class="text-end">Income</th>
                            <th class="text-end">Expenses</th>
                            <th class="text-end">Sales Revenue</th>
                            <th class="text-end">Net Cash Flow</th>
                            <th>Recent Entries</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dukaSummaries as $dukaSummary)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <svg class="me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #0d6efd;">
                                            <path d="M3 7V5C3 3.89543 3.89543 3 5 3H7M17 3H19C20.1046 3 21 3.89543 21 5V7M21 17V19C21 20.1046 20.1046 21 19 21H17M7 21H5C3.89543 21 3 20.1046 3 19V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <circle cx="9" cy="9" r="1" fill="currentColor"/>
                                            <circle cx="15" cy="15" r="1" fill="currentColor"/>
                                        </svg>
                                        <strong>{{ $dukaSummary['duka']->name }}</strong>
                                    </div>
                                </td>
                                <td>{{ $dukaSummary['duka']->location }}</td>
                                <td class="text-end">
                                    <span class="text-success">{{ number_format($dukaSummary['total_income'], 2) }} TSH</span>
                                </td>
                                <td class="text-end">
                                    <span class="text-danger">{{ number_format($dukaSummary['total_expense'], 2) }} TSH</span>
                                </td>
                                <td class="text-end">
                                    <span class="text-primary">{{ number_format($dukaSummary['total_sales_revenue'], 2) }} TSH</span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold {{ $dukaSummary['net_cash_flow'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format(abs($dukaSummary['net_cash_flow']), 2) }} TSH
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $dukaSummary['recent_entries_count'] }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ auth()->user()->hasRole('officer') ? route('officer.cashflow.index', ['duka_id' => $dukaSummary['duka']->id]) : route('cash-flow.index', ['duka_id' => $dukaSummary['duka']->id]) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <svg class="me-1" width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M3 3v18h18M7 14l4-4 4 4 4-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>View
                                        </a>
                                        <a href="{{ route('cash-flow.create', ['duka_id' => $dukaSummary['duka']->id]) }}"
                                           class="btn btn-sm btn-outline-success">
                                            <svg class="me-1" width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>Add Entry
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="2">Total</td>
                            <td class="text-end text-success">{{ number_format($overallSummary['total_income'], 2) }} TSH</td>
                            <td class="text-end text-danger">{{ number_format($overallSummary['total_expense'], 2) }} TSH</td>
                            <td class="text-end text-primary">{{ number_format($overallSummary['total_sales_revenue'], 2) }} TSH</td>
                            <td class="text-end {{ $overallSummary['net_cash_flow'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format(abs($overallSummary['net_cash_flow']), 2) }} TSH
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary">
                    <svg class="me-1" width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 12H5m7 7-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
