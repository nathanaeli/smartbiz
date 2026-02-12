@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Sale #{{ $sale->id }} Details</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-sm">Back to Sales</a>
                        <a href="{{ route('sales.invoice', $sale->id) }}" class="btn btn-primary btn-sm" target="_blank">
                            <i class="fas fa-file-invoice"></i> View Invoice
                        </a>
                        @if($sale->is_loan)
                            <a href="#" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">Make Payment</a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <strong>Date:</strong> {{ $sale->created_at->format('d/m/Y H:i') }}<br>
                            <strong>Customer:</strong> {{ $sale->customer ? $sale->customer->name : 'N/A' }}<br>
                            <strong>Duka:</strong> {{ $sale->duka->name }}<br>
                            <strong>Type:</strong> {{ $sale->is_loan ? 'Loan' : 'Sale' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Total Amount:</strong> {{ number_format($sale->total_amount, 2) }} TSH<br>
                            <strong>Discount Amount:</strong> {{ number_format($sale->discount_amount, 2) }} TSH<br>
                            <strong>Profit/Loss:</strong> {{ number_format($sale->profit_loss, 2) }} TSH<br>
                            @if($sale->discount_reason)
                                <strong>Discount Reason:</strong> {{ $sale->discount_reason }}<br>
                            @endif
                            @if($sale->is_loan)
                                <strong>Due Date:</strong> {{ $sale->due_date ? $sale->due_date->format('d/m/Y') : 'Not set' }}<br>
                                <strong>Total Paid:</strong> {{ number_format($sale->total_payments, 2) }} TSH<br>
                                <strong>Remaining Balance:</strong>
                                <span class="fw-bold {{ $sale->remaining_balance > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($sale->remaining_balance, 2) }} TSH
                                </span><br>
                                <strong>Payment Status:</strong>
                                <span class="badge bg-{{ $sale->payment_status === 'Fully Paid' ? 'success' : ($sale->payment_status === 'Partially Paid' ? 'warning' : 'danger') }}">
                                    {{ $sale->payment_status }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <h6>Sale Items</h6>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Discount</th>
                                    <th>Total</th>
                                    @if($sale->is_loan)
                                        <th>Remaining Loan</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->saleItems as $item)
                                    @php
                                        $remainingLoan = 0;
                                        if ($sale->is_loan && $sale->remaining_balance > 0) {
                                            // Calculate proportional remaining loan for this item
                                            $itemProportion = $item->total / $sale->total_amount;
                                            $remainingLoan = $sale->remaining_balance * $itemProportion;
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ number_format($item->unit_price, 2) }} TSH</td>
                                        <td>{{ number_format($item->discount_amount, 2) }} TSH</td>
                                        <td>{{ number_format($item->total, 2) }} TSH</td>
                                        @if($sale->is_loan)
                                            <td>
                                                <span class="fw-bold {{ $remainingLoan > 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format($remainingLoan, 2) }} TSH
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($sale->is_loan)
                        <h6>Loan Payments</h6>
                        @php
                            $payments = $sale->loanPayments ?? collect();
                        @endphp
                        @if($payments->isEmpty())
                            <p class="text-muted">No payments recorded yet.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Cumulative Paid</th>
                                            <th>Notes</th>
                                            <th>Recorded By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $cumulative = 0; @endphp
                                        @foreach($payments->sortBy('payment_date') as $payment)
                                            @php $cumulative += $payment->amount; @endphp
                                            <tr>
                                                <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                                <td>{{ number_format($payment->amount, 2) }} TSH</td>
                                                <td class="fw-bold">{{ number_format($cumulative, 2) }} TSH</td>
                                                <td>{{ $payment->notes ?: 'N/A' }}</td>
                                                <td>{{ $payment->user->name }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="mt-3 p-3 bg-light rounded">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Total Amount:</strong> {{ number_format($sale->total_amount, 2) }} TSH
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Total Paid:</strong> {{ number_format($sale->total_payments, 2) }} TSH
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Remaining Balance:</strong>
                                            <span class="fw-bold {{ $sale->remaining_balance > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ number_format($sale->remaining_balance, 2) }} TSH
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
@if($sale->is_loan)
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Make Payment for Loan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('loan.payments.store', $sale->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Payment Amount</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Payment Date</label>
                        <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Make Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
