<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToCollection, WithHeadingRow
{
    private $tenantId;
    private $officerId;
    private $dukaIds;
    private $dukaId; // Single duka ID for targeted imports
    private $errors = [];
    private $successCount = 0;
    private $skipCount = 0;
    private $validUnits = ['pcs', 'kg', 'g', 'ltr', 'ml', 'box', 'bag', 'pack', 'set', 'pair', 'dozen', 'carton'];

    public function __construct($tenantId, $officerId, $dukaIds, $dukaId = null)
    {
        $this->tenantId = $tenantId;
        $this->officerId = $officerId;
        $this->dukaIds = $dukaIds;
        $this->dukaId = $dukaId;
    }

    public function collection(Collection $rows)
    {
        \Log::info('Starting product import', ['total_rows' => $rows->count()]);

        DB::beginTransaction();
        try {
            foreach ($rows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // +2 because Excel rows start at 1 and we have headers

                // Skip empty rows
                if (empty(array_filter($row->toArray()))) {
                    \Log::debug("Skipping empty row {$rowNumber}");
                    continue;
                }

                // Convert Excel numeric values to strings where needed
                $row['name'] = trim($row['name'] ?? '');
                $row['description'] = trim($row['description'] ?? '');
                $row['category'] = trim($row['category'] ?? '');
                $row['unit'] = trim($row['unit'] ?? '');
                $row['barcode'] = trim(strval($row['barcode'] ?? ''));
                $row['duka'] = trim($row['duka'] ?? '');

                \Log::debug("Processing row {$rowNumber}", ['product_name' => $row['name']]);

                // Validate the row using our custom validation
                $validationErrors = $this->validateRow($row, $rowNumber);
                if (!empty($validationErrors)) {
                    \Log::warning("Row {$rowNumber} validation failed", ['errors' => $validationErrors]);
                    $this->errors = array_merge($this->errors, $validationErrors);
                    $this->skipCount++;
                    continue;
                }

                // Convert numeric values properly
                $buyingPrice = floatval($row['buying_price']);
                $sellingPrice = floatval($row['selling_price']);
                $initialStock = is_numeric($row['initial_stock'] ?? 0) ? intval($row['initial_stock']) : 0;

                // Smart category assignment
                $categoryId = null;
                if (!empty($row['category'])) {
                    $category = ProductCategory::where('tenant_id', $this->tenantId)
                        ->where('name', 'like', '%' . $row['category'] . '%')
                        ->where('status', 'active')
                        ->first();

                    if (!$category) {
                        // Create new category
                        $category = ProductCategory::create([
                            'name' => $row['category'],
                            'description' => 'Auto-created category from Excel import',
                            'status' => 'active',
                            'tenant_id' => $this->tenantId,
                            'created_by' => $this->officerId,
                        ]);
                    }
                    $categoryId = $category->id;
                }

                // Validate and fix unit
                $unit = strtolower(trim($row['unit'] ?? ''));
                if (empty($unit) || !in_array($unit, $this->validUnits)) {
                    if (!empty($row['unit'])) {
                        $warningMsg = "Row {$rowNumber}: Invalid unit '{$row['unit']}'. Using 'pcs' instead. Valid units: " . implode(', ', $this->validUnits);
                        \Log::warning($warningMsg);
                        $this->errors[] = $warningMsg;
                    }
                    $unit = 'pcs'; // Default to pcs
                }

                // Generate unique SKU
                $sku = $this->generateProductSKU($row['name'], $initialStock);

                // Check for duplicate SKU or name
                $existingProduct = Product::where('tenant_id', $this->tenantId)
                    ->where(function($q) use ($sku, $row) {
                        $q->where('sku', $sku)
                          ->orWhere('name', $row['name']);
                    })
                    ->first();

                if ($existingProduct) {
                    $errorMsg = "Row {$rowNumber}: Product with name '{$row['name']}' or SKU '{$sku}' already exists.";
                    \Log::warning($errorMsg);
                    $this->errors[] = $errorMsg;
                    $this->skipCount++;
                    continue;
                }

                // Create product
                $productDukaId = $this->dukaId ?: ($this->dukaIds[0] ?? null);

                $product = Product::create([
                    'name' => $row['name'],
                    'sku' => $sku,
                    'description' => $row['description'] ?: null,
                    'unit' => $unit,
                    'base_price' => $buyingPrice,
                    'selling_price' => $sellingPrice,
                    'category_id' => $categoryId,
                    'barcode' => !empty($row['barcode']) ? $row['barcode'] : null,
                    'tenant_id' => $this->tenantId,
                    'duka_id' => $productDukaId,
                    'is_active' => true,
                ]);

                \Log::info("Created product", ['id' => $product->id, 'name' => $product->name, 'sku' => $product->sku]);

                // Handle initial stock
                if ($initialStock > 0) {
                    // Determine duka - use targeted duka if specified, otherwise use first assigned duka
                    $dukaId = $this->dukaId;

                    if (!$dukaId) {
                        if (!empty($row['duka'])) {
                            $duka = \App\Models\Duka::whereIn('id', $this->dukaIds)
                                ->where('name', 'like', '%' . trim($row['duka']) . '%')
                                ->first();
                            $dukaId = $duka ? $duka->id : $this->dukaIds[0];
                        } else {
                            $dukaId = $this->dukaIds[0];
                        }
                    }

                    // Create stock
                    $stock = Stock::create([
                        'product_id' => $product->id,
                        'duka_id' => $dukaId,
                        'quantity' => $initialStock,
                        'last_updated_by' => $this->officerId,
                    ]);

                    // Record stock movement
                    StockMovement::create([
                        'stock_id' => $stock->id,
                        'user_id' => $this->officerId,
                        'type' => 'add',
                        'quantity_change' => $initialStock,
                        'previous_quantity' => 0,
                        'new_quantity' => $initialStock,
                        'reason' => 'Initial stock from Excel import',
                    ]);
                }

                $this->successCount++;
            }

            DB::commit();
            \Log::info('Product import completed', [
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
        if (empty(trim($row['name'] ?? ''))) {
            $errors[] = "Row {$rowNumber}: Product name is required.";
        }

        // Validate buying price
        $buyingPrice = is_numeric($row['buying_price'] ?? '') ? floatval($row['buying_price']) : 0;
        if (empty($row['buying_price']) || !is_numeric($row['buying_price'])) {
            $errors[] = "Row {$rowNumber}: Buying price must be a valid number.";
        } elseif ($buyingPrice <= 0) {
            $errors[] = "Row {$rowNumber}: Buying price must be greater than 0.";
        }

        // Validate selling price
        $sellingPrice = is_numeric($row['selling_price'] ?? '') ? floatval($row['selling_price']) : 0;
        if (empty($row['selling_price']) || !is_numeric($row['selling_price'])) {
            $errors[] = "Row {$rowNumber}: Selling price must be a valid number.";
        } elseif ($sellingPrice <= 0) {
            $errors[] = "Row {$rowNumber}: Selling price must be greater than 0.";
        } elseif ($buyingPrice > 0 && $sellingPrice <= $buyingPrice) {
            $errors[] = "Row {$rowNumber}: Selling price must be greater than buying price.";
        }

        // Validate initial stock
        if (!empty($row['initial_stock']) && (!is_numeric($row['initial_stock']) || intval($row['initial_stock']) < 0)) {
            $errors[] = "Row {$rowNumber}: Initial stock must be a valid non-negative number.";
        }

        // Validate barcode length
        if (!empty($row['barcode']) && strlen(strval($row['barcode'])) > 255) {
            $errors[] = "Row {$rowNumber}: Barcode must not exceed 255 characters.";
        }

        return $errors;
    }

    private function generateProductSKU($productName, $stockLevel)
    {
        $cleanName = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($productName));
        $namePrefix = substr($cleanName, 0, 4);
        $stockPart = str_pad($stockLevel, 3, '0', STR_PAD_LEFT);
        $randomPart = str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);
        $sku = $namePrefix . '-' . $stockPart . '-' . $randomPart;

        $counter = 1;
        $originalSku = $sku;
        while (Product::where('sku', $sku)->exists()) {
            $sku = $originalSku . '-' . $counter;
            $counter++;
        }

        return $sku;
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
