<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Stock;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class BackfillCogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cogs:backfill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate missing StockMovement (out) records for past sales to populate COGS.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting COGS Backfill...');

        // 1. Get all Sale Items
        // We'll process them in chunks to avoid memory issues
        $count = 0;
        $created = 0;
        $skipped = 0;
        $errors = 0;

        // 1. Get all Sales (not just items, so we can update the Sale record)
        $count = 0;
        $created = 0;
        $skipped = 0;
        $updatedSales = 0;
        $errors = 0;

        $sales = \App\Models\Sale::with(['saleItems.product'])->chunk(50, function ($sales) use (&$count, &$created, &$skipped, &$errors, &$updatedSales) {
            foreach ($sales as $sale) {
                $count++;
                $totalCogsForSale = 0;

                try {
                    foreach ($sale->saleItems as $item) {
                        // Check or create movement for this item
                        $saleTime = $sale->created_at;

                        // Try to find existing movement
                        $movement = StockMovement::where('type', 'out')
                            ->where('reason', 'sale')
                            ->where('created_at', '>=', $saleTime->subSeconds(5))
                            ->where('created_at', '<=', $saleTime->addSeconds(5))
                            ->whereHas('stock', function ($q) use ($item, $sale) {
                                $q->where('product_id', $item->product_id)
                                    ->where('duka_id', $sale->duka_id);
                            })
                            ->where('quantity_change', $item->quantity)
                            ->first();

                        if ($movement) {
                            $totalCogsForSale += ($movement->quantity_change * $movement->unit_cost);
                            $skipped++;
                            continue;
                        }

                        // No movement found. Create one.
                        $stock = Stock::where('duka_id', $sale->duka_id)
                            ->where('product_id', $item->product_id)
                            ->first();

                        if (!$stock) {
                            $this->warn("Skipping Item #{$item->id}: Stock record not found.");
                            $errors++;
                            continue;
                        }

                        $unitCost = $item->product ? $item->product->base_price : 0;
                        $cogs = $item->quantity * $unitCost;
                        $totalCogsForSale += $cogs;

                        StockMovement::create([
                            'stock_id' => $stock->id,
                            'user_id' => $sale->created_by,
                            'type' => 'out',
                            'quantity_change' => $item->quantity,
                            'previous_quantity' => 0,
                            'new_quantity' => 0,
                            'unit_cost' => $unitCost,
                            'unit_price' => $item->unit_price,
                            'total_value' => $item->quantity * $item->unit_price,
                            'batch_number' => 'BACKFILL',
                            'reason' => 'sale',
                            'notes' => "Backfill for Sale #{$sale->id}",
                            'created_at' => $sale->created_at,
                            'updated_at' => $sale->updated_at,
                        ]);

                        $created++;
                        $this->info("Created movement for Sale #{$sale->id} Item #{$item->id}");
                    }

                    // Update Sale Profit/Loss
                    $profitLoss = $sale->total_amount - $totalCogsForSale;
                    $sale->update([
                        'profit_loss' => $profitLoss
                    ]);
                    $updatedSales++;
                } catch (\Exception $e) {
                    $this->error("Error processing Sale #{$sale->id}: " . $e->getMessage());
                    $errors++;
                }
            }
        });

        $this->info("Backfill Complete.");
        $this->info("Processed: $count");
        $this->info("Created: $created");
        $this->info("Skipped (Already Exists): $skipped");
        $this->info("Errors: $errors");
    }
}
