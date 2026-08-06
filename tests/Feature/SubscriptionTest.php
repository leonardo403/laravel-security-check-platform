<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Payment\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private $stripe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->stripe = $this->mock(StripePaymentService::class);
    }

    public function test_checkout_creates_pending_subscription_with_stripe_intent(): void
    {
        $plan = $this->createPlan('basic');

        $this->stripe->shouldReceive('isConfigured')->andReturn(true);

        $this->stripe->shouldReceive('createPaymentIntent')->once()->andReturn([
            'id' => 'pi_test_123',
            'client_secret' => 'cs_test_123',
        ]);
        $this->stripe->shouldReceive('amountInCents')->andReturn(1990);
        $this->stripe->shouldReceive('publishableKey')->andReturn('pk_test_123');

        $response = $this->actingAs($this->freshUser())
            ->get(route('plans.checkout', $plan));

        $response->assertOk();
        $response->assertViewHas('clientSecret', 'cs_test_123');
        $response->assertViewHas('stripeKey', 'pk_test_123');

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->user->id,
            'subscription_plan_id' => $plan->id,
            'status' => Subscription::STATUS_PENDING,
            'stripe_payment_intent_id' => 'pi_test_123',
        ]);
    }

    public function test_confirm_activates_subscription_when_payment_succeeded(): void
    {
        $plan = $this->createPlan('basic');
        $this->user->subscription()->create([
            'subscription_plan_id' => $plan->id,
            'status' => Subscription::STATUS_PENDING,
            'stripe_payment_intent_id' => 'pi_test_123',
            'stripe_status' => 'pending',
        ]);

        $this->stripe->shouldReceive('isPaymentIntentSucceeded')->with('pi_test_123')->andReturn(true);

        $response = $this->actingAs($this->freshUser())
            ->get(route('subscription.confirm', ['payment_intent' => 'pi_test_123']));

        $response->assertRedirect(route('plans.index'));
        $response->assertSessionHas('success');

        $subscription = $this->freshUser()->subscription;
        $this->assertSame(Subscription::STATUS_ACTIVE, $subscription->status);
        $this->assertSame('succeeded', $subscription->stripe_status);
        $this->assertNotNull($subscription->starts_at);
        $this->assertTrue($subscription->expires_at->gt(Carbon::now()->addDays(29)));
        $this->assertTrue($this->freshUser()->hasActiveSubscription());
    }

    public function test_confirm_does_not_activate_when_payment_failed(): void
    {
        $plan = $this->createPlan('basic');
        $this->user->subscription()->create([
            'subscription_plan_id' => $plan->id,
            'status' => Subscription::STATUS_PENDING,
            'stripe_payment_intent_id' => 'pi_test_123',
            'stripe_status' => 'pending',
        ]);

        $this->stripe->shouldReceive('isPaymentIntentSucceeded')->with('pi_test_123')->andReturn(false);

        $response = $this->actingAs($this->freshUser())
            ->get(route('subscription.confirm', ['payment_intent' => 'pi_test_123']));

        $response->assertRedirect(route('plans.index'));
        $response->assertSessionHas('error');

        $this->assertSame(Subscription::STATUS_PENDING, $this->freshUser()->subscription->status);
        $this->assertFalse($this->freshUser()->hasActiveSubscription());
    }

    public function test_confirm_with_unknown_payment_intent_redirects_with_error(): void
    {
        $plan = $this->createPlan('basic');
        $this->user->subscription()->create([
            'subscription_plan_id' => $plan->id,
            'status' => Subscription::STATUS_PENDING,
            'stripe_payment_intent_id' => 'pi_test_123',
            'stripe_status' => 'pending',
        ]);

        $response = $this->actingAs($this->freshUser())
            ->get(route('subscription.confirm', ['payment_intent' => 'pi_other']));

        $response->assertRedirect(route('plans.index'));
        $response->assertSessionHas('error');
        $this->assertSame(Subscription::STATUS_PENDING, $this->freshUser()->subscription->status);
    }

    public function test_checkout_to_same_active_plan_redirects_with_info(): void
    {
        $this->createPlan('basic');
        $plan = $this->createPlan('premium');
        $this->user->subscription()->create([
            'subscription_plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
        ]);

        $response = $this->actingAs($this->freshUser())
            ->get(route('plans.checkout', $plan));

        $response->assertRedirect(route('plans.index'));
        $response->assertSessionHas('info');
        $this->assertDatabaseCount('subscriptions', 1);
    }

    public function test_cannot_checkout_to_an_inactive_plan(): void
    {
        $plan = $this->createPlan('basic', false);

        $response = $this->actingAs($this->freshUser())
            ->get(route('plans.checkout', $plan));

        $response->assertRedirect(route('plans.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_checkout_redirects_when_stripe_is_not_configured(): void
    {
        $this->stripe->shouldReceive('isConfigured')->andReturn(false);

        $plan = $this->createPlan('basic');

        $response = $this->actingAs($this->freshUser())
            ->get(route('plans.checkout', $plan));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_renewing_an_expired_subscription_creates_pending_renewal(): void
    {
        $plan = $this->createPlan('basic');
        $this->user->subscription()->create([
            'subscription_plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now()->subMonths(2),
            'expires_at' => now()->subDay(),
        ]);

        $this->stripe->shouldReceive('isConfigured')->andReturn(true);
        $this->stripe->shouldReceive('createPaymentIntent')->once()->andReturn([
            'id' => 'pi_test_123',
            'client_secret' => 'cs_test_123',
        ]);
        $this->stripe->shouldReceive('amountInCents')->andReturn(1990);
        $this->stripe->shouldReceive('publishableKey')->andReturn('pk_test_123');

        $response = $this->actingAs($this->freshUser())
            ->get(route('plans.checkout', $plan));

        $response->assertOk();
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->user->id,
            'subscription_plan_id' => $plan->id,
            'status' => Subscription::STATUS_PENDING,
            'stripe_payment_intent_id' => 'pi_test_123',
        ]);
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
