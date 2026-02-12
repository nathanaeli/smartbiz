<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'duka_id' => 'required|exists:dukas,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $user = Auth::user();
        $duka = $user->duka;

        // Verify the duka belongs to the user
        if (!$duka || $duka->id != $request->duka_id) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        // Create the customer
        Customer::create([
            'tenant_id' => $duka->tenant->id, // Customers belong to tenant
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->back()->with('success', 'Customer added successfully!');
    }
    public function downloadImportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customers_template.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['name', 'email', 'phone', 'address']); // Header row
            fputcsv($handle, ['John Doe', 'john@example.com', '1234567890', '123 Main St, City']); // Sample row
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
