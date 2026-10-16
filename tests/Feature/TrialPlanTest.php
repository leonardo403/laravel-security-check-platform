<?php

namespace Tests\Feature;

use App\Jobs\ProcessScan;
use App\Models\Scan;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TrialPlanTest extends TestCase
{
    use RefreshDatabase;

    private function createPlan(string $slug, array $overrides = []): SubscriptionPlan
    {
        return SubscriptionPlan::create(array_merge([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'price' => 19.90,
            'max_scans_per_month' => 5,
            'features' => ['scan_security' => true],
            'is_active' => true,
        ], $overrides));
    }

    private function createTrialPlan(): SubscriptionPlan
    {
        return $this->createPlan('trial', [
            'name' => 'Teste Grátis',
            'price' => 0,
            'max_scans_per_month' => 1,
            'is_trial' => true,
            'features' => [
                'scan_security' => true,
                'report_basic' => true,
            ],
        ]);
    }

    public function test_trial_plan_only_enables_basic_report_and_security_module(): void
    {
        $plan = $this->createTrialPlan();

        $this->assertTrue($plan->isTrial());

        $modules = array_map(fn ($m) => $m->value, $plan->scanModules());

        $this->assertSame(['security'], $modules);
        $this->assertTrue($plan->features['report_basic']);
    }

    public function test_user_can_start_trial_once(): void
    {
        $this->createTrialPlan();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('trial.start'));

        $response->assertRedirect(route('scans.create'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'status' => Subscription::STATUS_ACTIVE,
        ]);
        $this->assertNull($user->fresh()->trial_used_at);
    }

    public function test_user_cannot_start_trial_twice(): void
    {
        $this->createTrialPlan();
        $user = User::factory()->create();
        $user->update(['trial_used_at' => now()]);

        $response = $this->actingAs($user)->get(route('trial.start'));

        $response->assertRedirect(route('plans.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_user_with_active_paid_plan_cannot_start_trial(): void
    {
        $this->createTrialPlan();
        $paid = $this->createPlan('basic');
        $user = User::factory()->create();

        $user->subscription()->create([
            'subscription_plan_id' => $paid->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
        ]);

        $response = $this->actingAs($user)->get(route('trial.start'));

        $response->assertRedirect(route('scans.create'));
        $response->assertSessionHas('info');
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'subscription_plan_id' => $paid->id,
        ]);
    }

    public function test_checkout_of_trial_plan_redirects_to_trial_start(): void
    {
        $trial = $this->createTrialPlan();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('plans.checkout', $trial));

        $response->assertRedirect(route('trial.start'));
    }

    public function test_first_scan_consumes_the_trial(): void
    {
        Queue::fake();

        $this->createTrialPlan();
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('trial.start'));

        $response = $this->withoutMiddleware(PreventRequestForgery::class)
            ->actingAs($user->fresh())
            ->post(route('scans.store'), [
                'repository_url' => 'https://github.com/user/repo.git',
            ]);

        $response->assertRedirect();

        $this->assertSame(1, Scan::where('user_id', $user->id)->count());
        Queue::assertPushedOn('scans', ProcessScan::class);

        $user = $user->fresh();

        $this->assertNotNull($user->trial_used_at);
        $this->assertFalse($user->hasActiveSubscription());
        $this->assertSame(Subscription::STATUS_EXPIRED, $user->subscription->status);
    }

    public function test_trial_user_cannot_scan_again_after_consuming(): void
    {
        Queue::fake();

        $this->createTrialPlan();
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('trial.start'));

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->actingAs($user->fresh())
            ->post(route('scans.store'), ['repository_url' => 'https://github.com/user/repo.git']);

        $response = $this->withoutMiddleware(PreventRequestForgery::class)
            ->actingAs($user->fresh())
            ->post(route('scans.store'), ['repository_url' => 'https://github.com/user/repo2.git']);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertSame(1, Scan::where('user_id', $user->id)->count());
    }

    public function test_trial_plan_is_limited_to_one_scan(): void
    {
        $this->createTrialPlan();
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('trial.start'));

        $plan = $user->fresh()->activeSubscription()->plan;

        $this->assertSame(1, $plan->max_scans_per_month);
    }
}
