<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Duka;
use Livewire\Component;
use Livewire\WithPagination;

class DukaCustomers extends Component
{
    use WithPagination;
    public $dukaId;
    public $showAddModal = false;
    public $showEditModal = false;
    public $editingCustomerId;
    public $name;
    public $email;
    public $phone;
    public $address;
    public $loading = false;
    public $perPage = 10;
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ];

        // For editing, exclude current customer's email from unique validation
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
        $this->reset(['name', 'email', 'phone', 'address', 'editingCustomerId']);
        $this->showAddModal = true;
        $this->showEditModal = false;
    }

    public function openEditModal($customerId)
    {
        $this->resetValidation();
        $customer = Customer::findOrFail($customerId);

        // Verify customer belongs to user's tenant
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
    }

    public function saveCustomer()
    {
        $this->loading = true;

        $this->validate();

        $duka = Duka::findOrFail($this->dukaId);

        // Verify user owns this duka through tenant relationship
        if (!auth()->user()->tenant || auth()->user()->tenant->id != $duka->tenant_id) {
            $this->loading = false;
            session()->flash('error', 'Unauthorized access.');
            return;
        }

        if ($this->showEditModal && $this->editingCustomerId) {
            // Update existing customer
            $customer = Customer::findOrFail($this->editingCustomerId);

            // Double-check tenant ownership
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
            // Create new customer
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

        // Small delay for better UX
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

        // Verify customer belongs to user's tenant
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
        $this->reset(['name', 'email', 'phone', 'address', 'editingCustomerId']);
        $this->resetValidation();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
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
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.duka-customers', [
            'customers' => $customers,
            'duka' => $duka,
        ]);
    }
}
