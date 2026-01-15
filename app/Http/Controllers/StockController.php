<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockController extends Controller
{
   public function store(Request $request)
{
    \Log::info('Stock Store Request (Stock Flow Update)', [
        'user_id' => Auth::id(),
        'request_data' => $request->all()
    ]);

    try {
        $request->validate([
            'duka_id' => 'required|exists:dukas,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0', // Captured for Expense tracking
            'batch_number' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $user = Auth::user();

        // --- Tenant / Duka Scoping Logic ---
        $requestedDuka = \App\Models\Duka::findOrFail($request->duka_id);

        // Security check: Verify the Duka belongs to the User's Tenant
        if ($requestedDuka->tenant_id != $user->tenant_id) {
            \Log::warning('Unauthorized tenant access attempt', [
                'user_tenant' => $user->tenant_id,
                'duka_tenant' => $requestedDuka->tenant_id
            ]);
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        // Verify product belongs to duka
        $product = $requestedDuka->products()->find($request->product_id);
        if (!$product) {
            return redirect()->back()->with('error', 'Product not found in this duka.');
        }

        // Use base_price from product if unit_cost wasn't provided in the form
        $unitCost = $request->unit_cost ?: $product->base_price;

        return DB::transaction(function () use ($request, $user, $requestedDuka, $product, $unitCost) {

            // 1. Find or create the stock entry
            $stock = Stock::firstOrCreate(
                [
                    'duka_id' => $requestedDuka->id,
                    'product_id' => $product->id,
                ],
                [
                    'quantity' => 0,
                    'last_updated_by' => $user->id,
                ]
            );

            $previousQuantity = $stock->quantity;
            $newQuantity = $previousQuantity + $request->quantity;

            // 2. Update the Stock Header
            $stock->update([
                'quantity' => $newQuantity,
                'batch_number' => $request->batch_number ?: $stock->batch_number,
                'expiry_date' => $request->expiry_date ?: $stock->expiry_date,
                'notes' => $request->notes ?: $stock->notes,
                'last_updated_by' => $user->id,
            ]);

            // 3. Log the Stock Flow (Movement)
            // 'in' + 'purchase' = Expense
            StockMovement::create([
                'stock_id' => $stock->id,
                'user_id' => $user->id,
                'type' => 'in', // Corrected from 'add' to 'in'
                'quantity_change' => $request->quantity,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $newQuantity,
                'unit_cost' => $unitCost,
                'quantity_remaining' => $request->quantity,
                'unit_price' => 0, // No income yet, this is a purchase
                'total_value' => $request->quantity * $unitCost,
                'batch_number' => $request->batch_number,
                'expiry_date' => $request->expiry_date,
                'notes' => $request->notes,
                'reason' => $request->reason ?: 'purchase', // Default to purchase for flow
            ]);

            \Log::info('Stock Flow Updated: Purchase recorded', ['stock_id' => $stock->id, 'expense' => $request->quantity * $unitCost]);

            return redirect()->back()->with('success', 'Stock replenished and expense recorded successfully!');
        });

    } catch (\Exception $e) {
        \Log::error('Stock store failed', ['error' => $e->getMessage()]);
        return redirect()->back()->with('error', 'Failed to add stock: ' . $e->getMessage());
    }
}

    public function update(Request $request, $id)
    {
        \Log::info('Stock Update Request', [
            'user_id' => Auth::id(),
            'stock_id' => $id,
            'request_data' => $request->all()
        ]);

        try {
            $stock = Stock::findOrFail($id);

            $user = Auth::user();
            $duka = $user->duka;

            \Log::info('Stock update - found stock', ['stock_id' => $stock->id, 'current_quantity' => $stock->quantity]);

            // If user has a specific duka (officer), verify the stock belongs to it
            if ($duka && $stock->duka_id != $duka->id) {
                \Log::warning('Unauthorized stock access', ['user_duka' => $duka->id, 'stock_duka' => $stock->duka_id]);
                return redirect()->back()->with('error', 'Unauthorized access.');
            }

            // If no specific duka, check if the stock's duka belongs to user's tenant
            if (!$duka) {
                if ($stock->duka->tenant_id != $user->tenant->id) {
                    \Log::warning('Unauthorized tenant stock access', ['tenant_id' => $user->tenant->id, 'stock_duka_tenant' => $stock->duka->tenant_id]);
                    return redirect()->back()->with('error', 'Unauthorized access.');
                }
            }

            $request->validate([
                'quantity' => 'required|integer|min:0',
                'batch_number' => 'nullable|string|max:255',
                'expiry_date' => 'nullable|date|after:today',
                'notes' => 'nullable|string',
                'reason' => 'nullable|string',
            ]);

            $previousQuantity = $stock->quantity;
            $quantityChange = $request->quantity - $previousQuantity;

            \Log::info('Stock quantity change', [
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $request->quantity,
                'change' => $quantityChange
            ]);

            $stock->update([
                'quantity' => $request->quantity,
                'batch_number' => $request->batch_number,
                'expiry_date' => $request->expiry_date,
                'notes' => $request->notes,
                'last_updated_by' => $user->id,
            ]);

            // Log the movement if quantity changed
            if ($quantityChange != 0) {
                StockMovement::create([
                    'stock_id' => $stock->id,
                    'user_id' => $user->id,
                    'type' => 'update',
                    'quantity_change' => $quantityChange,
                    'previous_quantity' => $previousQuantity,
                    'new_quantity' => $request->quantity,
                    'batch_number' => $request->batch_number,
                    'expiry_date' => $request->expiry_date,
                    'notes' => $request->notes,
                    'reason' => $request->reason,
                ]);
                \Log::info('Stock movement recorded', ['change' => $quantityChange]);
            }

            \Log::info('Stock updated successfully', ['stock_id' => $stock->id]);
            return redirect()->back()->with('success', 'Stock updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Stock update failed', [
                'stock_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to update stock: ' . $e->getMessage());
        }
    }
}
