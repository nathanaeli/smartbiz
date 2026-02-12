<?php

namespace App\Http\Controllers;

use App\Models\Duka;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\TenantOfficer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportAnalysisController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Check if user is an officer
        if ($user->hasRole('officer')) {
            // For officers, get only assigned dukas
            $dukaIds = TenantOfficer::where('officer_id', $user->id)
                ->where('status', true)
                ->pluck('duka_id');
            $dukas = Duka::whereIn('id', $dukaIds)->get();
        } else {
            // For tenants, get all dukas
            $tenantId = $user->tenant->user_id ?? $user->id;
            $dukas = Duka::where('tenant_id', $tenantId)->get();
        }

        $dukaNames = $dukas->pluck('name')->toArray();

        // Sales data per duka
        $salesData = $dukas->map(function ($duka) {
            return Sale::where('duka_id', $duka->id)->sum('total_amount');
        })->toArray();

        // Stock data per duka
        $stockData = $dukas->map(function ($duka) {
            return Stock::where('duka_id', $duka->id)->sum('quantity');
        })->toArray();

        return view('report-analysis', compact('salesData', 'stockData', 'dukaNames'));
    }
}
