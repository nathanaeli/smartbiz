<?php

namespace App\Imports;

use App\Models\ProductCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CategoriesImport implements ToModel, WithHeadingRow
{
    protected $tenantId;

    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    public function model(array $row)
    {
        if (empty($row['name'])) {
            return null;
        }

        // Check if category already exists for this tenant
        $existing = ProductCategory::where('tenant_id', $this->tenantId)
            ->where('name', $row['name'])
            ->first();

        if ($existing) {
            return null;
        }

        return new ProductCategory([
            'tenant_id'   => $this->tenantId,
            'name'        => $row['name'],
            'description' => $row['description'] ?? null,
            'status'      => $row['status'] ?? 'active',
            'duka_id'     => 0, // Default to global or handled otherwise
        ]);
    }
}
