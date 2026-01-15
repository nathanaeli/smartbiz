<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductItem;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        \Log::info('Product Store Request', [
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
            'tenant_id' => Auth::user()->tenant->id ?? null
        ]);

        $request->validate([
            'duka_id'       => 'required|exists:dukas,id',
            'sku'           => 'required|string|unique:products,sku',
            'name'          => 'required|string|max:255',
            'category_id'   => 'nullable|exists:product_categories,id',
            'unit'          => 'required|in:pcs,kg,g,ltr,ml,box,bag,pack,set,pair,dozen,carton',
            'base_price'    => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'description'   => 'nullable|string',
            'initial_stock' => 'nullable|integer|min:0',
            'batch_number'  => 'nullable|string|max:255',
            'expiry_date'   => 'nullable|date',
        ]);

        try {
            $user = Auth::user();
            $duka = $user->duka;

            \Log::info('Creating product', ['duka_id' => $duka->id ?? 'null']);

            // Create the product
            $product = Product::create([
                'duka_id'       => $duka->id,
                'category_id'   => $request->category_id,
                'sku'           => $request->sku,
                'name'          => $request->name,
                'description'   => $request->description,
                'unit'          => $request->unit,
                'base_price'    => $request->base_price,
                'selling_price' => $request->selling_price,
            ]);

            \Log::info('Product created successfully', ['product_id' => $product->id]);

            // Add initial stock if provided
            if ($request->filled('initial_stock') && $request->initial_stock > 0) {
                Stock::updateOrCreate(
                    [
                        'duka_id'    => $duka->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'quantity'        => $request->initial_stock,
                        'last_updated_by' => $user->id,
                        'batch_number'    => $request->batch_number,
                        'expiry_date'     => $request->expiry_date,
                    ]
                );

                \Log::info('Initial stock added', ['quantity' => $request->initial_stock]);

                // Generate QR codes for each item if quantity provided
                for ($i = 0; $i < $request->initial_stock; $i++) {
                    ProductItem::create([
                        'product_id' => $product->id,
                        'qr_code' => uniqid('QR_'),
                        'status' => 'available',
                    ]);
                }
            }

            \Log::info('Product creation completed successfully');
            return redirect()->back()->with('success', 'Product added successfully!');
        } catch (\Exception $e) {
            \Log::error('Product creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to add product: ' . $e->getMessage());
        }
    }

  public function manage($encrypted)
{
    try {
        // 1. Decrypt and find product
        $productId = \Illuminate\Support\Facades\Crypt::decrypt($encrypted);
        $product = \App\Models\Product::with(['stocks', 'category'])->findOrFail($productId);

        $user = auth()->user();
        $tenantId = $user->tenant_id;

        // 2. Calculate EXPECTED Profit (Potential money on the shelf)
        // We look at all 'IN' movements that still have unsold items (quantity_remaining > 0)
        $batches = \App\Models\StockMovement::whereHas('stock', function($q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->where('type', 'in')
            ->where('quantity_remaining', '>', 0)
            ->get();

        $totalCostOfCurrentStock = 0;
        $totalStockQuantity = 0;

        foreach ($batches as $batch) {
            // Financial logic: Qty Left in Batch * Price Paid for that Batch
            $totalCostOfCurrentStock += ($batch->quantity_remaining * $batch->unit_cost);
            $totalStockQuantity += $batch->quantity_remaining;
        }

        $expectedRevenue = $totalStockQuantity * $product->selling_price;
        $expectedProfit = $expectedRevenue - $totalCostOfCurrentStock;

        // 3. Calculate ACTUAL Realized Profit (Money already made from sales)
        // Logic: (Selling Price - Cost Price recorded at time of sale) * Quantity Sold
        $movementsHistory = \App\Models\StockMovement::whereHas('stock', function($q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->with(['stock', 'user'])
            ->latest()
            ->get();

        $actualProfit = $movementsHistory->where('type', 'out')->where('reason', 'sale')->sum(function($m) {
            return ($m->unit_price - $m->unit_cost) * abs($m->quantity_change);
        });

        // 4. Get categories for the edit dropdown
        $categories = \App\Models\ProductCategory::where('tenant_id', $tenantId)->get();

        // 5. Return view with all financial variables
        return view('tenant.products.manage', [
            'product'                  => $product,
            'categories'               => $categories,
            'movements'                => $movementsHistory,
            'expectedProfit'           => $expectedProfit,
            'actualProfit'             => $actualProfit,
            'totalStockQuantity'       => $totalStockQuantity,
            'totalCostOfCurrentStock'  => $totalCostOfCurrentStock,
        ]);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Manage Product Error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Could not load product details.');
    }
}

    // Scan QR code and add to cart
    public function scanQr(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string'
        ]);

        $item = ProductItem::where('qr_code', $request->qr_code)
            ->where('status', 'available')
            ->first();

        if (!$item) {
            return response()->json(['error' => 'Item not available or already sold'], 404);
        }

        // Add to cart logic (session or DB)
        // Example using session:
        $cart = session()->get('cart', []);
        $cart[] = $item->id;
        session()->put('cart', $cart);

        return response()->json([
            'message' => 'Item added to cart',
            'item' => $item->load('product')
        ]);
    }
}
