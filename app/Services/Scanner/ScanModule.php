<?php

namespace App\Services\Scanner;

enum ScanModule: string
{
    case Security = 'security';

    case Dependencies = 'dependencies';

    case Secrets = 'secrets';

    case CodeQuality = 'code_quality';

    public function featureKey(): string
    {
        return 'scan_'.$this->value;
    }

    public static function values(): array
    {
        return array_map(fn (self $module) => $module->value, self::cases());
    }
}
