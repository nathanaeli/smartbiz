<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;
use App\Models\Feature;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create features
        $features = [
            ['code' => 'max_dukas', 'name' => 'Maximum Dukas', 'description' => 'Number of dukas allowed'],
            ['code' => 'max_products', 'name' => 'Maximum Products', 'description' => 'Number of products allowed per duka'],
            ['code' => 'inventory_management', 'name' => 'Inventory Management', 'description' => 'Basic inventory tracking'],
            ['code' => 'stock_transfers', 'name' => 'Stock Transfers', 'description' => 'Transfer stock between dukas'],
            ['code' => 'activity_logging', 'name' => 'Activity Logging', 'description' => 'Track all system activities'],
            ['code' => 'multi_tenant', 'name' => 'Multi-Tenant Support', 'description' => 'Support for multiple tenants'],
            ['code' => 'advanced_reporting', 'name' => 'Advanced Reporting', 'description' => 'Detailed analytics and reports'],
            ['code' => 'api_access', 'name' => 'API Access', 'description' => 'Access to REST API'],
            ['code' => 'priority_support', 'name' => 'Priority Support', 'description' => '24/7 priority customer support'],
            ['code' => 'custom_integrations', 'name' => 'Custom Integrations', 'description' => 'Custom API integrations'],
        ];

        foreach ($features as $feature) {
            Feature::firstOrCreate(['code' => $feature['code']], $feature);
        }

        // Create plans
        $plans = [
            [
                'name' => 'Starter Plan',
                'description' => 'Ideal for small businesses with multiple dukas',
                'price' => 10000,
                'billing_cycle' => 'monthly',
                'max_dukas' => 3,
                'max_products' => 500,
                'is_active' => true,
                'features' => [
                    'max_dukas' => '3',
                    'max_products' => '500',
                    'inventory_management' => 'true',
                    'stock_transfers' => 'true',
                    'activity_logging' => 'true',
                    'multi_tenant' => 'true',
                ]
            ],
            [
                'name' => 'Medium Plan',
                'description' => 'For growing businesses needing advanced features',
                'price' => 20000,
                'billing_cycle' => 'monthly',
                'max_dukas' => 1,
                'max_products' => 2000,
                'is_active' => true,
                'features' => [
                    'max_dukas' => '1',
                    'max_products' => '2000',
                    'inventory_management' => 'true',
                    'stock_transfers' => 'true',
                    'activity_logging' => 'true',
                    'multi_tenant' => 'true',
                    'advanced_reporting' => 'true',
                    'api_access' => 'true',
                ]
            ],
            [
                'name' => 'Professional Plan',
                'description' => 'Complete solution for professional retail operations',
                'price' => 30000,
                'billing_cycle' => 'monthly',
                'max_dukas' => 25,
                'max_products' => 10000,
                'is_active' => true,
                'features' => [
                    'max_dukas' => '25',
                    'max_products' => '10000',
                    'inventory_management' => 'true',
                    'stock_transfers' => 'true',
                    'activity_logging' => 'true',
                    'multi_tenant' => 'true',
                    'advanced_reporting' => 'true',
                    'api_access' => 'true',
                    'priority_support' => 'true',
                ]
            ],
        ];

        foreach ($plans as $planData) {
            $features = $planData['features'];
            unset($planData['features']);

            $plan = Plan::firstOrCreate(['name' => $planData['name']], $planData);

            $featureSync = [];
            foreach ($features as $featureCode => $value) {
                $feature = Feature::where('code', $featureCode)->first();
                if ($feature) {
                    $featureSync[$feature->id] = ['value' => $value];
                }
            }
            $plan->planFeatures()->sync($featureSync);
        }
    }
}
