<?php

namespace App\Http\Controllers;

use App\Models\AvailablePermission;
use App\Models\Feature;
use App\Models\Plan;
use App\Imports\PermissionImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class AvailablePermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    // Load features AND their related plans in one go
    $plans = Plan::all();
    $features = Feature::all();


    // Load permissions with their assigned feature and the plans that include that feature
    $permissions = AvailablePermission::with(['feature.plans'])
        ->when($request->search, function($q) use ($request) {
            $q->where('name', 'like', "%{$request->search}%")
              ->orWhere('display_name', 'like', "%{$request->search}%");
        })
        ->paginate(15);

    return view('super-admin.available-permissions.index', compact('permissions', 'features','plans'));
}

public function assignFeature(Request $request, $id)
{
    // 1. Validate the input
    $request->validate([
        'feature_id' => 'nullable|exists:features,id'
    ]);

    // 2. Find the permission
    $permission = AvailablePermission::findOrFail($id);

    // 3. Update the feature assignment
    $permission->update([
        'feature_id' => $request->feature_id
    ]);

    // 4. Redirect back with success message
    return back()->with('success', "Permission '{$permission->display_name}' has been successfully grouped.");
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('super-admin.available-permissions.create');
    }

    /**
     * Download sample Excel format with existing permissions
     */
    public function downloadSample()
    {
        $permissions = AvailablePermission::all();

        // If no permissions exist, provide sample data
        if ($permissions->isEmpty()) {
            $sampleData = collect([
                [
                    'name' => 'sale_report',
                    'display_name' => 'Sale Report',
                    'description' => 'Access to view sales reports',
                    'model' => 'Sale',
                    'is_active' => 'true'
                ],
                [
                    'name' => 'delete_sale',
                    'display_name' => 'Delete Sale',
                    'description' => 'Permission to delete sales records',
                    'model' => 'Sale',
                    'is_active' => 'true'
                ],
                [
                    'name' => 'adding_product',
                    'display_name' => 'Add Product',
                    'description' => 'Permission to add new products',
                    'model' => 'Product',
                    'is_active' => 'true'
                ],
                [
                    'name' => 'manage_customer',
                    'display_name' => 'Manage Customer',
                    'description' => 'Permission to manage customers',
                    'model' => 'Customer',
                    'is_active' => 'true'
                ],
                [
                    'name' => 'new-permission',
                    'display_name' => 'New Permission',
                    'description' => 'Description for your new permission',
                    'model' => '',
                    'is_active' => 'true'
                ]
            ]);
        } else {
            // Use existing permissions plus a sample row
            $sampleData = $permissions->map(function ($permission) {
                return [
                    'name' => $permission->name,
                    'display_name' => $permission->display_name,
                    'description' => $permission->description ?: '',
                    'model' => $permission->model ?: '',
                    'is_active' => $permission->is_active ? 'true' : 'false'
                ];
            });

            // Add a sample row for new permissions
            $sampleData->push([
                'name' => 'new-permission',
                'display_name' => 'New Permission',
                'description' => 'Description for your new permission',
                'is_active' => 'true'
            ]);
        }

        return Excel::download(new class($sampleData) implements FromCollection, WithHeadings, WithStyles {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return $this->data;
            }

            public function headings(): array
            {
                return [
                    'name',
                    'display_name',
                    'description',
                    'model',
                    'is_active'
                ];
            }

            public function styles(Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                    'A:A' => ['alignment' => ['horizontal' => 'center']],
                    'E:E' => ['alignment' => ['horizontal' => 'center']]
                ];
            }
        }, 'permissions-sample-format.xlsx');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if Excel file is uploaded
        if ($request->hasFile('excel_file')) {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
            ]);

            try {
                $import = new PermissionImport();
                Excel::import($import, $request->file('excel_file'));

                $successCount = $import->getSuccessCount();
                $errorCount = count($import->getErrors());
                $skipCount = $import->getSkipCount();

                $message = "Import completed. {$successCount} permissions imported successfully.";
                if ($errorCount > 0) {
                    $message .= " {$errorCount} errors occurred. {$skipCount} rows skipped.";
                }

                return redirect()->route('super-admin.available-permissions.index')->with('success', $message);
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['excel_file' => 'Import failed: ' . $e->getMessage()])->withInput();
            }
        } else {
            // Single permission creation
            $request->validate([
                'name' => 'required|string|unique:available_permissions,name',
                'display_name' => 'required|string',
                'description' => 'nullable|string',
                'model' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            AvailablePermission::create($request->all());

            return redirect()->route('super-admin.available-permissions.index')->with('success', 'Permission created successfully.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $permission = AvailablePermission::with(['staffPermissions.officer', 'staffPermissions.tenant'])->findOrFail($id);
        return view('super-admin.available-permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $permission = AvailablePermission::findOrFail($id);
        return view('super-admin.available-permissions.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $permission = AvailablePermission::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:available_permissions,name,' . $id,
            'display_name' => 'required|string',
            'description' => 'nullable|string',
            'model' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $permission->update($request->all());

        return redirect()->route('super-admin.available-permissions.index')->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $permission = AvailablePermission::findOrFail($id);
        $permission->delete();

        return redirect()->route('super-admin.available-permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
