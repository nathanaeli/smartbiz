<?php

namespace App\Livewire;

use App\Models\Duka;
use App\Models\Sale;
use App\Models\Stock;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ReportAnalysis extends Component
{
    public $salesData = [];
    public $stockData = [];
    public $dukaNames = [];

    public function mount()
    {
        $user = auth()->user();
        $tenantId = $user->tenant->user_id ?? $user->id;

        // Get dukas for this tenant
        $dukas = Duka::where('tenant_id', $tenantId)->get();

        $this->dukaNames = $dukas->pluck('name')->toArray();

        // Sales data per duka
        $this->salesData = $dukas->map(function ($duka) {
            return Sale::where('duka_id', $duka->id)->sum('total_amount');
        })->toArray();

        // Stock data per duka
        $this->stockData = $dukas->map(function ($duka) {
            return Stock::where('duka_id', $duka->id)->sum('quantity');
        })->toArray();
    }

    public function render()
    {
        return view('livewire.report-analysis');
    }
}
