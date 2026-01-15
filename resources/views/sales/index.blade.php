@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">

                <!-- Analytics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Sales
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $sales->count() }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Revenue
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ number_format($sales->sum('total_amount'), 2) }} TSH
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Total Profit
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ number_format($sales->sum('profit_loss'), 2) }} TSH
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Average Sale
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ number_format($sales->count() > 0 ? $sales->sum('total_amount') / $sales->count() : 0, 2) }} TSH
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-comments fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Sales Records</h6>
                    </div>

                    <div class="card-body">
                        @if ($sales->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th>Duka</th>
                                            <th>Amount</th>
                                            <th>Profit/Loss</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sales as $sale)
                                            <tr>
                                                <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>

                                                <td>
                                                    <strong>{{ $sale->customer->name ?? 'Walk-in Customer' }}</strong>
                                                    @if ($sale->customer && $sale->customer->phone)
                                                        <br>
                                                        <small class="text-muted">{{ $sale->customer->phone }}</small>
                                                    @endif
                                                </td>

                                                <td>{{ $sale->duka->name ?? 'N/A' }}</td>

                                                <td>
                                                    <strong>
                                                        {{ number_format($sale->total_amount, 2) }} TSH
                                                    </strong>
                                                    @if ($sale->discount_amount > 0)
                                                        <br>
                                                        <small class="text-success">
                                                            -{{ number_format($sale->discount_amount, 2) }} TSH discount
                                                        </small>
                                                    @endif
                                                </td>

                                                <td>
                                                    <span
                                                        class="font-weight-bold {{ $sale->profit_loss >= 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ number_format($sale->profit_loss, 2) }} TSH
                                                    </span>
                                                </td>

                                                <!-- TYPE -->
                                                <td>
                                                    <span class="badge badge-success text-dark">
                                                        {{ $sale->is_loan ? 'Loan' : 'Cash' }}
                                                    </span>
                                                </td>

                                                <!-- STATUS -->
                                                <td>
                                                    @if ($sale->is_loan)
                                                        @php
                                                            $totalPaid = $sale->loanPayments->sum('amount');
                                                            $paymentStatus =
                                                                $totalPaid >= $sale->total_amount
                                                                    ? 'Paid'
                                                                    : ($totalPaid > 0
                                                                        ? 'Partial'
                                                                        : 'Unpaid');

                                                            $statusClass =
                                                                $paymentStatus === 'Paid'
                                                                    ? 'badge-success'
                                                                    : ($paymentStatus === 'Partial'
                                                                        ? 'badge-warning'
                                                                        : 'badge-danger');
                                                        @endphp

                                                        <span class="badge {{ $statusClass }} text-dark">
                                                            {{ $paymentStatus }}
                                                        </span>
                                                    @else
                                                        <span class="badge badge-success text-dark">Paid</span>
                                                    @endif
                                                </td>

                                                <!-- ACTIONS -->
                                                <td>
                                                    <div class="btn-group" role="group">

                                                        <!-- VIEW -->
                                                        <a href="{{ route('sales.show', $sale) }}"
                                                            class="btn btn-info btn-sm" title="View">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                                                height="16" fill="currentColor" viewBox="0 0 16 16">
                                                                <path
                                                                    d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8z" />
                                                                <path d="M8 5a3 3 0 1 0 0 6 3 3 0 0 0 0-6z" />
                                                            </svg>
                                                        </a>

                                                        <!-- EDIT -->
                                                        <a href="{{ route('sales.edit', $sale) }}"
                                                            class="btn btn-warning btn-sm" title="Edit">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                                                height="16" fill="currentColor" viewBox="0 0 16 16">
                                                                <path
                                                                    d="M15.502 1.94a.5.5 0 0 1 0 .706l-1.439 1.439-2.121-2.121 1.439-1.439a.5.5 0 0 1 .707 0l1.414 1.415z" />
                                                                <path d="M13.061 4.561 11.5 3 4 10.5V12h1.5l7.561-7.439z" />
                                                                <path fill-rule="evenodd"
                                                                    d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a.5.5 0 0 0 0-1h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 0-1 0v11z" />
                                                            </svg>
                                                        </a>

                                                    </div>
                                                </td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i>
                                <h5 class="text-gray-600">No sales found</h5>
                                <p class="text-gray-500">Get started by creating your first sale.</p>
                                <a href="{{ route('sales.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create Sale
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                order: [
                    [0, "desc"]
                ],
                pageLength: 25,
                language: {
                    search: "Search sales:",
                    lengthMenu: "Show _MENU_ sales per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ sales",
                    infoEmpty: "No sales found",
                    infoFiltered: "(filtered from _MAX_ total sales)"
                }
            });
        });
    </script>
@endpush
