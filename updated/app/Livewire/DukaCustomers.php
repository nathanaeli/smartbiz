<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Duka;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CustomersImport; // Assuming an import class will be created or inline logic

class DukaCustomers extends Component
{
    use WithPagination, WithFileUploads;
    
    public $dukaId;
    public $showAddModal = false;
    public $showEditModal = false;
    public $showImportModal = false;
    public $importFile;

    public $editingCustomerId;
    public $name;
    public $email;
    public $phone;
    public $address;
    
    // ... existing properties ...
    public $loading = false;
    public $perPage = 10;
    public $search = '';
    public $sortBy = 'created_at';
    public $sortDir = 'desc';

    public function resetFilters()
    {
        $this->reset(['search', 'sortBy', 'sortDir']);
        $this->resetPage();
    }

    // ... existing rules ...
    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ];

        if ($this->showEditModal && $this->editingCustomerId) {
            $rules['email'] = 'nullable|email|unique:customers,email,' . $this->editingCustomerId;
        } else {
            $rules['email'] = 'nullable|email|unique:customers,email';
        }

        return $rules;
    }

    public function mount($dukaId)
    {
        $this->dukaId = $dukaId;
    }

    public function openAddModal()
    {
        $this->resetValidation();
        $this->reset(['name', 'email', 'phone', 'address', 'editingCustomerId', 'importFile']);
        $this->showAddModal = true;
        $this->showEditModal = false;
        $this->showImportModal = false;
    }

    public function openEditModal($customerId)
    {
        $this->resetValidation();
        $customer = Customer::findOrFail($customerId);

        if (!auth()->user()->tenant || auth()->user()->tenant->id != $customer->tenant_id) {
            session()->flash('error', 'Unauthorized access.');
            return;
        }

        $this->editingCustomerId = $customerId;
        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->address = $customer->address;

        $this->showEditModal = true;
        $this->showAddModal = false;
        $this->showImportModal = false;
    }

    public function openImportModal()
    {
        $this->reset(['importFile', 'showAddModal', 'showEditModal']);
        $this->showImportModal = true;
        $this->resetValidation();
    }

    public function importCustomers()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:csv,xlsx,xls|max:5120', // 5MB max
        ]);

        $duka = Duka::findOrFail($this->dukaId);
        
        // Ensure tenant ownership
        if (!auth()->user()->tenant || auth()->user()->tenant->id != $duka->tenant_id) {
             session()->flash('error', 'Unauthorized access.');
             return;
        }

        try {
            // Using logic similar to inline import or custom import class
            // For now, simpler implementation:
             $import = new \App\Imports\CustomersImport($duka->id, $duka->tenant_id);
             Excel::import($import, $this->importFile);

            $this->showImportModal = false;
            $this->importFile = null;
            session()->flash('success', 'Customers imported successfully!');
            $this->resetPage();

        } catch (\Exception $e) {
            session()->flash('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function saveCustomer()
    {
        $this->loading = true;
        $this->validate();

        $duka = Duka::findOrFail($this->dukaId);

        if (!auth()->user()->tenant || auth()->user()->tenant->id != $duka->tenant_id) {
            $this->loading = false;
            session()->flash('error', 'Unauthorized access.');
            return;
        }

        if ($this->showEditModal && $this->editingCustomerId) {
            $customer = Customer::findOrFail($this->editingCustomerId);
            
             if (!auth()->user()->tenant || auth()->user()->tenant->id != $customer->tenant_id) {
                 $this->loading = false;
                 session()->flash('error', 'Unauthorized access.');
                 return;
             }

            $customer->update([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
            ]);
            $message = 'Customer updated successfully!';
        } else {
            Customer::create([
                'tenant_id' => $duka->tenant_id,
                'duka_id' => $duka->id,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
            ]);
            $message = 'Customer added successfully!';
        }

        sleep(1);
        $this->loading = false;
        $this->showAddModal = false;
        $this->showEditModal = false;
        session()->flash('success', $message);
        $this->reset(['name', 'email', 'phone', 'address', 'editingCustomerId']);
    }

    public function deleteCustomer($customerId)
    {
        $customer = Customer::findOrFail($customerId);

        if (!auth()->user()->tenant || auth()->user()->tenant->id != $customer->tenant_id) {
            session()->flash('error', 'Unauthorized access.');
            return;
        }

        $customer->delete();
        session()->flash('success', 'Customer deleted successfully!');
    }

    public function closeModals()
    {
        $this->loading = false;
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->showImportModal = false;
        $this->reset(['name', 'email', 'phone', 'address', 'editingCustomerId', 'importFile']);
        $this->resetValidation();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $duka = Duka::findOrFail($this->dukaId);

        $customers = $duka->customers()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate($this->perPage);

        return view('livewire.duka-customers', [
            'customers' => $customers,
            'duka' => $duka,
        ]);
    }
}
