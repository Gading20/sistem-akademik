<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClassRoomPolicy
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
        );
    }

    public function view(User $user, ClassRoom $classRoom): bool
    {
        if ($user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::KEPALA_SEKOLAH->value,
            RoleEnum::WAKIL_KEPALA_SEKOLAH->value,
        )) {
            return true;
        }

        if ($user->hasRole(RoleEnum::GURU->value, RoleEnum::WALI_KELAS->value)) {
            return $user->teacher?->teachingAssignments()
                ->where('class_id', $classRoom->id)
                ->exists()
                || $classRoom->wali_kelas_id === $user->teacher?->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        );
    }

    public function update(User $user, ClassRoom $classRoom): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        );
    }

    public function delete(User $user, ClassRoom $classRoom): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        );
    }
}
