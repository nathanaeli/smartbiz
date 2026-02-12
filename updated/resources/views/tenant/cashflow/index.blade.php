@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 card">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
        <div>
            <h2 class="fw-bold mb-1">Cash Flow Statement</h2>
            <p class="text-muted">Tracking every cent in and out of your business.</p>
        </div>

        <div class="d-flex gap-2">
            <button type="button"
                class="btn btn-primary shadow-sm"
                data-bs-toggle="modal"
                data-bs-target="#manualTransactionModal">
                <i class="ri-add-line"></i> Add Record
            </button>

            @if($dukas->count() > 1)
            <form action="{{ route('tenant.cashflow.index') }}" method="GET">
                <select name="duka_id"
                    class="form-select form-select-sm"
                    onchange="this.form.submit()">
                    @foreach($dukas as $duka)
                    <option value="{{ $duka->id }}"
                        {{ $selectedDukaId == $duka->id ? 'selected' : '' }}>
                        {{ $duka->name }}
                    </option>
                    @endforeach
                </select>
            </form>
            @endif
        </div>
    </div>

    {{-- SUMMARY --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <p class="text-muted small fw-bold">TOTAL INFLOW</p>
                    <h3 class="text-success">{{ number_format($totalIncome) }} TSH</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-start border-danger border-4">
                <div class="card-body">
                    <p class="text-muted small fw-bold">TOTAL OUTFLOW</p>
                    <h3 class="text-danger">{{ number_format($totalExpense) }} TSH</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <p class="text-muted small fw-bold">NET CASH POSITION</p>
                    <h3 class="{{ $netCashFlow >= 0 ? 'text-primary' : 'text-warning' }}">
                        {{ number_format($netCashFlow) }} TSH
                    </h3>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Transaction History</h5>

            <form action="{{ route('tenant.cashflow.index') }}" method="GET" class="d-flex gap-2">
                <input type="hidden" name="duka_id" value="{{ $selectedDukaId }}">
                <input type="date" name="start_date" value="{{ $start->format('Y-m-d') }}" class="form-control form-control-sm">
                <input type="date" name="end_date" value="{{ $end->format('Y-m-d') }}" class="form-control form-control-sm">
                <button class="btn btn-sm btn-dark">Filter</button>
                <a href="{{ route('tenant.cashflow.index', ['duka_id' => $selectedDukaId, 'start_date' => $start->toDateString(), 'end_date' => $end->toDateString(), 'export' => 'pdf']) }}" class="btn btn-sm btn-outline-danger">
                    <i class="ri-file-pdf-line"></i> PDF
                </a>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="datatable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th class="text-end">Inflow (+)</th>
                            <th class="text-end">Outflow (-)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $trx)
                        <tr>
                            <td>{{ $trx->transaction_date->format('d M, Y') }}</td>
                            <td>{{ strtoupper($trx->category) }}</td>
                            <td>{{ $trx->description }}</td>
                            <td class="text-end text-success">
                                {{ $trx->type === 'income' ? number_format($trx->amount) : '' }}
                            </td>
                            <td class="text-end text-danger">
                                {{ $trx->type === 'expense' ? number_format($trx->amount) : '' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td>-</td>
                            <td>-</td>
                            <td class="text-center text-muted">No transactions found</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL --}}
<div class="modal fade" id="manualTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('tenant.cashflow.store') }}" method="POST">
            @csrf
            <input type="hidden" name="duka_id" value="{{ $selectedDukaId }}">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Transaction</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label>Type</label>
                        <select name="type" class="form-select">
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Category</label>
                        <select name="category" class="form-select">
                            <option value="rent">Rent</option>
                            <option value="salary">Salary</option>
                            <option value="utility">Utility</option>
                            <option value="transport">Transport</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Amount</label>
                        <input type="number" name="amount" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Date</label>
                        <input type="date" name="transaction_date"
                            value="{{ date('Y-m-d') }}"
                            class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary w-100">Save Record</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#datatable').DataTable({
            pageLength: 10,
            order: [
                [0, 'desc']
            ],
            destroy: true
        });
    });
</script>
@endpush