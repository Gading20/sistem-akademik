<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeacherPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(RoleEnum::SUPER_ADMIN->value)
            || $user->hasRole(RoleEnum::ADMIN_SEKOLAH->value)
            || $user->hasRole(RoleEnum::KEPALA_SEKOLAH->value)
            || $user->hasRole(RoleEnum::WAKIL_KEPALA_SEKOLAH->value);
    }

    public function view(User $user, Teacher $teacher): bool
    {
        if ($user->hasRole(RoleEnum::SUPER_ADMIN->value)
            || $user->hasRole(RoleEnum::ADMIN_SEKOLAH->value)
            || $user->hasRole(RoleEnum::KEPALA_SEKOLAH->value)
            || $user->hasRole(RoleEnum::WAKIL_KEPALA_SEKOLAH->value)) {
            return true;
        }

        if ($user->hasRole(RoleEnum::GURU->value) || $user->hasRole(RoleEnum::WALI_KELAS->value)) {
            return $user->teacher?->id === $teacher->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(RoleEnum::SUPER_ADMIN->value)
            || $user->hasRole(RoleEnum::ADMIN_SEKOLAH->value);
    }

    public function update(User $user, Teacher $teacher): bool
    {
        if ($user->hasRole(RoleEnum::SUPER_ADMIN->value)
            || $user->hasRole(RoleEnum::ADMIN_SEKOLAH->value)) {
            return true;
        }

        if ($user->hasRole(RoleEnum::GURU->value) || $user->hasRole(RoleEnum::WALI_KELAS->value)) {
            return $user->teacher?->id === $teacher->id;
        }

        return false;
    }

    public function delete(User $user, Teacher $teacher): bool
    {
        return $user->hasRole(RoleEnum::SUPER_ADMIN->value)
            || $user->hasRole(RoleEnum::ADMIN_SEKOLAH->value);
    }
}
