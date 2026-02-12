<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class CustomersImport implements ToModel, WithHeadingRow
{
    protected $dukaId;
    protected $tenantId;

    public function __construct($dukaId, $tenantId)
    {
        $this->dukaId = $dukaId;
        $this->tenantId = $tenantId;
    }

    public function model(array $row)
    {
        // Skip if name is missing
        if (empty($row['name'])) {
            return null;
        }

        // Try to update existing customer by email if present, or phone
        $customer = null;
        if (!empty($row['email'])) {
             $customer = Customer::where('email', $row['email'])
                ->where('tenant_id', $this->tenantId)
                ->first();
        }

        if (!$customer && !empty($row['phone'])) {
            $customer = Customer::where('phone', $row['phone'])
                ->where('tenant_id', $this->tenantId)
                ->first();
        }

        if ($customer) {
            // Update existing? Or skip? For now let's skip duplicates to avoid overwrite issues or update logic
            return null;
        }

        return new Customer([
            'tenant_id' => $this->tenantId,
            'duka_id'   => $this->dukaId,
            'name'      => $row['name'],
            'email'     => $row['email'] ?? null,
            'phone'     => $row['phone'] ?? null,
            'address'   => $row['address'] ?? null,
        ]);
    }
}
