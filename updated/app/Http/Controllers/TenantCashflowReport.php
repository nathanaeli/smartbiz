<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Duka;
use App\Models\CashFlow;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\TenantAccount;
use App\Models\CashflowCategory;


class TenantCashflowReport extends Controller
{
    //

    public function tenantcashflowindex()
    {
     $tenantid = Auth::User()->tenant->id;
     $dukas = Duka::where('tenant_id', $tenantid)->get();
     return view('tenant.cashflow.select-duka', compact('dukas'));

    }

    public function show($id)
    {
        $duka = Duka::findOrFail($id);
        $tenantAccount = TenantAccount::where('tenant_id', $duka->tenant_id)->first();
        $currency = $tenantAccount ? $tenantAccount->currency : 'TSH';
        $tenantId = Auth::User()->tenant->id;

        $startDate = request('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = request('end_date', now()->format('Y-m-d'));
        $type = request('type', '');

        // Get sales transactions
        $salesQuery = Sale::where('duka_id', $id)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);




        $cashflowcategorytype = CashflowCategory::where('tenant_id', $tenantId)
            ->where('duka_id', $id)
            ->where('type', $type)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();


        if ($type === 'income') {
            $salesQuery->where('total_amount', '>', 0);
        } elseif ($type === 'expense') {
            // Sales are income, so filter out
            $salesQuery->whereRaw('1=0');
        }

        $sales = $salesQuery->with('customer', 'saleItems.product')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get cashflow transactions
        $cashflowQuery = CashFlow::where('duka_id', $id)
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($type === 'income') {
            $cashflowQuery->where('type', 'income');
        } elseif ($type === 'expense') {
            $cashflowQuery->where('type', 'expense');
        }

        // Get regular cashflows (non-purchase transactions)
        $regularCashflowsQuery = clone $cashflowQuery;
        $regularCashflowsQuery->where(function($query) {
            $query->where('type', 'income')
                  ->orWhere(function($q) {
                      $q->where('type', 'expense')
                        ->where('category', '!=', 'Purchase of Goods');
                  });
        });

        // Get purchase of goods transactions grouped by date
        $purchaseTransactionsQuery = clone $cashflowQuery;
        $purchaseTransactionsQuery->where('type', 'expense')
                                ->where('category', 'Purchase of Goods')
                                ->selectRaw('DATE(transaction_date) as transaction_date_group, SUM(amount) as total_amount, COUNT(*) as transaction_count')
                                ->groupBy('transaction_date_group')
                                ->orderBy('transaction_date_group', 'desc');

        $regularCashflows = $regularCashflowsQuery->orderBy('transaction_date', 'desc')->paginate(10);
        $purchaseTransactions = $purchaseTransactionsQuery->get();

        // Calculate totals
        $totalIncomeFromSales = $sales->sum('total_amount');

        // Calculate expense from cost of goods sold (COGS) - sum of base prices for sold items
        $totalExpenseFromCost = $sales->sum(function($sale) {
            return $sale->saleItems->sum(function($item) {
                return $item->quantity * $item->product->base_price;
            });
        });

        // Calculate manual income (excluding sales revenue which is already counted)
        $totalManualIncome = CashFlow::where('duka_id', $id)
            ->where('type', 'income')
            ->where('category', '!=', 'Sales Revenue')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        // Calculate manual expenses (excluding purchase of goods which represents COGS)
        $totalManualExpense = CashFlow::where('duka_id', $id)
            ->where('type', 'expense')
            ->where('category', '!=', 'Purchase of Goods')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        // Calculate purchase of goods expenses (these represent the actual product buying prices)
        $totalPurchaseOfGoods = CashFlow::where('duka_id', $id)
            ->where('type', 'expense')
            ->where('category', 'Purchase of Goods')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $totalIncomeAll = $totalIncomeFromSales + $totalManualIncome;
        $totalExpenseAll = $totalExpenseFromCost + $totalManualExpense + $totalPurchaseOfGoods;
        $netCashflow = $totalIncomeAll - $totalExpenseAll;

        // Product-wise stats
        $productStats = [];
        $products = Product::where('duka_id', $id)->get();
        foreach ($products as $product) {
            $soldItems = SaleItem::where('product_id', $product->id)
                ->whereHas('sale', function($q) use ($id, $startDate, $endDate) {
                    $q->where('duka_id', $id)
                      ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                })
                ->with('product')
                ->get();
            $quantitySold = $soldItems->sum('quantity');
            $income = $soldItems->sum(function($item) {
                return $item->quantity * $item->unit_price;
            });
            $expense = $soldItems->sum(function($item) {
                return $item->quantity * $item->product->base_price;
            });
            $profit = $income - $expense;

            if ($quantitySold > 0) {
                $productStats[$product->id] = [
                    'name' => $product->name,
                    'quantity_sold' => $quantitySold,
                    'income' => $income,
                    'expense' => $expense,
                    'profit' => $profit
                ];
            }
        }

        // Get categories for the modal dropdown
        $incomeCategories = CashflowCategory::where('tenant_id', $tenantId)
            ->where('duka_id', $id)
            ->where('type', 'income')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $expenseCategories = CashflowCategory::where('tenant_id', $tenantId)
            ->where('duka_id', $id)
            ->where('type', 'expense')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('tenant.cashflow.show', compact(
            'duka', 'currency', 'startDate', 'endDate', 'type',
            'sales', 'regularCashflows', 'purchaseTransactions', 'totalIncomeAll', 'totalExpenseAll',
            'netCashflow', 'productStats', 'incomeCategories', 'expenseCategories', 'totalExpenseFromCost', 'totalManualExpense', 'totalPurchaseOfGoods'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'duka_id' => 'required|exists:dukas,id',
            'type' => 'required|in:income,expense',
            'income_category_id' => 'nullable|exists:cashflow_categories,id',
            'expense_category_id' => 'nullable|exists:cashflow_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'reference_number' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $duka = Duka::findOrFail($request->duka_id);

        // Validate that the user has access to this duka
        if ($duka->tenant_id !== $user->tenant->id) {
            return redirect()->back()->with('error', 'Unauthorized access to this duka.');
        }

        // Determine category and validate
        $category = null;
        if ($request->type === 'income') {
            if (!$request->income_category_id) {
                return redirect()->back()->with('error', 'Please select an income category.');
            }
            $category = CashflowCategory::where('id', $request->income_category_id)
                ->where('tenant_id', $user->tenant->id)
                ->where('duka_id', $duka->id)
                ->where('type', 'income')
                ->where('is_active', true)
                ->first();

            if (!$category) {
                return redirect()->back()->with('error', 'Invalid income category selected.');
            }
        } else {
            if (!$request->expense_category_id) {
                return redirect()->back()->with('error', 'Please select an expense category.');
            }
            $category = CashflowCategory::where('id', $request->expense_category_id)
                ->where('tenant_id', $user->tenant->id)
                ->where('duka_id', $duka->id)
                ->where('type', 'expense')
                ->where('is_active', true)
                ->first();

            if (!$category) {
                return redirect()->back()->with('error', 'Invalid expense category selected.');
            }
        }

        // Create the cashflow transaction
        $cashflow = CashFlow::create([
            'tenant_id' => $user->tenant->id,
            'duka_id' => $duka->id,
            'user_id' => $user->id,
            'type' => $request->type,
            'category' => $category->name,
            'description' => $request->description,
            'amount' => $request->amount,
            'transaction_date' => $request->transaction_date,
            'reference_number' => $request->reference_number,
            'metadata' => [
                'category_id' => $category->id,
                'category_type' => $category->type,
            ],
        ]);

        // Log the cashflow transaction creation
        Log::info('Cashflow transaction created successfully', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'tenant_id' => $user->tenant->id,
            'tenant_name' => $user->tenant->name,
            'duka_id' => $duka->id,
            'duka_name' => $duka->name,
            'cashflow_id' => $cashflow->id,
            'transaction_type' => $request->type,
            'category_name' => $category->name,
            'category_id' => $category->id,
            'amount' => $request->amount,
            'transaction_date' => $request->transaction_date,
            'description' => $request->description,
            'reference_number' => $request->reference_number,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('tenant.cashflow.show', $duka->id)
            ->with('success', 'Cashflow transaction added successfully.');
    }

    public function destroy(CashFlow $cashflow)
    {
        $user = Auth::user();

        // Validate that the user has access to this cashflow transaction
        if ($cashflow->tenant_id !== $user->tenant->id) {
            return redirect()->back()->with('error', 'Unauthorized access to this transaction.');
        }

        // Prevent deletion of "Purchase of Goods" expense transactions
        if ($cashflow->type === 'expense' && $cashflow->category === 'Purchase of Goods') {
            return redirect()->back()->with('error', 'Cannot delete "Purchase of Goods" transactions. These transactions are protected and represent product cost data.');
        }

        // Log the deletion attempt
        Log::info('Cashflow transaction deletion attempt', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'tenant_id' => $user->tenant->id,
            'tenant_name' => $user->tenant->name,
            'cashflow_id' => $cashflow->id,
            'transaction_type' => $cashflow->type,
            'category_name' => $cashflow->category,
            'amount' => $cashflow->amount,
            'transaction_date' => $cashflow->transaction_date,
            'description' => $cashflow->description,
        ]);

        // Delete the cashflow transaction
        $cashflow->delete();

        // Log the successful deletion
        Log::info('Cashflow transaction deleted successfully', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'tenant_id' => $user->tenant->id,
            'tenant_name' => $user->tenant->name,
            'cashflow_id' => $cashflow->id,
            'transaction_type' => $cashflow->type,
            'category_name' => $cashflow->category,
            'amount' => $cashflow->amount,
            'transaction_date' => $cashflow->transaction_date,
        ]);

        return redirect()->back()->with('success', 'Cashflow transaction deleted successfully.');
    }

    public function showPurchaseDetails($dukaId, $date)
    {
        $duka = Duka::findOrFail($dukaId);
        $user = Auth::user();

        // Validate that the user has access to this duka
        if ($duka->tenant_id !== $user->tenant->id) {
            return redirect()->back()->with('error', 'Unauthorized access to this duka.');
        }

        // Get all purchase of goods transactions for the specific date
        $purchaseTransactions = CashFlow::where('duka_id', $dukaId)
            ->where('type', 'expense')
            ->where('category', 'Purchase of Goods')
            ->whereDate('transaction_date', $date)
            ->orderBy('transaction_date', 'desc')
            ->get();

        if ($purchaseTransactions->isEmpty()) {
            return redirect()->route('tenant.cashflow.show', $dukaId)->with('error', 'No purchase transactions found for this date.');
        }

        $totalAmount = $purchaseTransactions->sum('amount');
        $transactionCount = $purchaseTransactions->count();

        // Get currency for the duka
        $tenantAccount = \App\Models\TenantAccount::where('tenant_id', $duka->tenant_id)->first();
        $currency = $tenantAccount ? $tenantAccount->currency : 'TSH';

        return view('tenant.cashflow.purchase-details', compact(
            'duka', 'purchaseTransactions', 'date', 'totalAmount', 'transactionCount', 'currency'
        ));
    }
}
