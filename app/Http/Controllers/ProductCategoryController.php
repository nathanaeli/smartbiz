<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $tenant = $user->tenant;
        $duka = $user->duka;

        $categories = ProductCategory::with(['parent', 'children'])->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->paginate(15);

        return view('categories.index', compact('categories', 'duka'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $tenant = $user->tenant;
        $duka = $user->duka;

        $parentCategories = ProductCategory::where('tenant_id', $tenant->id)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('categories.create', compact('duka', 'parentCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:product_categories,id',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        $user = Auth::user();
        $tenant = $user->tenant;

        $category = ProductCategory::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'description' => $request->description,
            'duka_id' => 0,
            'status' => $request->status,
        ]);

        notify()->success('Category created successfully!');
        return redirect()->route('categories.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $productCategory)
    {
        $user = Auth::user();
        $duka = $user->duka;



        $category->load(['parent', 'children', 'products']);

        return view('categories.show', compact('category', 'duka'));
    }

    /**
     * Show the form for editing the specified resource.
     */
   public function edit(ProductCategory $productCategory)
{
    $tenant = Auth::user()->tenant;


    // Fetch parent categories owned by this tenant
    $parentCategories = ProductCategory::where('tenant_id', $tenant->id)
        ->whereNull('parent_id')
        ->where('id', '!=', $productCategory->id)
        ->orderBy('name')
        ->get();

    return view('categories.edit', compact('productCategory', 'parentCategories'));
}


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductCategory $productCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:product_categories,id',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        $user = Auth::user();
        $duka = $user->duka;

        // Ensure category belongs to user's duka
        if (!$duka->productCategories()->where('product_category_id', $productCategory->id)->exists()) {
            abort(403, 'Unauthorized access to category.');
        }

        // Prevent circular reference
        if ($request->parent_id && $request->parent_id == $productCategory->id) {
            return back()->withErrors(['parent_id' => 'Category cannot be its own parent.']);
        }

        $productCategory->update([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        notify()->success('Category updated successfully!');
        return redirect()->route('categories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $productCategory)
    {
        $user = Auth::user();

        $productCategory->delete();

        notify()->success('Category deleted successfully!');
        return redirect()->route('categories.index');
    }
}
