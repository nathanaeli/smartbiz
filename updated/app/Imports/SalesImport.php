<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\Customer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class SalesImport implements ToCollection, WithHeadingRow
{
    private $dukaId;
    private $userId;
    private $tenantId;
    private $errors = [];
    private $successCount = 0;

    public function __construct($dukaId, $userId, $tenantId)
    {
        $this->dukaId = $dukaId;
        $this->userId = $userId;
        $this->tenantId = $tenantId;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            $batchId = (string) \Illuminate\Support\Str::uuid();
            foreach ($rows as $index => $row) {
                $sku = $row['sku'] ?? null;
                $qty = intval($row['quantity_sold'] ?? $row['quantity'] ?? 0);

                if (!$sku || $qty <= 0) continue;

                $rowNum = $index + 2;
                $product = Product::where('sku', $sku)
                    ->where('tenant_id', $this->tenantId)
                    ->first();

                if (!$product) {
                    $this->errors[] = "Row $rowNum: Product with SKU '$sku' not found.";
                    continue;
                }

                $stock = Stock::firstOrCreate(
                    ['product_id' => $product->id, 'duka_id' => $this->dukaId],
                    ['quantity' => 0]
                );

                $buyingPrice = $row['buying_price_cost'] ?? $row['buying_price'] ?? $product->base_price ?? 0;
                $unitPrice = $row['unit_price_selling'] ?? $row['unit_price'] ?? $product->selling_price;
                $totalAmount = $unitPrice * $qty;

                $dateStr = $row['sale_date'] ?? null;
                $saleDate = $dateStr ? Carbon::parse($dateStr) : now();

                // --- STEP A: VIRTUAL STOCK IN ---
                // Tunatengeneza 'in' movement ili kuongeza quantity_remaining ya zamani
                $movementIn = StockMovement::create([
                    'stock_id'           => $stock->id,
                    'user_id'            => $this->userId,
                    'type'               => 'in',
                    'quantity_change'    => $qty,
                    'quantity_remaining' => $qty, // Muhimu kwa FIFO consumption
                    'previous_quantity'  => $stock->quantity,
                    'new_quantity'       => $stock->quantity + $qty,
                    'unit_cost'          => $buyingPrice,
                    'reason'             => 'historical_import',
                    'notes'              => 'Virtual Stock for backdated sale import',
                    'import_batch'       => $batchId,
                    'created_at'         => $saleDate->copy()->subSeconds(5), // Iingie kabla ya sale
                ]);

                $stock->increment('quantity', $qty);

                // --- STEP B: SALE PROCESSING ---
                $customerId = $this->resolveCustomer($row);

                $isLoanCol = strtolower($row['is_loan_yesno'] ?? $row['is_loan'] ?? 'no');
                $isLoan = in_array($isLoanCol, ['yes', 'y', 'true', '1']);

                if ($isLoan && !$customerId) {
                    $this->errors[] = "Row $rowNum: Loan sale requires a valid Customer.";
                    // Rollback stock change for this row
                    $stock->decrement('quantity', $qty);
                    $movementIn->delete();
                    continue;
                }

                $profitLoss = ($unitPrice - $buyingPrice) * $qty;

                $sale = Sale::create([
                    'tenant_id'       => $this->tenantId,
                    'duka_id'         => $this->dukaId,
                    'customer_id'     => $customerId,
                    'total_amount'    => $totalAmount,
                    'profit_loss'     => $profitLoss,
                    'is_loan'         => $isLoan,
                    'due_date'        => $isLoan ? ($row['due_date'] ?? null) : null,
                    'created_at'      => $saleDate,
                    'created_by'      => $this->userId,
                    'import_batch'    => $batchId,
                ]);

                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'unit_price' => $unitPrice,
                    'total'      => $totalAmount,
                    'created_at' => $saleDate,
                ]);

                // --- STEP C: FIFO CONSUMPTION ---
                // Mauzo haya yanakula ile 'quantity_remaining' tuliyoingiza juu
                $qtyToProcess = $qty;
                $batches = StockMovement::where('stock_id', $stock->id)
                    ->whereIn('type', ['in', 'add'])
                    ->where('quantity_remaining', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($qtyToProcess <= 0) break;
                    $take = min($batch->quantity_remaining, $qtyToProcess);
                    $batch->decrement('quantity_remaining', $take);
                    $qtyToProcess -= $take;
                }

                $stock->decrement('quantity', $qty);

                // --- RECORD OUTWARD MOVEMENT ---
                StockMovement::create([
                    'stock_id'           => $stock->id,
                    'user_id'            => $this->userId,
                    'type'               => 'out',
                    'quantity_change'    => $qty,
                    'quantity_remaining' => 0,
                    'previous_quantity'  => $stock->quantity + $qty,
                    'new_quantity'       => $stock->quantity,
                    'unit_cost'          => $buyingPrice,
                    'unit_price'         => $unitPrice,
                    'reason'             => 'sale',
                    'notes'              => "Imported Sale #{$sale->id}",
                    'import_batch'       => $batchId,
                    'created_at'         => $saleDate,
                ]);

                // --- STEP D: FINANCIAL TRANSACTION ---
                if (!$isLoan) {
                    Transaction::create([
                        'duka_id'          => $this->dukaId,
                        'user_id'          => $this->userId,
                        'type'             => 'income',
                        'category'         => 'sale',
                        'amount'           => $totalAmount,
                        'status'           => 'active',
                        'payment_method'   => strtolower($row['payment_method'] ?? 'cash'),
                        'reference_id'     => $sale->id,
                        'transaction_date' => $saleDate->toDateString(),
                        'created_at'       => $saleDate,
                    ]);
                }

                $this->successCount++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errors[] = "System Error: " . $e->getMessage();
        }
    }

    private function resolveCustomer($row)
    {
        $name = trim($row['customer_name'] ?? '');
        $phone = trim($row['customer_phone'] ?? '');
        if (!$name && !$phone) return null;

        $customer = Customer::where('tenant_id', $this->tenantId)
            ->where(function ($q) use ($name, $phone) {
                if ($phone) $q->where('phone', $phone);
                elseif ($name) $q->where('name', $name);
            })->first();

        if (!$customer && $name) {
            $customer = Customer::create([
                'tenant_id' => $this->tenantId,
                'name'      => $name,
                'phone'     => $phone,
            ]);
        }
        return $customer ? $customer->id : null;
    }

    public function getErrors()
    {
        return $this->errors;
    }
    public function getSuccessCount()
    {
        return $this->successCount;
    }
}
