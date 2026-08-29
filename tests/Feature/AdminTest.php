<?php

namespace Tests\Feature;

use App\Models\PlatformSetting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->user = User::factory()->create();

        $this->seed(PlanSeeder::class);
    }

    public function test_non_admin_cannot_access_admin_settings(): void
    {
        $this->actingAs($this->user)
            ->get(route('admin.settings'))
            ->assertForbidden();
    }

    public function test_guest_cannot_access_admin_settings(): void
    {
        $this->get(route('admin.settings'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_settings_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee(__('admin.settings_title'));
    }

    public function test_admin_can_update_settings(): void
    {
        PlatformSetting::set(PlatformSetting::KEY_MAINTENANCE_MODE, false);

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->actingAs($this->admin)
            ->post(route('admin.settings.update'), [
                'platform_name' => 'SecureOps',
                'support_email' => 'help@secureops.test',
                'maintenance_mode' => true,
            ])
            ->assertRedirect(route('admin.settings'));

        $this->assertSame('SecureOps', PlatformSetting::get(PlatformSetting::KEY_PLATFORM_NAME));
        $this->assertSame('help@secureops.test', PlatformSetting::get(PlatformSetting::KEY_SUPPORT_EMAIL));
        $this->assertTrue(PlatformSetting::getBool(PlatformSetting::KEY_MAINTENANCE_MODE));
    }

    public function test_admin_can_view_plans_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.plans'))
            ->assertOk()
            ->assertSee(__('admin.plans_title'));
    }

    public function test_admin_can_update_plan(): void
    {
        $plan = SubscriptionPlan::where('slug', 'basic')->firstOrFail();

        $features = array_fill_keys(SubscriptionPlan::FEATURES, false);
        $features['scan_security'] = true;
        $features['scan_dependencies'] = true;

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->actingAs($this->admin)
            ->post(route('admin.plans.update', $plan), [
                'name' => 'Básico Pro',
                'price' => 9.99,
                'max_scans_per_month' => 12,
                'is_active' => true,
                ...collect(SubscriptionPlan::FEATURES)->mapWithKeys(fn ($f) => ["feature_{$f}" => $features[$f] ? '1' : '0']),
            ])
            ->assertRedirect(route('admin.plans'));

        $plan->refresh();

        $this->assertSame('Básico Pro', $plan->name);
        $this->assertSame('9.99', $plan->price);
        $this->assertSame(12, $plan->max_scans_per_month);
        $this->assertTrue($plan->features['scan_security']);
        $this->assertTrue($plan->features['scan_dependencies']);
        $this->assertFalse($plan->features['email_notifications']);
    }

    public function test_non_admin_cannot_update_plan(): void
    {
        $plan = SubscriptionPlan::where('slug', 'basic')->firstOrFail();
        $originalPrice = $plan->price;

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->actingAs($this->user)
            ->post(route('admin.plans.update', $plan), [
                'name' => 'Hacked',
                'price' => 0.01,
                'max_scans_per_month' => 999,
            ])
            ->assertForbidden();

        $this->assertSame($originalPrice, $plan->refresh()->price);
    }

    public function test_maintenance_mode_blocks_regular_users_but_allows_admins(): void
    {
        PlatformSetting::set(PlatformSetting::KEY_MAINTENANCE_MODE, true);

        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertStatus(503);

        $this->get(route('login'))
            ->assertStatus(503);

        $this->actingAs($this->admin)
            ->get(route('admin.settings'))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_user_model_exposes_is_admin_helper(): void
    {
        $this->assertTrue($this->admin->isAdmin());
        $this->assertFalse($this->user->isAdmin());
    }

    public function test_database_seeder_creates_admin_and_default_settings(): void
    {
        $this->seed();

        $admin = User::where('email', env('ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();
        $this->assertTrue($admin->isAdmin());

        $this->assertSame('Security Platform', PlatformSetting::get(PlatformSetting::KEY_PLATFORM_NAME));
        $this->assertFalse(PlatformSetting::getBool(PlatformSetting::KEY_MAINTENANCE_MODE));
        $this->assertDatabaseHas('subscription_plans', ['slug' => 'basic']);
    }
}
