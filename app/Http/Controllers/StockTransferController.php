<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Duka;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\TenantOfficer;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StockTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            abort(403, 'Unauthorized');
        }

        return view('tenant.stock-transfers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'from_duka_id' => [
                'required',
                'integer',
                Rule::exists('dukas', 'id')->where('tenant_id', $tenant->id),
            ],
            'to_duka_id' => [
                'required',
                'integer',
                'different:from_duka_id',
                Rule::exists('dukas', 'id')->where('tenant_id', $tenant->id),
            ],
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('tenant_id', $tenant->id),
            ],
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $fromDuka = Duka::findOrFail($request->from_duka_id);
        $toDuka = Duka::findOrFail($request->to_duka_id);
        $product = Product::findOrFail($request->product_id);

        // Check if from_duka has enough stock
        $fromStock = Stock::firstOrCreate([
            'duka_id' => $fromDuka->id,
            'product_id' => $product->id,
        ]);

        if ($fromStock->quantity < $request->quantity) {
            return back()->withErrors(['quantity' => 'Insufficient stock in the source duka.']);
        }

        DB::transaction(function () use ($request, $tenant, $fromDuka, $toDuka, $product, $fromStock) {
            // Update from_duka stock (reduce)
            $fromStock->decrement('quantity', $request->quantity);
            $fromStock->last_updated_by = Auth::id();
            $fromStock->save();

            // Update to_duka stock (increase)
            $toStock = Stock::firstOrCreate([
                'duka_id' => $toDuka->id,
                'product_id' => $product->id,
            ]);
            $toStock->increment('quantity', $request->quantity);
            $toStock->last_updated_by = Auth::id();
            $toStock->save();

            // Create StockTransferItem (header)
            $transferItem = StockTransferItem::create([
                'tenant_id' => $tenant->id,
                'from_duka_id' => $fromDuka->id,
                'to_duka_id' => $toDuka->id,
                'transferred_by' => Auth::id(),
                'status' => 'completed',
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            // Create StockTransfer (line item)
            StockTransfer::create([
                'stock_transfer_id' => $transferItem->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'notes' => $request->notes,
            ]);

            // Create StockMovement OUT for from_duka
            StockMovement::create([
                'stock_id' => $fromStock->id,
                'user_id' => Auth::id(),
                'type' => 'remove',
                'quantity_change' => -$request->quantity,
                'previous_quantity' => $fromStock->quantity + $request->quantity, // before decrement
                'new_quantity' => $fromStock->quantity,
                'reason' => 'Stock Transfer',
                'notes' => "Transferred to {$toDuka->name}",
            ]);

            // Create StockMovement IN for to_duka
            StockMovement::create([
                'stock_id' => $toStock->id,
                'user_id' => Auth::id(),
                'type' => 'add',
                'quantity_change' => $request->quantity,
                'previous_quantity' => $toStock->quantity - $request->quantity, // before increment
                'new_quantity' => $toStock->quantity,
                'reason' => 'Stock Transfer',
                'notes' => "Transferred from {$fromDuka->name}",
            ]);
        });

        return redirect()->route('tenant.dashboard')->with('success', 'Stock transferred successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(StockTransfer $stockTransfer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockTransfer $stockTransfer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockTransfer $stockTransfer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockTransfer $stockTransfer)
    {
        //
    }
}
