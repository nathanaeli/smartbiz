<div>
    <div class="container-fluid">
        <div class="row">
            <!-- Total Products Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-2 text-secondary text-uppercase font-size-12 fw-bold tracking-wide">{{ __('messages.total_products') }}</p>
                                <h4 class="mb-0 fw-bolder text-dark">{{ number_format($totalProducts, 0) }}</h4>
                            </div>
                            <div class="p-3 bg-soft-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="ri-product-hunt-line h4 mb-0 text-primary"></i>
                            </div>
                        </div>
                        <div class="mt-3 d-flex align-items-center">
                            <span class="badge bg-soft-primary text-primary me-2"><i class="ri-arrow-up-line"></i> {{ __('messages.products') }}</span>
                            <small class="text-muted">{{ __('messages.in_stock') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Customers Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-2 text-secondary text-uppercase font-size-12 fw-bold tracking-wide">{{ __('messages.total_customers') }}</p>
                                <h4 class="mb-0 fw-bolder text-dark">{{ number_format($totalCustomers, 0) }}</h4>
                            </div>
                            <div class="p-3 bg-soft-success rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="ri-team-line h4 mb-0 text-success"></i>
                            </div>
                        </div>
                        <div class="mt-3 d-flex align-items-center">
                            <span class="badge bg-soft-success text-success me-2"><i class="ri-user-add-line"></i> Active</span>
                            <small class="text-muted">{{ __('messages.total_customers') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Loan Sales Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-2 text-secondary text-uppercase font-size-12 fw-bold tracking-wide">{{ __('messages.total_loan_sales') }}</p>
                                <h4 class="mb-0 fw-bolder text-dark">TZS {{ number_format($totalLoanSales, 0) }}</h4>
                            </div>
                            <div class="p-3 bg-soft-warning rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="ri-money-dollar-circle-line h4 mb-0 text-warning"></i>
                            </div>
                        </div>
                        <div class="mt-3 d-flex align-items-center">
                             <span class="badge bg-soft-warning text-warning me-2"><i class="ri-time-line"></i> Pending</span>
                             <small class="text-muted">Receivables</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Profit/Loss Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-2 text-secondary text-uppercase font-size-12 fw-bold tracking-wide">
                                    {{ $totalProfit >= 0 ? __('messages.total_profit') : __('messages.total_loss') }}
                                </p>
                                <h4 class="mb-0 fw-bolder {{ $totalProfit >= 0 ? 'text-dark' : 'text-danger' }}">
                                    {{ $totalProfit >= 0 ? '+' : '-' }}TZS {{ number_format(abs($totalProfit), 0) }}
                                </h4>
                            </div>
                            <div class="p-3 {{ $totalProfit >= 0 ? 'bg-soft-info' : 'bg-soft-danger' }} rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="{{ $totalProfit >= 0 ? 'ri-bar-chart-groupped-line text-info' : 'ri-arrow-down-circle-line text-danger' }} h4 mb-0"></i>
                            </div>
                        </div>
                         <div class="mt-3 d-flex align-items-center">
                            <span class="badge {{ $totalProfit >= 0 ? 'bg-soft-info text-info' : 'bg-soft-danger text-danger' }} me-2">
                                <i class="{{ $totalProfit >= 0 ? 'ri-line-chart-line' : 'ri-arrow-down-line' }}"></i> {{ __('messages.financial_status') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Loan Activities (Larger Width) -->
            <div class="col-lg-12 mb-4">
                <div class="card border-0 shadow-sm" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-header bg-transparent border-bottom-0 pb-0 d-flex justify-content-between align-items-center mt-3">
                        <h5 class="card-title fw-bold mb-0 text-dark">{{ __('messages.recent_loan_activities') }}</h5>
                    </div>
                    <div class="card-body">
                         @if ($recentLoanActivities->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless align-middle mb-0">
                                <thead class="bg-light text-secondary text-uppercase font-size-12">
                                    <tr>
                                        <th scope="col" class="py-3 ps-3 rounded-start">{{ __('messages.customer') }}</th>
                                        <th scope="col" class="py-3">{{ __('messages.date') }}</th>
                                        <th scope="col" class="py-3">{{ __('messages.amount') }}</th>
                                        <th scope="col" class="py-3 pe-3 text-end rounded-end">{{ __('messages.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentLoanActivities as $payment)
                                    <tr class="shadow-hover">
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-40 rounded-circle bg-soft-primary d-flex align-items-center justify-content-center me-3 fw-bold text-primary">
                                                    {{ substr($payment->sale->customer->name ?? 'W', 0, 1) }}
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark font-size-14">{{ $payment->sale->customer->name ?? 'Walk-in Customer' }}</h6>
                                                    <small class="text-muted">{{ __('messages.loan_payment') }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted font-weight-500">{{ $payment->payment_date->format('M d, Y') }}</td>
                                        <td class="fw-bold text-dark">TZS {{ number_format($payment->amount, 0) }}</td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-soft-success text-success px-3 py-2 rounded-pill">{{ __('messages.paid') }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <div class="avatar-60 rounded-circle bg-soft-secondary d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="ri-secure-payment-line h3 mb-0 text-secondary"></i>
                            </div>
                            <h6 class="text-muted">{{ __('messages.no_loan_activities_found') }}</h6>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Duka Analytics -->
            <div class="col-lg-12">
                 <div class="card border-0 shadow-sm" data-aos="fade-up" data-aos-delay="300">
                    <div class="card-header bg-transparent border-bottom-0 pb-0 d-flex justify-content-between align-items-center mt-3">
                        <h5 class="card-title fw-bold mb-0 text-dark">{{ __('messages.duka_performance_analytics') }}</h5>
                    </div>
                    <div class="card-body">
                        @if ($dukaAnalytics->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless align-middle mb-0">
                                <thead class="bg-light text-secondary text-uppercase font-size-12">
                                    <tr>
                                        <th scope="col" class="py-3 ps-3 rounded-start">{{ __('messages.duka_name') }}</th>
                                        <th scope="col" class="py-3 text-center">{{ __('messages.sales_count') }}</th>
                                        <th scope="col" class="py-3 text-end">{{ __('messages.total_sales_amount') }}</th>
                                        <th scope="col" class="py-3 text-end">{{ __('messages.profit') }}</th>
                                        <th scope="col" class="py-3 text-end">{{ __('messages.loan_sales') }}</th>
                                        <th scope="col" class="py-3 text-end">{{ __('messages.stock_value') }}</th>
                                        <th scope="col" class="py-3 text-center">{{ __('messages.products') }}</th>
                                        <th scope="col" class="py-3 pe-3 text-center rounded-end">{{ __('messages.low_stock') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dukaAnalytics as $duka)
                                    <tr class="border-bottom border-light">
                                        <td class="ps-3 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-40 rounded-3 bg-soft-warning d-flex align-items-center justify-content-center me-3">
                                                    <i class="ri-store-2-fill h5 mb-0 text-warning"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark font-size-14">{{ $duka['name'] }}</h6>
                                                    <small class="text-muted">{{ $duka['location'] }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-bolder text-dark">{{ $duka['sales_count'] }}</span>
                                        </td>
                                        <td class="text-end fw-bold text-dark">
                                            TZS {{ number_format($duka['total_sales_amount']) }}
                                        </td>
                                        <td class="text-end">
                                            <span class="text-success fw-bold">+ {{ number_format($duka['profit']) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-muted">{{ number_format($duka['loan_sales']) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-soft-info text-info">TZS {{ number_format($duka['stock_value']) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-bold">{{ $duka['products_count'] }}</span>
                                        </td>
                                        <td class="text-center pe-3">
                                            @if ($duka['low_stock_count'] > 0)
                                                <span class="badge bg-danger rounded-pill px-3">{{ $duka['low_stock_count'] }} items</span>
                                            @else
                                                <span class="badge bg-success rounded-pill px-3">Good</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <div class="avatar-60 rounded-circle bg-soft-secondary d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="ri-store-line h3 mb-0 text-secondary"></i>
                            </div>
                            <h6 class="text-muted">{{ __('messages.no_dukas_found') }}</h6>
                            <p class="text-muted font-size-12">{{ __('messages.create_dukas_to_see_performance') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
