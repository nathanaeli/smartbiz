<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesSummaryExport implements FromArray
{
    private $tenantId;
    private $dukaId;

    public function __construct($tenantId, $dukaId = null)
    {
        $this->tenantId = $tenantId;
        $this->dukaId = $dukaId;
    }

    public function array(): array
    {
        $query = Sale::where('tenant_id', $this->tenantId)->with(['duka']);

        if ($this->dukaId) {
            $query->where('duka_id', $this->dukaId);
        }

        $sales = $query->get();

        $data = [
            ['Sales Summary Report'],
            ['Generated', now()->toDateTimeString()],
            [''],
            ['Metric', 'Value'],
            ['Total Sales', $sales->count()],
            ['Total Revenue', number_format($sales->sum('total_amount'), 2)],
            ['Total Profit', number_format($sales->sum('profit_loss'), 2)],
            ['Average Sale', number_format($sales->avg('total_amount'), 2)],
            [''],
            ['Breakdown by Duka'],
            ['Duka Name', 'Sales Count', 'Revenue', 'Profit'],
        ];

        foreach ($sales->groupBy('duka_id') as $group) {
            $data[] = [
                $group->first()->duka->name ?? 'Unknown',
                $group->count(),
                number_format($group->sum('total_amount'), 2),
                number_format($group->sum('profit_loss'), 2),
            ];
        }

        return $data;
    }
}
