<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
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

    public function subscribe(Request $request, SubscriptionPlan $plan)
    {
        if (! $plan->isActive()) {
            return back()->with('error', 'Este plano não está mais disponível.');
        }

        $user = $request->user();
        $subscription = $user->subscription;

        if ($subscription && $subscription->subscription_plan_id === $plan->id && $subscription->isActive()) {
            return back()->with('info', "Você já está assinando o plano {$plan->name}.");
        }

        $data = [
            'subscription_plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
        ];

        if ($subscription) {
            $subscription->update($data);
        } else {
            $user->subscription()->create($data);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', "Plano {$plan->name} assinado com sucesso!");
    }
}
