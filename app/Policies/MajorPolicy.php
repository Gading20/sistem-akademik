<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Major;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MajorPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::KEPALA_SEKOLAH->value,
            RoleEnum::WAKIL_KEPALA_SEKOLAH->value,
        );
    }

    public function view(User $user, Major $major): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::KEPALA_SEKOLAH->value,
            RoleEnum::WAKIL_KEPALA_SEKOLAH->value,
        );
    }

    public function create(User $user): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        );
    }

    public function update(User $user, Major $major): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        );
    }

    public function delete(User $user, Major $major): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        );
    }
}
