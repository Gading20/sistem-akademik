<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Assignment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssignmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::GURU->value,
            RoleEnum::WALI_KELAS->value,
            RoleEnum::SISWA->value,
        );
    }

    public function view(User $user, Assignment $assignment): bool
    {
        if ($user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        )) {
            return true;
        }

        if ($user->hasRole(RoleEnum::GURU->value, RoleEnum::WALI_KELAS->value)) {
            return $user->teacher?->teachingAssignments()
                ->where('subject_id', $assignment->subject_id)
                ->where('class_id', $assignment->class_id)
                ->exists();
        }

        if ($user->hasRole(RoleEnum::SISWA->value)) {
            return $user->student?->classMembers()
                ->where('class_id', $assignment->class_id)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::GURU->value,
            RoleEnum::WALI_KELAS->value,
        );
    }

    public function update(User $user, Assignment $assignment): bool
    {
        if ($user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        )) {
            return true;
        }

        if ($user->hasRole(RoleEnum::GURU->value, RoleEnum::WALI_KELAS->value)) {
            return $assignment->teacher_id === $user->teacher?->id;
        }

        return false;
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        if ($user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        )) {
            return true;
        }

        if ($user->hasRole(RoleEnum::GURU->value)) {
            return $assignment->teacher_id === $user->teacher?->id;
        }

        return false;
    }
}
