<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ProformaInvoice;
use App\Models\ProformaInvoiceItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Stock;
use App\Models\TenantOfficer;
use App\Models\TenantAccount;
use App\Mail\ProformaInvoiceMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class OfficerProformaInvoice extends Component
{
    public $assignedDukas = [];
    public $currency = 'TZS';
    public $selectedDukaId;

    // Form properties
    public $selectedCustomerId;
    public $customerSearch = '';
    public $productSearch = '';
    public $cart = [];
    public $subtotal = 0;
    public $discount = 0;
    public $taxAmount = 0;
    public $total = 0;
    public $notes = '';
    public $validUntil;

    // Available data
    public $customers = [];
    public $products = [];
    public $filteredCustomers = [];
    public $filteredProducts = [];

    // Loading states
    public $sendingEmail = false;
    public $generatingPreview = false;
    public $creatingInvoice = false;

    public function mount()
    {
        $user = auth()->user();

        // Get tenant ID from officer's assignments (same as OfficerController)
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            session()->flash('error', 'No active assignments found.');
            return;
        }

        // Verify tenant exists
        $tenant = \App\Models\Tenant::find($assignment->tenant_id);
        if (!$tenant) {
            session()->flash('error', 'Assigned tenant no longer exists. Please contact administrator.');
            return;
        }

        // Get officer's assigned dukas for this tenant
        $this->assignedDukas = TenantOfficer::with(['duka', 'tenant'])
            ->where('tenant_id', $assignment->tenant_id)
            ->where('officer_id', $user->id)
            ->where('status', true)
            ->get();

        if ($this->assignedDukas->isEmpty()) {
            session()->flash('error', 'No dukas assigned to you.');
            return;
        }

        $this->currency = TenantAccount::where('tenant_id', $assignment->tenant_id)->first()->currency ?? 'TZS';

        // Set default duka
        $this->selectedDukaId = $this->assignedDukas->first()->duka_id;

        // Set default valid until date (30 days from now)
        $this->validUntil = now()->addDays(30)->format('Y-m-d');

        $this->loadCustomersAndProducts();
    }

    private function loadCustomersAndProducts()
    {
        if (!is_object($this->assignedDukas) || $this->assignedDukas->isEmpty()) {
            return;
        }



        $dukaIds = $this->assignedDukas->pluck('duka_id');
        $tenantId = $this->assignedDukas->first()->tenant_id;

        $this->customers = Customer::where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('duka_id', $dukaIds)
            ->orderBy('name')
            ->get();

        $this->products = Product::where('tenant_id', $tenantId)
            ->with(['stocks' => function($q) use ($dukaIds) {
                $q->whereIn('duka_id', $dukaIds);
            }, 'category'])
            ->orderBy('name')
            ->get();
    }


    public function updatedCustomerSearch()
    {
        if (!is_object($this->assignedDukas) || $this->assignedDukas->isEmpty()) {
            $this->filteredCustomers = [];
            return;
        }

        if (strlen($this->customerSearch) > 1) {
            $dukaIds = $this->assignedDukas->pluck('duka_id');
            $tenantId = $this->assignedDukas->first()->tenant_id;
            $this->filteredCustomers = Customer::where('tenant_id', $tenantId)
                ->whereIn('duka_id', $dukaIds)
                ->where(function($q) {
                    $q->where('name', 'like', '%' . $this->customerSearch . '%')
                      ->orWhere('phone', 'like', '%' . $this->customerSearch . '%')
                      ->orWhere('email', 'like', '%' . $this->customerSearch . '%');
                })
                ->take(10)
                ->get();
        } else {
            $this->filteredCustomers = [];
        }
    }

    public function updatedProductSearch()
    {
        if (!is_object($this->assignedDukas) || $this->assignedDukas->isEmpty()) {
            $this->filteredProducts = [];
            return;
        }

        if (strlen($this->productSearch) > 1) {
            $dukaIds = $this->assignedDukas->pluck('duka_id');
            $tenantId = $this->assignedDukas->first()->tenant_id;
            $this->filteredProducts = Product::where('tenant_id', $tenantId)
                ->with(['stocks' => function($q) use ($dukaIds) {
                    $q->whereIn('duka_id', $dukaIds);
                }, 'category'])
                ->where(function($q) {
                    $q->where('name', 'like', '%' . $this->productSearch . '%')
                      ->orWhere('sku', 'like', '%' . $this->productSearch . '%');
                })
                ->take(10)
                ->get();
        } else {
            $this->filteredProducts = [];
        }
    }


    public function addToCart($productId)
    {
        $product = $this->products->find($productId);
        if (!$product) return;

        // Check if product already in cart
        $existingIndex = collect($this->cart)->search(function($item) use ($productId) {
            return $item['product_id'] == $productId;
        });

        if ($existingIndex !== false) {
            // Increase quantity if already in cart
            $this->cart[$existingIndex]['quantity']++;
            $this->cart[$existingIndex]['total'] = $this->cart[$existingIndex]['quantity'] * $this->cart[$existingIndex]['price'];
        } else {
            // Add new item to cart
            $this->cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'description' => $product->description,
                'price' => $product->selling_price,
                'quantity' => 1,
                'total' => $product->selling_price,
            ];
        }

        $this->calculateTotal();
        $this->productSearch = '';
        $this->filteredProducts = [];
    }

    public function updateCartQuantity($index, $quantity)
    {
        if ($quantity <= 0) {
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart);
        } else {
            $this->cart[$index]['quantity'] = $quantity;
            $this->cart[$index]['total'] = $quantity * $this->cart[$index]['price'];
        }

        $this->calculateTotal();
    }

    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->calculateTotal();
    }

    public function updatedDiscount()
    {
        $this->calculateTotal();
    }

    private function calculateTotal()
    {
        $this->subtotal = collect($this->cart)->sum('total');
        $this->total = $this->subtotal - $this->discount;
        // Tax calculation can be added here if needed
        $this->taxAmount = 0; // For now, no tax
    }

    public function generateProformaInvoicePreview()
    {
        $this->generatingPreview = true;

        if (!is_object($this->assignedDukas) || $this->assignedDukas->isEmpty()) {
            session()->flash('error', 'No valid assignments found. Please contact administrator.');
            $this->generatingPreview = false;
            return null;
        }

        $this->validate([
            'selectedDukaId' => 'required|exists:dukas,id',
            'selectedCustomerId' => 'required|exists:customers,id',
            'cart' => 'required|array|min:1',
            'validUntil' => 'required|date|after:today',
            'discount' => 'numeric|min:0',
        ]);

        if (empty($this->cart)) {
            session()->flash('error', 'Please add at least one product to the cart.');
            $this->generatingPreview = false;
            return;
        }

        // Get tenant record from officer's assignments
        $tenantRecord = $this->assignedDukas->first()->tenant;
        if (!$tenantRecord) {
            session()->flash('error', 'Assigned tenant no longer exists. Please contact administrator.');
            $this->generatingPreview = false;
            return null;
        }

        // Get the tenant user ID (ProformaInvoice expects user ID, not tenant record ID)
        $tenantId = $tenantRecord->user_id;

        // Get customer and duka details
        $customer = Customer::find($this->selectedCustomerId);
        $duka = \App\Models\Duka::find($this->selectedDukaId);

        // Generate invoice data without saving
        $invoiceData = [
            'invoice_number' => 'PREVIEW-' . now()->format('YmdHis'),
            'tenant_id' => $tenantId,
            'duka_id' => $this->selectedDukaId,
            'customer_id' => $this->selectedCustomerId,
            'invoice_date' => now(),
            'valid_until' => $this->validUntil,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->taxAmount,
            'discount_amount' => $this->discount,
            'total_amount' => $this->total,
            'currency' => $this->currency,
            'notes' => $this->notes,
            'status' => 'preview',
            'customer' => $customer,
            'duka' => $duka,
            'tenant' => $tenantRecord,
            'items' => collect($this->cart)->map(function($item) {
                return (object) [
                    'product_name' => $item['name'],
                    'product_sku' => $item['sku'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['total'],
                ];
            })
        ];

        // Store preview data in session
        session(['proforma_invoice_preview' => $invoiceData]);

        // Reset form
        $this->resetForm();

        $this->generatingPreview = false;

        // Redirect to preview page
        return redirect()->route('officer.proforma-invoice.preview');
    }

    public function createProformaInvoice()
    {
        $this->creatingInvoice = true;

        if (!is_object($this->assignedDukas) || $this->assignedDukas->isEmpty()) {
            session()->flash('error', 'No valid assignments found. Please contact administrator.');
            $this->creatingInvoice = false;
            return null;
        }

        $this->validate([
            'selectedDukaId' => 'required|exists:dukas,id',
            'selectedCustomerId' => 'required|exists:customers,id',
            'cart' => 'required|array|min:1',
            'validUntil' => 'required|date|after:today',
            'discount' => 'numeric|min:0',
        ]);

        if (empty($this->cart)) {
            session()->flash('error', 'Please add at least one product to the cart.');
            $this->creatingInvoice = false;
            return;
        }

        // Get tenant record from officer's assignments
        $tenantRecord = $this->assignedDukas->first()->tenant;
        if (!$tenantRecord) {
            session()->flash('error', 'Assigned tenant no longer exists. Please contact administrator.');
            $this->creatingInvoice = false;
            return null;
        }

        // Get the tenant user ID (ProformaInvoice expects user ID, not tenant record ID)
        $tenantId = $tenantRecord->user_id;

        DB::beginTransaction();
        try {
            // Create proforma invoice
            $invoice = ProformaInvoice::create([
                'invoice_number' => ProformaInvoice::generateInvoiceNumber(),
                'tenant_id' => $tenantId,
                'duka_id' => $this->selectedDukaId,
                'customer_id' => $this->selectedCustomerId,
                'invoice_date' => now(),
                'valid_until' => $this->validUntil,
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->taxAmount,
                'discount_amount' => $this->discount,
                'total_amount' => $this->total,
                'currency' => $this->currency,
                'notes' => $this->notes,
                'status' => 'draft',
            ]);

            // Create invoice items
            foreach ($this->cart as $item) {
                ProformaInvoiceItem::create([
                    'proforma_invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'product_sku' => $item['sku'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['total'],
                ]);
            }

            DB::commit();

            // Reset form
            $this->resetForm();

            $this->creatingInvoice = false;
            session()->flash('success', 'Proforma invoice created successfully!');

            // Return the invoice ID for potential further actions
            return $invoice->id;

        } catch (\Exception $e) {
            DB::rollback();
            $this->creatingInvoice = false;
            session()->flash('error', 'Failed to create proforma invoice: ' . $e->getMessage());
            return null;
        }
    }

    public function sendEmail($invoiceId = null)
    {
        $this->sendingEmail = true;

        if ($invoiceId) {
            $invoice = ProformaInvoice::with(['customer', 'items', 'duka'])->find($invoiceId);
        } else {
            // Generate invoice data without saving to database
            if (!is_object($this->assignedDukas) || $this->assignedDukas->isEmpty()) {
                session()->flash('error', 'No valid assignments found. Please contact administrator.');
                $this->sendingEmail = false;
                return;
            }

            $this->validate([
                'selectedDukaId' => 'required|exists:dukas,id',
                'selectedCustomerId' => 'required|exists:customers,id',
                'cart' => 'required|array|min:1',
                'validUntil' => 'required|date|after:today',
                'discount' => 'numeric|min:0',
            ]);

            if (empty($this->cart)) {
                session()->flash('error', 'Please add at least one product to the cart.');
                $this->sendingEmail = false;
                return;
            }

            // Get tenant record from officer's assignments
            $tenantRecord = $this->assignedDukas->first()->tenant;
            if (!$tenantRecord) {
                session()->flash('error', 'Assigned tenant no longer exists. Please contact administrator.');
                $this->sendingEmail = false;
                return;
            }

            // Get the tenant user ID (ProformaInvoice expects user ID, not tenant record ID)
            $tenantId = $tenantRecord->user_id;

            // Get customer and duka details
            $customer = Customer::find($this->selectedCustomerId);
            $duka = \App\Models\Duka::find($this->selectedDukaId);

            // Create a temporary invoice object that mimics the ProformaInvoice model
            $invoice = (object) [
                'invoice_number' => 'EMAIL-' . now()->format('YmdHis'),
                'tenant_id' => $tenantId,
                'duka_id' => $this->selectedDukaId,
                'customer_id' => $this->selectedCustomerId,
                'invoice_date' => now(),
                'valid_until' => \Carbon\Carbon::parse($this->validUntil),
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->taxAmount,
                'discount_amount' => $this->discount,
                'total_amount' => $this->total,
                'currency' => $this->currency,
                'notes' => $this->notes,
                'status' => 'sent',
                'customer' => $customer,
                'duka' => $duka,
                'tenant' => $tenantRecord,
                'items' => collect($this->cart)->map(function($item) {
                    return (object) [
                        'product_name' => $item['name'],
                        'product_sku' => $item['sku'],
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'total_price' => $item['total'],
                    ];
                })
            ];
        }

        if (!$invoice) {
            session()->flash('error', 'Invoice not found.');
            $this->sendingEmail = false;
            return;
        }

        try {
            Mail::to($invoice->customer->email)->send(new ProformaInvoiceMail($invoice));

            // Only update status if it's a real database invoice
            if (isset($invoice->id)) {
                $realInvoice = ProformaInvoice::find($invoice->id);
                if ($realInvoice) {
                    $realInvoice->update(['status' => 'sent']);
                }
            }

            // Reset form only if we generated the invoice data (not from existing invoice)
            if (!$invoiceId) {
                $this->resetForm();
            }

            $this->sendingEmail = false;
            session()->flash('success', 'Proforma invoice sent successfully via email!');
        } catch (\Exception $e) {
            $this->sendingEmail = false;
            session()->flash('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->selectedCustomerId = null;
        $this->customerSearch = '';
        $this->productSearch = '';
        $this->cart = [];
        $this->subtotal = 0;
        $this->discount = 0;
        $this->taxAmount = 0;
        $this->total = 0;
        $this->notes = '';
        $this->validUntil = now()->addDays(30)->format('Y-m-d');
        $this->filteredCustomers = [];
        $this->filteredProducts = [];
    }

    public function render()
    {
        // Ensure assignedDukas is a collection for the view
        if (!is_object($this->assignedDukas)) {
            $this->assignedDukas = collect();
        }

        return view('livewire.officer-proforma-invoice');
    }
}
