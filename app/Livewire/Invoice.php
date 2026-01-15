<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Duka;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProformaInvoice;
use App\Models\ProformaInvoiceItem;
use App\Models\TenantAccount;
use Barryvdh\DomPDF\Facade\Pdf;

class Invoice extends Component
{
    public $dukaList = [];
    public $selectedDuka = null;

    public $customers = [];
    public $products = [];

    public $customer_id;
    public $items = [];
    public $subtotal = 0;
    public $tax_amount = 0;
    public $discount_amount = 0;
    public $total_amount = 0;
    public $currency = 'TZS';
    public $notes;
    public $valid_days = 30;

    public function mount()
    {
        $user     = auth()->user();
        $tenantId = $user->tenant->id;
        $this->dukaList = Duka::where('tenant_id', $tenantId)->get();
        $account = TenantAccount::where('tenant_id', $tenantId)->first();
        $this->currency = $account->currency ?? 'TZS';
    }

    public function selectDuka($dukaId)
    {
        $this->selectedDuka = Duka::find($dukaId);
        $this->customers = Customer::where('duka_id', $dukaId)->get();
        $this->products = Product::where('duka_id', $dukaId)->active()->get();
        $this->items = [];
        $this->addItem(); // Add initial item

        session()->flash('message', 'Duka Selected: ' . $this->selectedDuka->name);
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => '',
            'product_name' => '',
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'total_price' => 0,
        ];
    }

    public function updateItemPrice($index)
    {
        if (!empty($this->items[$index]['product_id'])) {
            // Check if product is already selected in another item
            $existing = collect($this->items)->pluck('product_id')->filter()->values();
            $count = $existing->filter(function ($id) use ($index) {
                return $id == $this->items[$index]['product_id'];
            })->count();
            if ($count > 1) {
                $this->items[$index]['product_id'] = '';
                session()->flash('error', 'Product already added to the invoice.');
                $this->calculateTotals();
                return;
            }

            $product = Product::find($this->items[$index]['product_id']);
            if ($product) {
                $this->items[$index]['product_name'] = $product->name;
                $this->items[$index]['unit_price'] = $product->selling_price;
                $this->items[$index]['total_price'] = $this->items[$index]['quantity'] * $this->items[$index]['unit_price'];
            }
        }
        $this->calculateTotals();
    }

    public function updateItemTotal($index)
    {
        $this->items[$index]['total_price'] = $this->items[$index]['quantity'] * $this->items[$index]['unit_price'];
        $this->calculateTotals();
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotals();
    }

    public function updatedItems()
    {
        $this->calculateTotals();
    }

    public function updatedTaxAmount()
    {
        $this->calculateTotals();
    }

    public function updatedDiscountAmount()
    {
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = (float) collect($this->items)->sum('total_price');
        $this->tax_amount = (float) $this->tax_amount;
        $this->discount_amount = (float) $this->discount_amount;
        $this->total_amount = ($this->subtotal + $this->tax_amount) - $this->discount_amount;
    }

    public function preview()
    {
        $this->validate([
            'selectedDuka.id' => 'required',
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'valid_days' => 'nullable|integer|min:1|max:365',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Store invoice data in session for the controller to use
        session([
            'temp_invoice_data' => [
                'selectedDuka' => $this->selectedDuka,
                'customer_id' => $this->customer_id,
                'items' => $this->items,
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->tax_amount,
                'discount_amount' => $this->discount_amount,
                'total_amount' => $this->total_amount,
                'currency' => $this->currency,
                'notes' => $this->notes,
                'valid_days' => $this->valid_days,
            ]
        ]);

        // Redirect to controller method that shows the HTML preview
        return redirect()->route('proforma.preview.temp');
    }

    public function render()
    {
        return view('livewire.invoice');
    }
}
