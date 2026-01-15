<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function edit($id)
    {
        $sale = Sale::with(['customer', 'duka', 'saleItems.product'])->findOrFail($id);
        $user = auth()->user();
        if ($sale->tenant_id != $user->tenant->id) {
            abort(403, 'Unauthorized access.');
        }
        return view('sales.edit', compact('sale'));
    }

    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        $user = auth()->user();
        if ($sale->tenant_id != $user->tenant->id) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string|max:255',
        ]);

        $sale->update([
            'discount_amount' => $request->discount_amount ?? 0,
            'discount_reason' => $request->discount_reason,
        ]);

        // Recalculate total_amount if discount changed
        $total = $sale->saleItems->sum('total') - $sale->discount_amount;
        $sale->update(['total_amount' => $total]);

        return redirect()->route('sales.show', $sale->id)->with('success', 'Sale updated successfully.');
    }


    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Sale::where('tenant_id', $user->tenant->id)->with(['customer', 'duka']);

        if ($request->has('duka_id') && $request->duka_id) {
            $query->where('duka_id', $request->duka_id);
        }

        $sales = $query->get();
        return view('sales.index', compact('sales'));
    }
}
