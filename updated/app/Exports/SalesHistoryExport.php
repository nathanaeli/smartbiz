<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesHistoryExport implements FromCollection, WithHeadings, WithMapping
{
    private $tenantId;
    private $dukaId;

    public function __construct($tenantId, $dukaId = null)
    {
        $this->tenantId = $tenantId;
        $this->dukaId = $dukaId;
    }

    public function collection()
    {
        $query = Sale::where('tenant_id', $this->tenantId)->with(['customer', 'duka', 'user']);

        if ($this->dukaId) {
            $query->where('duka_id', $this->dukaId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Sale ID',
            'Duka',
            'Customer',
            'Total Amount',
            'Discount',
            'Profit',
            'Type',
            'Status',
            'Created By'
        ];
    }

    public function map($sale): array
    {
        $status = 'Paid';
        if ($sale->is_loan) {
            $paid = $sale->loanPayments->sum('amount');
            if ($paid >= $sale->total_amount) $status = 'Paid';
            elseif ($paid > 0) $status = 'Partial';
            else $status = 'Unpaid';
        }

        return [
            $sale->created_at->format('Y-m-d H:i'),
            $sale->id,
            $sale->duka->name ?? 'N/A',
            $sale->customer->name ?? 'Walk-in',
            $sale->total_amount,
            $sale->discount_amount,
            $sale->profit_loss,
            $sale->is_loan ? 'Loan' : 'Cash',
            $status,
            $sale->user->name ?? 'System',
        ];
    }
}
