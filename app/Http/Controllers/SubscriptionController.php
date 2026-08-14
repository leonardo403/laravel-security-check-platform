<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\Payment\StripePaymentService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function confirm(Request $request, StripePaymentService $stripe)
    {
        $paymentIntent = $request->query('payment_intent');

        if (! $paymentIntent) {
            return redirect()->route('plans.index')->with('error', __('plans.payment_not_found'));
        }

        $user = $request->user();
        $subscription = $user->subscription;

        if (! $subscription || $subscription->stripe_payment_intent_id !== $paymentIntent) {
            return redirect()->route('plans.index')->with('error', __('plans.payment_not_located'));
        }

        if (! $stripe->isPaymentIntentSucceeded($paymentIntent)) {
            return redirect()->route('plans.index')->with('error', __('plans.payment_not_confirmed'));
        }

        $subscription->update([
            'status' => Subscription::STATUS_ACTIVE,
            'stripe_status' => 'succeeded',
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
        ]);

        return redirect()->route('plans.index')->with('success', __('plans.subscription_activated'));
    }
}
