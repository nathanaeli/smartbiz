<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="ri-dashboard-line me-2"></i>
                            {{ __('messages.business_dashboard_comprehensive_analytics') }}
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Total Products Card -->
                            <div class="col-md-3 mb-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <div class="d-flex align-items-center justify-content-center mb-3">
                                            <div class="icon-wrapper p-3 rounded-circle"
                                                style="background: rgba(255,255,255,0.2);">
                                                <i class="ri-product-hunt-line" style="font-size: 2rem;"></i>
                                            </div>
                                        </div>
                                        <h2 class="mb-2">{{ number_format($totalProducts, 0) }}</h2>
                                        <p class="mb-0 opacity-75">{{ __('messages.total_products') }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Customers Card -->
                            <div class="col-md-3 mb-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <div class="d-flex align-items-center justify-content-center mb-3">
                                            <div class="icon-wrapper p-3 rounded-circle"
                                                style="background: rgba(255,255,255,0.2);">
                                                <i class="ri-team-line" style="font-size: 2rem;"></i>
                                            </div>
                                        </div>
                                        <h2 class="mb-2">{{ number_format($totalCustomers, 0) }}</h2>
                                        <p class="mb-0 opacity-75">{{ __('messages.total_customers') }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Loan Sales Card -->
                            <div class="col-md-3 mb-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <div class="d-flex align-items-center justify-content-center mb-3">
                                            <div class="icon-wrapper p-3 rounded-circle"
                                                style="background: rgba(255,255,255,0.2);">
                                                <i class="ri-money-dollar-circle-line" style="font-size: 2rem;"></i>
                                            </div>
                                        </div>
                                        <h2 class="mb-2">TZS {{ number_format($totalLoanSales, 0) }}</h2>
                                        <p class="mb-0 opacity-75">{{ __('messages.total_loan_sales') }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Profit/Loss Card -->
                            <div class="col-md-3 mb-4">
                                <div class="card {{ $totalProfit >= 0 ? 'bg-success' : 'bg-danger' }} text-white">
                                    <div class="card-body text-center">
                                        <div class="d-flex align-items-center justify-content-center mb-3">
                                            <div class="icon-wrapper p-3 rounded-circle"
                                                style="background: rgba(255,255,255,0.2);">
                                                <i class="{{ $totalProfit >= 0 ? 'ri-arrow-up-circle-line' : 'ri-arrow-down-circle-line' }}"
                                                    style="font-size: 2rem;"></i>
                                            </div>
                                        </div>
                                        <h2 class="mb-2">{{ $totalProfit >= 0 ? '+' : '-' }}TZS
                                            {{ number_format(abs($totalProfit), 0) }}</h2>
                                        <p class="mb-0 opacity-75">
                                            {{ $totalProfit >= 0 ? __('messages.total_profit') : __('messages.total_loss') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Loan Activities Section -->
                        @if ($recentLoanActivities->count() > 0)
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="ri-history-line me-2"></i>
                                                {{ __('messages.recent_loan_activities') }}
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('messages.date') }}</th>
                                                            <th>{{ __('messages.customer') }}</th>
                                                            <th>{{ __('messages.amount') }}</th>
                                                            <th>{{ __('messages.status') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($recentLoanActivities as $payment)
                                                            <tr>
                                                                <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                                                <td>{{ $payment->sale->customer->name ?? 'Walk-in Customer' }}</td>
                                                                <td>TZS {{ number_format($payment->amount, 0) }}</td>
                                                                <td>
                                                                    <span class="badge bg-success">{{ __('messages.paid') }}</span>
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
                        @else
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="text-center py-4">
                                        <i class="ri-money-dollar-circle-line text-muted"
                                            style="font-size: 3rem; opacity: 0.3;"></i>
                                        <h5 class="mt-3 text-muted">{{ __('messages.no_loan_activities_found') }}</h5>
                                        <p class="text-muted">{{ __('messages.loan_payments_will_appear_here') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Duka Analytics Table -->
                        @if ($dukaAnalytics->count() > 0)
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="ri-store-2-line me-2"></i>
                                                {{ __('messages.duka_performance_analytics') }}
                                            </h5>
                                        </div>
                                        <div class="card-body">

                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('messages.duka_name') }}</th>
                                                            <th>{{ __('messages.location') }}</th>
                                                            <th>{{ __('messages.sales_count') }}</th>
                                                            <th>{{ __('messages.profit_contribution') }}</th>
                                                            <th>{{ __('messages.total_sales_amount') }}</th> {{-- Add this header --}}
                                                            <th>{{ __('messages.loan_sales') }}</th>
                                                            <th>{{ __('messages.stock_value') }}</th>
                                                            <th>{{ __('messages.products') }}</th>
                                                            <th>{{ __('messages.low_stock') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($dukaAnalytics as $duka)
                                                            <tr>
                                                                <td>{{ $duka['name'] }}</td>
                                                                <td>{{ $duka['location'] }}</td>
                                                                <td>{{ $duka['sales_count'] }}</td>
                                                                <td>{{ number_format($duka['profit']) }}</td>
                                                                <td>{{ number_format($duka['total_sales_amount']) }}
                                                                </td> {{-- Add this cell --}}
                                                                <td>{{ number_format($duka['loan_sales']) }}</td>
                                                                <td>{{ number_format($duka['stock_value']) }}</td>
                                                                <td>{{ $duka['products_count'] }}</td>
                                                                <td>
                                                                    @if ($duka['low_stock_count'] > 0)
                                                                        <span
                                                                            class="badge bg-danger">{{ $duka['low_stock_count'] }}</span>
                                                                    @else
                                                                        <span class="badge bg-success">0</span>
                                                                    @endif
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
                        @else
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="text-center py-4">
                                        <i class="ri-store-2-line text-muted"
                                            style="font-size: 3rem; opacity: 0.3;"></i>
                                        <h5 class="mt-3 text-muted">{{ __('messages.no_dukas_found') }}</h5>
                                        <p class="text-muted">{{ __('messages.create_dukas_to_see_performance') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
