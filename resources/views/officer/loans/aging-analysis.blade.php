@extends('layouts.officer')

@section('content')
<div class="container-fluid content-inner mt-n5 py-0 card">
    <div class="row">
        <div class="col-lg-12">
            <div class="card rounded-0 bg-transparent border-0 mb-4">
                <div class="card-body p-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div class="mb-3 mb-md-0">
                            <h4 class="mb-1 fw-bold">Loan Aging Analysis</h4>
                            <p class="mb-0 text-secondary">
                                <span class="badge bg-primary rounded-pill me-2">{{ $assignedDukas->count() }} Assinged Dukas</span>
                                <span class="text-uppercase small fw-bold text-muted">Currency: {{ $currency }}</span>
                            </p>
                        </div>
                        <div class="d-flex gap-3">
                             <button class="btn btn-soft-primary rounded-pill btn-sm d-flex align-items-center gap-2">
                                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Export Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Metrics Cards -->
        <div class="col-lg-12">
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="p-2 rounded-3 bg-primary-subtle text-primary">
                                   <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <span class="badge bg-primary text-white rounded-pill">Total</span>
                            </div>
                            <h2 class="mb-1 counter fw-bold text-dark">{{ $summary['total_loans'] }}</h2>
                            <p class="mb-0 text-muted small text-uppercase fw-bold ls-1">Total Loans</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="p-2 rounded-3 bg-success-subtle text-success">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <span class="badge bg-success text-white rounded-pill">Active</span>
                            </div>
                            <h4 class="mb-1 counter fw-bold text-dark">{{ number_format($summary['total_outstanding'], 2) }} <small class="text-muted fs-6">{{ $currency }}</small></h4>
                            <p class="mb-0 text-muted small text-uppercase fw-bold ls-1">Outstanding</p>
                            <!-- Visual decorative element -->
                            <div class="position-absolute bottom-0 end-0 opacity-10 p-3">
                                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="p-2 rounded-3 bg-warning-subtle text-warning">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <span class="badge bg-warning text-white rounded-pill">Attention</span>
                            </div>
                            <h4 class="mb-1 counter fw-bold text-dark">{{ number_format($summary['total_overdue'], 2) }} <small class="text-muted fs-6">{{ $currency }}</small></h4>
                            <p class="mb-0 text-muted small text-uppercase fw-bold ls-1">Total Overdue</p>
                            
                            @if($summary['total_outstanding'] > 0)
                            <div class="progress mt-3" style="height: 6px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ($summary['total_overdue'] / $summary['total_outstanding']) * 100 }}%" aria-valuenow="{{ ($summary['total_overdue'] / $summary['total_outstanding']) * 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="p-2 rounded-3 bg-danger-subtle text-danger">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <span class="badge bg-danger text-white rounded-pill">High Risk</span>
                            </div>
                            <h4 class="mb-1 counter fw-bold text-dark">{{ number_format($summary['total_high_risk'], 2) }} <small class="text-muted fs-6">{{ $currency }}</small></h4>
                            <p class="mb-0 text-muted small text-uppercase fw-bold ls-1">Risk Amount</p>
                             @if($summary['total_outstanding'] > 0)
                            <div class="progress mt-3" style="height: 6px;">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ ($summary['total_high_risk'] / $summary['total_outstanding']) * 100 }}%" aria-valuenow="{{ ($summary['total_high_risk'] / $summary['total_outstanding']) * 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Breakdown Row -->
        <div class="col-lg-8 mb-4">
             <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-header border-0 bg-transparent py-3">
                    <h5 class="fw-bold mb-0">Aging Distribution</h5>
                </div>
                <div class="card-body">
                     <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="list-group list-group-flush">
                                @foreach($summary['aging_distribution'] as $category => $data)
                                    @php
                                        $percentage = $summary['total_loans'] > 0 ? ($data['count'] / $summary['total_loans']) * 100 : 0;
                                        $color = 'primary';
                                        if(str_contains($category, 'Overdue 1')) $color = 'warning';
                                        if(str_contains($category, 'Overdue 2')) $color = 'danger';
                                        if(str_contains($category, 'High Risk')) $color = 'danger';
                                        if(str_contains($category, 'Current')) $color = 'success';
                                    @endphp
                                <div class="list-group-item border-0 px-0 py-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }} rounded-pill me-2">{{ $data['count'] }} Loans</span>
                                            <span class="fw-bold text-{{ $color }}">{{ $category }}</span>
                                        </div>
                                        <span class="fw-bold">{{ number_format($data['total_balance'], 2) }} {{ $currency }}</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                     </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
             <!-- Risk Assessment Card -->
             @php
                $riskLevel = 'Low';
                $riskColor = 'success';
                $riskPercentage = 10;
                
                if ($summary['total_outstanding'] > 0) {
                     if ($summary['total_high_risk'] > 0.5 * $summary['total_outstanding']) {
                        $riskLevel = 'High';
                        $riskColor = 'danger';
                        $riskPercentage = 90;
                    } elseif ($summary['total_overdue'] > 0.3 * $summary['total_outstanding']) {
                        $riskLevel = 'Medium';
                        $riskColor = 'warning';
                        $riskPercentage = 50;
                    }
                }
            @endphp
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-{{ $riskColor }}-subtle">
                <div class="card-body text-center p-5 d-flex flex-column justify-content-center align-items-center">
                    <div class="mb-4">
                        <div class="position-relative d-inline-block">
                             <svg width="100" height="100" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-{{ $riskColor }}">
                                <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.2"/>
                                <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div class="position-absolute top-50 start-50 translate-middle">
                                <span class="fw-bold fs-4 text-{{ $riskColor }}">{{ $riskPercentage }}%</span>
                            </div>
                        </div>
                    </div>
                    <h3 class="fw-bold text-{{ $riskColor }} mb-2">{{ $riskLevel }} Risk</h3>
                     <p class="text-{{ $riskColor }} opacity-75 mb-0">
                        @if($riskLevel == 'Low')
                            Minimal overdue loans. Healthy portfolio.
                        @elseif($riskLevel == 'Medium')
                            Moderate risk detected. Increase follow-ups.
                        @else
                            High risk portfolio. Immediate action required.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Top Debtors -->
        <div class="col-lg-12 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                 <div class="card-header border-0 bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Top Debtors</h5>
                    @if($summary['top_debtors']->count() > 0)
                    <span class="badge bg-danger rounded-pill">{{ $summary['top_debtors']->count() }} High Value</span>
                    @endif
                </div>
                <div class="card-body p-0">
                     <div class="table-responsive">
                        <table class="table table-hover align-middle table-borderless mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted text-uppercase small">
                                    <th class="ps-4">Customer</th>
                                    <th>Duka</th>
                                    <th>Outstanding</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($summary['top_debtors'] as $debtor)
                                <tr class="border-bottom border-light">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                             <div class="avatar avatar-40 rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center me-3 fw-bold">
                                                {{ substr($debtor['customer_name'], 0, 1) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">{{ $debtor['customer_name'] }}</h6>
                                                <small class="text-muted">{{ $debtor['customer_phone'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $debtor['duka_name'] }}</span></td>
                                    <td>
                                        <h6 class="mb-0 fw-bold text-danger">{{ number_format($debtor['outstanding_balance'], 2) }} {{ $currency }}</h6>
                                        <small class="text-muted">Original: {{ number_format($debtor['original_amount'], 2) }}</small>
                                    </td>
                                    <td>
                                        @php 
                                            $catColor = 'secondary';
                                            if($debtor['aging_category'] == 'Current') $catColor = 'success';
                                            if(str_contains($debtor['aging_category'], 'Overdue')) $catColor = 'warning';
                                            if(str_contains($debtor['aging_category'], 'High Risk')) $catColor = 'danger';
                                        @endphp
                                         <span class="badge bg-{{ $catColor }}-subtle text-{{ $catColor }} rounded-pill px-3">
                                            {{ $debtor['aging_category'] }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-icon btn-light rounded-circle" title="View Details">
                                            <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <p class="text-muted mb-0">No top debtors found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed List (Accordion or Table) -->
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header border-0 bg-transparent py-3">
                    <h5 class="fw-bold mb-0">Detailed Loan Portfolio</h5>
                </div>
                <div class="card-body p-0">
                     <div class="table-responsive">
                         <table class="table table-hover align-middle table-borderless mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted text-uppercase small">
                                    <th class="ps-4">Loan Details</th>
                                    <th>Customer</th>
                                    <th>Financials</th>
                                    <th>Timeline</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Recommendation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loans as $loan)
                                <tr class="border-bottom border-light">
                                    <td class="ps-4">
                                        <span class="fw-bold d-block text-dark">#{{ $loan['loan_id'] }}</span>
                                        <span class="badge bg-light text-dark border mt-1">{{ $loan['duka_name'] }}</span>
                                    </td>
                                    <td>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $loan['customer_name'] }}</h6>
                                        <small class="text-muted">{{ $loan['customer_phone'] }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-danger fw-bold">{{ number_format($loan['outstanding_balance'], 2) }} {{ $currency }}</span>
                                            <small class="text-success"><i class="fas fa-arrow-down me-1"></i>Pd: {{ number_format($loan['amount_paid'], 2) }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <small class="text-muted">Due: <span class="text-dark fw-bold">{{ $loan['due_date'] }}</span></small>
                                            @if($loan['days_overdue'] > 0)
                                                <small class="text-danger fw-bold">{{ $loan['days_overdue'] }} Days late</small>
                                            @else
                                                 <small class="text-success fw-bold">On Time</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                         @php 
                                            $catColor = 'secondary';
                                            if($loan['aging_category'] == 'Current') $catColor = 'success';
                                            if(str_contains($loan['aging_category'], 'Overdue')) $catColor = 'warning';
                                            if(str_contains($loan['aging_category'], 'High Risk')) $catColor = 'danger';
                                        @endphp
                                         <span class="badge bg-{{ $catColor }}-subtle text-{{ $catColor }} rounded-pill px-3">
                                            {{ $loan['aging_category'] }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <span class="text-muted fst-italic small">{{ $loan['recommended_action'] }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="opacity-50">
                                            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                        <p class="text-muted mt-2">No loans found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                     </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
