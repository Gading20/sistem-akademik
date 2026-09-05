<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentPolicy
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

    public function view(User $user, Student $student): bool
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
                ->where('class_id', $student->class_id)
                ->exists();
        }

        if ($user->hasRole(RoleEnum::SISWA->value)) {
            return $user->student?->id === $student->id;
        }

        if ($user->hasRole(RoleEnum::ORANG_TUA->value)) {
            return $user->parentProfile?->id === $student->parent_id;
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

    public function update(User $user, Student $student): bool
    {
        if ($user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        )) {
            return true;
        }

        if ($user->hasRole(RoleEnum::WALI_KELAS->value)) {
            return $user->teacher?->teachingAssignments()
                ->where('class_id', $student->class_id)
                ->exists();
        }

        if ($user->hasRole(RoleEnum::SISWA->value)) {
            return $user->student?->id === $student->id;
        }

        return false;
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        );
    }
}
