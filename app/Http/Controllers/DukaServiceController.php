<?php

namespace App\Http\Controllers;

use App\Models\Duka;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class DukaServiceController extends Controller
{
    public function index($duka_id)
    {
        $duka = Duka::findOrFail($duka_id);

        // Ensure user belongs to the same tenant as the duka
        if ($duka->tenant_id !== Auth::user()->tenant->id) {
            abort(403);
        }

        $categories = ServiceCategory::where('duka_id', $duka_id)
            ->with('services')
            ->get();

        return view('duka.services', compact('duka', 'categories'));
    }

    public function storeCategory(Request $request, $duka_id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        ServiceCategory::create([
            'tenant_id' => Auth::user()->tenant->id,
            'duka_id' => $duka_id,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Service category created successfully.');
    }

    public function updateCategory(Request $request, $duka_id, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = ServiceCategory::where('duka_id', $duka_id)->findOrFail($id);
        $category->update($request->only(['name', 'description']));

        return back()->with('success', 'Service category updated successfully.');
    }

    public function destroyCategory($duka_id, $id)
    {
        $category = ServiceCategory::where('duka_id', $duka_id)->findOrFail($id);

        if ($category->services()->count() > 0) {
            return back()->with('error', 'Cannot delete category that has services.');
        }

        $category->delete();
        return back()->with('success', 'Service category deleted successfully.');
    }

    public function storeService(Request $request, $duka_id)
    {
        $request->validate([
            'category_id' => 'required|exists:service_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_type' => 'required|string|max:255',
        ]);

        Service::create([
            'tenant_id' => Auth::user()->tenant->id,
            'duka_id' => $duka_id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'billing_type' => $request->billing_type,
            'is_active' => true,
        ]);

        return back()->with('success', 'Service created successfully.');
    }

    public function updateService(Request $request, $duka_id, $id)
    {
        $request->validate([
            'category_id' => 'required|exists:service_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_type' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $service = Service::where('duka_id', $duka_id)->findOrFail($id);
        $service->update($request->all());

        return back()->with('success', 'Service updated successfully.');
    }

    public function destroyService($duka_id, $id)
    {
        $service = Service::where('duka_id', $duka_id)->findOrFail($id);
        $service->delete();
        return back()->with('success', 'Service deleted successfully.');
    }
}
