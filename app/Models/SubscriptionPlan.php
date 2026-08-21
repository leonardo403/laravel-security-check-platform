<?php

namespace App\Models;

use App\Services\Scanner\ScanModule;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
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
}
