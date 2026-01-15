<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\TenantOfficer;

class OfficerSearch extends Component
{
    public $search = '';
    public $results = [];

    public function mount()
    {
        // Initialize
    }

    public function updatedSearch()
    {
        if (strlen($this->search) > 2) {
            $user = auth()->user();

            // Get officer's assigned dukas
            $assignedDukas = TenantOfficer::where('officer_id', $user->id)
                ->where('status', true)
                ->pluck('duka_id');

            if ($assignedDukas->isEmpty()) {
                $this->results = [];
                return;
            }

            $tenantId = TenantOfficer::where('officer_id', $user->id)->first()->tenant_id;

            // Search customers
            $customers = Customer::where('tenant_id', $tenantId)
                ->whereIn('duka_id', $assignedDukas)
                ->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->with(['sales' => function($q) use ($assignedDukas) {
                    $q->whereIn('duka_id', $assignedDukas)
                      ->with('saleItems.product')
                      ->latest()
                      ->take(5);
                }])
                ->take(10)
                ->get();

            $this->results = $customers;
        } else {
            $this->results = [];
        }
    }

    public function render()
    {
        return view('livewire.officer-search');
    }
}
