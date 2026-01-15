<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Duka;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
   public function index()
    {
        $user = Auth::user();
        $allduka = Duka::whereHas('tenant', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with([
                'products',
                'stocks.product',
                'dukaSubscriptions',
                'tenant.customers'
            ])
            ->get();

        return view('tenant.dashboard', compact('user', 'allduka'));
    }
}
