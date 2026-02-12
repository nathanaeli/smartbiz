<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
// Models
use App\Models\Customer;
use App\Models\Duka;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Tenant;
use App\Models\TenantAccount;
use App\Models\TenantOfficer;
use App\Models\ProductCategory;
use App\Models\StockMovement;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Transaction;
use App\Models\LoanPayment;
use App\Models\Message;
use App\Models\ProformaInvoice;
use App\Models\ProformaInvoiceItem;
use App\Models\StaffPermission;
use App\Models\DukaSubscription;
use App\Models\User;
// Facades & Utilities
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Advnacedinventory extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();
        Log::info($request->all());
        // 1. Validation
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'category_id'   => 'nullable|exists:product_categories,id',
            'base_price'    => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0|gte:base_price',
            'unit'          => 'nullable|string|max:20',
            'initial_stock' => 'required|integer|min:0',
            'description'   => 'nullable|string',
            'image'         => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // 2. Identify Tenant/Duka Context automatically
        $assignment = TenantOfficer::where('officer_id', $user->id)->where('status', true)->first();
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'No active shop assignment found.'], 403);
        }

        DB::beginTransaction();
        try {
            // 3. Handle Image
            $imageName = null;
            if ($request->hasFile('image')) {
                $imageName = time() . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
                $request->file('image')->storeAs('products', $imageName, 'public');
            }

            // 4. Create Product
            $product = Product::create([
                'tenant_id'     => $assignment->tenant_id,
                'duka_id'       => $assignment->duka_id,
                'sku'           => $this->generateSku($request->name, $assignment->tenant_id),
                'name'          => $request->name,
                'category_id'   => $request->category_id,
                'unit'          => $request->unit,
                'base_price'    => $request->base_price,
                'selling_price' => $request->selling_price,
                'description'   => $request->description,
                'is_active'     => true,
                'image'         => $imageName,
            ]);

            // 5. Initialize Stock Header
            $stock = Stock::create([
                'duka_id'         => $assignment->duka_id,
                'product_id'      => $product->id,
                'quantity'        => $request->initial_stock,
                'last_updated_by' => $user->id,
            ]);

            // 6. Create FIFO Movement (Critical for Sales Logic)
            $stock->movements()->create([
                'user_id'            => $user->id,
                'type'               => 'in',
                'quantity_change'    => $request->initial_stock,
                'quantity_remaining' => $request->initial_stock, // Key for FIFO consumption
                'previous_quantity'  => 0,
                'new_quantity'       => $request->initial_stock,
                'unit_cost'          => $request->base_price,
                'unit_price'         => $request->selling_price,
                'total_value'        => $request->initial_stock * $request->base_price,
                'reason'             => 'purchase',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product saved successfully!',
                'data'    => array_merge($product->toArray(), [
                    'image_url' => $product->image ? asset('storage/products/' . $product->image) : null
                ])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Product creation failed:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    private function generateSku($name, $tenantId) {
        $sku = strtoupper(substr($name, 0, 3)) . '-' . rand(1000, 9999);
        return $sku;
    }

 public function apiCreateSale(Request $request)
{
    $user = auth()->user();

    // Log the incoming request properly
    Log::info("Incoming Sale Request:", $request->all());
    Log::info("Authenticated User:", ['user_id' => $user->id, 'name' => $user->name]);

    DB::beginTransaction();
    try {
        // 1. Identify Officer Assignment
        $assignment = TenantOfficer::where('officer_id', $user->id)->first();


        $tenantId = $assignment->tenant_id;
        $dukaId   = $assignment->duka_id;


        // Determine the timestamp (Support Backdating)
        $saleDate = $request->has('created_at') ? Carbon::parse($request->created_at) : now();

        // 2. Create the Sale record
        $sale = Sale::create([
            'tenant_id'       => $tenantId,
            'duka_id'         => $dukaId,
            'customer_id'     => $request->customer_id,
            'total_amount'    => 0,
            'discount_amount' => $request->discount_amount ?? 0,
            'profit_loss'     => 0,
            'is_loan'         => $request->boolean('is_loan', false),
            'due_date'        => $request->due_date,
            'discount_reason' => $request->discount_reason,
            'created_at'      => $saleDate,
        ]);

        $totalAmount = 0;
        $finalSaleProfit = 0;

        foreach ($request->items as $itemData) {
            $product = Product::where('id', $itemData['product_id'])
                ->where('tenant_id', $tenantId)
                ->firstOrFail();

            $qtyToProcess = $itemData['quantity'];
            $totalCostForThisItem = 0;

            // 3. FIFO Consumption Logic
            $batches = StockMovement::whereHas('stock', function($q) use ($product, $dukaId) {
                    $q->where('product_id', $product->id)->where('duka_id', $dukaId);
                })
                ->whereIn('type', ['in', 'add'])
                ->where('quantity_remaining', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($batches as $batch) {
                if ($qtyToProcess <= 0) break;
                $take = min($batch->quantity_remaining, $qtyToProcess);
                $unitCost = ($batch->unit_cost > 0) ? $batch->unit_cost : $product->base_price;
                $totalCostForThisItem += ($take * $unitCost);

                $batch->decrement('quantity_remaining', $take);
                $qtyToProcess -= $take;
            }

            if ($qtyToProcess > 0) {
                $totalCostForThisItem += ($qtyToProcess * $product->base_price);
            }

            $itemRevenue = ($itemData['unit_price'] * $itemData['quantity']) - ($itemData['discount_amount'] ?? 0);
            $itemProfit = $itemRevenue - $totalCostForThisItem;

            $totalAmount += $itemRevenue;
            $finalSaleProfit += $itemProfit;

            // 4. Create Sale Item
            SaleItem::create([
                'sale_id'         => $sale->id,
                'product_id'      => $product->id,
                'quantity'        => $itemData['quantity'],
                'unit_price'      => $itemData['unit_price'],
                'discount_amount' => $itemData['discount_amount'] ?? 0,
                'total'           => $itemRevenue,
                'created_at'      => $saleDate,
            ]);

            // 5. Update Stock
            $stock = Stock::where('product_id', $product->id)->where('duka_id', $dukaId)->firstOrFail();
            $prevQty = $stock->quantity;
            $stock->decrement('quantity', $itemData['quantity']);

            StockMovement::create([
                'stock_id'          => $stock->id,
                'user_id'           => $user->id,
                'type'              => 'out',
                'quantity_change'   => $itemData['quantity'],
                'previous_quantity' => $prevQty,
                'new_quantity'      => $prevQty - $itemData['quantity'],
                'unit_cost'         => $itemData['quantity'] > 0 ? ($totalCostForThisItem / $itemData['quantity']) : 0,
                'unit_price'        => $itemData['unit_price'],
                'total_value'       => $itemRevenue,
                'reason'            => 'sale',
                'created_at'        => $saleDate,
            ]);
        }

        // 6. Update Sale with Final Totals
        $finalTotal = $totalAmount - ($request->discount_amount ?? 0);
        $sale->update([
            'total_amount' => $finalTotal,
            'profit_loss'  => $finalSaleProfit - ($request->discount_amount ?? 0),
        ]);

        // 7. Create Income Transaction
        if (!$sale->is_loan) {
            Transaction::create([
                'duka_id'          => $dukaId,
                'user_id'          => $user->id,
                'type'             => 'income',
                'category'         => 'sale',
                'amount'           => $finalTotal,
                'status'           => 'active',
                'payment_method'   => $request->payment_method ?? 'cash',
                'reference_id'     => $sale->id,
                'description'      => "Sale #{$sale->id}",
                'transaction_date' => $saleDate->toDateString(),
                'created_at'        => $saleDate,
            ]);
        }

        DB::commit();
        return response()->json(['success' => true, 'sale_id' => $sale->id], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Sale Error: " . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

public function destroySale($id)
{
    $sale = Sale::with('saleItems.product')->findOrFail($id);
    $user = auth()->user();

    // Permission Check


    try {
        DB::beginTransaction();

        Log::info("--- INITIATING SALE DELETION ---", [
            'sale_id' => $id,
            'officer_id' => $user->id,
            'duka_id' => $sale->duka_id,
            'total_amount' => $sale->total_amount
        ]);

        // Capture pre-deletion stock levels for logging
        $productIds = $sale->saleItems->pluck('product_id')->unique();
        $preDeleteStocks = Stock::whereIn('product_id', $productIds)
            ->where('duka_id', $sale->duka_id)
            ->get(['product_id', 'quantity'])
            ->pluck('quantity', 'product_id');

        // Log specific items being returned
        foreach ($sale->saleItems as $item) {
            Log::info("  - Returning Item: {$item->product->name}", [
                'qty_to_restore' => $item->quantity,
                'pre_restore_stock' => $preDeleteStocks[$item->product_id] ?? 0
            ]);
        }

        // Execution: Triggers the restoreStock() logic in SaleItem model
        $sale->delete();

        // Fetch post-deletion stock levels
        $updatedStocks = Stock::whereIn('product_id', $productIds)
            ->where('duka_id', $sale->duka_id)
            ->get(['product_id', 'quantity']);

        Log::info("--- SALE DELETED SUCCESSFULLY ---", [
            'sale_id' => $id,
            'restored_items_count' => count($updatedStocks),
            'new_stock_levels' => $updatedStocks->toArray()
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Sale deleted and stock restored.',
            'updated_stocks' => $updatedStocks
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Sale Deletion Failed: #{$id}", [
            'error' => $e->getMessage(),
            'line' => $e->getLine()
        ]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


public function updateSale(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);

        DB::beginTransaction();
        try {
            // 1. REVERSE: Restore original stock to specific batches
            // This fills back the 'quantity_remaining' in StockMovements
            foreach ($sale->saleItems as $item) {
                $item->restoreStock();
            }

            // 2. CLEAR: Remove old items and old financial transaction
            $sale->saleItems()->delete();
            Transaction::where('reference_id', $sale->id)->where('category', 'sale')->delete();

            // 3. APPLY: Process new items using FIFO consumption
            $totalAmount = 0;
            $finalSaleProfit = 0;

            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $qtyToProcess = $itemData['quantity'];
                $totalCost = 0;

                // Standard FIFO Batch Consumption
                $batches = StockMovement::whereHas('stock', fn($q) => $q->where('product_id', $product->id))
                    ->whereIn('type', ['in', 'add'])
                    ->where('quantity_remaining', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($qtyToProcess <= 0) break;
                    $take = min($batch->quantity_remaining, $qtyToProcess);
                    $totalCost += ($take * $batch->unit_cost);
                    $batch->decrement('quantity_remaining', $take);
                    $qtyToProcess -= $take;
                }

                $itemRevenue = ($itemData['unit_price'] * $itemData['quantity']);
                $totalAmount += $itemRevenue;
                $finalSaleProfit += ($itemRevenue - $totalCost);

                $sale->saleItems()->create([
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total' => $itemRevenue,
                ]);
            }

            // 4. UPDATE: Finalize Sale totals
            $sale->update([
                'total_amount' => $totalAmount,
                'profit_loss' => $finalSaleProfit,
                'customer_id' => $request->customer_id,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Sale updated and stock recalculated.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Sale Update Failed: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }


    public function storeApi(Request $request)
{
    // 1. Use Validator instead of $request->validate to prevent auto-redirects
    $validator = Validator::make($request->all(), [
        'name'         => 'required|string|max:255',
        'location'     => 'required|string|max:255',
        'manager_name' => 'nullable|string|max:255',
        'latitude'     => 'nullable|numeric',
        'longitude'    => 'nullable|numeric',
    ]);


    try {
        // 3. Create the Duka linked to the authenticated tenant
        $duka = Duka::create([
            'tenant_id'    => auth()->user()->tenant_id,
            'name'         => $request->name,
            'location'     => $request->location,
            'manager_name' => $request->manager_name ?? auth()->user()->name,
            'latitude'     => $request->latitude,
            'longitude'    => $request->longitude,
            'status'       => 'active',
        ]);

        // 4. Return success JSON for Flutter
        return response()->json([
            'success' => true,
            'message' => 'Duka registered successfully!',
            'data'    => $duka
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Server Error: ' . $e.getMessage()
        ], 500);
    }
}


public function apiManageProduct(Request $request)
{
    // 1. Validate that a product ID was provided
    $request->validate([
        'product_id' => 'required|integer|exists:products,id'
    ]);

    try {
        $productId = $request->product_id;
        $user = auth()->user();

        // 2. Find product and verify it belongs to the User's Tenant
        $product = Product::with(['stocks.duka', 'category'])
            ->where('id', $productId)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (!$product) {
            Log::warning("Unauthorized Access: User {$user->id} tried to access Product {$productId}");
            return response()->json(['success' => false, 'message' => 'Product not found or unauthorized'], 403);
        }

        // 3. Expected Profit (Potential value of items on shelf)
        $batches = StockMovement::whereHas('stock', function($q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->where('type', 'in')
            ->where('quantity_remaining', '>', 0)
            ->get();

        $totalCostOfCurrentStock = 0;
        $totalStockQuantity = 0;

        foreach ($batches as $batch) {
            $totalCostOfCurrentStock += ($batch->quantity_remaining * $batch->unit_cost);
            $totalStockQuantity += $batch->quantity_remaining;
        }

        $expectedRevenue = $totalStockQuantity * $product->selling_price;
        $expectedProfit = $expectedRevenue - $totalCostOfCurrentStock;

        // 4. Actual Profit (Money already made from completed sales)
        $movementsHistory = StockMovement::whereHas('stock', function($q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->with(['stock.duka', 'user'])
            ->latest()
            ->get();

        $actualProfit = $movementsHistory->where('type', 'out')->where('reason', 'sale')->sum(function($m) {
            return ($m->unit_price - $m->unit_cost) * abs($m->quantity_change);
        });

        Log::info("Analytics Accessed: Product '{$product->name}' viewed by User ID {$user->id}");

        // 5. Return JSON for Flutter
        return response()->json([
            'success' => true,
            'data' => [
                'product' => [
                    'name' => $product->name,
                    'selling_price' => $product->selling_price,
                    'category' => $product->category->name ?? 'Uncategorized',
                ],
                'financials' => [
                    'expected_profit' => $expectedProfit,
                    'actual_profit' => $actualProfit,
                    'stock_value_cost' => $totalCostOfCurrentStock,
                    'current_stock_count' => $totalStockQuantity,
                    'potential_revenue' => $expectedRevenue,
                ],
                'history' => $movementsHistory
            ]
        ], 200);

    } catch (\Exception $e) {
        Log::error("API Manage Product Error: " . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Internal Server Error'], 500);
    }
}

public function apiReduceStock(Request $request)
{
    $validated = $request->validate([
        'product_id' => 'required|exists:products,id',
        'duka_id'    => 'required|exists:dukas,id',
        'quantity'   => 'required|integer|min:1',
        'type'       => 'required|in:damaged,lost,expired,destroyed,returned_to_supplier',
        'notes'      => 'nullable|string|max:500',
    ]);

    $userId = Auth::id();

    try {
        return DB::transaction(function () use ($validated, $userId) {
            // 1. Find the existing stock record
            $stock = Stock::where('product_id', $validated['product_id'])
                ->where('duka_id', $validated['duka_id'])
                ->first();

            if (!$stock || $stock->quantity < $validated['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock or product not found in this shop.'
                ], 400);
            }

            $previousQuantity = $stock->quantity;
            $reductionAmount = $validated['quantity'];

            // 2. Decrease the stock count
            $stock->decrement('quantity', $reductionAmount);
            $newQuantity = $stock->fresh()->quantity;

            // 3. Record the movement with the specific reason
            StockMovement::create([
                'stock_id'          => $stock->id,
                'user_id'           => $userId,
                'type'              => 'out',
                'quantity_change'   => -$reductionAmount,
                'previous_quantity' => $previousQuantity,
                'new_quantity'      => $newQuantity,
                'reason'            => $validated['type'], // damaged, lost, etc.
                'notes'             => $validated['notes'],
            ]);

            Log::info("Stock Reduced: Product {$validated['product_id']} at Duka {$validated['duka_id']} reduced by {$reductionAmount} due to {$validated['type']}.");

            return response()->json([
                'success' => true,
                'message' => 'Stock reduced successfully and recorded as ' . $validated['type'],
                'data' => [
                    'previous_quantity' => $previousQuantity,
                    'current_quantity'  => $newQuantity,
                    'reason'            => $validated['type']
                ]
            ], 200);
        });
    } catch (\Exception $e) {
        Log::error("Stock Reduction Failed: " . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Server error occurred'], 500);
    }
}


public function updateSaleDate(Request $request, $id)
{
    $request->validate([
        'created_at' => 'required|date',
    ]);

    DB::beginTransaction();
    try {
        $sale = Sale::findOrFail($id);
        $newDate = Carbon::parse($request->created_at);

        // 1. Update the Sale record
        $sale->update([
            'created_at' => $newDate
        ]);

        // 2. Sync associated Transactions (Crucial for Revenue Reports)
        // We update transaction_date and created_at
        Transaction::where('reference_id', $sale->id)
            ->where('category', 'sale')
            ->update([
                'transaction_date' => $newDate->toDateString(),
                'created_at' => $newDate
            ]);

        // 3. Sync Stock Movements (Crucial for COGS and Inventory Reports)
        // We find movements related to this sale via SaleItems
        $saleItemIds = $sale->saleItems()->pluck('id');
        StockMovement::whereIn('product_item_id', $saleItemIds)
            ->where('reason', 'sale')
            ->update(['created_at' => $newDate]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Sale and related financial records backdated successfully',
            'data' => $sale
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Failed to update date: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Delete tenant account and all related data.
 */
public function apiDeleteAccount(Request $request)
{
    $user = Auth::user();
    $tenant = $user->tenant;

    if (!$tenant) {
        return response()->json(['success' => false, 'message' => 'Tenant not found.'], 404);
    }

    DB::beginTransaction();

    try {
        $dukaIds = $tenant->dukas()->pluck('id')->toArray();

        // 1. Get all user IDs associated with this tenant
        $officerIds = TenantOfficer::where('tenant_id', $tenant->id)->pluck('officer_id')->toArray();
        $allTenantUserIds = array_merge($officerIds, [$user->id]);

        // 2. Clear Transaction & Movement dependencies for ALL tenant users
        // This is the critical fix for the foreign key violation
        Transaction::whereIn('user_id', $allTenantUserIds)->delete();
        StockMovement::whereIn('user_id', $allTenantUserIds)->delete();

        // 3. Delete Duka-specific data (Sales, Invoices, etc.)
        SaleItem::whereHas('sale', function($query) use ($dukaIds) {
            $query->whereIn('duka_id', $dukaIds);
        })->delete();

        Sale::whereIn('duka_id', $dukaIds)->delete();

        LoanPayment::whereHas('sale', function($query) use ($dukaIds) {
            $query->whereIn('duka_id', $dukaIds);
        })->delete();

        ProformaInvoiceItem::whereHas('proformaInvoice', function($query) use ($dukaIds) {
            $query->whereIn('duka_id', $dukaIds);
        })->delete();

        ProformaInvoice::whereIn('duka_id', $dukaIds)->delete();

        // 4. Stock and Product cleanup
        StockMovement::whereHas('stock', function($query) use ($dukaIds) {
            $query->whereIn('duka_id', $dukaIds);
        })->delete();

        $stockTransferItemIds = StockTransferItem::whereIn('from_duka_id', $dukaIds)
            ->orWhereIn('to_duka_id', $dukaIds)
            ->pluck('id');

        StockTransfer::whereIn('stock_transfer_id', $stockTransferItemIds)->delete();
        StockTransferItem::whereIn('from_duka_id', $dukaIds)
            ->orWhereIn('to_duka_id', $dukaIds)
            ->delete();

        Transaction::whereIn('duka_id', $dukaIds)->delete();

        \App\Models\ProductItem::whereHas('product', function($query) use ($dukaIds) {
            $query->whereIn('duka_id', $dukaIds);
        })->delete();

        Stock::whereIn('duka_id', $dukaIds)->delete();
        Product::whereIn('duka_id', $dukaIds)->delete();
        Customer::whereIn('duka_id', $dukaIds)->delete();

        // 5. Delete Tenant-level configurations
        TenantOfficer::where('tenant_id', $tenant->id)->delete();
        StaffPermission::where('tenant_id', $tenant->id)->delete();
        Message::where('tenant_id', $tenant->id)->delete();
        TenantAccount::where('tenant_id', $tenant->id)->delete();
        DukaSubscription::where('tenant_id', $tenant->id)->delete();
        ProductCategory::where('tenant_id', $tenant->id)->delete();
        Duka::whereIn('id', $dukaIds)->delete();

        // 6. Delete all Officer Users
        User::whereIn('id', $officerIds)->delete();

        // 7. Delete Tenant and then the Owner User
        $tenant->delete();

        // Capture owner ID and delete him last
        $ownerId = $user->id;
        User::where('id', $ownerId)->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully.',
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Tenant Account Deletion Failed: ' . $e->getMessage(), [
            'tenant_id' => $tenant->id ?? null,
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to delete account.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}
