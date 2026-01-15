<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AvailablePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'sale_report',
                'display_name' => 'Sale Report',
                'description' => 'Access to view sales reports',
            ],
            [
                'name' => 'delete_sale',
                'display_name' => 'Delete Sale',
                'description' => 'Permission to delete sales records',
            ],
            [
                'name' => 'adding_product',
                'display_name' => 'Add Product',
                'description' => 'Permission to add new products',
            ],
            [
                'name' => 'delete_product',
                'display_name' => 'Delete Product',
                'description' => 'Permission to delete products',
            ],
            [
                'name' => 'adding_stock',
                'display_name' => 'Add Stock',
                'description' => 'Permission to add stock to products',
            ],
            [
                'name' => 'reduce_stock',
                'display_name' => 'Reduce Stock',
                'description' => 'Permission to reduce stock quantities',
            ],
            [
                'name' => 'reduce_price',
                'display_name' => 'Reduce Price',
                'description' => 'Permission to reduce product prices',
            ],
            [
                'name' => 'edit_product',
                'display_name' => 'Edit Product',
                'description' => 'Permission to edit product details',
            ],
            [
                'name' => 'manage_customer',
                'display_name' => 'Manage Customer',
                'description' => 'Permission to manage customers',
            ],
            [
                'name' => 'manage_category',
                'display_name' => 'Manage Category',
                'description' => 'Permission to manage product categories',
            ],
            [
                'name' => 'manage_cashflow',
                'display_name' => 'Manage Cash Flow',
                'description' => 'Permission to manage cash flow records',
            ],
            [
                'name' => 'view_cashflow',
                'display_name' => 'View Cash Flow',
                'description' => 'Permission to view cash flow reports',
            ],
        ];

        foreach ($permissions as $permission) {
            \App\Models\AvailablePermission::firstOrCreate([
                'name' => $permission['name'],
            ], $permission);
        }

        $this->command->info('Available permissions seeded successfully.');
    }
}
