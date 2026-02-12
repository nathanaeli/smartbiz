<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_item_id',
        'quantity',
        'unit_price',
        'discount_amount',
        'total',
        'created_at', // Added to allow backdating sync
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productItem()
    {
        return $this->belongsTo(ProductItem::class);
    }

    /**
     * Restore stock to batches and main inventory.
     * Used when a sale is deleted or edited.
     */
    public function restoreStock()
    {
        DB::transaction(function () {
            $product = $this->product;
            $sale = $this->sale; // Load the sale once
            $dukaId = $sale->duka_id;
            $qtyToRestore = $this->quantity;

            // Use the original sale's date for the restoration movement
            $originalSaleDate = $sale->created_at;

            // 1. Update the main Stock table
            $stock = Stock::where('product_id', $this->product_id)
                ->where('duka_id', $dukaId)
                ->first();

            if ($stock) {
                $stock->increment('quantity', $qtyToRestore);
            }

            // 2. FIFO Reversal: Restore quantity_remaining to the correct batches
            $batches = StockMovement::whereHas('stock', function ($q) use ($dukaId) {
                    $q->where('product_id', $this->product_id)
                      ->where('duka_id', $dukaId);
                })
                ->whereIn('type', ['in', 'add'])
                ->whereColumn('quantity_remaining', '<', 'quantity_change')
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($batches as $batch) {
                if ($qtyToRestore <= 0) break;

                $capacity = $batch->quantity_change - $batch->quantity_remaining;
                $restore = min($capacity, $qtyToRestore);

                $batch->increment('quantity_remaining', $restore);
                $qtyToRestore -= $restore;
            }

            // 3. Create a "RESTORE" Movement for the Audit Trail
            // Critical: We set created_at to $originalSaleDate to keep reports clean
            if ($stock) {
                StockMovement::create([
                    'stock_id'          => $stock->id,
                    'user_id'           => auth()->id(),
                    'type'              => 'in',
                    'quantity_change'   => $this->quantity,
                    'previous_quantity' => $stock->quantity - $this->quantity,
                    'new_quantity'      => $stock->quantity,
                    'unit_cost'         => $this->getAverageUnitCost(), // Helper to get cost
                    'reason'            => 'sale_return',
                    'notes'             => "Stock returned from deleted/edited Sale #{$this->sale_id}",
                    'created_at'        => $originalSaleDate, // BACKDATED
                ]);
            }
        });
    }

    /**
     * Optional helper to find the cost recorded in the original movement
     */
    private function getAverageUnitCost()
    {
        $movement = StockMovement::where('stock_id', function($q) {
                $q->select('id')->from('stocks')
                  ->where('product_id', $this->product_id)
                  ->where('duka_id', $this->sale->duka_id);
            })
            ->where('reason', 'sale')
            ->where('created_at', $this->sale->created_at)
            ->first();

        return $movement ? $movement->unit_cost : ($this->product->base_price ?? 0);
    }
}
