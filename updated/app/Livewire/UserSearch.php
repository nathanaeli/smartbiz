<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Tenant;

class UserSearch extends Component
{
    public $search = '';
    public $showResults = false;

    public function updatedSearch()
    {
        $this->showResults = !empty($this->search);
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->showResults = false;
    }

    public function getUsersProperty()
    {
        if (empty($this->search)) {
            return collect();
        }

        $query = User::where(function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->with(['tenant', 'officers.duka'])
            ->limit(10);

        // Filter by tenant if user is not superadmin
        if (auth()->user()->role !== 'superadmin') {
            // For tenants, show officers assigned to their tenant
            $query->whereHas('officerAssignments', function($q) {
                $q->where('tenant_id', auth()->user()->id)
                  ->where('status', true);
            });
        }

        return $query->get();
    }

    public function render()
    {
        return view('livewire.user-search');
    }
}
