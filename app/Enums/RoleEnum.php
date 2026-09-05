<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN_SEKOLAH = 'admin_sekolah';
    case KEPALA_SEKOLAH = 'kepala_sekolah';
    case WAKIL_KEPALA_SEKOLAH = 'wakil_kepala_sekolah';
    case GURU = 'guru';
    case WALI_KELAS = 'wali_kelas';
    case SISWA = 'siswa';
    case ORANG_TUA = 'orang_tua';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN_SEKOLAH => 'Admin Sekolah',
            self::KEPALA_SEKOLAH => 'Kepala Sekolah',
            self::WAKIL_KEPALA_SEKOLAH => 'Wakil Kepala Sekolah',
            self::GURU => 'Guru',
            self::WALI_KELAS => 'Wali Kelas',
            self::SISWA => 'Siswa',
            self::ORANG_TUA => 'Orang Tua / Wali',
        };
    }
}
