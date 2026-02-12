<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\TenantOfficer;
use App\Models\TenantAccount;
use App\Models\LoanPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OfficerLoanController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get officer's assigned dukas
        $assignedDukas = TenantOfficer::with('duka')
            ->where('officer_id', $user->id)
            ->where('status', true)
            ->get();

        if ($assignedDukas->isEmpty()) {
            return view('officer.loans.index', [
                'loans' => collect(),
                'currency' => 'TZS'
            ]);
        }

        // Get duka IDs assigned to this officer
        $dukaIds = $assignedDukas->pluck('duka_id');

        // Get all loan sales for these dukas
        $loans = Sale::with(['customer', 'duka', 'saleItems.product', 'loanPayments'])
            ->whereIn('duka_id', $dukaIds)
            ->where('is_loan', true)
            ->where('tenant_id', $assignedDukas->first()->tenant_id)
            ->whereHas('customer')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get currency for the tenant
        $currency = TenantAccount::where('tenant_id', $assignedDukas->first()->tenant_id)
            ->first()->currency ?? 'TZS';

        return view('officer.loans.index', compact('loans', 'currency'));
    }

    public function show($id)
    {
        $user = auth()->user();

        // Get officer's assigned dukas
        $assignedDukas = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('duka_id');

        if ($assignedDukas->isEmpty()) {
            abort(403, 'No dukas assigned to you.');
        }

        // Get the loan sale
        $loan = Sale::with(['customer', 'duka', 'saleItems.product', 'loanPayments'])
            ->where('id', $id)
            ->where('is_loan', true)
            ->whereIn('duka_id', $assignedDukas)
            ->whereHas('customer')
            ->firstOrFail();

        // Get currency for the tenant
        $currency = TenantAccount::where('tenant_id', $loan->tenant_id)
            ->first()->currency ?? 'TZS';

        // Calculate payment summary
        $totalPaid = $loan->loanPayments->sum('amount');
        $remainingBalance = $loan->total_amount - $totalPaid;

        return view('officer.loans.show', compact('loan', 'currency', 'totalPaid', 'remainingBalance'));
    }

   public function storePayment(Request $request, $loanId)
    {
        $user = Auth::user();
        $assignedDukas = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('duka_id');

        if ($assignedDukas->isEmpty()) {
            return redirect()->back()->with('error', 'Unauthorized: No dukas assigned.');
        }
        $loan = Sale::with(['loanPayments', 'customer'])
            ->where('id', $loanId)
            ->where('is_loan', true)
            ->whereIn('duka_id', $assignedDukas)
            ->firstOrFail();
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:255',
        ]);

        $totalPaid = $loan->loanPayments->sum('amount');
        $remainingBalance = (float)$loan->total_amount - (float)$totalPaid;

        if ($request->amount > ($remainingBalance + 0.01)) {
            return redirect()->back()->with('error', "Payment exceeds balance. Remaining: " . number_format($remainingBalance));
        }

        // 4. Atomic Execution
        try {
            DB::beginTransaction();

            LoanPayment::create([
                'sale_id' => $loan->id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
                'user_id' => $user->id,
            ]);

            DB::commit();
            return redirect()->route('officer.loans.show', $loan->id)
                ->with('success', 'Payment of ' . number_format($request->amount) . ' successfully synchronized.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'System Error: ' . $e->getMessage());
        }
    }

    public function agingAnalysis()
    {
        $user = auth()->user();

        // Get officer's assigned dukas
        $assignedDukas = TenantOfficer::with('duka')
            ->where('officer_id', $user->id)
            ->where('status', true)
            ->get();

        if ($assignedDukas->isEmpty()) {
            return view('officer.loans.aging-analysis', [
                'loans' => collect(),
                'summary' => [
                    'total_loans' => 0,
                    'total_outstanding' => 0,
                    'total_overdue' => 0,
                    'total_high_risk' => 0,
                    'count_overdue_customers' => 0,
                    'top_debtors' => collect(),
                    'aging_distribution' => collect(),
                ],
                'currency' => 'TZS'
            ]);
        }

        // Get duka IDs assigned to this officer
        $dukaIds = $assignedDukas->pluck('duka_id');

        $currentDate = Carbon::now();

        // Fetch loans for all assigned dukas
        $loans = Sale::whereIn('duka_id', $dukaIds)
            ->where('is_loan', true)
            ->where('tenant_id', $assignedDukas->first()->tenant_id)
            ->whereHas('customer') // Ensure customer exists
            ->with(['customer', 'duka', 'loanPayments', 'saleItems.product'])
            ->get()
            ->filter(function ($sale) {
                return $sale->remaining_balance > 0;
            })
            ->map(function ($sale) use ($currentDate) {
                $daysOverdue = $sale->due_date ? $currentDate->diffInDays($sale->due_date, false) : 0;
                if ($daysOverdue < 0) $daysOverdue = 0; // If not due yet

                $agingCategory = $this->getAgingCategory($daysOverdue);
                $recommendedAction = $this->getRecommendedAction($agingCategory);

                return [
                    'customer_name' => $sale->customer->name ?? 'N/A',
                    'customer_phone' => $sale->customer->phone ?? 'N/A',
                    'customer_email' => $sale->customer->email ?? 'N/A',
                    'customer_address' => $sale->customer->address ?? 'N/A',
                    'duka_name' => $sale->duka->name,
                    'loan_id' => $sale->id,
                    'original_amount' => $sale->total_amount,
                    'amount_paid' => $sale->total_payments,
                    'outstanding_balance' => $sale->remaining_balance,
                    'due_date' => $sale->due_date ? $sale->due_date->format('Y-m-d') : 'N/A',
                    'days_overdue' => $daysOverdue,
                    'aging_category' => $agingCategory,
                    'recommended_action' => $recommendedAction,
                    'loan_date' => $sale->created_at->format('Y-m-d'),
                    'products' => $sale->saleItems->map(function ($item) {
                        return [
                            'name' => $item->product->name,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total' => $item->total,
                        ];
                    }),
                    'payments' => $sale->loanPayments->map(function ($payment) {
                        return [
                            'date' => $payment->payment_date->format('Y-m-d'),
                            'amount' => $payment->amount,
                            'notes' => $payment->notes,
                        ];
                    }),
                ];
            });

        // Summary Metrics for all assigned dukas
        $totalLoans = $loans->count();
        $totalOutstanding = $loans->sum('outstanding_balance');
        $totalOverdue = $loans->where('days_overdue', '>', 0)->sum('outstanding_balance');
        $totalHighRisk = $loans->where('aging_category', 'High Risk / Bad Debt')->sum('outstanding_balance');
        $countOverdueCustomers = $loans->where('days_overdue', '>', 0)->unique('customer_name')->count();

        // Top 5 highest debtors
        $topDebtors = $loans->sortByDesc('outstanding_balance')->take(5)->values();

        // Loan distribution by aging category
        $agingDistribution = $loans->groupBy('aging_category')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_balance' => $group->sum('outstanding_balance'),
            ];
        });

        // Get currency for the tenant
        $currency = TenantAccount::where('tenant_id', $assignedDukas->first()->tenant_id)
            ->first()->currency ?? 'TZS';

        $data = [
            'loans' => $loans,
            'assignedDukas' => $assignedDukas,
            'summary' => [
                'total_loans' => $totalLoans,
                'total_outstanding' => $totalOutstanding,
                'total_overdue' => $totalOverdue,
                'total_high_risk' => $totalHighRisk,
                'count_overdue_customers' => $countOverdueCustomers,
                'top_debtors' => $topDebtors,
                'aging_distribution' => $agingDistribution,
            ],
            'currency' => $currency,
        ];

        return view('officer.loans.aging-analysis', $data);
    }

    private function getAgingCategory($daysOverdue)
    {
        if ($daysOverdue <= 30) return 'Current';
        if ($daysOverdue <= 60) return 'Overdue 1';
        if ($daysOverdue <= 90) return 'Overdue 2';
        return 'High Risk / Bad Debt';
    }

    private function getRecommendedAction($category)
    {
        switch ($category) {
            case 'Current': return 'No action needed';
            case 'Overdue 1': return 'Reminder SMS';
            case 'Overdue 2': return 'Follow-up Call';
            case 'High Risk / Bad Debt': return 'High Risk Flag';
            default: return 'Review';
        }
    }
}
