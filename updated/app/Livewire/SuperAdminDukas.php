<?php

namespace App\Livewire;

use App\Models\Duka;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class SuperAdminDukas extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $planStatusFilter = '';
    public $viewMode = 'table'; // table or grid
    public $selectedDukas = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'planStatusFilter' => ['except' => ''],
        'viewMode' => ['except' => 'table'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPlanStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedDukas = $this->getDukasQuery()->pluck('id')->toArray();
        } else {
            $this->selectedDukas = [];
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->planStatusFilter = '';
        $this->resetPage();
    }

    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'table' ? 'grid' : 'table';
    }

    public function bulkDelete()
    {
        if (empty($this->selectedDukas)) {
            session()->flash('error', 'No dukas selected for deletion.');
            return;
        }

        $dukas = Duka::whereIn('id', $this->selectedDukas)->get();
        $deletedCount = 0;
        $skippedDukas = [];

        foreach ($dukas as $duka) {
            // Check if duka has active subscriptions
            $activeSubscriptions = $duka->dukaSubscriptions()->where('status', 'active')->count();
            if ($activeSubscriptions > 0) {
                $skippedDukas[] = $duka->name . ' (active subscriptions)';
                continue;
            }

            // Check if duka has sales
            $salesCount = $duka->sales()->count();
            if ($salesCount > 0) {
                $skippedDukas[] = $duka->name . ' (has sales records)';
                continue;
            }

            $duka->delete();
            $deletedCount++;
        }

        $this->selectedDukas = [];

        $message = "Successfully deleted {$deletedCount} duka(s).";
        if (!empty($skippedDukas)) {
            $message .= " Skipped: " . implode(', ', $skippedDukas) . " (protected).";
        }

        session()->flash('success', $message);
    }

    private function getDukasQuery()
    {
        $query = Duka::with(['tenant.user', 'dukaSubscriptions.plan']);

        // Search functionality
        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('location', 'like', '%' . $search . '%')
                  ->orWhereHas('tenant.user', function($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }

        // Status filter
        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        // Plan expiry filter
        if (!empty($this->planStatusFilter)) {
            if ($this->planStatusFilter === 'expired') {
                $query->whereHas('dukaSubscriptions', function($q) {
                    $q->where('status', 'active')
                      ->where('end_date', '<', Carbon::now());
                });
            } elseif ($this->planStatusFilter === 'active') {
                $query->whereHas('dukaSubscriptions', function($q) {
                    $q->where('status', 'active')
                      ->where('end_date', '>=', Carbon::now());
                });
            } elseif ($this->planStatusFilter === 'no_plan') {
                $query->whereDoesntHave('dukaSubscriptions', function($q) {
                    $q->where('status', 'active');
                });
            }
        }

        return $query;
    }

    public function render()
    {
        $query = $this->getDukasQuery();

        // Get statistics
        $totalDukas = (clone $query)->count();
        $activeDukas = (clone $query)->where('status', 'active')->count();
        $activePlans = (clone $query)->whereHas('dukaSubscriptions', function($q) {
            $q->where('status', 'active')->where('end_date', '>=', Carbon::now());
        })->count();
        $expiredPlans = (clone $query)->whereHas('dukaSubscriptions', function($q) {
            $q->where('status', 'active')->where('end_date', '<', Carbon::now());
        })->count();

        $dukas = $query->orderBy('created_at', 'desc')->paginate(10);

        // Calculate expiry status for each duka
        foreach ($dukas as $duka) {
            $activeSubscription = $duka->dukaSubscriptions->where('status', 'active')->first();
            if ($activeSubscription) {
                $statusInfo = $activeSubscription->getStatusWithDays();
                $duka->plan_status = $statusInfo['status'];
                $duka->days_remaining = $statusInfo['days_remaining'];
                $duka->plan_name = $activeSubscription->plan->name ?? 'N/A';
            } else {
                $duka->plan_status = 'no_plan';
                $duka->days_remaining = 0;
                $duka->plan_name = null;
            }
        }

        return view('livewire.super-admin-dukas', compact(
            'dukas',
            'totalDukas',
            'activeDukas',
            'activePlans',
            'expiredPlans'
        ));
    }
}
