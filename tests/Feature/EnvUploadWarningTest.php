<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EnvUploadWarningTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $plan = SubscriptionPlan::create([
            'name' => 'Basic',
            'slug' => 'basic',
            'price' => 19.90,
            'max_scans_per_month' => 10,
            'features' => ['scan_security' => true],
            'is_active' => true,
        ]);

        $this->user = User::factory()->create();
        $this->user->subscription()->create([
            'subscription_plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
        ]);
    }

    public function test_uploading_env_with_keys_shows_security_warning(): void
    {
        $envFile = UploadedFile::fake()->createWithContent('secrets.txt', "APP_KEY=base64:abc123\nDB_PASSWORD=secret\n");

        $response = $this->withoutMiddleware(PreventRequestForgery::class)
            ->actingAs($this->user)
            ->post(route('scans.store'), ['env_file' => $envFile]);

        $response->assertSessionHas('warning');
        $this->assertDatabaseHas('scans', [
            'user_id' => $this->user->id,
            'scan_type' => 'env',
        ]);
    }

    public function test_uploading_env_without_keys_does_not_show_warning(): void
    {
        $envFile = UploadedFile::fake()->createWithContent('plain.txt', "# somente comentario\n\n# outro comentario\n");

        $response = $this->withoutMiddleware(PreventRequestForgery::class)
            ->actingAs($this->user)
            ->post(route('scans.store'), ['env_file' => $envFile]);

        $response->assertSessionMissing('warning');
        $this->assertDatabaseHas('scans', [
            'user_id' => $this->user->id,
            'scan_type' => 'env',
        ]);
    }

    public function test_uploading_env_with_only_empty_values_does_not_show_warning(): void
    {
        $envFile = UploadedFile::fake()->createWithContent('empty.txt', "APP_KEY=\nDB_PASSWORD=\n");

        $response = $this->withoutMiddleware(PreventRequestForgery::class)
            ->actingAs($this->user)
            ->post(route('scans.store'), ['env_file' => $envFile]);

        $response->assertSessionMissing('warning');
        $this->assertDatabaseHas('scans', [
            'user_id' => $this->user->id,
            'scan_type' => 'env',
        ]);
    }
}
