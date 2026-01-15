<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class StaffPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the staff permissions
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
        ];

        // Insert permissions into the permissions table using Spatie Permission
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission['name'],
                'guard_name' => 'web', // Assuming web guard
            ]);
        }

        $this->command->info('Staff permissions seeded successfully.');
        $this->command->table(
            ['Name', 'Display Name', 'Description'],
            array_map(function ($perm) {
                return [$perm['name'], $perm['display_name'], $perm['description']];
            }, $permissions)
        );
    }
}
