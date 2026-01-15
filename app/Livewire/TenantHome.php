<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Sale;
use App\Models\Duka;
use App\Models\Customer;
use App\Models\Product;
use App\Models\LoanPayment;
use App\Models\SaleItem;
use Carbon\Carbon;

class TenantHome extends Component
{
    public $totalProducts;
    public $totalCustomers;
    public $totalLoanSales;
    public $totalProfit;
    public $dukaAnalytics;
    public $lowStockAlerts;
    public $recentLoanActivities;
    public $totalAnalyticsProfit;
    public $totalAnalyticsSalesCount;
    public $totalAnalyticsTotalSalesAmount;
    public $totalAnalyticsLoanSales;
    public $totalAnalyticsStockValue;
    public $totalAnalyticsProductsCount;
    public $totalAnalyticsLowStockCount;

    public function mount()
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id ?? $user->tenant->id;

        // Total Products
        $this->totalProducts = Product::whereHas('duka', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })->count();

        // Total Customers
        $this->totalCustomers = Customer::where('tenant_id', $tenantId)->count();

        // Total Loan Sales (sum of all loan payments)
        $this->totalLoanSales = LoanPayment::whereHas('sale', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })->sum('amount');

        // Total Profit (calculated from sale items considering discounts)
        $this->totalProfit = Sale::where('tenant_id', $tenantId)
            ->with(['saleItems.product'])
            ->get()
            ->sum(function($sale) {
                $saleProfit = 0;
                $totalSaleAmount = $sale->saleItems->sum('total');
                $saleDiscount = $sale->discount_amount ?? 0;

                foreach ($sale->saleItems as $saleItem) {
                    // Calculate profit per unit: selling price - buying price
                    $profitPerUnit = $saleItem->product->selling_price - $saleItem->product->base_price;

                    // Calculate item-level profit before discounts
                    $itemProfit = $profitPerUnit * $saleItem->quantity;

                    // Subtract item-level discount
                    $itemProfit -= $saleItem->discount_amount;

                    // Calculate proportional sale-level discount
                    $itemAmount = $saleItem->total;
                    $proportionalSaleDiscount = $totalSaleAmount > 0 ? ($itemAmount / $totalSaleAmount) * $saleDiscount : 0;

                    // Subtract proportional sale discount
                    $itemProfit -= $proportionalSaleDiscount;

                    $saleProfit += $itemProfit;
                }

                return $saleProfit;
            });

        // Recent Loan Activities
        $this->recentLoanActivities = LoanPayment::whereHas('sale', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
        ->with(['sale.customer'])
        ->latest()
        ->take(5)
        ->get();

        // Duka Analytics (profit, sales count, stock values, loan sales)
        $this->dukaAnalytics = Duka::where('tenant_id', $tenantId)
            ->with(['sales.saleItems.product', 'sales.loanPayments', 'stocks.product'])
            ->get()
            ->map(function($duka) {
                // Calculate profit from sales
                $totalProfit = 0;
                $salesCount = $duka->sales->count();
                $loanSalesAmount = 0;
                $totalSalesAmount = 0;

                foreach ($duka->sales as $sale) {
                    // Calculate loan sales amount
                    if ($sale->is_loan) {
                        $loanSalesAmount += $sale->total_amount;
                    }

                    $totalSalesAmount += $sale->total_amount;

                    $saleProfit = 0;
                    $totalSaleAmount = $sale->saleItems->sum('total');
                    $saleDiscount = $sale->discount_amount ?? 0;

                    foreach ($sale->saleItems as $saleItem) {
                        $profitPerUnit = $saleItem->product->selling_price - $saleItem->product->base_price;
                        $itemProfit = $profitPerUnit * $saleItem->quantity;
                        $itemProfit -= $saleItem->discount_amount;

                        $itemAmount = $saleItem->total;
                        $proportionalSaleDiscount = $totalSaleAmount > 0 ? ($itemAmount / $totalSaleAmount) * $saleDiscount : 0;
                        $itemProfit -= $proportionalSaleDiscount;

                        $saleProfit += $itemProfit;
                    }

                    $totalProfit += $saleProfit;
                }

                // Calculate stock value based on buying price (base_price)
                $stockValue = $duka->stocks->sum(function($stock) {
                    return $stock->quantity * ($stock->product->base_price ?? 0);
                });

                // Calculate low stock products (less than or equal to 10 items)
                $lowStockCount = $duka->stocks->filter(function($stock) {
                    return $stock->quantity <= 10;
                })->count();

                return [
                    'id' => $duka->id,
                    'name' => $duka->name,
                    'location' => $duka->location,
                    'profit' => $totalProfit,
                    'sales_count' => $salesCount,
                    'total_sales_amount' => $totalSalesAmount,
                    'loan_sales' => $loanSalesAmount,
                    'stock_value' => $stockValue,
                    'products_count' => $duka->stocks->sum('quantity'),
                    'low_stock_count' => $lowStockCount
                ];
            });

        // Get all low stock products across all dukas for alerts
        $this->lowStockAlerts = \App\Models\Stock::whereHas('product.duka', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->with(['product.duka'])
            ->where('quantity', '<=', 10)
            ->orderBy('quantity', 'asc')
            ->take(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.tenant-home');
    }
}
