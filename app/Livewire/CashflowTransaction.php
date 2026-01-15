<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Duka;
use App\Models\CashflowCategory;

class CashflowTransaction extends Component
{
    public $duka;
    public $type = 'income';
    public $category = '';
    public $amount;
    public $transaction_date;
    public $description;
    public $reference_number;

    protected $listeners = ['openModal' => 'loadDuka'];

    public function mount($dukaId = null)
    {
        $this->transaction_date = now()->format('Y-m-d');
        if ($dukaId) {
            $this->loadDuka($dukaId);
        }
    }

    public function loadDuka($dukaId)
    {
        $tenantId = Auth::User()->tenant->id;
        $this->duka = Duka::where('id', $dukaId)->where('tenant_id', $tenantId)->firstOrFail();
    }

    public function updatedType($value)
    {
        $this->category = '';
    }

    public function getCategoriesProperty()
    {
        if (!$this->duka) {
            return collect();
        }

        $tenantId = Auth::User()->tenant->id;

        dd(CashflowCategory::where('tenant_id', $tenantId)
            ->where('duka_id', $this->duka->id)
            ->where('type', $this->type)
            ->where('is_active', true)
            ->orderBy('name')
            ->get());

        return CashflowCategory::where('tenant_id', $tenantId)
            ->where('duka_id', $this->duka->id)
            ->where('type', $this->type)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

    }

    public function save()
    {
        $this->validate([
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:1000',
            'reference_number' => 'nullable|string|max:255',
        ]);

        $tenantId = Auth::User()->tenant->id;

        // Create cashflow transaction
        $cashflow = \App\Models\CashFlow::create([
            'tenant_id' => $tenantId,
            'duka_id' => $this->duka->id,
            'user_id' => Auth::id(),
            'type' => $this->type,
            'category' => $this->category,
            'amount' => $this->amount,
            'transaction_date' => $this->transaction_date,
            'description' => $this->description,
            'reference_number' => $this->reference_number,
        ]);

        $this->reset(['category', 'amount', 'description', 'reference_number']);
        $this->transaction_date = now()->format('Y-m-d');

        $this->dispatch('cashflowSaved');
        $this->dispatch('closeModal');

        session()->flash('success', __('Transaction recorded successfully.'));
    }

    public function render()
    {
        return view('livewire.cashflow-transaction', [
            'categories' => $this->categories,
        ]);
    }
}
