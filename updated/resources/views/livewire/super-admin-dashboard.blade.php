<div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4>Super Admin Dashboard</h4>
                </div>
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-building fa-2x mb-2"></i>
                                    <h5 class="card-title">Total Tenants</h5>
                                    <h2>{{ number_format($totalTenants) }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <h5 class="card-title">Total Users</h5>
                                    <h2>{{ number_format($totalUsers) }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                                    <h5 class="card-title">Active Tenants</h5>
                                    <h2>{{ number_format($activeTenants) }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-store fa-2x mb-2"></i>
                                    <h5 class="card-title">Total Dukas</h5>
                                    <h2>{{ number_format($totalDukas) }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Overview -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <i class="fas fa-shopping-cart fa-2x mb-2 text-primary"></i>
                                    <h5 class="card-title">Total Sales</h5>
                                    <h3>{{ number_format($totalSales) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <i class="fas fa-dollar-sign fa-2x mb-2 text-success"></i>
                                    <h5 class="card-title">Total Revenue</h5>
                                    <h3>TZS {{ number_format($totalRevenue, 0) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Stats -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>This Month's Performance</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <h4 class="text-primary">{{ number_format($systemStats['total_sales_this_month']) }}</h4>
                                            <small class="text-muted">Sales This Month</small>
                                        </div>
                                        <div class="col-md-3">
                                            <h4 class="text-success">TZS {{ number_format($systemStats['total_revenue_this_month'], 0) }}</h4>
                                            <small class="text-muted">Revenue This Month</small>
                                        </div>
                                        <div class="col-md-3">
                                            <h4 class="text-info">{{ number_format($systemStats['new_tenants_this_month']) }}</h4>
                                            <small class="text-muted">New Tenants</small>
                                        </div>
                                        <div class="col-md-3">
                                            <h4 class="text-warning">{{ number_format($systemStats['new_users_this_month']) }}</h4>
                                            <small class="text-muted">New Users</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Recent Tenants</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Status</th>
                                                    <th>Joined</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($recentTenants as $tenant)
                                                <tr>
                                                    <td>{{ $tenant->name }}</td>
                                                    <td>{{ $tenant->email }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $tenant->status == 'active' ? 'success' : 'secondary' }}">
                                                            {{ $tenant->status }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $tenant->created_at->format('M d, Y') }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No tenants found</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Recent Users</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Role</th>
                                                    <th>Joined</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($recentUsers as $user)
                                                <tr>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>
                                                        @if($user->roles->count() > 0)
                                                            @foreach($user->roles as $role)
                                                                <span class="badge bg-primary">{{ $role->name }}</span>
                                                            @endforeach
                                                        @else
                                                            <span class="badge bg-secondary">No Role</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No users found</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Information -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>System Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>PHP Version:</strong> {{ $systemStats['php_version'] }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Laravel Version:</strong> {{ $systemStats['laravel_version'] }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Database:</strong> {{ $systemStats['database'] }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Environment:</strong> {{ $systemStats['environment'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
