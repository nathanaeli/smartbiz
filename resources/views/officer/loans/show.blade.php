@extends('layouts.officer')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Loan Header -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                            <path d="M12 2L13.09 8.26L20 9L13.09 9.74L12 16L10.91 9.74L4 9L10.91 8.26L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Loan Details - #{{ $loan->id }}
                    </h4>
                    <div class="d-flex gap-2">
                        <span class="badge badge-info">{{ $loan->duka->name }}</span>
                        <span class="badge badge-primary">{{ $currency }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Customer Information</h5>
                            <div class="mb-3">
                                <strong>Name:</strong> {{ $loan->customer->name }}
                            </div>
                            <div class="mb-3">
                                <strong>Phone:</strong> {{ $loan->customer->phone }}
                            </div>
                            <div class="mb-3">
                                <strong>Email:</strong> {{ $loan->customer->email }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>Loan Summary</h5>
                            <div class="mb-3">
                                <strong>Loan Amount:</strong> {{ $currency }} {{ number_format($loan->total_amount, 2) }}
                            </div>
                            <div class="mb-3">
                                <strong>Total Paid:</strong> <span class="text-success">{{ $currency }} {{ number_format($totalPaid, 2) }}</span>
                            </div>
                            <div class="mb-3">
                                <strong>Remaining Balance:</strong>
                                @if($remainingBalance > 0)
                                    <span class="text-danger"><strong>{{ $currency }} {{ number_format($remainingBalance, 2) }}</strong></span>
                                @else
                                    <span class="text-success"><strong>Paid Off</strong></span>
                                @endif
                            </div>
                            <div class="mb-3">
                                <strong>Payment Progress:</strong>
                                @php
                                    $paymentPercentage = $loan->total_amount > 0 ? ($totalPaid / $loan->total_amount) * 100 : 0;
                                @endphp
                                <div class="progress mt-2" style="height: 25px;">
                                    <div class="progress-bar {{ $paymentPercentage >= 100 ? 'bg-success' : ($paymentPercentage >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                         role="progressbar"
                                         style="width: {{ min($paymentPercentage, 100) }}%"
                                         aria-valuenow="{{ $paymentPercentage }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                        {{ number_format($paymentPercentage, 1) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loan Items -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                            <path d="M9 5H7C5.89543 5 5 5.89543 5 7V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V7C19 5.89543 18.1046 5 17 5H15M9 5C9 6.10457 9.89543 7 11 7H13C14.1046 7 15 6.10457 15 5M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Loan Items
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Unit Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($loan->saleItems as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->product->name }}</strong>
                                        </td>
                                        <td>{{ $item->product->sku }}</td>
                                        <td>{{ $currency }} {{ number_format($item->unit_price, 2) }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td><strong>{{ $currency }} {{ number_format($item->total, 2) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                            <path d="M12 8C13.6569 8 15 6.65685 15 5C15 3.34315 13.6569 2 12 2C10.3431 2 9 3.34315 9 5C9 6.65685 10.3431 8 12 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 14C13.6569 14 15 12.6569 15 11C15 9.34315 13.6569 8 12 8C10.3431 8 9 9.34315 9 11C9 12.6569 10.3431 14 12 14Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 20C13.6569 20 15 18.6569 15 17C15 15.3431 13.6569 14 12 14C10.3431 14 9 15.3431 9 17C9 18.6569 10.3431 20 12 20Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Payment History
                    </h5>
                    <span class="badge badge-info">{{ $loan->loanPayments->count() }} payments</span>
                </div>
                <div class="card-body">
                    @if($loan->loanPayments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Notes</th>
                                        <th>Recorded By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loan->loanPayments as $payment)
                                        <tr>
                                            <td>
                                                <strong>{{ $payment->payment_date->format('M d, Y') }}</strong>
                                                <br><small class="text-muted">{{ $payment->payment_date->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <span class="text-success"><strong>{{ $currency }} {{ number_format($payment->amount, 2) }}</strong></span>
                                            </td>
                                            <td>{{ $payment->notes ?: 'No notes' }}</td>
                                            <td>{{ $payment->user->name ?? 'Unknown' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-muted mb-3">
                                <path d="M12 8C13.6569 8 15 6.65685 15 5C15 3.34315 13.6569 2 12 2C10.3431 2 9 3.34315 9 5C9 6.65685 10.3431 8 12 8Z" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 14C13.6569 14 15 12.6569 15 11C15 9.34315 13.6569 8 12 8C10.3431 8 9 9.34315 9 11C9 12.6569 10.3431 14 12 14Z" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 20C13.6569 20 15 18.6569 15 17C15 15.3431 13.6569 14 12 14C10.3431 14 9 15.3431 9 17C9 18.6569 10.3431 20 12 20Z" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <h5 class="text-muted">No Payments Recorded</h5>
                            <p class="text-muted">No loan payments have been recorded for this loan yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Record Payment -->
            @if($remainingBalance > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                            <path d="M12 2V6M12 2H10M12 2H14M12 6V10M12 10H10M12 10H14M12 14V18M12 18H10M12 18H14M12 22V18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        Record New Payment
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('officer.loans.payments.store', $loan->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Payment Amount ({{ $currency }})</label>
                                    <input type="number" class="form-control" id="amount" name="amount"
                                           step="0.01" min="0.01" max="{{ $remainingBalance }}"
                                           value="{{ old('amount') }}" required>
                                    <div class="form-text">Maximum: {{ $currency }} {{ number_format($remainingBalance, 2) }}</div>
                                    @error('amount')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_date" class="form-label">Payment Date</label>
                                    <input type="date" class="form-control" id="payment_date" name="payment_date"
                                           value="{{ old('payment_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                                    @error('payment_date')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                      placeholder="Add any notes about this payment...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Recording payment as: <strong>{{ auth()->user()->name }}</strong>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                                    <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Record Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @else
            <div class="card mt-4 border-success">
                <div class="card-body text-center py-4">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-success mb-3">
                        <path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.7089 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <h5 class="text-success">Loan Fully Paid!</h5>
                    <p class="text-muted mb-0">This loan has been completely paid off.</p>
                </div>
            </div>
            @endif

            <!-- Back Button -->
            <div class="mt-4">
                <a href="{{ route('officer.loanmanagement') }}" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                        <path d="M19 12H5M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Back to Loan Management
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
