<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AnnouncementPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::KEPALA_SEKOLAH->value,
            RoleEnum::WAKIL_KEPALA_SEKOLAH->value,
            RoleEnum::GURU->value,
            RoleEnum::WALI_KELAS->value,
            RoleEnum::SISWA->value,
            RoleEnum::ORANG_TUA->value,
        );
    }

    public function view(User $user, Announcement $announcement): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::KEPALA_SEKOLAH->value,
            RoleEnum::WAKIL_KEPALA_SEKOLAH->value,
            RoleEnum::GURU->value,
            RoleEnum::WALI_KELAS->value,
            RoleEnum::SISWA->value,
            RoleEnum::ORANG_TUA->value,
        );
    }

    public function create(User $user): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::KEPALA_SEKOLAH->value,
            RoleEnum::WAKIL_KEPALA_SEKOLAH->value,
        );
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::KEPALA_SEKOLAH->value,
            RoleEnum::WAKIL_KEPALA_SEKOLAH->value,
        );
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::KEPALA_SEKOLAH->value,
            RoleEnum::WAKIL_KEPALA_SEKOLAH->value,
        );
    }
}
