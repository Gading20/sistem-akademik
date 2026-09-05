<?php

namespace App\Enums;

enum SemesterEnum: string
{
    case GANJIL = 'ganjil';
    case GENAP = 'genap';

    public function label(): string
    {
        return match ($this) {
            self::GANJIL => 'Ganjil',
            self::GENAP => 'Genap',
        };
    }
}
