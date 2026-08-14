<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\Payment\StripePaymentService;
use Illuminate\Http\Request;

class PlansController extends Controller
{
    public function index(Request $request)
    {
        $plans = SubscriptionPlan::where('is_active', true)->get();

        $subscription = $request->user()->subscription;
        $currentPlan = $subscription?->isActive() ? $subscription->plan : null;
        $expiredPlan = $subscription && $subscription->isExpired() ? $subscription->plan : null;

        return view('plans.index', compact('plans', 'currentPlan', 'expiredPlan'));
    }

    public function show(SubscriptionPlan $plan)
    {
        return view('plans.show', compact('plan'));
    }

    public function checkout(Request $request, SubscriptionPlan $plan, StripePaymentService $stripe)
    {
        if (! $plan->isActive()) {
            return redirect()->route('plans.index')->with('error', __('plans.plan_unavailable'));
        }

        $user = $request->user();

        if ($user->activeSubscription()?->subscription_plan_id === $plan->id) {
            return redirect()->route('plans.index')->with('info', __('plans.already_subscribed', ['plan' => $plan->name]));
        }

        if (! $stripe->isConfigured()) {
            return back()->with('error', __('plans.stripe_not_configured'));
        }

        $intent = $stripe->createPaymentIntent($plan, $user);

        $subscription = $user->subscription;

        $data = [
            'subscription_plan_id' => $plan->id,
            'status' => Subscription::STATUS_PENDING,
            'stripe_payment_intent_id' => $intent['id'],
            'stripe_status' => 'pending',
        ];

        if ($subscription) {
            $subscription->update($data);
        } else {
            $user->subscription()->create($data);
        }

        return view('plans.checkout', [
            'plan' => $plan,
            'amount' => $stripe->amountInCents($plan),
            'clientSecret' => $intent['client_secret'],
            'stripeKey' => $stripe->publishableKey(),
        ]);
    }
}
