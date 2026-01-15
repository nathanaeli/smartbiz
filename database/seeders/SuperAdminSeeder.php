<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the superadmin role exists
        $superadminRole = Role::firstOrCreate(['name' => 'superadmin']);

        // Create super admin user if it doesn't exist
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@smartbiz.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'), // Change this in production
                'email_verified_at' => now(),
                'role' => 'superadmin',
            ]
        );

        // Assign the superadmin role
        if (!$superAdmin->hasRole('superadmin')) {
            $superAdmin->assignRole($superadminRole);
        }

        $this->command->info('Super Admin user created/updated successfully.');
        $this->command->info('Email: superadmin@smartbiz.com');
        $this->command->info('Password: password');
    }
}
