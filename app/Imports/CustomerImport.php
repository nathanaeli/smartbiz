<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Duka;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CustomerImport implements ToCollection, WithHeadingRow, WithValidation
{
    private $tenantId;
    private $officerId;
    private $dukaIds;
    private $errors = [];
    private $successCount = 0;
    private $skipCount = 0;

    public function __construct($tenantId, $officerId, $dukaIds)
    {
        $this->tenantId = $tenantId;
        $this->officerId = $officerId;
        $this->dukaIds = $dukaIds;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // +2 because Excel rows start at 1 and we have headers

                // Skip empty rows
                if (empty(array_filter($row->toArray()))) {
                    continue;
                }

                // Validate required fields
                if (empty($row['name'])) {
                    $this->errors[] = "Row {$rowNumber}: Name is required.";
                    $this->skipCount++;
                    continue;
                }

                // Convert Excel numeric values to strings where needed
                $row['name'] = trim($row['name'] ?? '');
                $row['email'] = trim($row['email'] ?? '');
                $row['phone'] = trim(strval($row['phone'] ?? ''));
                $row['address'] = trim($row['address'] ?? '');
                $row['duka'] = trim($row['duka'] ?? '');

                // Check for duplicate email if provided
                if (!empty($row['email'])) {
                    $existingCustomer = Customer::where('tenant_id', $this->tenantId)
                        ->where('email', $row['email'])
                        ->first();

                    if ($existingCustomer) {
                        $this->errors[] = "Row {$rowNumber}: Customer with email '{$row['email']}' already exists.";
                        $this->skipCount++;
                        continue;
                    }
                }

                // Check for duplicate name (optional - can be allowed for same name different people)
                // But let's make it a warning and allow it

                // Determine duka - use first assigned duka if not specified
                $dukaId = null;
                if (!empty($row['duka'])) {
                    $duka = Duka::whereIn('id', $this->dukaIds)
                        ->where('name', 'like', '%' . trim($row['duka']) . '%')
                        ->first();
                    $dukaId = $duka ? $duka->id : $this->dukaIds[0];
                } else {
                    $dukaId = $this->dukaIds[0];
                }

                // Create customer
                $customer = Customer::create([
                    'name' => $row['name'],
                    'email' => !empty($row['email']) ? $row['email'] : null,
                    'phone' => !empty($row['phone']) ? $row['phone'] : null,
                    'address' => !empty($row['address']) ? $row['address'] : null,
                    'duka_id' => $dukaId,
                    'tenant_id' => $this->tenantId,
                    'status' => 'active',
                    'created_by' => $this->officerId,
                ]);

                $this->successCount++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errors[] = "Import failed: " . $e->getMessage();
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'duka' => 'nullable|string|max:255',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Customer name is required.',
            'name.max' => 'Customer name must not exceed 255 characters.',
            'email.email' => 'Email must be a valid email address.',
            'email.max' => 'Email must not exceed 255 characters.',
            'phone.max' => 'Phone number must not exceed 20 characters.',
            'duka.max' => 'Duka name must not exceed 255 characters.',
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getSkipCount()
    {
        return $this->skipCount;
    }
}
