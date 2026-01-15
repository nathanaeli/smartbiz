<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Duka;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class TenantCashFlowController extends Controller
{
    //
public function index(Request $request)
    {
        $user = auth()->user();
        $tenantId = $user->tenant->id;
        $dukas = Duka::where('tenant_id', $tenantId)->get();
        $selectedDukaId = $request->duka_id;
        if ($dukas->count() === 1) {
            $selectedDukaId = $dukas->first()->id;
        } elseif (!$selectedDukaId) {
            // Multiple dukas exist but none selected, show selection view
            return view('tenant.cashflow.select_duka', compact('dukas'));
        }
        $start = $request->start_date ? Carbon::parse($request->start_date) : now()->startOfMonth();
        $end = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfMonth();
        $query = Transaction::where('duka_id', $selectedDukaId)
            ->whereBetween('transaction_date', [$start, $end])
            ->where('status', 'active')
            ->orderBy('transaction_date', 'desc');

        $transactions = $query->get();

        // 5. Totals
        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $netCashFlow = $totalIncome - $totalExpense;

        return view('tenant.cashflow.index', compact(
            'transactions',
            'totalIncome',
            'totalExpense',
            'netCashFlow',
            'start',
            'end',
            'selectedDukaId',
            'dukas' // Pass all dukas to allow switching on the main page
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'duka_id' => 'required|exists:dukas,id',
            'type' => 'required|in:income,expense',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        Transaction::create([
            'duka_id' => $request->duka_id,
            'user_id' => auth()->id(),
            'type' => $request->type,
            'category' => $request->category,
            'amount' => $request->amount,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date,
            'status' => 'active',
            'payment_method' => 'cash',
        ]);

        return back()->with('success', 'Record saved successfully!');
    }

  public function profitAndLoss(Request $request)
{
    $user = auth()->user();
    $tenantId = $user->tenant->id;

    $dukas = Duka::where('tenant_id', $tenantId)->get();
    if ($dukas->count() === 0) {
        return redirect()->back()->with('error', 'No Duka found.');
    }

    $selectedDukaId = $request->duka_id;
    if ($dukas->count() === 1) {
        $selectedDukaId = $dukas->first()->id;
    } elseif (!$selectedDukaId) {
        return view('tenant.reports.select_duka_pl', compact('dukas'));
    }

    $start = $request->start_date ? Carbon::parse($request->start_date) : now()->startOfMonth();
    $end = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfMonth();

    // 1. Calculate Revenue (Sales)
    $totalRevenue = Sale::where('duka_id', $selectedDukaId)
        ->whereBetween('created_at', [$start, $end])
        ->sum('total_amount');

    // 2. Calculate COGS (FIFO Cost of items sold)
    $cogs = StockMovement::whereHas('stock', function($q) use ($selectedDukaId) {
            $q->where('duka_id', $selectedDukaId);
        })
        ->where('type', 'out')
        ->where('reason', 'sale')
        ->whereBetween('created_at', [$start, $end])
        ->sum(DB::raw('quantity_change * unit_cost'));

    $grossProfit = $totalRevenue - $cogs;

    // 3. INVENTORY VALUATION (Check Stock Info)
    // We use Product buying price (base_price) multiplied by current stock
    $inventory = Product::where('duka_id', $selectedDukaId)
        ->withSum(['stocks as total_qty'], 'quantity')
        ->get();

    $totalStockBuyingValue = $inventory->sum(function($product) {
        return $product->total_qty * $product->base_price;
    });

    $totalStockSellingValue = $inventory->sum(function($product) {
        return $product->total_qty * $product->selling_price;
    });

    // 4. Operating Expenses
    $expenses = Transaction::where('duka_id', $selectedDukaId)
        ->where('type', 'expense')
        ->where('category', '!=', 'stock_purchase')
        ->whereBetween('transaction_date', [$start, $end])
        ->select('category', DB::raw('SUM(amount) as total'))
        ->groupBy('category')
        ->get();

    $totalExpenses = $expenses->sum('total');
    $netProfit = $grossProfit - $totalExpenses;

    return view('tenant.reports.p_and_l', compact(
        'totalRevenue', 'cogs', 'grossProfit', 'expenses',
        'totalExpenses', 'netProfit', 'totalStockBuyingValue',
        'totalStockSellingValue', 'start', 'end', 'selectedDukaId', 'dukas'
    ));
}

public function consolidatedCashFlow(Request $request)
{
    $user = auth()->user();
    $tenantId = $user->tenant->id;

    // Filter by Date (Default to current month)
    $start = $request->start_date ? Carbon::parse($request->start_date) : now()->startOfMonth();
    $end = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfMonth();

    // 1. Get all Dukas for this tenant
    $dukas = Duka::where('tenant_id', $tenantId)->get();
    $dukaIds = $dukas->pluck('id');

    // 2. Query Transactions for ALL Dukas
    $transactions = Transaction::whereIn('duka_id', $dukaIds)
        ->whereBetween('transaction_date', [$start, $end])
        ->where('status', 'active')
        ->with('duka') // Load duka info to show which shop the money came from
        ->orderBy('transaction_date', 'desc')
        ->get();

    // 3. Aggregate Data for the Summary Cards
    $totalIncome = $transactions->where('type', 'income')->sum('amount');
    $totalExpense = $transactions->where('type', 'expense')->sum('amount');
    $netCashFlow = $totalIncome - $totalExpense;

    // 4. Breakdown by Duka (For the comparison table)
    $dukaSummaries = $transactions->groupBy('duka_id')->map(function ($items) {
        return [
            'name' => $items->first()->duka->name,
            'income' => $items->where('type', 'income')->sum('amount'),
            'expense' => $items->where('type', 'expense')->sum('amount'),
            'net' => $items->where('type', 'income')->sum('amount') - $items->where('type', 'expense')->sum('amount'),
        ];
    });

    return view('tenant.cashflow.consolidated', compact(
        'totalIncome', 'totalExpense', 'netCashFlow', 'transactions',
        'dukaSummaries', 'start', 'end'
    ));
}

public function consolidatedProfitLoss(Request $request)
{
    $user = auth()->user();
    $tenantId = $user->tenant->id;

    // Default dates: Start of month to today
    $start = $request->start_date ? Carbon::parse($request->start_date) : now()->startOfMonth();
    $end = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfMonth();

    // 1. Get all Dukas belonging to this tenant
    $dukas = Duka::where('tenant_id', $tenantId)->get();
    $dukaIds = $dukas->pluck('id');

    // 2. Aggregate Revenue (All Sales)
    $totalRevenue = Sale::whereIn('duka_id', $dukaIds)
        ->whereBetween('created_at', [$start, $end])
        ->sum('total_amount');

    // 3. Aggregate COGS (All FIFO Movements Out)
    $cogs = StockMovement::whereHas('stock', function($q) use ($dukaIds) {
            $q->whereIn('duka_id', $dukaIds);
        })
        ->where('type', 'out')
        ->where('reason', 'sale')
        ->whereBetween('created_at', [$start, $end])
        ->sum(DB::raw('quantity_change * unit_cost'));

    $grossProfit = $totalRevenue - $cogs;

    // 4. Aggregate Operating Expenses
    $expenses = Transaction::whereIn('duka_id', $dukaIds)
        ->where('type', 'expense')
        ->where('category', '!=', 'stock_purchase')
        ->whereBetween('transaction_date', [$start, $end])
        ->select('category', DB::raw('SUM(amount) as total'))
        ->groupBy('category')
        ->get();

    $totalExpenses = $expenses->sum('total');
    $netProfit = $grossProfit - $totalExpenses;

    // 5. Inventory Valuation (Grand Total Buying Price)
    $totalStockBuyingValue = Product::whereIn('duka_id', $dukaIds)
        ->withSum(['stocks as total_qty'], 'quantity')
        ->get()
        ->sum(fn($p) => $p->total_qty * $p->base_price);

    return view('tenant.reports.consolidated_pl', compact(
        'totalRevenue', 'cogs', 'grossProfit', 'expenses',
        'totalExpenses', 'netProfit', 'totalStockBuyingValue',
        'start', 'end', 'dukas'
    ));
}
}
