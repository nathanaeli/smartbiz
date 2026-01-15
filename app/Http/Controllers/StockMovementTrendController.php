<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Duka;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StockMovementTrendController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            $tenantId = $user->tenant_id ?? $user->tenant->id;

            Log::info('Stock movement trends index accessed', [
                'user_id' => $user->id,
                'tenant_id' => $tenantId
            ]);

            // Get all dukas for the tenant
            $dukas = Duka::where('tenant_id', $tenantId)->with(['products.stocks'])->get();

            // Get stock movements for the last 30 days
            $startDate = Carbon::now()->subDays(30);
            $movements = StockMovement::whereHas('stock.product.duka', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->with(['stock.product.duka', 'user'])
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->get();

            // Calculate trends
            $trends = $this->calculateTrends($movements);

            return view('tenant.stock-movement-trends', compact('dukas', 'movements', 'trends'));

        } catch (\Exception $e) {
            Log::error('Stock movement trends error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to load stock movement trends: ' . $e->getMessage());
        }
    }

    public function showProduct($encryptedId)
    {
        try {
            $user = Auth::user();
            $tenantId = $user->tenant_id ?? $user->tenant->id;
            $productId = decrypt($encryptedId);

            Log::info('Product stock movement trends', [
                'user_id' => $user->id,
                'product_id' => $productId
            ]);

            // Get the product
            $product = Product::where('id', $productId)
                ->whereHas('duka', function($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId);
                })
                ->with(['duka', 'stocks.movements.user'])
                ->firstOrFail();

            // Get all stock movements for this product
            $movements = StockMovement::whereHas('stock', function($q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->with(['stock', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

            // Calculate product-specific trends
            $trends = $this->calculateProductTrends($movements, $product);

            return view('tenant.product-stock-trends', compact('product', 'movements', 'trends'));

        } catch (\Exception $e) {
            Log::error('Product stock movement trends error', [
                'product_id' => $encryptedId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to load product stock trends: ' . $e->getMessage());
        }
    }

    public function showDuka($encryptedId)
    {
        try {
            $user = Auth::user();
            $tenantId = $user->tenant_id ?? $user->tenant->id;
            $dukaId = decrypt($encryptedId);

            Log::info('Duka stock movement trends', [
                'user_id' => $user->id,
                'duka_id' => $dukaId
            ]);

            // Get the duka
            $duka = Duka::where('id', $dukaId)
                ->where('tenant_id', $tenantId)
                ->with(['products.stocks.movements.user'])
                ->firstOrFail();

            // Get all stock movements for this duka
            $movements = StockMovement::whereHas('stock.product.duka', function($q) use ($dukaId) {
                $q->where('id', $dukaId);
            })
            ->with(['stock.product', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

            // Calculate duka-specific trends
            $trends = $this->calculateDukaTrends($movements, $duka);

            return view('tenant.duka-stock-trends', compact('duka', 'movements', 'trends'));

        } catch (\Exception $e) {
            Log::error('Duka stock movement trends error', [
                'duka_id' => $encryptedId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to load duka stock trends: ' . $e->getMessage());
        }
    }

    private function calculateTrends($movements)
    {
        $dailyTrends = [];
        $productTrends = [];
        $typeTrends = [
            'add' => 0,
            'update' => 0,
            'reduce' => 0
        ];

        // Process movements for daily trends
        foreach ($movements as $movement) {
            $date = $movement->created_at->format('Y-m-d');

            if (!isset($dailyTrends[$date])) {
                $dailyTrends[$date] = [
                    'date' => $date,
                    'additions' => 0,
                    'reductions' => 0,
                    'net_change' => 0
                ];
            }

            if ($movement->quantity_change > 0) {
                $dailyTrends[$date]['additions'] += $movement->quantity_change;
            } else {
                $dailyTrends[$date]['reductions'] += abs($movement->quantity_change);
            }

            $dailyTrends[$date]['net_change'] += $movement->quantity_change;
            $typeTrends[$movement->type] = ($typeTrends[$movement->type] ?? 0) + abs($movement->quantity_change);

            // Product trends
            $productName = $movement->stock->product->name ?? 'Unknown';
            if (!isset($productTrends[$productName])) {
                $productTrends[$productName] = [
                    'name' => $productName,
                    'total_movement' => 0,
                    'current_stock' => $movement->stock->quantity ?? 0
                ];
            }
            $productTrends[$productName]['total_movement'] += abs($movement->quantity_change);
        }

        // Sort daily trends by date
        ksort($dailyTrends);
        $dailyTrends = array_values($dailyTrends);

        // Sort product trends by movement volume
        usort($productTrends, function($a, $b) {
            return $b['total_movement'] <=> $a['total_movement'];
        });

        return [
            'daily_trends' => $dailyTrends,
            'product_trends' => array_slice($productTrends, 0, 10), // Top 10 most active products
            'type_trends' => $typeTrends,
            'total_movements' => $movements->count(),
            'period_days' => 30
        ];
    }

    private function calculateProductTrends($movements, $product)
    {
        $dailyData = [];
        $cumulativeStock = $product->stocks->sum('quantity');

        // Get historical data by working backwards from current stock
        foreach ($movements as $movement) {
            $date = $movement->created_at->format('Y-m-d');

            if (!isset($dailyData[$date])) {
                $dailyData[$date] = [
                    'date' => $date,
                    'stock_level' => $cumulativeStock,
                    'movement' => 0,
                    'movement_type' => null
                ];
            }

            $cumulativeStock -= $movement->quantity_change;
            $dailyData[$date]['stock_level'] = $cumulativeStock;
            $dailyData[$date]['movement'] = $movement->quantity_change;
            $dailyData[$date]['movement_type'] = $movement->type;
        }

        ksort($dailyData);

        return [
            'daily_data' => array_values($dailyData),
            'current_stock' => $product->stocks->sum('quantity'),
            'total_movements' => $movements->count(),
            'movement_frequency' => $movements->groupBy(function($movement) {
                return $movement->created_at->format('Y-m-d');
            })->map->count()->avg() ?? 0
        ];
    }

    private function calculateDukaTrends($movements, $duka)
    {
        $productMovements = [];
        $dailyTotals = [];

        foreach ($movements as $movement) {
            $productName = $movement->stock->product->name ?? 'Unknown';
            $date = $movement->created_at->format('Y-m-d');

            // Product-wise movements
            if (!isset($productMovements[$productName])) {
                $productMovements[$productName] = [
                    'name' => $productName,
                    'total_additions' => 0,
                    'total_reductions' => 0,
                    'net_change' => 0,
                    'movement_count' => 0
                ];
            }

            if ($movement->quantity_change > 0) {
                $productMovements[$productName]['total_additions'] += $movement->quantity_change;
            } else {
                $productMovements[$productName]['total_reductions'] += abs($movement->quantity_change);
            }
            $productMovements[$productName]['net_change'] += $movement->quantity_change;
            $productMovements[$productName]['movement_count']++;

            // Daily totals
            if (!isset($dailyTotals[$date])) {
                $dailyTotals[$date] = [
                    'date' => $date,
                    'total_movements' => 0,
                    'net_change' => 0
                ];
            }
            $dailyTotals[$date]['total_movements']++;
            $dailyTotals[$date]['net_change'] += $movement->quantity_change;
        }

        // Sort by activity
        usort($productMovements, function($a, $b) {
            return ($b['total_additions'] + $b['total_reductions']) <=> ($a['total_additions'] + $a['total_reductions']);
        });

        ksort($dailyTotals);

        return [
            'product_movements' => array_slice($productMovements, 0, 15),
            'daily_totals' => array_values($dailyTotals),
            'total_movements' => $movements->count(),
            'active_products' => count($productMovements)
        ];
    }
}
