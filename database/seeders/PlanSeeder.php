<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('subscription_plans')->insert([
            [
                'name' => 'Básico',
                'slug' => 'basic',
                'price' => 5.00,
                'max_scans_per_month' => 4,
                'features' => json_encode([
                    'scan_security' => true,
                    'report_basic' => true,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Médio',
                'slug' => 'medium',
                'price' => 15.00,
                'max_scans_per_month' => 10,
                'features' => json_encode([
                    'scan_security' => true,
                    'scan_dependencies' => true,
                    'report_detailed' => true,
                    'email_notifications' => true,
                    'slack_notifications' => true,
                    'api_access' => true,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'price' => 45.00,
                'max_scans_per_month' => 20,
                'features' => json_encode([
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
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
