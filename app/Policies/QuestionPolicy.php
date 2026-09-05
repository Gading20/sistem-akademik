<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::GURU->value,
        );
    }

    public function view(User $user, Question $question): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::GURU->value,
        );
    }

    public function create(User $user): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::GURU->value,
        );
    }

    public function update(User $user, Question $question): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::GURU->value,
        );
    }

    public function delete(User $user, Question $question): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::GURU->value,
        );
    }
}
