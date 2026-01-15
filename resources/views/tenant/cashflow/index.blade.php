@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 card">
    <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
        <div>
            <h2 class="fw-bold mb-1">Cash Flow Statement</h2>
            <p class="text-muted">Tracking every cent in and out of your business.</p>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#manualTransactionModal">
                <i class="ri-add-line"></i> Add Record
            </button>

            @if($dukas->count() > 1)
            <div class="bg-white p-1 rounded border shadow-sm">
                <form action="{{ route('tenant.cashflow.index') }}" method="GET">
                    <select name="duka_id" class="form-select form-select-sm border-0 bg-light" onchange="this.form.submit()">
                        @foreach($dukas as $duka)
                            <option value="{{ $duka->id }}" {{ $selectedDukaId == $duka->id ? 'selected' : '' }}>
                                {{ $duka->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            @endif
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <p class="text-muted small fw-bold mb-1">TOTAL INFLOW</p>
                    <h3 class="mb-0 text-success">{{ number_format($totalIncome) }} TSH</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-danger border-4">
                <div class="card-body">
                    <p class="text-muted small fw-bold mb-1">TOTAL OUTFLOW</p>
                    <h3 class="mb-0 text-danger">{{ number_format($totalExpense) }} TSH</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <p class="text-muted small fw-bold mb-1">NET CASH POSITION</p>
                    <h3 class="mb-0 {{ $netCashFlow >= 0 ? 'text-primary' : 'text-warning' }}">
                        {{ number_format($netCashFlow) }} TSH
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Transaction History</h5>
                </div>
                <div class="col-auto">
                    <form action="{{ route('tenant.cashflow.index') }}" method="GET" class="d-flex gap-2">
                        <input type="hidden" name="duka_id" value="{{ $selectedDukaId }}">
                        <input type="date" name="start_date" value="{{ $start->format('Y-m-d') }}" class="form-control form-control-sm">
                        <input type="date" name="end_date" value="{{ $end->format('Y-m-d') }}" class="form-control form-control-sm">
                        <button type="submit" class="btn btn-sm btn-dark px-3">Filter</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th class="text-end">Inflow (+)</th>
                            <th class="text-end pe-4">Outflow (-)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $trx)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold">{{ $trx->transaction_date->format('d M, Y') }}</span>
                            </td>
                            <td><span class="badge bg-light text-dark">{{ strtoupper($trx->category) }}</span></td>
                            <td>{{ $trx->description }}</td>
                            <td class="text-end text-success fw-bold">{{ $trx->type == 'income' ? number_format($trx->amount) : '-' }}</td>
                            <td class="text-end text-danger fw-bold pe-4">{{ $trx->type == 'expense' ? number_format($trx->amount) : '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-5">No transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="manualTransactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('tenant.cashflow.store') }}" method="POST">
            @csrf
            <input type="hidden" name="duka_id" value="{{ $selectedDukaId }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="expense">Expense (Money Out)</option>
                            <option value="income">Income (Money In)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="rent">Rent</option>
                            <option value="salary">Salary</option>
                            <option value="utility">Utility (Luku/Water)</option>
                            <option value="transport">Transport</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Save Record</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
