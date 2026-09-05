<?php

namespace App\Enums;

enum AttendanceStatusEnum: string
{
    case HADIR = 'hadir';
    case SAKIT = 'sakit';
    case IZIN = 'izin';
    case ALPA = 'alpa';
    case TERLAMBAT = 'terlambat';

    public function label(): string
    {
        return match ($this) {
            self::HADIR => 'Hadir',
            self::SAKIT => 'Sakit',
            self::IZIN => 'Izin',
            self::ALPA => 'Alpa',
            self::TERLAMBAT => 'Terlambat',
        };
    }
}
