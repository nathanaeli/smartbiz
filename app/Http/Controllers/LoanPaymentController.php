<?php

namespace App\Http\Controllers;

use App\Models\LoanPayment;
use App\Models\Sale;
use Illuminate\Http\Request;

class LoanPaymentController extends Controller
{
    public function store(Request $request, $saleId)
    {
        $sale = Sale::findOrFail($saleId);
        $user = auth()->user();
        if ($sale->tenant_id != $user->tenant->id) {
            abort(403, 'Unauthorized access.');
        }

        if (!$sale->is_loan) {
            abort(403, 'This sale is not a loan.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        LoanPayment::create([
            'sale_id' => $sale->id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'notes' => $request->notes,
            'user_id' => $user->id,
        ]);

        return redirect()->route('sales.show', $sale->id)->with('success', 'Payment recorded successfully.');
    }
}
