<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Duka;
use App\Models\TenantAccount;

class Salenow extends Component
{
    public $selectedDukaId;
    public $dukaList;
    public $search = '';
    public $statusFilter = 'all';

    public function mount()
    {
        $user     = auth()->user();
        $tenantId = $user->tenant->id;
        $this->dukaList = Duka::where('tenant_id', $tenantId)
            ->with(['customers', 'sales' => function($query) {
                $query->whereDate('created_at', '>=', now()->subDays(30));
            }])
            ->withCount(['customers'])
            ->get();

        // Add products count (only products with stock > 0) and recent sales count to each duka
        $this->dukaList->each(function($duka) {
            $duka->products_count = $duka->stocks()->where('quantity', '>', 0)->count();
            $duka->recent_sales_count = $duka->sales->count();
        });

        $account = TenantAccount::where('tenant_id', $tenantId)->first();
        $this->currency = $account->currency ?? 'TZS';

        // Auto-select if only one duka exists
        if ($this->dukaList->count() == 1) {
            $this->selectedDukaId = $this->dukaList->first()->id;
            // Auto-navigate to the sale process
            $this->selectDuka($this->selectedDukaId);
        } elseif ($this->dukaList->isNotEmpty()) {
            $this->selectedDukaId = $this->dukaList->first()->id;
        }
    }

    public function updatedSearch()
    {
        $this->filterDukas();
    }

    public function updatedStatusFilter()
    {
        $this->filterDukas();
    }

    private function filterDukas()
    {
        $user = auth()->user();
        $tenantId = $user->tenant->id;

        $query = Duka::where('tenant_id', $tenantId)
            ->with(['customers', 'sales' => function($query) {
                $query->whereDate('created_at', '>=', now()->subDays(30));
            }])
            ->withCount(['customers']);

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('location', 'like', '%' . $this->search . '%')
                  ->orWhere('manager_name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $this->dukaList = $query->get();

        // Add products count (only products with stock > 0) and recent sales count to each duka
        $this->dukaList->each(function($duka) {
            $duka->products_count = $duka->stocks()->where('quantity', '>', 0)->count();
            $duka->recent_sales_count = $duka->sales->count();
        });

        // Reset selection if filtered list doesn't contain current selection
        if ($this->selectedDukaId && !$this->dukaList->contains('id', $this->selectedDukaId)) {
            $this->selectedDukaId = $this->dukaList->isNotEmpty() ? $this->dukaList->first()->id : null;
        }
    }
    public function selectDuka($dukaId)
    {
        $this->selectedDukaId = $dukaId;

        // Navigate to the sale process page
        return redirect()->route('sale.process', ['dukaId' => $dukaId]);
    }

    public function render()
    {
        return view('livewire.salenow-smart');
    }
}
