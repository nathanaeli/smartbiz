<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoanPayment;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoanPaymentController extends Controller
{
    /**
     * Display a listing of loan payments
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = LoanPayment::with(['sale.customer', 'user']);

            // Filter by sale_id if provided
            if ($request->has('sale_id')) {
                $query->where('sale_id', $request->sale_id);
            }

            // Filter by date range
            if ($request->has('from_date')) {
                $query->where('payment_date', '>=', $request->from_date);
            }
            if ($request->has('to_date')) {
                $query->where('payment_date', '<=', $request->to_date);
            }

            // Filter by user_id (officer who made the payment)
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Filter by amount range
            if ($request->has('min_amount')) {
                $query->where('amount', '>=', $request->min_amount);
            }
            if ($request->has('max_amount')) {
                $query->where('amount', '<=', $request->max_amount);
            }

            // Sort by payment_date (newest first by default)
            $sortBy = $request->get('sort_by', 'payment_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $payments = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Loan payments retrieved successfully',
                'data' => $payments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving loan payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created loan payment
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'sale_id' => 'required|exists:sales,id',
                'amount' => 'required|numeric|min:0.01',
                'payment_date' => 'required|date',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify the sale exists and belongs to the user's tenant
            $sale = Sale::findOrFail($request->sale_id);
            $user = Auth::user();

            // Check if user has access to this sale (belongs to same tenant)
            if ($sale->tenant_id !== $user->tenant->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this sale'
                ], 403);
            }

            // Create the loan payment
            $loanPayment = LoanPayment::create([
                'sale_id' => $request->sale_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
                'user_id' => $user->id,
            ]);

            // Load relationships for response
            $loanPayment->load(['sale.customer', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Loan payment created successfully',
                'data' => $loanPayment
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating loan payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified loan payment
     */
    public function show($id): JsonResponse
    {
        try {
            $loanPayment = LoanPayment::with(['sale.customer', 'user'])->findOrFail($id);

            // Check if user has access to this loan payment
            $user = Auth::user();
            if ($loanPayment->sale->tenant_id !== $user->tenant->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this loan payment'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Loan payment retrieved successfully',
                'data' => $loanPayment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Loan payment not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified loan payment
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $loanPayment = LoanPayment::findOrFail($id);

            // Check if user has access to this loan payment
            $user = Auth::user();
            if ($loanPayment->sale->tenant_id !== $user->tenant->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this loan payment'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'amount' => 'sometimes|required|numeric|min:0.01',
                'payment_date' => 'sometimes|required|date',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update the loan payment
            $loanPayment->update($request->only(['amount', 'payment_date', 'notes']));

            // Load relationships for response
            $loanPayment->load(['sale.customer', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Loan payment updated successfully',
                'data' => $loanPayment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating loan payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified loan payment
     */
    public function destroy($id): JsonResponse
    {
        try {
            $loanPayment = LoanPayment::findOrFail($id);

            // Check if user has access to this loan payment
            $user = Auth::user();
            if ($loanPayment->sale->tenant_id !== $user->tenant->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this loan payment'
                ], 403);
            }

            $loanPayment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Loan payment deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting loan payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get loan payment statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = LoanPayment::whereHas('sale', function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant->id);
            });

            // Apply date range filter
            if ($request->has('from_date')) {
                $query->where('payment_date', '>=', $request->from_date);
            }
            if ($request->has('to_date')) {
                $query->where('payment_date', '<=', $request->to_date);
            }

            $statistics = [
                'total_payments' => $query->count(),
                'total_amount' => $query->sum('amount'),
                'average_payment' => $query->avg('amount'),
                'payments_this_month' => $query->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year)
                    ->count(),
                'amount_this_month' => $query->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year)
                    ->sum('amount'),
                'daily_average' => $query->selectRaw('AVG(amount) as avg_amount, DATE(payment_date) as date')
                    ->groupBy('date')
                    ->avg('avg_amount'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Loan payment statistics retrieved successfully',
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving loan payment statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payments by sale
     */
    public function bySale($saleId): JsonResponse
    {
        try {
            $sale = Sale::findOrFail($saleId);

            // Check if user has access to this sale
            $user = Auth::user();
            if ($sale->tenant_id !== $user->tenant->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this sale'
                ], 403);
            }

            $payments = LoanPayment::with(['user'])
                ->where('sale_id', $saleId)
                ->orderBy('payment_date', 'desc')
                ->get();

            // Calculate remaining balance
            $totalPaid = $payments->sum('amount');
            $saleAmount = $sale->total_amount ?? 0;
            $remainingBalance = max(0, $saleAmount - $totalPaid);

            return response()->json([
                'success' => true,
                'message' => 'Sale payments retrieved successfully',
                'data' => [
                    'payments' => $payments,
                    'sale_info' => [
                        'id' => $sale->id,
                        'customer' => $sale->customer->name,
                        'total_amount' => $saleAmount,
                        'total_paid' => $totalPaid,
                        'remaining_balance' => $remainingBalance,
                        'payment_status' => $remainingBalance <= 0 ? 'paid' : 'partial'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving sale payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
