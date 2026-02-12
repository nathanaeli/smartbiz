<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private $dukaId;

    public function __construct($dukaId)
    {
        $this->dukaId = $dukaId;
    }

    public function collection()
    {
        return Product::where('duka_id', $this->dukaId)
            ->with(['category', 'stocks' => function ($query) {
                $query->where('duka_id', $this->dukaId);
            }])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'SKU',
            'Category',
            'Selling Price',
            'Current Stock',
            'Stock Value',
            'Status'
        ];
    }

    public function map($product): array
    {
        $stock = $product->stocks->first();
        $quantity = $stock ? $stock->quantity : 0;
        $value = $product->selling_price * $quantity;

        $status = 'In Stock';
        if ($quantity <= 0) $status = 'Out of Stock';
        elseif ($quantity <= 10) $status = 'Low Stock';

        return [
            $product->name,
            $product->sku,
            $product->category->name ?? 'Uncategorized',
            $product->selling_price,
            $quantity,
            $value,
            $status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
