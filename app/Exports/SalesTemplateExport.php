<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Product;
use App\Models\Stock;

class SalesTemplateExport implements FromCollection, WithHeadings, WithMapping
{
    protected $dukaId;
    protected $tenantId;

    public function __construct($dukaId, $tenantId)
    {
        $this->dukaId = $dukaId;
        $this->tenantId = $tenantId;
    }

    public function collection()
    {
        return Product::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->with(['stocks' => function ($q) {
                $q->where('duka_id', $this->dukaId);
            }])
            ->get();
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Product Name',
            'Quantity Sold',
            'Buying Price (Cost)', // New Column
            'Unit Price (Selling)',
            'Customer Name',
            'Customer Phone',
            'Payment Method', // Cash, M-Pesa, Bank, etc.
            'Sale Date', // YYYY-MM-DD
            'Is Loan (Yes/No)',
        ];
    }

    public function map($product): array
    {
        return [
            $product->sku,
            $product->name,
            '', // Quantity Sold
            $product->buying_price, // Default Buying Price
            $product->selling_price,
            '', // Customer Name
            '',
            'Cash',
            now()->toDateString(),
            'No',
        ];
    }
}
