<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_user_can_subscribe_to_an_active_plan(): void
    {
        $plan = $this->createPlan('basic');

        $response = $this->actingAs($this->freshUser())
            ->post(route('plans.subscribe', $plan));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->user->id,
            'subscription_plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
        ]);

        $subscription = $this->freshUser()->subscription;
        $this->assertNotNull($subscription->starts_at);
        $this->assertTrue($subscription->expires_at->gt(Carbon::now()->addDays(29)));
        $this->assertTrue($this->freshUser()->hasActiveSubscription());
    }

    public function test_subscribing_again_to_same_active_plan_does_not_duplicate(): void
    {
        $plan = $this->createPlan('basic');
        $this->user->subscription()->create([
            'subscription_plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
        ]);

        $response = $this->actingAs($this->freshUser())
            ->post(route('plans.subscribe', $plan));

        $response->assertRedirect();
        $response->assertSessionHas('info');
        $this->assertDatabaseCount('subscriptions', 1);
    }

    public function test_subscribing_to_a_different_plan_switches_it(): void
    {
        $basic = $this->createPlan('basic');
        $premium = $this->createPlan('premium');

        $this->actingAs($this->freshUser())->post(route('plans.subscribe', $basic));
        $this->actingAs($this->freshUser())->post(route('plans.subscribe', $premium));

        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->user->id,
            'subscription_plan_id' => $premium->id,
        ]);
        $this->assertTrue($this->freshUser()->subscription->plan->is($premium));
    }

    public function test_cannot_subscribe_to_an_inactive_plan(): void
    {
        $plan = $this->createPlan('basic', false);

        $response = $this->actingAs($this->freshUser())
            ->post(route('plans.subscribe', $plan));

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_expired_subscription_does_not_grant_plan_access(): void
    {
        $plan = $this->createPlan('basic');
        $this->user->subscription()->create([
            'subscription_plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now()->subMonths(2),
            'expires_at' => now()->subDay(),
        ]);

        $this->assertNull($this->freshUser()->activeSubscription());

        $response = $this->actingAs($this->freshUser())->get(route('scans.create'));

        $response->assertViewHas('plan', null);
    }

    public function test_renewing_an_expired_subscription_reactivates_it(): void
    {
        $plan = $this->createPlan('basic');
        $this->user->subscription()->create([
            'subscription_plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now()->subMonths(2),
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->freshUser())
            ->post(route('plans.subscribe', $plan));

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseCount('subscriptions', 1);

        $subscription = $this->freshUser()->subscription;
        $this->assertSame(Subscription::STATUS_ACTIVE, $subscription->status);
        $this->assertTrue($subscription->expires_at->gt(Carbon::now()->addDays(29)));
        $this->assertTrue($subscription->isActive());
    }

    private function freshUser(): User
    {
        return User::findOrFail($this->user->id);
    }

    private function createPlan(string $slug = 'basic', bool $active = true): SubscriptionPlan
    {
        return SubscriptionPlan::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'price' => 19.90,
            'max_scans_per_month' => 5,
            'features' => ['scan_security' => true],
            'is_active' => $active,
        ]);
    }
}
