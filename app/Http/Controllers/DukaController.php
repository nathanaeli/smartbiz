<?php

namespace App\Http\Controllers;

use App\Models\Duka;
use App\Models\ProductCategory;
use App\Models\Plan;
use App\Models\DukaSubscription;
use App\Models\Sale;
use Carbon\Carbon;
use App\Mail\LoanReminder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class DukaController extends Controller
{

    public function createWithPlan()
    {
        $plans = Plan::where('is_active', true)->get();
        return view('duka.create-plan', compact('plans'));
    }


    public function storecreateduka(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        // 1. Log the attempt
        Log::info("Duka creation attempt started", [
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'plan_name' => optional($tenant->activeSubscription)->plan_name
        ]);

        if (!$tenant->canAddDuka()) {
            Log::warning("Duka creation blocked: Plan limit reached", [
                'tenant_id' => $tenant->id,
                'current_duka_count' => $tenant->dukas()->count()
            ]);

            return redirect()->route('tenant.dukas.index')->with('error', 'Plan limit reached.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'business_type' => 'required|in:product,service,both',
        ]);

        try {
            $duka = $tenant->dukas()->create([
                'name' => $request->name,
                'location' => $request->location,
                'manager_name' => $request->manager_name,
                'business_type' => $request->business_type,
                'status' => 'active',
            ]);

            // 2. Log success info
            Log::info("Duka successfully created", [
                'duka_id' => $duka->id,
                'duka_name' => $duka->name,
                'created_by' => $user->id
            ]);

            return redirect()->route('tenant.dukas.index')->with('success', 'Duka created successfully!');
        } catch (\Exception $e) {
            // 3. Log critical errors
            Log::error("Critical error during Duka creation: " . $e->getMessage(), [
                'tenant_id' => $tenant->id,
                'input_data' => $request->all()
            ]);

            return redirect()->back()->with('error', 'An internal error occurred.');
        }
    }


    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'location'     => 'required|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'latitude'     => 'nullable|numeric',
            'longitude'    => 'nullable|numeric',
        ]);

        $duka = Duka::create([
            'tenant_id'    => auth()->id(),
            'name'         => $request->name,
            'location'     => $request->location,
            'manager_name' => $request->manager_name ?? auth()->user()->name,
            'latitude'     => $request->latitude,
            'longitude'    => $request->longitude,
            'status'       => 'active',
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Duka registered successfully!');
    }

    public function showduka($id_or_encrypted)
    {
        // Try to decrypt first (for encrypted IDs), if it fails, treat as regular ID
        try {
            $id = Crypt::decrypt($id_or_encrypted);
        } catch (\Exception $e) {
            // If decryption fails, treat as regular ID
            $id = $id_or_encrypted;
        }

        $duka = Duka::with([
            'products',
            'stocks.product',
            'tenant.customers',
            'dukaSubscriptions.plan',
            'activeSubscription.plan',
        ])->findOrFail($id);

        // Calculate products with stock > 0 for this duka
        $duka->products_with_stock_count = $duka->stocks->where('quantity', '>', 0)->count();

        $user = auth()->user();

        // Load categories registered by the tenant (authenticated user)
        $categories = ProductCategory::where('tenant_id', $user->id)
            ->orderBy('name')
            ->get();



        return view('duka.show', compact('duka', 'categories'));
    }

    public function edit($id)
    {
        $duka = Duka::findOrFail($id);


        return view('duka.edit', compact('duka'));
    }

    public function update(Request $request, $id)
    {
        $duka = Duka::findOrFail($id);



        $request->validate([
            'name'         => 'required|string|max:255',
            'location'     => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'latitude'     => 'nullable|numeric',
            'longitude'    => 'nullable|numeric',
            'business_type' => 'required|in:product,service,both',
        ]);

        $duka->update($request->only(['name', 'location', 'manager_name', 'latitude', 'longitude', 'business_type']));

        return redirect()->route('duka.show', $duka->id)->with('success', 'Duka updated successfully!');
    }

    public function allDukas()
    {
        $user     = auth()->user();
        $tenantId = $user->tenant->id;
        $dukas    = Duka::where('tenant_id', $tenantId)
            ->with([
                'tenant',
                'dukaSubscriptions.plan',
                'activeSubscription.plan',
                'stocks.product',
                'stockTransferItemsFrom',
                'stockTransferItemsTo',
                'products',
                'productCategories',
            ])
            ->latest()
            ->get();

        // Calculate products with stock > 0 for each duka
        $dukas->each(function ($duka) {
            $duka->products_with_stock_count = $duka->stocks->where('quantity', '>', 0)->count();
        });

        // Auto-navigate if only one duka exists, but ONLY if no feedback messages need to be shown
        if ($dukas->count() == 1 && !session()->has('error') && !session()->has('success')) {
            return redirect()->route('duka.show', ['id' => Crypt::encrypt($dukas->first()->id)]);
        }

        return view('duka.all', compact('dukas'));
    }

    public function changePlan($encrypted_id)
    {
        try {
            $dukaId = Crypt::decrypt($encrypted_id);
        } catch (\Exception $e) {
            abort(404, 'Invalid Duka reference.');
        }

        $duka = Duka::with(['activeSubscription.plan'])->findOrFail($dukaId);



        $plans = Plan::where('is_active', true)->get();

        return view('duka.change-plan', compact('duka', 'plans'));
    }

    public function updatePlan(Request $request, $encrypted_id)
    {
        try {
            $dukaId = Crypt::decrypt($encrypted_id);
        } catch (\Exception $e) {
            abort(404, 'Invalid Duka reference.');
        }

        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'duration' => 'required|in:1,12,36',
        ]);

        $duka = Duka::findOrFail($dukaId);
        $user = auth()->user();
        $tenant = $user->tenant;


        $plan = Plan::findOrFail($request->plan_id);
        $months = (int) $request->duration;
        $discount = $months === 12 ? 0.10 : ($months === 36 ? 0.20 : 0);
        $amount = $plan->price * $months * (1 - $discount);

        DB::beginTransaction();

        try {
            // Create new subscription
            $subscription = DukaSubscription::create([
                'tenant_id' => $tenant->id,
                'duka_id' => $duka->id,
                'plan_id' => $plan->id,
                'amount' => $amount,
                'plan_name' => $plan->name,
                'start_date' => now(),
                'end_date' => now()->addMonths($months),
                'status' => 'pending',
            ]);

            DB::commit();

            notify()->success('Plan change initiated! Proceed to payment.');

            return redirect()->route('payment.checkout', [
                'tenant' => Crypt::encrypt($tenant->id),
                'subscription' => Crypt::encrypt($subscription->id),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            notify()->error('Failed to change plan. Please try again.');
            return back();
        }
    }

    public function loanAgingAnalysis($duka_id)
    {
        $duka = Duka::findOrFail($duka_id);

        $currentDate = Carbon::now();

        // Fetch loans for this duka
        $loans = Sale::where('duka_id', $duka_id)
            ->where('is_loan', true)
            ->whereHas('customer') // Ensure customer exists
            ->with(['customer', 'loanPayments', 'saleItems.product'])
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

        // Duka-Level Summary Metrics
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

        $data = [
            'duka' => $duka,
            'loans' => $loans,
            'summary' => [
                'total_loans' => $totalLoans,
                'total_outstanding' => $totalOutstanding,
                'total_overdue' => $totalOverdue,
                'total_high_risk' => $totalHighRisk,
                'count_overdue_customers' => $countOverdueCustomers,
                'top_debtors' => $topDebtors,
                'aging_distribution' => $agingDistribution,
            ],
        ];

        return view('duka.aging-analysis', $data);
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
            case 'Current':
                return 'No action needed';
            case 'Overdue 1':
                return 'Reminder SMS';
            case 'Overdue 2':
                return 'Follow-up Call';
            case 'High Risk / Bad Debt':
                return 'High Risk Flag';
            default:
                return 'Review';
        }
    }

    public function sendLoanReminder(Request $request, $duka_id)
    {
        $request->validate([
            'loan_id' => 'required|exists:sales,id',
            'message_type' => 'required|in:reminder,overdue_warning,final_notice,payment_confirmation',
        ]);

        $duka = Duka::findOrFail($duka_id);


        $loan = Sale::where('id', $request->loan_id)
            ->where('duka_id', $duka_id)
            ->where('is_loan', true)
            ->with(['customer', 'loanPayments', 'saleItems.product', 'duka'])
            ->firstOrFail();

        // Prepare loan data for email
        $loanData = $this->prepareLoanDataForEmail($loan);

        // Send email
        try {
            Mail::to($loan->customer->email)->queue(new LoanReminder($loanData, $request->message_type));

            notify()->success('Loan reminder email sent successfully!');
            return back();
        } catch (\Exception $e) {
            notify()->error('Failed to send email. Please try again.');
            return back();
        }
    }

    public function sendBulkLoanReminders(Request $request, $duka_id)
    {
        $request->validate([
            'aging_category' => 'required|in:Current,Overdue 1,Overdue 2,High Risk / Bad Debt',
            'message_type' => 'required|in:reminder,overdue_warning,final_notice',
        ]);

        $duka = Duka::findOrFail($duka_id);



        // Get loans for the specified aging category
        $currentDate = Carbon::now();
        $loans = Sale::where('duka_id', $duka_id)
            ->where('is_loan', true)
            ->whereHas('customer', function ($query) {
                $query->whereNotNull('email');
            })
            ->with(['customer', 'loanPayments', 'saleItems.product', 'duka'])
            ->get()
            ->filter(function ($sale) use ($currentDate, $request) {
                if ($sale->remaining_balance <= 0) return false;

                $daysOverdue = $sale->due_date ? $currentDate->diffInDays($sale->due_date, false) : 0;
                if ($daysOverdue < 0) $daysOverdue = 0;

                $agingCategory = $this->getAgingCategory($daysOverdue);
                return $agingCategory === $request->aging_category;
            });

        if ($loans->isEmpty()) {
            notify()->warning('No customers found in the selected aging category with valid email addresses.');
            return back();
        }

        $sentCount = 0;
        foreach ($loans as $loan) {
            try {
                $loanData = $this->prepareLoanDataForEmail($loan);
                Mail::to($loan->customer->email)->queue(new LoanReminder($loanData, $request->message_type));
                $sentCount++;
            } catch (\Exception $e) {
                // Continue with other emails even if one fails
                continue;
            }
        }

        notify()->success("Bulk email reminders sent successfully! {$sentCount} emails queued.");
        return back();
    }

    private function prepareLoanDataForEmail($loan)
    {
        $currentDate = Carbon::now();
        $daysOverdue = $loan->due_date ? $currentDate->diffInDays($loan->due_date, false) : 0;
        if ($daysOverdue < 0) $daysOverdue = 0;

        return [
            'customer_name' => $loan->customer->name ?? 'Valued Customer',
            'customer_phone' => $loan->customer->phone ?? 'N/A',
            'customer_email' => $loan->customer->email ?? 'N/A',
            'customer_address' => $loan->customer->address ?? 'N/A',
            'duka_name' => $loan->duka->name,
            'loan_id' => $loan->id,
            'original_amount' => $loan->total_amount,
            'amount_paid' => $loan->total_payments,
            'outstanding_balance' => $loan->remaining_balance,
            'due_date' => $loan->due_date ? $loan->due_date->format('M d, Y') : 'N/A',
            'days_overdue' => $daysOverdue,
            'aging_category' => $this->getAgingCategory($daysOverdue),
            'loan_date' => $loan->created_at->format('M d, Y'),
            'products' => $loan->saleItems->map(function ($item) {
                return [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                ];
            }),
            'payments' => $loan->loanPayments->sortByDesc('payment_date')->take(5)->map(function ($payment) {
                return [
                    'date' => $payment->payment_date->format('M d, Y'),
                    'amount' => $payment->amount,
                    'notes' => $payment->notes,
                ];
            }),
        ];
    }

    public function inventory($id)
    {
        $duka = Duka::findOrFail($id);

        return view('duka.inventory', compact('duka'));
    }

    public function customers($id)
    {
        $duka = Duka::findOrFail($id);

        return view('duka.customers', compact('duka'));
    }

    public function exportInventoryExcel($id)
    {
        $duka = Duka::findOrFail($id);


        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\InventoryExport($duka->id),
            'inventory_' . Str::slug($duka->name) . '_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportInventoryPdf($id)
    {
        $duka = Duka::findOrFail($id);


        $products = \App\Models\Product::where('duka_id', $duka->id)
            ->with(['category', 'stocks' => function ($query) use ($duka) {
                $query->where('duka_id', $duka->id);
            }])
            ->orderBy('name')
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.inventory_pdf', compact('duka', 'products'));

        return $pdf->download('inventory_' . Str::slug($duka->name) . '_' . now()->format('Y-m-d') . '.pdf');
    }
}
