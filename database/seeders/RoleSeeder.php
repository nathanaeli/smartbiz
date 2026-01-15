<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $tenant = Role::firstOrCreate(['name' => 'tenant']);
        $officer = Role::firstOrCreate(['name' => 'officer']);

        // Create permissions (if needed)
        // Permission::firstOrCreate(['name' => 'manage users']);
        // etc.
    }
}
