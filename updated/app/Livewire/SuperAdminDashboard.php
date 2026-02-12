<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Sale;
use App\Models\Duka;
use App\Models\Customer;
use Carbon\Carbon;

class SuperAdminDashboard extends Component
{
    public $totalTenants;
    public $totalUsers;
    public $activeTenants;
    public $totalDukas;
    public $totalSales;
    public $totalRevenue;
    public $recentTenants;
    public $recentUsers;
    public $systemStats;

    public function mount()
    {
        // Basic counts
        $this->totalTenants = Tenant::count();
        $this->totalUsers = User::count();
        $this->activeTenants = Tenant::where('status', 'active')->count();
        $this->totalDukas = Duka::count();

        // Financial metrics
        $this->totalSales = Sale::count();
        $this->totalRevenue = Sale::sum('total_amount');

        // Recent data
        $this->recentTenants = Tenant::with('user')
            ->latest()
            ->take(5)
            ->get();

        $this->recentUsers = User::with('roles')
            ->latest()
            ->take(5)
            ->get();

        // System statistics
        $this->systemStats = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => config('database.default'),
            'environment' => app()->environment(),
            'total_sales_this_month' => Sale::whereMonth('created_at', Carbon::now()->month)->count(),
            'total_revenue_this_month' => Sale::whereMonth('created_at', Carbon::now()->month)->sum('total_amount'),
            'new_tenants_this_month' => Tenant::whereMonth('created_at', Carbon::now()->month)->count(),
            'new_users_this_month' => User::whereMonth('created_at', Carbon::now()->month)->count(),
        ];
    }

    public function render()
    {
        return view('livewire.super-admin-dashboard')
            ->layout('layouts.super-admin');
    }
}
