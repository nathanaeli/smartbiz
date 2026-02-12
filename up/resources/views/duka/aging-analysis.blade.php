@extends('layouts.app')
@section('title', 'Loan Aging Analysis - ' . $duka->name)
@section('content')
<div class="container-fluid card p-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Loan Aging Analysis</h2>
            <p class="text-muted mb-0">
                <i class="ri-building-line me-1"></i>{{ $duka->name }} • Generated on {{ now()->format('M d, Y H:i') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('duka.show', $duka->id) }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i>Back to Duka
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="ri-printer-line me-1"></i>Print Report
            </button>
        </div>
    </div>

    <!-- Duka Summary Header -->
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="ri-information-line me-2"></i>Duka Summary</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Duka Name:</strong> {{ $duka->name }}</div>
                <div class="col-md-3"><strong>Location:</strong> {{ $duka->location ?? 'N/A' }}</div>
                <div class="col-md-3"><strong>Manager:</strong> {{ $duka->manager_name ?? 'N/A' }}</div>
                <div class="col-md-3"><strong>Report Date:</strong> {{ now()->format('Y-m-d') }}</div>
            </div>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row g-4 mb-4">
        <div class="col-xl-2 col-md-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Loans</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['total_loans'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="ri-file-list-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Outstanding</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($summary['total_outstanding'], 2) }} TZS</div>
                        </div>
                        <div class="col-auto">
                            <i class="ri-money-dollar-circle-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Overdue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($summary['total_overdue'], 2) }} TZS</div>
                        </div>
                        <div class="col-auto">
                            <i class="ri-time-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">High Risk</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($summary['total_high_risk'], 2) }} TZS</div>
                        </div>
                        <div class="col-auto">
                            <i class="ri-alert-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Overdue Customers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['count_overdue_customers'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="ri-user-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Loan Aging Table -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="ri-table-line me-2"></i>Detailed Loan Aging Table</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Customer Name</th>
                            <th>Loan ID</th>
                            <th>Original Amount</th>
                            <th>Amount Paid</th>
                            <th>Outstanding Balance</th>
                            <th>Due Date</th>
                            <th>Days Overdue</th>
                            <th>Aging Category</th>
                            <th>Recommended Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $groupedLoans = $loans->groupBy('customer_name');
                            $rowCount = 0;
                        @endphp
                        @forelse($groupedLoans as $customerName => $customerLoans)
                            @php
                                $customerLoanCount = $customerLoans->count();
                                $isFirstRow = true;
                            @endphp
                            @foreach($customerLoans as $loan)
                            <tr class="{{ $customerLoanCount > 1 ? 'table-group-' . md5($customerName) : '' }}"
                                style="{{ $customerLoanCount > 1 ? 'border-left: 3px solid #007bff;' : '' }}">
                                @if($isFirstRow)
                                    <td rowspan="{{ $customerLoanCount }}" class="align-middle fw-bold"
                                        style="background-color: {{ $customerLoanCount > 1 ? '#f8f9fa' : 'transparent' }};">
                                        {{ $customerName }}
                                        @if($customerLoanCount > 1)
                                            <br><small class="text-muted">{{ $customerLoanCount }} loans</small>
                                        @endif
                                    </td>
                                    @php $isFirstRow = false; @endphp
                                @endif
                                <td>{{ $loan['loan_id'] }}</td>
                                <td>{{ number_format($loan['original_amount'], 2) }} TZS</td>
                                <td>{{ number_format($loan['amount_paid'], 2) }} TZS</td>
                                <td>{{ number_format($loan['outstanding_balance'], 2) }} TZS</td>
                                <td>{{ $loan['due_date'] }}</td>
                                <td>{{ $loan['days_overdue'] }}</td>
                                <td>
                                    <span class="badge
                                        @if($loan['aging_category'] == 'Current') bg-success
                                        @elseif($loan['aging_category'] == 'Overdue 1') bg-warning
                                        @elseif($loan['aging_category'] == 'Overdue 2') bg-danger
                                        @else bg-dark @endif">
                                        {{ $loan['aging_category'] }}
                                    </span>
                                </td>
                                <td>{{ $loan['recommended_action'] }}</td>
                            </tr>
                            @endforeach
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No loans found for this duka.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Customer Information & Loan History -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="ri-user-line me-2"></i>Customer Information & Loan History</h5>
        </div>
        <div class="card-body">
            <div class="accordion" id="customerAccordion">
                @foreach($loans->groupBy('customer_name') as $customerName => $customerLoans)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading{{ md5($customerName) }}">
                        <button class="accordion-button {{ !$loop->first ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ md5($customerName) }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse{{ md5($customerName) }}">
                            <strong>{{ $customerName }}</strong>
                            <span class="badge bg-primary ms-2">{{ $customerLoans->count() }} loan(s)</span>
                            <span class="badge bg-danger ms-2">Total Outstanding: {{ number_format($customerLoans->sum('outstanding_balance'), 2) }} TZS</span>
                        </button>
                    </h2>
                    <div id="collapse{{ md5($customerName) }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading{{ md5($customerName) }}" data-bs-parent="#customerAccordion">
                        <div class="accordion-body">
                            @php
                                $customerInfo = $customerLoans->first();
                            @endphp
                            <!-- Customer Details -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6><i class="ri-user-line me-2"></i>Customer Information</h6>
                                    <table class="table table-sm">
                                        <tr><td><strong>Name:</strong></td><td>{{ $customerInfo['customer_name'] }}</td></tr>
                                        <tr><td><strong>Phone:</strong></td><td>{{ $customerInfo['customer_phone'] }}</td></tr>
                                        <tr><td><strong>Email:</strong></td><td>{{ $customerInfo['customer_email'] }}</td></tr>
                                        <tr><td><strong>Address:</strong></td><td>{{ $customerInfo['customer_address'] }}</td></tr>
                                    </table>
                                    @if($customerInfo['customer_email'] && $customerInfo['customer_email'] !== 'N/A')
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#emailModal{{ md5($customerName) }}">
                                            <i class="ri-mail-send-line me-1"></i>Send Email Reminder
                                        </button>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="ri-bar-chart-line me-2"></i>Loan Summary</h6>
                                    <table class="table table-sm">
                                        <tr><td><strong>Total Loans:</strong></td><td>{{ $customerLoans->count() }}</td></tr>
                                        <tr><td><strong>Total Borrowed:</strong></td><td>{{ number_format($customerLoans->sum('original_amount'), 2) }} TZS</td></tr>
                                        <tr><td><strong>Total Paid:</strong></td><td>{{ number_format($customerLoans->sum('amount_paid'), 2) }} TZS</td></tr>
                                        <tr><td><strong>Outstanding Balance:</strong></td><td><strong>{{ number_format($customerLoans->sum('outstanding_balance'), 2) }} TZS</strong></td></tr>
                                        <tr><td><strong>Overdue Loans:</strong></td><td>{{ $customerLoans->where('days_overdue', '>', 0)->count() }}</td></tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Individual Loan History -->
                            <h6><i class="ri-history-line me-2"></i>Loan History</h6>
                            @foreach($customerLoans as $loan)
                            <div class="card mb-3 border-left-primary">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6>Loan #{{ $loan['loan_id'] }} - {{ $loan['loan_date'] }}</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <small><strong>Original Amount:</strong> {{ number_format($loan['original_amount'], 2) }} TZS</small><br>
                                                    <small><strong>Amount Paid:</strong> {{ number_format($loan['amount_paid'], 2) }} TZS</small><br>
                                                    <small><strong>Outstanding:</strong> {{ number_format($loan['outstanding_balance'], 2) }} TZS</small>
                                                </div>
                                                <div class="col-md-6">
                                                    <small><strong>Due Date:</strong> {{ $loan['due_date'] }}</small><br>
                                                    <small><strong>Days Overdue:</strong> {{ $loan['days_overdue'] }}</small><br>
                                                    <small><strong>Status:</strong>
                                                        <span class="badge
                                                            @if($loan['aging_category'] == 'Current') bg-success
                                                            @elseif($loan['aging_category'] == 'Overdue 1') bg-warning
                                                            @elseif($loan['aging_category'] == 'Overdue 2') bg-danger
                                                            @else bg-dark @endif">
                                                            {{ $loan['aging_category'] }}
                                                        </span>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>Products Purchased</h6>
                                            <div style="max-height: 100px; overflow-y: auto;">
                                                @foreach($loan['products'] as $product)
                                                <small>• {{ $product['name'] }} ({{ $product['quantity'] }} × {{ number_format($product['unit_price'], 2) }} TZS)</small><br>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bulk Email Actions -->
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0"><i class="ri-mail-send-line me-2"></i>Bulk Email Actions</h5>
                                        </div>
                                        <div class="card-body">
                                            <p>Send automated email reminders to all customers in specific aging categories:</p>
                                            <div class="row">
                                                @foreach(['Current', 'Overdue 1', 'Overdue 2', 'High Risk / Bad Debt'] as $category)
                                                <div class="col-md-3 mb-3">
                                                    <div class="card h-100">
                                                        <div class="card-body text-center">
                                                            <h6 class="card-title">{{ $category }}</h6>
                                                            <p class="mb-2">{{ $summary['aging_distribution'][$category]['count'] ?? 0 }} customers</p>
                                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulkEmailModal{{ md5($category) }}">
                                                                <i class="ri-mail-send-line me-1"></i>Send Bulk Email
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    @if(count($loan['payments']) > 0)
                                    <hr>
                                    <h6>Payment History</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($loan['payments'] as $payment)
                                                <tr>
                                                    <td>{{ $payment['date'] }}</td>
                                                    <td>{{ number_format($payment['amount'], 2) }} TZS</td>
                                                    <td>{{ $payment['notes'] ?: 'N/A' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="alert alert-warning mt-2">
                                        <small>No payments recorded for this loan yet.</small>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Aging Category Breakdown -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="ri-bar-chart-line me-2"></i>Aging Category Breakdown</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($summary['aging_distribution'] as $category => $data)
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h6 class="card-title">{{ $category }}</h6>
                            <h4 class="text-primary">{{ $data['count'] }}</h4>
                            <p class="mb-0">Loans</p>
                            <small class="text-muted">{{ number_format($data['total_balance'], 2) }} TZS</small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Top Debtors List -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="ri-trophy-line me-2"></i>Top 5 Highest Debtors</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Customer Name</th>
                            <th>Outstanding Balance</th>
                            <th>Aging Category</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summary['top_debtors'] as $index => $debtor)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $debtor['customer_name'] }}</td>
                            <td>{{ number_format($debtor['outstanding_balance'], 2) }} TZS</td>
                            <td>
                                <span class="badge
                                    @if($debtor['aging_category'] == 'Current') bg-success
                                    @elseif($debtor['aging_category'] == 'Overdue 1') bg-warning
                                    @elseif($debtor['aging_category'] == 'Overdue 2') bg-danger
                                    @else bg-dark @endif">
                                    {{ $debtor['aging_category'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Insights & Patterns -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="ri-lightbulb-line me-2"></i>Insights & Patterns</h5>
        </div>
        <div class="card-body">
            <ul class="list-unstyled">
                <li class="mb-2"><i class="ri-check-line text-success me-2"></i>Total outstanding loans: {{ number_format($summary['total_outstanding'], 2) }} TZS</li>
                <li class="mb-2"><i class="ri-check-line text-warning me-2"></i>{{ number_format(($summary['total_overdue'] / max($summary['total_outstanding'], 1)) * 100, 1) }}% of total outstanding is overdue</li>
                <li class="mb-2"><i class="ri-check-line text-danger me-2"></i>{{ $summary['count_overdue_customers'] }} customers have overdue payments</li>
                @if($summary['total_high_risk'] > 0)
                <li class="mb-2"><i class="ri-alert-line text-danger me-2"></i>High risk loans total: {{ number_format($summary['total_high_risk'], 2) }} TZS</li>
                @endif
            </ul>
        </div>
    </div>

    <!-- Risk Assessment -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="ri-shield-line me-2"></i>Risk Assessment</h5>
        </div>
        <div class="card-body">
            @php
                $riskLevel = 'Low';
                $riskColor = 'success';
                if ($summary['total_high_risk'] > 0.5 * $summary['total_outstanding']) {
                    $riskLevel = 'High';
                    $riskColor = 'danger';
                } elseif ($summary['total_overdue'] > 0.3 * $summary['total_outstanding']) {
                    $riskLevel = 'Medium';
                    $riskColor = 'warning';
                }
            @endphp
            <div class="alert alert-{{ $riskColor }}">
                <h6>Risk Level: <strong>{{ $riskLevel }}</strong></h6>
                <p class="mb-0">
                    @if($riskLevel == 'Low')
                        The duka has minimal overdue loans. Continue monitoring.
                    @elseif($riskLevel == 'Medium')
                        Moderate risk. Consider increasing follow-up efforts.
                    @else
                        High risk. Immediate action required for collections.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Recommended Actions for Collections -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="ri-task-line me-2"></i>Recommended Actions for Collections</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Immediate Actions (Next 7 days):</h6>
                    <ul>
                        <li>Send reminder SMS to {{ $loans->where('aging_category', 'Overdue 1')->count() }} customers in Overdue 1</li>
                        <li>Call {{ $loans->where('aging_category', 'Overdue 2')->count() }} customers in Overdue 2</li>
                        <li>Flag {{ $loans->where('aging_category', 'High Risk / Bad Debt')->count() }} high-risk accounts</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Long-term Strategies:</h6>
                    <ul>
                        <li>Review credit policies for high-risk customers</li>
                        <li>Implement automated payment reminders</li>
                        <li>Consider legal action for bad debts if necessary</li>
                        <li>Train staff on collection procedures</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Individual Email Modals -->
@foreach($loans->groupBy('customer_name') as $customerName => $customerLoans)
@if($customerLoans->first()['customer_email'] && $customerLoans->first()['customer_email'] !== 'N/A')
<div class="modal fade" id="emailModal{{ md5($customerName) }}" tabindex="-1" aria-labelledby="emailModalLabel{{ md5($customerName) }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailModalLabel{{ md5($customerName) }}">Send Email to {{ $customerName }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('duka.send.loan.reminder', $duka->id) }}" method="POST">
                @csrf
                <input type="hidden" name="loan_id" value="{{ $customerLoans->first()['loan_id'] }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="message_type" class="form-label">Message Type</label>
                        <select name="message_type" class="form-control" required>
                            <option value="reminder">Payment Reminder</option>
                            <option value="overdue_warning">Overdue Warning</option>
                            <option value="final_notice">Final Notice</option>
                            <option value="payment_confirmation">Payment Confirmation</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <strong>Customer:</strong> {{ $customerName }}<br>
                        <strong>Email:</strong> {{ $customerLoans->first()['customer_email'] }}<br>
                        <strong>Outstanding Balance:</strong> {{ number_format($customerLoans->sum('outstanding_balance'), 2) }} TZS
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Email</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

<!-- Bulk Email Modals -->
@foreach(['Current', 'Overdue 1', 'Overdue 2', 'High Risk / Bad Debt'] as $category)
<div class="modal fade" id="bulkEmailModal{{ md5($category) }}" tabindex="-1" aria-labelledby="bulkEmailModalLabel{{ md5($category) }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkEmailModalLabel{{ md5($category) }}">Send Bulk Emails - {{ $category }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('duka.send.bulk.reminders', $duka->id) }}" method="POST">
                @csrf
                <input type="hidden" name="aging_category" value="{{ $category }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="message_type" class="form-label">Message Type</label>
                        <select name="message_type" class="form-control" required>
                            <option value="reminder">Payment Reminder</option>
                            <option value="overdue_warning">Overdue Warning</option>
                            <option value="final_notice">Final Notice</option>
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <strong>Bulk Email Action</strong><br>
                        This will send emails to all {{ $summary['aging_distribution'][$category]['count'] ?? 0 }} customers in the "{{ $category }}" category who have valid email addresses.<br>
                        <strong>Total Recipients:</strong> {{ $summary['aging_distribution'][$category]['count'] ?? 0 }}<br>
                        <strong>Total Outstanding:</strong> {{ number_format($summary['aging_distribution'][$category]['total_balance'] ?? 0, 2) }} TZS
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirm_bulk{{ md5($category) }}" required>
                        <label class="form-check-label" for="confirm_bulk{{ md5($category) }}">
                            I confirm that I want to send bulk emails to all customers in this category
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Bulk Emails</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<style>
@media print {
    .btn, .card-header .d-flex, .modal {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
        margin-bottom: 20px;
    }
}
</style>
@endsection
