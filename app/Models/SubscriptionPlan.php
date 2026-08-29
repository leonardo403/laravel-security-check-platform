<?php

namespace App\Models;

use App\Services\Scanner\ScanModule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    public const FEATURES = [
        'scan_security',
        'scan_dependencies',
        'scan_secrets',
        'scan_code_quality',
        'report_basic',
        'report_detailed',
        'report_executive',
        'email_notifications',
        'slack_notifications',
        'all_notifications',
        'api_access',
        'priority_support',
        'ci_cd_integration',
    ];

    protected $fillable = [
        'name', 'slug', 'price', 'max_scans_per_month', 'features', 'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function scanModules(): array
    {
        $enabled = array_values(array_filter(
            ScanModule::cases(),
            fn (ScanModule $module) => (bool) ($this->features[$module->featureKey()] ?? false)
        ));

        if ($enabled === []) {
            return [ScanModule::Security];
        }

        return $enabled;
    }

    public function hasScanModule(ScanModule $module): bool
    {
        return in_array($module, $this->scanModules(), true);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'subscription_plan_id');
    }
}
