<?php

namespace App\Imports;

use App\Models\ProductCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CategoryImport implements ToCollection, WithHeadingRow
{
    private $tenantId;
    private $officerId;
    private $errors = [];
    private $successCount = 0;
    private $skipCount = 0;
    private $validStatuses = ['active', 'inactive'];

    public function __construct($tenantId, $officerId)
    {
        $this->tenantId = $tenantId;
        $this->officerId = $officerId;
    }

    public function collection(Collection $rows)
    {
        \Log::info('Starting category import', ['total_rows' => $rows->count()]);

        DB::beginTransaction();
        try {
            foreach ($rows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // +2 because Excel rows start at 1 and we have headers

                // Skip empty rows
                if (empty(array_filter($row->toArray()))) {
                    \Log::debug("Skipping empty row {$rowNumber}");
                    continue;
                }

                // Validate required fields
                if (empty($row['name'])) {
                    $errorMsg = "Row {$rowNumber}: Name is required.";
                    \Log::warning($errorMsg);
                    $this->errors[] = $errorMsg;
                    $this->skipCount++;
                    continue;
                }

                // Convert Excel numeric values to strings where needed
                $row['name'] = trim($row['name'] ?? '');
                $row['description'] = trim($row['description'] ?? '');
                $row['parent_category'] = trim($row['parent_category'] ?? '');
                $row['status'] = trim(strtolower($row['status'] ?? ''));

                \Log::debug("Processing row {$rowNumber}", ['category_name' => $row['name']]);

                // Validate status
                if (!empty($row['status']) && !in_array($row['status'], $this->validStatuses)) {
                    $warningMsg = "Row {$rowNumber}: Invalid status '{$row['status']}'. Using 'active' instead. Valid statuses: " . implode(', ', $this->validStatuses);
                    \Log::warning($warningMsg);
                    $this->errors[] = $warningMsg;
                    $row['status'] = 'active'; // Default to active
                }

                // Check for duplicate category name within tenant
                $existingCategory = ProductCategory::where('tenant_id', $this->tenantId)
                    ->where('name', $row['name'])
                    ->first();

                if ($existingCategory) {
                    $errorMsg = "Row {$rowNumber}: Category with name '{$row['name']}' already exists.";
                    \Log::warning($errorMsg);
                    $this->errors[] = $errorMsg;
                    $this->skipCount++;
                    continue;
                }

                // Handle parent category
                $parentId = null;
                if (!empty($row['parent_category'])) {
                    $parentCategory = ProductCategory::where('tenant_id', $this->tenantId)
                        ->where('name', 'like', '%' . $row['parent_category'] . '%')
                        ->first();

                    if (!$parentCategory) {
                        // Create parent category if it doesn't exist
                        $parentCategory = ProductCategory::create([
                            'name' => $row['parent_category'],
                            'description' => 'Auto-created parent category from Excel import',
                            'status' => 'active',
                            'tenant_id' => $this->tenantId,
                            'created_by' => $this->officerId,
                        ]);
                    }
                    $parentId = $parentCategory->id;
                }

                // Create category
                $category = ProductCategory::create([
                    'name' => $row['name'],
                    'description' => !empty($row['description']) ? $row['description'] : null,
                    'parent_id' => $parentId,
                    'status' => !empty($row['status']) ? $row['status'] : 'active',
                    'tenant_id' => $this->tenantId,
                    'created_by' => $this->officerId,
                ]);

                \Log::info("Created category", ['id' => $category->id, 'name' => $category->name]);

                $this->successCount++;
            }

            DB::commit();
            \Log::info('Category import completed', [
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
