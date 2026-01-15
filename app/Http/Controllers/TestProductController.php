<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\Duka;

class TestProductController extends Controller
{
    public function test()
    {
        Log::info('Test Product Controller accessed', [
            'user_id' => Auth::id(),
            'user_email' => Auth::user()->email ?? 'no email',
            'has_duka' => Auth::user()->duka ? 'yes' : 'no',
            'duka_id' => Auth::user()->duka->id ?? 'null',
            'tenant_id' => Auth::user()->tenant->id ?? 'null'
        ]);

        $user = Auth::user();
        $duka = $user->duka;

        $data = [
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'has_duka' => $duka ? true : false,
                'duka_id' => $duka->id ?? null,
                'duka_name' => $duka->name ?? null,
                'tenant_id' => $user->tenant->id ?? null
            ],
            'test_timestamp' => now(),
            'message' => 'Test successful - check logs for details'
        ];

        return response()->json($data);
    }

    public function testProductCreation()
    {
        try {
            $user = Auth::user();
            $duka = $user->duka;

            Log::info('Testing product creation', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'duka_id' => $duka ? $duka->id : null,
                'duka_name' => $duka ? $duka->name : null
            ]);

            if (!$duka) {
                return response()->json([
                    'error' => 'User does not have a duka assigned',
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ], 400);
            }

            // Try to create a test product
            $testProduct = Product::create([
                'duka_id' => $duka->id,
                'sku' => 'TEST-' . time(),
                'name' => 'Test Product ' . time(),
                'base_price' => 1000,
                'selling_price' => 1500,
                'unit' => 'pcs',
                'is_active' => true,
            ]);

            Log::info('Test product created successfully', [
                'product_id' => $testProduct->id,
                'product_name' => $testProduct->name
            ]);

            // Clean up the test product
            $testProduct->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product creation test passed',
                'product_id' => $testProduct->id
            ]);

        } catch (\Exception $e) {
            Log::error('Product creation test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Product creation test failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
