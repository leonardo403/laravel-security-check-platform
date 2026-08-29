<?php

namespace Database\Seeders;

use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedAdminUser();

        //User::factory()->create([
        //    'name' => 'Test User',
        //    'email' => 'test@example.com',
        //]);

        $this->call(PlanSeeder::class);

        PlatformSetting::set(PlatformSetting::KEY_PLATFORM_NAME, __('common.app_name'));
        PlatformSetting::set(PlatformSetting::KEY_SUPPORT_EMAIL, 'support@example.com');
        PlatformSetting::set(PlatformSetting::KEY_MAINTENANCE_MODE, false);
    }

    private function seedAdminUser(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD');

        if ($password === null) {
            $password = Str::random(16);
            $this->command?->warn("ADMIN_PASSWORD not defined in .env. Generated admin password: {$password}");
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Super Admin'),
                'password' => Hash::make($password),
                'is_admin' => true,
            ],
        );

        $this->command?->info("Super admin ready: {$email}");
    }
}
