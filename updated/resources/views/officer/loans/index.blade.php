@extends('layouts.officer')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                            <path d="M12 2L13.09 8.26L20 9L13.09 9.74L12 16L10.91 9.74L4 9L10.91 8.26L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ __('loans.loan_management') }}
                    </h4>
                    <div class="d-flex gap-2">
                        <span class="badge badge-primary">{{ __('loans.total_loans', ['count' => $loans->total()]) }}</span>
                        <span class="badge badge-success">{{ __('loans.currency', ['currency' => $currency]) }}</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($loans->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                    <tr>
                                        <th>{{ __('loans.loan_id') }}</th>
                                        <th>{{ __('loans.customer') }}</th>
                                        <th>{{ __('loans.duka') }}</th>
                                        <th>{{ __('loans.loan_amount') }}</th>
                                        <th>{{ __('loans.paid_amount') }}</th>
                                        <th>{{ __('loans.balance') }}</th>
                                        <th>{{ __('loans.payment_status') }}</th>
                                        <th>{{ __('loans.date') }}</th>
                                        <th>{{ __('loans.actions') }}</th>
                                    </tr>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loans as $loan)
                                        @php
                                            $totalPaid = $loan->loanPayments->sum('amount');
                                            $balance = $loan->total_amount - $totalPaid;
                                            $paymentPercentage = $loan->total_amount > 0 ? ($totalPaid / $loan->total_amount) * 100 : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>#{{ $loan->id }}</strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $loan->customer->name }}</strong>
                                                    <br><small class="text-muted">{{ $loan->customer->phone }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $loan->duka->name }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $currency }} {{ number_format($loan->total_amount, 2) }}</strong>
                                            </td>
                                            <td>
                                                <span class="text-success">{{ $currency }} {{ number_format($totalPaid, 2) }}</span>
                                            </td>
                                            <td>
                                                @if($balance > 0)
                                                    <span class="text-danger"><strong>{{ $currency }} {{ number_format($balance, 2) }}</strong></span>
                                                @else
                                                    <span class="text-success"><strong>{{ __('loans.paid_off') }}</strong></span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar {{ $paymentPercentage >= 100 ? 'bg-success' : ($paymentPercentage >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                         role="progressbar"
                                                         style="width: {{ min($paymentPercentage, 100) }}%"
                                                         aria-valuenow="{{ $paymentPercentage }}"
                                                         aria-valuemin="0"
                                                         aria-valuemax="100">
                                                        {{ number_format($paymentPercentage, 1) }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <small>{{ $loan->created_at->format('M d, Y') }}</small>
                                                <br><small class="text-muted">{{ $loan->created_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <a href="{{ route('officer.loans.show', $loan->id) }}"
                                                   class="btn btn-sm btn-primary">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-1">
                                                        <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                                    </svg>
                                                    {{ __('loans.view_details') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $loans->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-muted mb-3">
                                <path d="M12 2L13.09 8.26L20 9L13.09 9.74L12 16L10.91 9.74L4 9L10.91 8.26L12 2Z" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <h4 class="text-muted">{{ __('loans.no_loans_found') }}</h4>
                            <p class="text-muted">{{ __('loans.no_loans_desc') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
