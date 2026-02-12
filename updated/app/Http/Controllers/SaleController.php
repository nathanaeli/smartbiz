<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Sale, Product, ProductCategory, Stock, SaleItem, Transaction, Customer, TenantOfficer, StockMovement};
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesTemplateExport;
use App\Imports\SalesImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleController extends Controller
{
    public function edit($id)
    {
        $sale = Sale::with(['customer', 'duka', 'saleItems.product'])->findOrFail($id);
        $user = auth()->user();
        if ($sale->tenant_id != $user->tenant->id) {
            abort(403, 'Unauthorized access.');
        }
        return view('sales.edit', compact('sale'));
    }

    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        $user = auth()->user();
        if ($sale->tenant_id != $user->tenant->id) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string|max:255',
        ]);

        $sale->update([
            'discount_amount' => $request->discount_amount ?? 0,
            'discount_reason' => $request->discount_reason,
        ]);

        // Recalculate total_amount if discount changed
        $total = $sale->saleItems->sum('total') - $sale->discount_amount;
        $sale->update(['total_amount' => $total]);

        return redirect()->route('sales.show', $sale->id)->with('success', 'Sale updated successfully.');
    }


    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Sale::where('tenant_id', $user->tenant->id)->with(['customer', 'duka']);

        if ($request->has('duka_id') && $request->duka_id) {
            $query->where('duka_id', $request->duka_id);
        }

        $sales = $query->get();
        return view('sales.index', compact('sales'));
    }

    public function exportSales(Request $request)
    {
        return Excel::download(new \App\Exports\SalesHistoryExport(auth()->user()->tenant->id, $request->duka_id), 'sales_history.xlsx');
    }

    public function exportSummaryExcel(Request $request)
    {
        return Excel::download(new \App\Exports\SalesSummaryExport(auth()->user()->tenant->id, $request->duka_id), 'sales_summary.xlsx');
    }

    public function exportSummaryPdf(Request $request)
    {
        $user = auth()->user();
        $query = Sale::where('tenant_id', $user->tenant->id)->with(['duka']);
        if ($request->duka_id) $query->where('duka_id', $request->duka_id);
        $sales = $query->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sales_history_summary', compact('sales'));
        return $pdf->download('sales_summary.pdf');
    }

    /**
     * Smart redirection for "Sales" link.
     * Redirects to POS if 1 duka, else shows duka selection.
     */
    public function smartIndex()
    {
        $user = auth()->user();
        $dukas = \App\Models\Duka::where('tenant_id', $user->tenant->id)->get();

        if ($dukas->count() === 1) {
            // If only one Duka, go straight to Sale Process (POS)
            return redirect()->route('sale.process', ['dukaId' => $dukas->first()->id]);
        }

        // Otherwise show selection page
        return view('sales.select-duka', compact('dukas'));
    }

    // ==========================================
    // NORMAL BLADE POS METHODS
    // ==========================================

    public function process(Request $request, $dukaId)
    {
        $user = auth()->user();
        $duka = \App\Models\Duka::where('tenant_id', $user->tenant->id)->findOrFail($dukaId);

        // Products & Categories
        $categories = ProductCategory::where('tenant_id', $user->tenant->id)->get();

        $productsQuery = Product::where('tenant_id', $user->tenant->id)
            ->where('duka_id', $dukaId)
            ->where('is_active', true);

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $productsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Category Filter
        if ($request->has('category_id') && !empty($request->category_id)) {
            $productsQuery->where('category_id', $request->category_id);
        }

        $products = $productsQuery->limit(20)->get(); // Pagination would be better, but limiting for now

        // Get Stock for these products
        $productIds = $products->pluck('id');
        $stocks = Stock::where('duka_id', $dukaId)->whereIn('product_id', $productIds)->pluck('quantity', 'product_id');

        // Cart
        $cart = session()->get("cart_{$dukaId}", []);
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['total'];
        }

        return view('sale.process_normal', compact('duka', 'categories', 'products', 'stocks', 'cart', 'total'));
    }

    public function addToCart(Request $request, $dukaId)
    {
        $productId = $request->product_id;
        $product = Product::findOrFail($productId);

        $cartKey = "cart_{$dukaId}";
        $cart = session()->get($cartKey, []);

        // Check Stock
        $stock = Stock::where('duka_id', $dukaId)->where('product_id', $productId)->first();
        $available = $stock ? $stock->quantity : 0;

        $currentQty = isset($cart[$productId]) ? $cart[$productId]['quantity'] : 0;

        if ($currentQty + 1 > $available) {
            return back()->with('error', 'Insufficient stock.');
        }

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity']++;
            $cart[$productId]['total'] = $cart[$productId]['quantity'] * $cart[$productId]['unit_price'];
        } else {
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'unit_price' => $product->selling_price,
                'quantity' => 1,
                'total' => $product->selling_price,
            ];
        }

        session()->put($cartKey, $cart);

        if ($request->ajax()) {
            return $this->getCartResponse($dukaId);
        }

        // If Ajax request? assume normal form for now
        return back(); // No success message to avoid clutter
    }

    public function removeFromCart(Request $request, $dukaId, $productId)
    {
        $cartKey = "cart_{$dukaId}";
        $cart = session()->get($cartKey, []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put($cartKey, $cart);
        }

        if ($request->ajax()) {
            return $this->getCartResponse($dukaId);
        }

        return back();
    }

    public function clearCart(Request $request, $dukaId)
    {
        session()->forget("cart_{$dukaId}");

        if ($request->ajax()) {
            return $this->getCartResponse($dukaId);
        }

        return back();
    }

    private function getCartResponse($dukaId)
    {
        $cart = session()->get("cart_{$dukaId}", []);
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['total'];
        }
        $duka = \App\Models\Duka::find($dukaId);

        $html = view('sale.partials.cart-items', compact('cart', 'duka'))->render();

        return response()->json([
            'status' => 'success',
            'html' => $html,
            'total' => number_format($total),
            'cart_empty' => empty($cart)
        ]);
    }

    public function checkout(Request $request, $dukaId)
    {
        $request->validate([
            'amount_tendered' => 'nullable|numeric|min:0',
        ]);

        $user = auth()->user();
        $cartKey = "cart_{$dukaId}";
        $cart = session()->get($cartKey, []);

        if (empty($cart)) {
            return back()->with('error', 'Cart is empty.');
        }

        // Re-calculate Total
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['total'];
        }

        // Use tendered amount if provided, otherwise assume exact cash (total)
        $amountTendered = $request->filled('amount_tendered') ? $request->amount_tendered : $total;

        if ($amountTendered < $total && !$request->has('is_loan')) {
            return back()->with('error', 'Amount tendered is less than total (and not marked as loan).');
        }

        DB::beginTransaction();
        try {
            // Create Sale
            $sale = Sale::create([
                'tenant_id' => $user->tenant->id,
                'duka_id' => $dukaId,
                'customer_id' => $request->customer_id ?? null,
                'total_amount' => $total,
                'amount_tendered' => $amountTendered,
                'change_amount' => max(0, $amountTendered - $total),
                'is_loan' => $request->has('is_loan'),
                'created_by' => $user->id,
            ]);

            foreach ($cart as $item) {
                // Deduct Stock
                $stock = Stock::where('duka_id', $dukaId)->where('product_id', $item['id'])->first();
                if (!$stock || $stock->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$item['name']}");
                }

                // FIFO Logic with COGS tracking
                $qtyToProcess = $item['quantity'];

                // Get inbound batches that have remaining stock
                $batches = StockMovement::where('stock_id', $stock->id)
                    ->whereIn('type', ['in', 'add', 'update'])
                    ->where('quantity_remaining', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->get();

                $runningStockQty = $stock->quantity;
                $originalQty = $item['quantity'];

                foreach ($batches as $batch) {
                    if ($qtyToProcess <= 0) break;

                    $take = min($batch->quantity_remaining, $qtyToProcess);
                    $batch->decrement('quantity_remaining', $take);

                    // Create Outbound Movement to record COGS for this batch
                    StockMovement::create([
                        'stock_id' => $stock->id,
                        'user_id' => $user->id,
                        'type' => 'out',
                        'quantity_change' => $take,
                        'previous_quantity' => $runningStockQty,
                        'new_quantity' => $runningStockQty - $take,
                        'unit_cost' => $batch->unit_cost, // Cost from the specific batch
                        'unit_price' => $item['unit_price'], // Selling Price
                        'total_value' => $take * $item['unit_price'],
                        'reason' => 'sale',
                        'notes' => "Sale #{$sale->id} (Batch {$batch->id})",
                    ]);

                    $runningStockQty -= $take;
                    $qtyToProcess -= $take;
                }

                // If we still have quantity to process but no batches found (e.g. migration data or stock adjustment without batch),
                // fall back to the Product's base_price.
                if ($qtyToProcess > 0) {
                    $product = Product::find($item['id']);
                    $baseCost = $product ? $product->base_price : 0;

                    StockMovement::create([
                        'stock_id' => $stock->id,
                        'user_id' => $user->id,
                        'type' => 'out',
                        'quantity_change' => $qtyToProcess,
                        'previous_quantity' => $runningStockQty,
                        'new_quantity' => $runningStockQty - $qtyToProcess,
                        'unit_cost' => $baseCost,
                        'unit_price' => $item['unit_price'],
                        'total_value' => $qtyToProcess * $item['unit_price'],
                        'reason' => 'sale',
                        'notes' => "Sale #{$sale->id} (No Batch)",
                    ]);
                }

                $stock->decrement('quantity', $item['quantity']);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item->unit_price,
                    'total' => $item['total'],
                ]);
            }

            // Calculate Total COGS for this Sale to store Profit/Loss
            $totalCogs = StockMovement::where('type', 'out')
                ->where('reason', 'sale')
                ->whereHas('stock', function ($q) use ($dukaId) {
                    $q->where('duka_id', $dukaId);
                })
                ->where('created_at', '>=', $sale->created_at->subSeconds(5)) // Heuristic to catch just-created movements
                ->where('created_at', '<=', $sale->created_at->addSeconds(5))
                ->get()
                ->filter(function ($movement) use ($sale) {
                    return str_contains($movement->notes, "Sale #{$sale->id}");
                })
                ->sum(function ($movement) {
                    return $movement->quantity_change * $movement->unit_cost;
                });

            $sale->update(['profit_loss' => $total - $totalCogs]);

            Transaction::create([
                'duka_id' => $dukaId,
                'user_id' => $user->id,
                'type' => 'income',
                'category' => 'sale',
                'amount' => $total,
                'status' => 'active',
                'reference_id' => $sale->id,
                'transaction_date' => now()->toDateString(),
            ]);

            DB::commit();
            session()->forget($cartKey);

            return back()->with('success', 'Sale completed successfully! Receipt #' . $sale->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Sale failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate($dukaId)
    {
        $tenantId = auth()->user()->tenant->id;
        return Excel::download(new SalesTemplateExport($dukaId, $tenantId), 'sales_import_template.xlsx');
    }

    public function downloadImportInstructions()
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.import_instruction');
        return $pdf->download('sales_import_instructions.pdf');
    }

    public function importSales(Request $request, $dukaId)
    {
        $request->validate([
            'import_file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $user = auth()->user();

        try {
            $importer = new SalesImport($dukaId, $user->id, $user->tenant->id);
            Excel::import($importer, $request->file('import_file'));

            $errors = $importer->getErrors();
            $successCount = $importer->getSuccessCount();

            if (count($errors) > 0) {
                $msg = "Imported $successCount sales. Failures: " . implode(' | ', array_slice($errors, 0, 5));
                if (count($errors) > 5) $msg .= " ... and " . (count($errors) - 5) . " more.";

                if ($successCount == 0) {
                    return back()->with('error', "Import Failed: " . $msg);
                }

                return back()->with('warning', $msg);
            }

            return back()->with('success', "Successfully imported $successCount sales.");
        } catch (\Exception $e) {
            return back()->with('error', 'Import Error: ' . $e->getMessage());
        }
    }
}
