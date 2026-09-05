<?php

namespace App\Enums;

enum StudentStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case GRADUATED = 'graduated';
    case TRANSFERRED = 'transferred';
    case DROPPED = 'dropped';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::INACTIVE => 'Non Aktif',
            self::GRADUATED => 'Lulus',
            self::TRANSFERRED => 'Pindah',
            self::DROPPED => 'Keluar',
        };
    }
}
