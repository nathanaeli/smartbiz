<?php
namespace App\Livewire;

use App\Imports\CategoryImport;
use App\Imports\ProductImport;
use App\Models\Duka;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Stock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class DukaProducts extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $duka;
    public $categories     = [];
    public $showAddModal   = false;
    public $showExcelModal = false;

    public $name, $category_id, $unit, $base_price, $selling_price, $description, $initial_stock;
    public $image;
    public $productImage = null;
    public $excelFile;
    public $importType    = 'products'; // products or categories
    public $importResults = [];

    public function mount($dukaId = null)
    {
        if ($dukaId) {
            $this->duka = Duka::findOrFail($dukaId);
        } else {
            $this->duka = Auth::user()->duka;
        }

        if (! $this->duka) {
            abort(403, 'No Duka assigned to this tenant.');
        }

        $this->loadCategories();
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="p-3">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">Loading products...</h5>
                </div>
                <div class="p-3">
                    <div class="placeholder-glow">
                        <span class="placeholder col-12 mb-2" style="height:35px;"></span>
                        <span class="placeholder col-12 mb-2" style="height:35px;"></span>
                        <span class="placeholder col-12 mb-2" style="height:35px;"></span>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }

    public function updating()
    {
        $this->resetPage();
    }

    public function loadCategories()
    {
        $tenantId = $this->duka->tenant_id;

        $this->categories = ProductCategory::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->orderBy('name')
            ->get();
    }

    public function generateSku()
    {
        do {
            $sku = 'SKU-' . strtoupper(Str::random(6));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }

    public function saveProduct()
    {
        $this->validate([
            'name'          => 'required|string|max:255',
            'category_id'   => 'nullable|exists:product_categories,id',
            'base_price'    => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'unit'          => 'nullable|string|max:20',
            'initial_stock' => 'required|integer|min:0',
            'description'   => 'nullable|string',
            'image'         => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $imageName = $this->productImage;

            if ($this->image) {
                $imageName = time() . '_' . $this->image->getClientOriginalName();
                $this->image->storeAs('products', $imageName, 'public');
            }

            $product = Product::create([
                'tenant_id'     => $this->duka->tenant_id,
                'duka_id'       => $this->duka->id,
                'sku'           => $this->generateSku(),
                'name'          => $this->name,
                'category_id'   => $this->category_id,
                'unit'          => $this->unit,
                'base_price'    => $this->base_price,
                'selling_price' => $this->selling_price,
                'description'   => $this->description,
                'is_active'     => true,
                'image'         => $imageName,
            ]);

            $stock = Stock::create([
                'duka_id'         => $this->duka->id,
                'product_id'      => $product->id,
                'quantity'        => $this->initial_stock,
                'last_updated_by' => auth()->id(),
                'notes'           => 'Initial stock on product creation',
            ]);
            $stock->movements()->create([
                'user_id'           => auth()->id(),
                'type'              => 'in',
                'quantity_change'   => $this->initial_stock,
                'previous_quantity' => 0,
                'new_quantity'      => $this->initial_stock,
                'unit_cost'         => $this->base_price,
                'unit_price'        => 0, // No income yet
                'total_value'       => $this->initial_stock * $this->base_price,
                'reason'            => 'purchase', // Initial stock is essentially a purchase
                'notes'             => 'Initial inventory setup',
            ]);

            DB::commit();

            $this->reset([
                'name', 'category_id', 'unit', 'base_price', 'selling_price',
                'description', 'initial_stock', 'image', 'productImage',
            ]);

            $this->showAddModal = false;

            notify()->success('Product & initial stock saved successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Product creation failed:', ['error' => $e->getMessage()]);
            notify()->error('Product registration failed. Please try again.');
        }
    }

    public function importFromExcel()
    {
        $this->validate([
            'excelFile'  => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
            'importType' => 'required|in:products,categories',
        ]);

        try {
            $tenantId  = $this->duka->tenant_id;
            $officerId = auth()->id();
            $dukaIds   = [$this->duka->id];

            if ($this->importType === 'products') {
                $import = new ProductImport($tenantId, $officerId, $dukaIds, $this->duka->id);
                Excel::import($import, $this->excelFile->getRealPath());

                $this->importResults = [
                    'success' => $import->getSuccessCount(),
                    'errors'  => $import->getErrors(),
                    'skipped' => $import->getSkipCount(),
                    'type'    => 'products',
                ];
            } else {
                $import = new CategoryImport($tenantId, $officerId);
                Excel::import($import, $this->excelFile->getRealPath());

                $this->importResults = [
                    'success' => $import->getSuccessCount(),
                    'errors'  => $import->getErrors(),
                    'skipped' => $import->getSkipCount(),
                    'type'    => 'categories',
                ];
            }

            // Refresh categories if we imported categories
            if ($this->importType === 'categories') {
                $this->loadCategories();
            }

            $this->reset(['excelFile']);
            $this->showExcelModal = false;

            $totalProcessed = $this->importResults['success'] + $this->importResults['skipped'];
            $message        = "Excel import completed! {$this->importResults['success']} out of {$totalProcessed} records imported successfully.";

            if (! empty($this->importResults['errors'])) {
                notify()->warning($message . ' Some errors occurred during import.');
            } else {
                notify()->success($message);
            }

        } catch (\Exception $e) {
            \Log::error('Excel import failed:', ['error' => $e->getMessage()]);
            notify()->error('Excel import failed: ' . $e->getMessage());
        }
    }

    public function downloadSampleExcel($type = 'products')
    {
        if ($type === 'products') {
            return $this->downloadProductSample();
        } else {
            return $this->downloadCategorySample();
        }
    }

    public function downloadProductSample()
    {
        $headers = [
            'name', 'category', 'buying_price', 'selling_price', 'unit',
            'initial_stock', 'description', 'barcode',
        ];

        $sampleData = [
            ['Sample Product 1', 'Electronics', '100.00', '150.00', 'pcs', '10', 'Sample description', '123456789'],
            ['Sample Product 2', 'Clothing', '50.00', '80.00', 'pcs', '25', '', ''],
            ['Sample Product 3', 'Food', '25.00', '35.00', 'kg', '5', 'Perishable item', '987654321'],
        ];

        $filename = 'product_import_sample.csv';
        $filePath = storage_path('app/temp/' . $filename);

        // Create temp directory if it doesn't exist
        if (! file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $file = fopen($filePath, 'w');

        // Add BOM for Excel UTF-8 support
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($file, $headers);

        foreach ($sampleData as $row) {
            fputcsv($file, $row);
        }

        fclose($file);

        return response()->download($filePath, $filename)->deleteFileAfterSend();
    }

    public function downloadCategorySample()
    {
        $headers = ['name', 'description', 'parent_category', 'status'];

        $sampleData = [
            ['Electronics', 'Electronic devices and accessories', '', 'active'],
            ['Computers', 'Desktop and laptop computers', 'Electronics', 'active'],
            ['Mobile Phones', 'Smartphones and tablets', 'Electronics', 'active'],
            ['Clothing', 'Apparel and fashion items', '', 'active'],
            ['Men Clothing', 'Clothing for men', 'Clothing', 'active'],
        ];

        $filename = 'category_import_sample.csv';
        $filePath = storage_path('app/temp/' . $filename);

        // Create temp directory if it doesn't exist
        if (! file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $file = fopen($filePath, 'w');

        // Add BOM for Excel UTF-8 support
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($file, $headers);

        foreach ($sampleData as $row) {
            fputcsv($file, $row);
        }

        fclose($file);

        return response()->download($filePath, $filename)->deleteFileAfterSend();
    }

    public function deleteProduct($productId)
    {
        $product = Product::where('duka_id', $this->duka->id)->findOrFail($productId);

        try {
            $product->delete(); // Soft delete

            notify()->success('Product deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('Product deletion failed:', ['error' => $e->getMessage()]);
            notify()->error('Failed to delete product. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.duka-products', [
            'products' => Product::whereHas('stocks', function ($query) {
                $query->where('duka_id', $this->duka->id)->where('quantity', '>', 0);
            })
                ->with(['category', 'stocks' => function ($query) {
                    $query->where('duka_id', $this->duka->id);
                }])
                ->orderBy('id', 'desc')
                ->paginate(8),
        ]);
    }
}
