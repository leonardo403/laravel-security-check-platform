<?php
namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class PlansController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::where('is_active', true)->get();
        return view('plans.index', compact('plans'));
    }

    public function show(SubscriptionPlan $plan)
    {
        return view('plans.show', compact('plan'));
    }

    public function subscribe(Request $request, SubscriptionPlan $plan)
    {
        $user = $request->user();

        $user->subscription()->updateOrCreate(
            ['user_id' => $user->id],
            ['subscription_plan_id' => $plan->id],
        );

        return redirect()
            ->route('dashboard')
            ->with('success', "Assinatura do plano {$plan->name} realizada com sucesso!");
    }
}
