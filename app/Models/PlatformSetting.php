<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

#[Fillable(['key', 'value'])]
class PlatformSetting extends Model
{
    public const KEY_PLATFORM_NAME = 'platform_name';

    public const KEY_SUPPORT_EMAIL = 'support_email';

    public const KEY_MAINTENANCE_MODE = 'maintenance_mode';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    public static function get(string $key, mixed $default = null): mixed
    {
        if (! Schema::hasTable((new static)->getTable())) {
            return $default;
        }

        $setting = static::query()->find($key);

        return $setting?->value ?? $default;
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = static::get($key);

        if ($value === null) {
            return $default;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'on', 'yes'], true);
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => is_bool($value) ? ($value ? '1' : '0') : $value]);
    }

    public static function platformName(): string
    {
        return (string) static::get(self::KEY_PLATFORM_NAME, __('common.app_name'));
    }
}
