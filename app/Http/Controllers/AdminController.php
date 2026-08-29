<?php

namespace App\Http\Controllers;

use App\Models\PlatformSetting;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.settings');
    }

    public function settings()
    {
        return view('admin.settings', [
            'platformName' => PlatformSetting::get(PlatformSetting::KEY_PLATFORM_NAME),
            'supportEmail' => PlatformSetting::get(PlatformSetting::KEY_SUPPORT_EMAIL),
            'maintenanceMode' => PlatformSetting::getBool(PlatformSetting::KEY_MAINTENANCE_MODE),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'platform_name' => ['required', 'string', 'max:80'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'maintenance_mode' => ['sometimes', 'boolean'],
        ]);

        PlatformSetting::set(PlatformSetting::KEY_PLATFORM_NAME, trim($data['platform_name']));
        PlatformSetting::set(PlatformSetting::KEY_SUPPORT_EMAIL, $data['support_email'] ? trim($data['support_email']) : null);
        PlatformSetting::set(PlatformSetting::KEY_MAINTENANCE_MODE, $request->boolean('maintenance_mode'));

        return redirect()->route('admin.settings')->with('success', __('admin.settings_updated'));
    }

    public function plans()
    {
        $plans = SubscriptionPlan::orderBy('price')->withCount('subscriptions')->get();

        return view('admin.plans', compact('plans'));
    }

    public function updatePlan(Request $request, SubscriptionPlan $plan)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999'],
            'max_scans_per_month' => ['required', 'integer', 'min:1', 'max:100000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $features = [];

        foreach (SubscriptionPlan::FEATURES as $feature) {
            $features[$feature] = $request->boolean("feature_{$feature}");
        }

        $plan->update([
            'name' => $data['name'],
            'price' => $data['price'],
            'max_scans_per_month' => $data['max_scans_per_month'],
            'is_active' => $request->boolean('is_active'),
            'features' => $features,
        ]);

        return redirect()->route('admin.plans')->with('success', __('admin.plan_updated', ['plan' => $plan->name]));
    }
}
