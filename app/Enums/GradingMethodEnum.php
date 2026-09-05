<?php

namespace App\Enums;

enum GradingMethodEnum: string
{
    case AUTOMATIC = 'automatic';
    case MANUAL = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::AUTOMATIC => 'Otomatis',
            self::MANUAL => 'Manual',
        };
    }
}
