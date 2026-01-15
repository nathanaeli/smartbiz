<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\AvailablePermission;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    public function run()
    {
        // 1. Define Features and which of your existing permissions belong to them
        $groups = [
            [
                'code' => 'inventory_mgmt',
                'name' => 'Inventory Management',
                'description' => 'Tools for managing products, categories, and stock levels.',
                'matches' => ['adding_product', 'edit_product', 'delete_product', 'manage_category', 'adding_stock', 'reduce_stock', 'reduce_price']
            ],
            [
                'code' => 'sales_pos',
                'name' => 'Sales & Customers',
                'description' => 'Handle customer data, sales records, and reporting.',
                'matches' => ['manage_customer', 'sale_report', 'delete_sale']
            ],
            [
                'code' => 'finance_mgmt',
                'name' => 'Finance & Cashflow',
                'description' => 'Monitor business cashflow and financial health.',
                'matches' => ['manage_cashflow', 'view_cashflow']
            ],
        ];

        foreach ($groups as $group) {
            // Create the Feature group
            $feature = Feature::updateOrCreate(
                ['code' => $group['code']],
                [
                    'name' => $group['name'],
                    'description' => $group['description']
                ]
            );

            // Update your existing permissions to link them to this feature and set model
            foreach ($group['matches'] as $permName) {
                $model = $this->getModelFromPermissionName($permName);
                AvailablePermission::where('name', $permName)
                    ->update(['feature_id' => $feature->id, 'model' => $model]);
            }
        }

        // 2. Add any MISSING permissions you might need for a complete system
        $this->addNewPermissions($groups);
    }

    private function addNewPermissions($groups)
    {
        // Example: Add a new permission that doesn't exist yet
        $inventory = Feature::where('code', 'inventory_mgmt')->first();
        AvailablePermission::firstOrCreate(
            ['name' => 'stock_transfer'],
            [
                'display_name' => 'Stock Transfer',
                'description' => 'Allow moving stock between stores',
                'feature_id' => $inventory->id,
                'model' => 'StockTransfer',
                'is_active' => true
            ]
        );
    }

    private function getModelFromPermissionName($name)
    {
        $mappings = [
            'product' => 'Product',
            'category' => 'ProductCategory',
            'stock' => 'Stock',
            'transfer' => 'StockTransfer',
            'customer' => 'Customer',
            'sale' => 'Sale',
            'cashflow' => 'Transaction',
        ];

        foreach ($mappings as $key => $model) {
            if (str_contains($name, $key)) {
                return $model;
            }
        }

        return null;
    }
}
