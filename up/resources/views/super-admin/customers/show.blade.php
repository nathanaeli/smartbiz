@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Customer Details: {{ $customer->name }}</h4>
                <a href="{{ route('super-admin.customers.index') }}" class="btn btn-secondary">Back to Customers</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Customer Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Name:</th>
                                        <td>{{ $customer->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td>{{ $customer->phone ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>{{ $customer->email ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            <span class="badge bg-{{ $customer->status === 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($customer->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Duka:</th>
                                        <td>
                                            @if($customer->duka)
                                                <a href="{{ route('super-admin.dukas.show', $customer->duka->id) }}">
                                                    {{ $customer->duka->name }}
                                                </a>
                                            @else
                                                <span class="text-muted">No Duka</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Tenant:</th>
                                        <td>
                                            @if($customer->tenant && $customer->tenant->user)
                                                <a href="{{ route('super-admin.tenants.show', $customer->tenant->id) }}">
                                                    {{ $customer->tenant->user->name }}
                                                </a>
                                            @else
                                                <span class="text-muted">No Tenant</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Total Spent:</th>
                                        <td><strong>{{ $currency }} {{ number_format($totalSpent, $currency === 'TZS' ? 0 : 2) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Outstanding Loans:</th>
                                        <td><strong>{{ $currency }} {{ number_format($totalLoans, $currency === 'TZS' ? 0 : 2) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Joined:</th>
                                        <td>{{ $customer->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Purchase Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-6">
                                        <h3 class="text-primary">{{ $customer->sales->count() }}</h3>
                                        <p class="text-muted">Total Purchases</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h3 class="text-success">{{ $customer->sales->where('is_loan', false)->count() }}</h3>
                                        <p class="text-muted">Paid Purchases</p>
                                    </div>
                                </div>
                                <div class="row text-center mt-3">
                                    <div class="col-md-6">
                                        <h3 class="text-warning">{{ $customer->sales->where('is_loan', true)->count() }}</h3>
                                        <p class="text-muted">Active Loans</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h3 class="text-info">{{ $currency }} {{ number_format($customer->sales->avg('total_amount'), $currency === 'TZS' ? 0 : 2) }}</h3>
                                        <p class="text-muted">Avg Purchase</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase History -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Purchase History</h5>
                            </div>
                            <div class="card-body">
                                @if($purchaseHistory->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Duka</th>
                                                    <th>Total Amount</th>
                                                    <th>Payment Status</th>
                                                    <th>Items</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($purchaseHistory as $sale)
                                                <tr>
                                                    <td>{{ $sale->id }}</td>
                                                    <td>{{ $sale->duka->name ?? 'N/A' }}</td>
                                                    <td>{{ $currency }} {{ number_format($sale->total_amount, $currency === 'TZS' ? 0 : 2) }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $sale->is_loan ? 'warning' : 'success' }}">
                                                            {{ $sale->is_loan ? 'Loan' : 'Paid' }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $sale->saleItems->count() }} items</td>
                                                    <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted">No purchase history</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loan Details (if any) -->
                @if($totalLoans > 0)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Active Loans</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Sale ID</th>
                                                <th>Amount</th>
                                                <th>Loan Date</th>
                                                <th>Payments Made</th>
                                                <th>Remaining Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $loanSales = $customer->sales->where('is_loan', true);
                                            @endphp
                                            @foreach($loanSales as $loan)
                                            <tr>
                                                <td>{{ $loan->id }}</td>
                                                <td>{{ $currency }} {{ number_format($loan->total_amount, $currency === 'TZS' ? 0 : 2) }}</td>
                                                <td>{{ $loan->created_at->format('M d, Y') }}</td>
                                                <td>{{ $loan->loanPayments->count() }}</td>
                                                <td>
                                                    @php
                                                        $paidAmount = $loan->loanPayments->sum('amount');
                                                        $remaining = $loan->total_amount - $paidAmount;
                                                    @endphp
                                                    {{ $currency }} {{ number_format($remaining, $currency === 'TZS' ? 0 : 2) }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
