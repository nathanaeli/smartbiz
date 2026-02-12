<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Duka;
use App\Models\Product;
use App\Models\ProformaInvoice;
use App\Models\ProformaInvoiceItem;
use App\Models\TenantAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

/**
 * Proforma Invoice Controller
 *
 * Handles the creation, preview, and management of proforma invoices
 * including PDF generation and temporary invoice previews
 */
class ProformaInvoiceController extends Controller
{
    public function index()
    {
        return view('proforma-invoices.index');
    }

    public function generatePdf($id)
    {
        $invoice = ProformaInvoice::with('items', 'customer', 'duka')->findOrFail($id);
        $account = TenantAccount::where('tenant_id', $invoice->tenant_id)->first();
        $pdf = Pdf::loadView('proforma-invoices.pdf', compact('invoice', 'account'));
        return $pdf->download('proforma-invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Preview temporary proforma invoice
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function previewTemp()
    {
        try {
            // Get data from session
            $data = session('temp_invoice_data');

            if (!$data) {
                \Log::error('Proforma Invoice Preview: No session data found', [
                    'user_id' => Auth::id(),
                    'session_keys' => array_keys(session()->all())
                ]);
                return redirect()->back()
                    ->with('error', 'No invoice data found. Please try again.')
                    ->withInput();
            }

            \Log::info('Proforma Invoice Preview Data:', ['data' => $data]);

            // Validate required fields
            $validator = Validator::make($data, [
                'customer_id' => 'required|exists:customers,id',
                'selectedDuka' => 'required',
                'items' => 'required|array|min:1',
                'subtotal' => 'required|numeric|min:0',
                'total_amount' => 'required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                \Log::error('Proforma Invoice Preview: Validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'data' => $data
                ]);
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Please check your input and try again.');
            }

            $tenantId = Auth::id();
            $account = TenantAccount::where('tenant_id', $tenantId)->first();
            $customer = Customer::find($data['customer_id']);

            if (!$customer) {
                \Log::error('Proforma Invoice Preview: Customer not found', ['customer_id' => $data['customer_id']]);
                return redirect()->back()
                    ->with('error', 'Selected customer not found. Please select a valid customer.')
                    ->withInput();
            }

            // Validate duka existence if provided as object/array
            $selectedDuka = null;
            if (is_array($data['selectedDuka']) && isset($data['selectedDuka']['id'])) {
                $selectedDuka = Duka::find($data['selectedDuka']['id']);
                if (!$selectedDuka) {
                    \Log::error('Proforma Invoice Preview: Duka not found', ['duka_id' => $data['selectedDuka']['id']]);
                    return redirect()->back()
                        ->with('error', 'Selected duka not found. Please select a valid duka.')
                        ->withInput();
                }
            } else {
                $selectedDuka = is_object($data['selectedDuka']) ? $data['selectedDuka'] : (object) $data['selectedDuka'];
            }

            // Validate items
            foreach ($data['items'] as $index => $item) {
                if (!isset($item['product_name']) || !isset($item['quantity']) || !isset($item['unit_price'])) {
                    \Log::error('Proforma Invoice Preview: Invalid item data', [
                        'item_index' => $index,
                        'item_data' => $item
                    ]);
                    return redirect()->back()
                        ->with('error', "Invalid item data at position " . ($index + 1) . ". Please check your input.")
                        ->withInput();
                }
            }

            // Create temporary invoice object
            $invoice = (object) [
                'invoice_number' => 'TEMP-' . time(),
                'invoice_date' => now(),
                'valid_until' => isset($data['valid_days']) && $data['valid_days'] ?
                    now()->addDays($data['valid_days']) :
                    now()->addDays(30),
                'duka' => $selectedDuka,
                'customer' => $customer,
                'items' => collect($data['items'])->map(function ($item) {
                    return (object) $item;
                }),
                'subtotal' => (float)($data['subtotal'] ?? 0),
                'tax_amount' => (float)($data['tax_amount'] ?? 0),
                'discount_amount' => (float)($data['discount_amount'] ?? 0),
                'total_amount' => (float)($data['total_amount'] ?? 0),
                'currency' => $data['currency'] ?? 'TZS',
                'notes' => $data['notes'] ?? null,
            ];

            \Log::info('Proforma Invoice Preview: Invoice object created', [
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->customer->name ?? 'N/A',
                'duka_name' => $invoice->duka->name ?? 'N/A',
                'items_count' => count($invoice->items),
                'total_amount' => $invoice->total_amount
            ]);

            // Don't clear session data so user can refresh or print
            return view('proforma-invoices.preview', compact('invoice', 'account'));
        } catch (\Exception $e) {
            \Log::error('Proforma Invoice Preview: Unexpected error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                ->with('error', 'An unexpected error occurred. Please try again or contact support.')
                ->withInput();
        }
    }

    public function salenow()
    {
        return view('proforma-invoices.salenow');
    }

    /**
     * Create proforma invoice for specific duka
     *
     * @param string $dukaId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function createForDuka($dukaId)
    {
        try {
            $duka = \App\Models\Duka::findOrFail($dukaId);

            // Verify user has access to this duka
            $user = auth()->user();
            if ($duka->tenant_id !== $user->tenant->id) {
                abort(403, 'Unauthorized access to this duka.');
            }

            return view('proforma-invoices.create-for-duka', compact('duka'));
        } catch (\Exception $e) {
            \Log::error('Create for Duka Error: ' . $e->getMessage());
            return redirect()->route('proforma.index')
                ->with('error', 'Unable to load duka data. Please try again.');
        }
    }

    /**
     * Store a new proforma invoice
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            // TODO: Implement invoice storage logic
            // This is a placeholder implementation
            \Log::info('Store method called', $request->all());

            return redirect()->route('proforma.index')
                ->with('success', 'Invoice created successfully!');
        } catch (\Exception $e) {
            \Log::error('Store Invoice Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Unable to create invoice. Please try again.')
                ->withInput();
        }
    }
}
