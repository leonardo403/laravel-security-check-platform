<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Básico',
                'slug' => 'basic',
                'price' => 5.00,
                'max_scans_per_month' => 4,
                'features' => [
                    'scan_security' => true,
                    'report_basic' => true,
                ],
            ],
            [
                'name' => 'Médio',
                'slug' => 'medium',
                'price' => 15.00,
                'max_scans_per_month' => 10,
                'features' => [
                    'scan_security' => true,
                    'scan_dependencies' => true,
                    'report_detailed' => true,
                    'email_notifications' => true,
                    'slack_notifications' => true,
                    'api_access' => true,
                ],
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'price' => 45.00,
                'max_scans_per_month' => 20,
                'features' => [
                    'scan_security' => true,
                    'scan_dependencies' => true,
                    'scan_secrets' => true,
                    'scan_code_quality' => true,
                    'report_detailed' => true,
                    'report_executive' => true,
                    'all_notifications' => true,
                    'api_access' => true,
                    'priority_support' => true,
                    'ci_cd_integration' => true,
                ],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
