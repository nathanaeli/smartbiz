<?php

namespace App\Imports;

use App\Models\AvailablePermission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PermissionImport implements ToCollection, WithHeadingRow
{
    private $errors = [];
    private $successCount = 0;
    private $skipCount = 0;

    public function collection(Collection $rows)
    {
        \Log::info('Starting permission import', ['total_rows' => $rows->count()]);

        DB::beginTransaction();
        try {
            foreach ($rows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // +2 because Excel rows start at 1 and we have headers

                // Skip empty rows
                if (empty(array_filter($row->toArray()))) {
                    \Log::debug("Skipping empty row {$rowNumber}");
                    continue;
                }

                // Trim values
                $row['name'] = trim($row['name'] ?? '');
                $row['display_name'] = trim($row['display_name'] ?? '');
                $row['description'] = trim($row['description'] ?? '');
                $row['is_active'] = trim($row['is_active'] ?? '');

                \Log::debug("Processing row {$rowNumber}", ['permission_name' => $row['name']]);

                // Validate the row
                $validationErrors = $this->validateRow($row, $rowNumber);
                if (!empty($validationErrors)) {
                    \Log::warning("Row {$rowNumber} validation failed", ['errors' => $validationErrors]);
                    $this->errors = array_merge($this->errors, $validationErrors);
                    $this->skipCount++;
                    continue;
                }

                // Check for duplicate name
                $existingPermission = AvailablePermission::where('name', $row['name'])->first();
                if ($existingPermission) {
                    $errorMsg = "Row {$rowNumber}: Permission with name '{$row['name']}' already exists.";
                    \Log::warning($errorMsg);
                    $this->errors[] = $errorMsg;
                    $this->skipCount++;
                    continue;
                }

                // Convert is_active to boolean
                $isActive = true; // default
                if (!empty($row['is_active'])) {
                    $activeValue = strtolower($row['is_active']);
                    if (in_array($activeValue, ['0', 'false', 'no', 'inactive'])) {
                        $isActive = false;
                    } elseif (in_array($activeValue, ['1', 'true', 'yes', 'active'])) {
                        $isActive = true;
                    }
                }

                // Create permission
                AvailablePermission::create([
                    'name' => $row['name'],
                    'display_name' => $row['display_name'],
                    'description' => $row['description'] ?: null,
                    'is_active' => $isActive,
                ]);

                \Log::info("Created permission", ['name' => $row['name']]);

                $this->successCount++;
            }

            DB::commit();
            \Log::info('Permission import completed', [
                'success_count' => $this->successCount,
                'skip_count' => $this->skipCount,
                'error_count' => count($this->errors)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMsg = "Import failed: " . $e->getMessage();
            \Log::error($errorMsg, ['exception' => $e]);
            $this->errors[] = $errorMsg;
        }
    }

    private function validateRow($row, $rowNumber)
    {
        $errors = [];

        // Validate required fields
        if (empty($row['name'])) {
            $errors[] = "Row {$rowNumber}: Permission name is required.";
        } elseif (!preg_match('/^[a-z-]+$/u', $row['name'])) {
            $errors[] = "Row {$rowNumber}: Permission name must be lowercase with hyphens only.";
        }

        if (empty($row['display_name'])) {
            $errors[] = "Row {$rowNumber}: Display name is required.";
        }

        return $errors;
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
