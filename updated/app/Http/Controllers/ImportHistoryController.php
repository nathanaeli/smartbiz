<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportHistoryController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant->id;

        $batches = Sale::select('import_batch', 'created_at', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total'))
            ->where('tenant_id', $tenantId)

            ->whereNotNull('import_batch')
            ->where('import_batch', '!=', '')
            ->groupBy('import_batch', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('imports.index', compact('batches'));
    }

    public function rollback($batchId)
    {
        DB::transaction(function () use ($batchId) {
            // 1. Delete Sales (Observer restores stock)
            $sales = Sale::where('import_batch', $batchId)->get();
            foreach ($sales as $sale) {
                $sale->delete();
                // Remove the 'sale_return' artifact created by restoreStock
                StockMovement::where('notes', "Stock returned from deleted/edited Sale #{$sale->id}")->delete();
            }

            // 2. Delete Virtual Stock Input (and reverse stock)
            $movements = StockMovement::where('import_batch', $batchId)->get();
            foreach ($movements as $mov) {
                // Determine if we need to reverse stock
                // Since this was an 'in' movement that added stock, we must remove it.
                // Note: The Sale Delete (Step 1) already put the sold items 'back' into stock (increment).
                // So now we have the original quantity. We must remove the source.

                $stock = $mov->stock;
                if ($stock && $mov->type === 'in') {
                    $stock->decrement('quantity', $mov->quantity_change);
                }
                $mov->delete();
            }
        });

        return back()->with('success', 'Import rolled back successfully.');
    }

    /**
     * Special function to clean up imports done before Batch ID was implemented.
     * Targets imports from TODAY with Profit 0.
     */
    public function cleanupLegacy()
    {
        DB::transaction(function () {
            $user = auth()->user();

            // 1. Find and Delete Likely Legacy Sales (Today, Profit 0, No Batch)
            // We filter by date to avoid deleting intentional 0 profit sales from past.
            $sales = Sale::where('tenant_id', $user->tenant->id)
                ->where(function ($q) {
                    $q->whereNull('import_batch')->orWhere('import_batch', '');
                })
                ->where('profit_loss', 0)
                ->whereDate('updated_at', Carbon::today()) // updated_at is reliable for "When it was imported"
                ->get();

            $count = $sales->count();

            foreach ($sales as $sale) {
                $sale->delete(); // This RESTORES stock via Observer
                // Remove the 'sale_return' artifact
                StockMovement::where('notes', "Stock returned from deleted/edited Sale #{$sale->id}")->delete();
            }

            // 2. Find and Delete Likely Legacy Virtual Stock (Today, reason='historical_import')
            // These would have been created at same time by the same user.
            $movements = StockMovement::where('user_id', $user->id)
                ->where('reason', 'historical_import')
                ->where(function ($q) {
                    $q->whereNull('import_batch')->orWhere('import_batch', '');
                })
                ->whereDate('updated_at', Carbon::today()) // reliable timestamp
                ->get();

            foreach ($movements as $mov) {
                $stock = $mov->stock;
                if ($stock) {
                    $stock->decrement('quantity', $mov->quantity_change);
                }
                $mov->delete();
            }

            return $count;
        });

        return back()->with('success', 'Legacy cleanup completed.');
    }
}
